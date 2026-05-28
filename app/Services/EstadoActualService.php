<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class EstadoActualService
{
    // Controla si se filtran solo estaciones en emergencia (configurable por env).
    private const ENV_SOLO_EMERGENCIAS = 'SOLO_EMERGENCIAS';

    private static function soloEmergencias(): bool
    {
        return filter_var(env(self::ENV_SOLO_EMERGENCIAS, false), FILTER_VALIDATE_BOOLEAN);
    }

    public static function modoVisualizacionEsperado(): string
    {
        return self::soloEmergencias() ? 'solo_emergencias' : 'todas';
    }

    /**
     * * FUNCIÓN DE SINCRONIZACIÓN
     * * Descarga todo y lo guarda en la Caché
     */
    public function sincronizarCaches(bool $modoRapido = false): void
    {
        // * Coge todos los datos de la BBDD
        $estacionesConfiguradas = $this->obtenerEstacionesEstadoActual();

        // * Junta los datos que nos interesan de la BBDD (código, nombre, provincia, umbrales, comunidad), con los datos en tiempo real de la api (valor actual, fecha, tendencia, señal)
        $estadoActualCalculado = $this->construirEstadoActual($estacionesConfiguradas, $modoRapido);

        // * Comprueba el booleano de SOLO_EMERGENCIAS para mostrar todas las estaciones o las alertadas
        $soloEmergencias = self::soloEmergencias();
        $modoVisualizacion = self::modoVisualizacionEsperado();
        $estacionesParaMostrar = $soloEmergencias
            ? $estadoActualCalculado->filter(fn($estacion) => (int) ($estacion['alerta'] ?? 0) > 0)->values()
            : $estadoActualCalculado;

        // * Cuarda en la CACHÉ agrupadas por comunidad autónoma
        $estadoPorComunidad = $estacionesParaMostrar
            ->groupBy('comunidad')
            ->map(fn($grupoComunidad) => $grupoComunidad->sortByDesc('alerta')->values())
            ->sortKeys();

        $cacheTtl = now()->addMinutes($this->cacheTtlMinutes());
        Cache::put('api_estado_actual_global', $estadoPorComunidad, $cacheTtl);

        // * Guarda en CACÉ agrupando por ID de comunidad autónoma
        $estadoPorIdComunidad = $estacionesParaMostrar
            ->groupBy('comunidad_id')
            ->map(fn($grupoComunidad) => $grupoComunidad->sortByDesc('alerta')->values());

        $idsComunidades = DB::table('umbrales_ccaa')->pluck('c_id');

        // ? Aunque no haya estaciones activas, se guarda en un array vacío para asi evitar errores
        foreach ($idsComunidades as $idComunidad) {
            Cache::put(
                'api_estado_actual_ccaa_' . (int) $idComunidad,
                $estadoPorIdComunidad->get((int) $idComunidad, collect())->values(),
                $cacheTtl
            );
        }

        // * Almacenamos lafecha de la ultima sincronización para asi poder mostrarlo
        Cache::put('api_estado_actual_sync_at', now()->toDateTimeString(), $cacheTtl);
        Cache::put('api_estado_actual_modo', $modoVisualizacion, $cacheTtl);
    }

    /**
     * * EXTRACCIÓN DE BBDD
     * * Carga aforos y embalses activos junto con sus umbrales
     */
    public function obtenerEstacionesEstadoActual(?int $ccaaId = null)
    {
        // * Consulta para almacenar toda la información de aforos activos
        $consultaAforos = DB::table('umbrales_umbralesran')
            ->join('umbrales_ccaa', 'umbrales_umbralesran.ur_comunidad_autonoma_id', '=', 'umbrales_ccaa.c_id')
            ->select(
                DB::raw("'AFORO' as tipo"),
                'umbrales_umbralesran.ur_codigo as codigo',
                'umbrales_umbralesran.ur_nombre as nombre',
                'umbrales_umbralesran.ur_rio as rio',
                'umbrales_umbralesran.ur_provincia as provincia',
                'umbrales_umbralesran.ur_tag_ip21 as tag_ip21',
                'umbrales_umbralesran.ur_umbral1 as nivel1',
                'umbrales_umbralesran.ur_umbral2 as nivel2',
                'umbrales_umbralesran.ur_umbral3 as nivel3',
                'umbrales_ccaa.c_id as comunidad_id',
                'umbrales_ccaa.c_comunidad_autonoma as nombre_comunidad'
            )
            ->where('umbrales_umbralesran.ur_activo', 1);

        // * Consulta para almacenar toda la información de Embalses activos
        $consultaEmbalses = DB::table('umbrales_embalsesran')
            ->join('umbrales_ccaa', 'umbrales_embalsesran.er_comunidad_autonoma_id', '=', 'umbrales_ccaa.c_id')
            ->select(
                DB::raw("'EMBALSE' as tipo"),
                'umbrales_embalsesran.er_codigo as codigo',
                'umbrales_embalsesran.er_nombre as nombre',
                'umbrales_embalsesran.er_rio as rio',
                'umbrales_embalsesran.er_provincia as provincia',
                'umbrales_embalsesran.er_tag_ip21 as tag_ip21',
                'umbrales_embalsesran.er_umbral1 as nivel1',
                'umbrales_embalsesran.er_umbral2 as nivel2',
                'umbrales_embalsesran.er_umbral3 as nivel3',
                'umbrales_ccaa.c_id as comunidad_id',
                'umbrales_ccaa.c_comunidad_autonoma as nombre_comunidad'
            )
            ->where('umbrales_embalsesran.er_activo', 1);

        // ? Si el método recibe un ID, aplica el WHERE a ambas consultas
        if (! is_null($ccaaId)) {
            $consultaAforos->where('umbrales_umbralesran.ur_comunidad_autonoma_id', $ccaaId);
            $consultaEmbalses->where('umbrales_embalsesran.er_comunidad_autonoma_id', $ccaaId);
        }

        // * Junta ambas consultas para asi procesar todo junto
        return $consultaAforos->get()->merge($consultaEmbalses->get());
    }

    /**
     * * CONSTRUCTOR DEL ESTADO FINAL
     * * Une la base de datos con los resultados de la API
     */
    public function construirEstadoActual($datosEstaciones, bool $modoRapido = false)
    {
        // * Pide los datos reales a la API de SAIH
        $lecturasPorEstacion = $this->obtenerLecturasApiPorEstacion($datosEstaciones, $modoRapido);

        $estacionesCalculadas = collect();

        foreach ($datosEstaciones as $estacionBase) {
            $claveUnicaEstacion = $this->claveEstacion((string) $estacionBase->tipo, (string) $estacionBase->codigo);
            $codigoEstacion = (string) $estacionBase->codigo;

            // ? Si la API falló para esta estación, crea un array "vacío" de seguridad
            $lecturaEstacion = $lecturasPorEstacion[$claveUnicaEstacion] ?? [
                'valor' => null,
                'fecha' => 'Sin conexión',
                'json' => [],
                'tag' => trim((string) ($estacionBase->tag_ip21 ?? '')) !== ''
                    ? (string) $estacionBase->tag_ip21
                    : $codigoEstacion . 'LI__02',
                'tendencia' => '→',
            ];

            $valorLeido = $lecturaEstacion['valor'];
            $jsonLectura = $lecturaEstacion['json'];

            //* Ensamblamos el Array definitivo para guardar en Caché
            $estacionesCalculadas->push([
                'tipo' => $estacionBase->tipo,
                'codigo' => $estacionBase->codigo,
                'nombre' => $estacionBase->nombre,
                'rio' => $estacionBase->rio,
                'provincia' => $estacionBase->provincia,
                'estacion' => $jsonLectura['estacion'] ?? $estacionBase->codigo,
                'senal' => $jsonLectura['senal'] ?? $lecturaEstacion['tag'],
                'valor' => $valorLeido,
                'fecha' => $lecturaEstacion['fecha'],
                'tendencia' => $lecturaEstacion['tendencia'] ?? '→',
                'alerta' => $this->calcularNivelAlerta(
                    $valorLeido,
                    $estacionBase->nivel1,
                    $estacionBase->nivel2,
                    $estacionBase->nivel3
                ),
                'comunidad_id' => (int) $estacionBase->comunidad_id,
                'comunidad' => strtoupper((string) $estacionBase->nombre_comunidad),
                'nivel1' => (float) ($estacionBase->nivel1 ?? 0),
                'nivel2' => (float) ($estacionBase->nivel2 ?? 0),
                'nivel3' => (float) ($estacionBase->nivel3 ?? 0),
            ]);
        }

        return $estacionesCalculadas;
    }

    public function contarEstacionesActivas(): int
    {
        $aforos = DB::table('umbrales_umbralesran')->where('ur_activo', 1)->count();
        $embalses = DB::table('umbrales_embalsesran')->where('er_activo', 1)->count();

        return (int) $aforos + (int) $embalses;
    }


    //* CONEXIÓN A LA API
    private function obtenerLecturasApiPorEstacion($estaciones, bool $modoRapido = false): array
    {
        $estacionesColeccion = collect($estaciones)->values();

        if ($estacionesColeccion->isEmpty()) {
            return [];
        }

        $lecturasPorClave = [];

        foreach ($estacionesColeccion as $estacionBase) {
            $codigoEstacion = (string) $estacionBase->codigo;
            $tagConfigurado = strtoupper(trim((string) ($estacionBase->tag_ip21 ?? '')));
            $claveUnicaEstacion = $this->claveEstacion((string) $estacionBase->tipo, $codigoEstacion);
            $tipoEstacion = strtoupper((string) ($estacionBase->tipo ?? 'AFORO'));

            // ? Tag por defecto si la base de datos no tiene ninguno, se pone ese tag genérico
            $tagDefecto = $tagConfigurado !== '' ? $tagConfigurado : ($codigoEstacion . 'LI__02');

            $lecturaResuelta = [
                'valor' => null,
                'fecha' => 'Sin conexión',
                'json' => [],
                'tag' => $tagDefecto,
                'tendencia' => '→',
            ];

            $tags = $this->construirTagsCandidatos($tipoEstacion, $codigoEstacion, $tagConfigurado);

            foreach ($tags as $tag) {
                // * Se usa Http::retry para evitar bloqueos si la red tiene micro cortes
                $urlApiSaih = env('API_SAIH_URL', 'http://vcmas08:8001/tr/ultimo_valor_tag/');
                $respuesta = Http::retry($modoRapido ? 1 : 4, 300, null, false)
                    ->connectTimeout(1)
                    ->timeout($modoRapido ? 3 : 8)
                    ->get($urlApiSaih . '?tag=' . urlencode($tag));
                if (! ($respuesta instanceof Response) || ! $respuesta->ok()) {
                    continue; // ? Si la petición falla, pasa a la siguiente estación
                }

                $json = $respuesta->json();
                $valor = isset($json['valor']) && is_numeric($json['valor']) ? (float) $json['valor'] : null;

                // ? Si el valor es un número y no un null prueba con cada tag hasta que encuentra el correcto, si no enciuentra ninguno, valor null
                if ($valor !== null) {
                    $lecturaResuelta = [
                        'valor' => $valor,
                        'fecha' => $json['fecha'] ?? 'Sin conexión',
                        'json' => $json,
                        'tag' => $tag,
                        'tendencia' => $modoRapido ? '→' : $this->calcularTendencia($tag),
                    ];
                    break;
                }
            }

            $lecturasPorClave[$claveUnicaEstacion] = $lecturaResuelta;
        }

        return $lecturasPorClave;
    }

    private function cacheTtlMinutes(): int
    {
        $ttl = (int) env('ESTADO_ACTUAL_MAX_AGE_MIN', 6);

        return $ttl > 0 ? $ttl : 1;
    }

    private function claveEstacion(string $tipo, string $codigo): string
    {
        return strtoupper($tipo) . '|' . strtoupper($codigo);
    }

    private function construirTagsCandidatos(string $tipoEstacion, string $codigoEstacion, string $tagConfigurado): array
    {
        $codigo = strtoupper(trim($codigoEstacion));
        $tagPrincipal = strtoupper(trim($tagConfigurado));

        $tags = [];
        if ($tagPrincipal !== '') {
            $tags[] = $tagPrincipal;
        }
        // ! Si se añaden nuevos formatos de tags, se añaden aquí
        //* Comprueba primero si son embalses yprueba tags
        $esEmbalse = $tipoEstacion === 'EMBALSE' || str_starts_with($codigo, 'E_');
        if ($esEmbalse) {
            $tags = array_merge($tags, [
                $codigo . 'FICT95',
                'FICT95',
                $codigo . 'LI__01',
                $codigo . 'LI__02',
            ]);
            //* En el caso que no sean embalses, prueba con los tags de los aforos
        } else {
            $tags = array_merge($tags, [
                $codigo . 'LI__01',
                $codigo . 'LI__02',
                $codigo . 'LICT01',
                $codigo . 'LICT02',
                $codigo . 'LICT03',
                $codigo . 'FICT98',
                $codigo . 'FICT95',
            ]);
        }
        // ? Elimina duplicados, vacios, ordena y resetea las claves
        return array_values(array_filter(array_unique($tags)));
    }

    private function calcularTendencia(string $tag): string
    {
        //* Redondea a hora actual a su al ultimo cuarto de hora
        $fechaActual15 = $this->redondearAQuinceminuto(now());
        $fechaAnterior15 = $fechaActual15->copy()->subMinutes(15);
        //* Pide a la API los valores de ese tag y calcula el inicio del intervalo 15 min antes
        $urlValores = env('API_SAIH_VALORES_URL', 'http://vcmas08:8001/tr/valores_periodo_tag/');
        $respuesta = Http::retry(3, 250, null, false)
            ->connectTimeout(1)
            ->timeout(8)
            ->get($urlValores, [
                'tag' => $tag,
                'start' => $fechaAnterior15->format('d/m/y H:i:s'),
                'end' => $fechaActual15->format('d/m/y H:i:s'),
            ]);
        //* Si la respuesta no es valida devuelve '---'
        if (! ($respuesta instanceof Response) || ! $respuesta->ok()) {
            return '---';
        }


        $periodoFechas = $respuesta->json();
        if (! is_array($periodoFechas)) {
            return '---';
        }

        $puntosPeriodoFechas = $this->normalizarPeriodoFechas($periodoFechas);
        if (count($puntosPeriodoFechas) < 2) {
            return '---';
        }

        // * Compara el valor de inicio con el de final para sacar la tendencia
        $valorAnterior = $puntosPeriodoFechas[0]['valor'] ?? null;
        $valorActual = $puntosPeriodoFechas[count($puntosPeriodoFechas) - 1]['valor'] ?? null;

        if ($valorAnterior === null || $valorActual === null) {
            return 'Error al calcular tendencia';
        }

        if ($valorActual > $valorAnterior) {
            return '⬈';
        }
        if ($valorActual < $valorAnterior) {
            return '⬊';
        }

        return '➞';
    }

    private function redondearAQuinceminuto(Carbon $fecha): Carbon
    {
        // Clona la fecha y pone los segundos a 0
        $fechaRedondeada = $fecha->copy()->setSecond(0);
        //* Redondea los minutos al su ultimo cuarto de hora (0, 15, 30, 45)
        $minutoRedondeado = intdiv((int) $fechaRedondeada->minute, 15) * 15;
        $fechaRedondeada->setMinute($minutoRedondeado);

        return $fechaRedondeada;
    }

    private function normalizarPeriodoFechas(array $periodoFechas): array
    {
        $puntos = [];

        foreach ($periodoFechas as $fila) {
            if (! is_array($fila)) {
                continue;
            }

            $fechaFila = $this->parsearFecha(isset($fila['fecha']) ? (string) $fila['fecha'] : null);
            $valorFila = $fila['valor'] ?? null;
            if ($fechaFila === null || ! is_numeric($valorFila)) {
                continue;
            }

            $puntos[] = [
                'fecha' => $fechaFila,
                'valor' => (float) $valorFila,
            ];
        }
        //* Ordena los puntos por fecha
        usort($puntos, function (array $a, array $b) {
            return $a['fecha']->getTimestamp() <=> $b['fecha']->getTimestamp();
        });

        return $puntos;
    }

    private function parsearFecha(?string $fechaTexto): ?Carbon
    {
        if ($fechaTexto === null || trim($fechaTexto) === '') {
            return null;
        }
        //* Quita los espacios y deja uno entre medias
        $fechaLimpia = preg_replace('/\s+/', ' ', trim($fechaTexto));
        if (! is_string($fechaLimpia) || $fechaLimpia === '') {
            return null;
        }
        //* acepta años con 2 o 4 digitos y los minutos y segundos opcionales
        $formatos = ['d/m/y H:i:s', 'd/m/Y H:i:s'];
        foreach ($formatos as $formato) {
            try {
                //* Parsea la fechacon la zona horaria de la aplicación
                $fechaCarbon = Carbon::createFromFormat($formato, $fechaLimpia, config('app.timezone'));
                if ($fechaCarbon !== false) {
                    return $fechaCarbon;
                }
            } catch (\Throwable $e) {
                //* Prueba con todos los formatos posibles
                continue;
            }
        }
        //? Si ningun formato funciona devuelve null
        return null;
    }

    private function calcularNivelAlerta(?float $valorSensor, $nivel1, $nivel2, $nivel3): int
    {
        if ($valorSensor === null) {
            // ? Si no devuelve nada o algo no válido, se pondra en alarma 0 para no alertar sin sentido
            return 0;
        }

        $u1 = (float) ($nivel1 ?? 0);
        $u2 = (float) ($nivel2 ?? 0);
        $u3 = (float) ($nivel3 ?? 0);

        if ($u3 > 0 && $valorSensor >= $u3) {
            return 3;
        }
        if ($u2 > 0 && $valorSensor >= $u2) {
            return 2;
        }
        if ($u1 > 0 && $valorSensor >= $u1) {
            return 1;
        }

        return 0;
    }
}
