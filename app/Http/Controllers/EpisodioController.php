<?php

namespace App\Http\Controllers;

use App\Services\EstadoActualService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class EpisodioController extends Controller
{
    private function sincronizarEstadoActualSiHaceFalta(EstadoActualService $estadoActualSyncService): void
    {
        $ultimaSincronizacion = Cache::get('api_estado_actual_sync_at');
        $cacheGlobal = Cache::get('api_estado_actual_global');
        $requiereSincronizacion = empty($ultimaSincronizacion) || $cacheGlobal === null;
        $modoGuardado = (string) Cache::get('api_estado_actual_modo', '');
        $modoEsperado = EstadoActualService::modoVisualizacionEsperado();

        if (! $requiereSincronizacion && $modoGuardado !== $modoEsperado) {
            $requiereSincronizacion = true;
        }

        if (! $requiereSincronizacion && $cacheGlobal instanceof \Illuminate\Support\Collection) {
            $faltanTendencias = $cacheGlobal
                ->flatMap(fn ($estaciones) => collect($estaciones))
                ->contains(fn ($estacion) => ! is_array($estacion) || ! array_key_exists('tendencia', $estacion));
            $requiereSincronizacion = $faltanTendencias;
        }

        // La actualización periódica la hace el scheduler (api:sync-datos cada 5 min).
        // Evitamos recalcular desde petición web para no bloquear la respuesta.

        if (! $requiereSincronizacion) {
            return;
        }

        try {
            $estadoActualSyncService->sincronizarCaches();
        } catch (Throwable $e) {
            Log::error('No se pudo refrescar la cache de estado actual para inicio', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function inicio(EstadoActualService $estadoActualSyncService)
    {
        $this->sincronizarEstadoActualSiHaceFalta($estadoActualSyncService);

        $resultadoFinal = Cache::get('api_estado_actual_global', collect());
        $ultimaSincronizacion = (string) Cache::get('api_estado_actual_sync_at', '');
        $alertasApi = collect($resultadoFinal)->flatMap(function ($estaciones) {
            return collect($estaciones);
        })->values();
        $modoVisualizacion = (string) Cache::get('api_estado_actual_modo', 'todas');
        $cacheInicioKey = 'inicio_panel_principal_v3_'.md5(($ultimaSincronizacion !== '' ? $ultimaSincronizacion : 'sin_sync').'|'.$modoVisualizacion);
        $panelData = Cache::remember($cacheInicioKey, now()->addMinutes(20), function () use ($alertasApi) {
            $codigosAforo = $alertasApi
                ->filter(fn ($estacion) => strtoupper((string) data_get($estacion, 'tipo', '')) !== 'EMBALSE')
                ->pluck('codigo')
                ->filter()
                ->values()
                ->all();

            $codigosEmbalse = $alertasApi
                ->filter(fn ($estacion) => strtoupper((string) data_get($estacion, 'tipo', '')) === 'EMBALSE')
                ->pluck('codigo')
                ->filter()
                ->values()
                ->all();

            $tagSecundarioAforo = empty($codigosAforo)
                ? collect()
                : DB::table('umbrales_umbralesran')
                    ->whereIn('ur_codigo', $codigosAforo)
                    ->pluck('ur_tag_ip21_caudal', 'ur_codigo');

            $tagSecundarioEmbalse = empty($codigosEmbalse)
                ? collect()
                : DB::table('umbrales_embalsesran')
                    ->whereIn('er_codigo', $codigosEmbalse)
                    ->pluck('er_tag_volumen', 'er_codigo');

            $codigosTotales = array_values(array_unique(array_merge($codigosAforo, $codigosEmbalse)));
            $valoresAccesorios = empty($codigosTotales)
                ? collect()
                : DB::table(function ($query) use ($codigosTotales) {
                    $query->select(
                        'rde_estacion',
                        'rde_valor_accesorio',
                        DB::raw('ROW_NUMBER() OVER (PARTITION BY rde_estacion ORDER BY rde_hora DESC) as posicion')
                    )
                        ->from('umbrales_randatosepisodio')
                        ->whereIn('rde_estacion', $codigosTotales);
                }, 'subconsulta')
                    ->where('posicion', 1)
                    ->pluck('rde_valor_accesorio', 'rde_estacion');

            $estacionesNormalizadas = $alertasApi->map(function ($estacion) use ($tagSecundarioAforo, $tagSecundarioEmbalse, $valoresAccesorios) {
                $tipo = strtoupper((string) data_get($estacion, 'tipo', ''));
                $codigo = (string) data_get($estacion, 'codigo', '---');
                $esEmbalse = $tipo === 'EMBALSE';
                $tagSecundario = $esEmbalse
                    ? ($tagSecundarioEmbalse->get($codigo) ?? '---')
                    : ($tagSecundarioAforo->get($codigo) ?? '---');

                return (object) [
                    'tipo' => $esEmbalse ? 'embalse' : 'aforo',
                    'codigo' => $codigo,
                    'nombre' => data_get($estacion, 'nombre', '---'),
                    'rio' => data_get($estacion, 'provincia', '---'),
                    'ccaa' => data_get($estacion, 'comunidad', '---'),
                    'tag_salida' => data_get($estacion, 'senal', '---'),
                    'tag_secundario' => ! empty($tagSecundario) ? $tagSecundario : '---',
                    'valor' => data_get($estacion, 'valor'),
                    'valor_acc' => $valoresAccesorios->get($codigo),
                    'hora' => data_get($estacion, 'fecha', '---'),
                    'tendencia' => data_get($estacion, 'tendencia', '→'),
                    'nivel_alerta' => (int) data_get($estacion, 'alerta', 0),
                    'umbral1' => (float) data_get($estacion, 'nivel1', 0),
                    'umbral2' => (float) data_get($estacion, 'nivel2', 0),
                    'umbral3' => (float) data_get($estacion, 'nivel3', 0),
                ];
            })->sortByDesc('nivel_alerta')->values();

            $aforos = $estacionesNormalizadas->where('tipo', 'aforo')->values();
            $embalses = $estacionesNormalizadas->where('tipo', 'embalse')->values();

            return [
                'aforos' => $aforos,
                'embalses' => $embalses,
                'filtroCcaaAforos' => $aforos->pluck('ccaa')->filter()->unique()->sort()->values(),
                'filtroCcaaEmbalses' => $embalses->pluck('ccaa')->filter()->unique()->sort()->values(),
            ];
        });

        return view('auth.inicio_umbrales', [
            'titulo' => 'Panel Principal - Estado Actual',
            'aforos' => $panelData['aforos'],
            'embalses' => $panelData['embalses'],
            'filtroCcaaAforos' => $panelData['filtroCcaaAforos'],
            'filtroCcaaEmbalses' => $panelData['filtroCcaaEmbalses'],
            'ultimaSincronizacion' => $ultimaSincronizacion,
        ]);
    }

    private function obtenerNivelesAlertaVarios($episodios)
    {
        $codigos = [];
        foreach ($episodios as $ep) {
            if (! empty($ep->re_estaciones_historicas)) {
                $codigos = array_merge($codigos, explode(',', $ep->re_estaciones_historicas));
            }
            if (! empty($ep->re_estaciones_activas)) {
                $codigos = array_merge($codigos, explode(',', $ep->re_estaciones_activas));
            }
        }
        $codigos = array_unique(array_filter(array_map('trim', $codigos)));

        if (empty($codigos)) {
            return [];
        }

        $mediciones = DB::table(function ($query) use ($codigos) {
            $query->select(
                'rde_estacion',
                'rde_valor',
                DB::raw('ROW_NUMBER() OVER (PARTITION BY rde_estacion ORDER BY rde_hora DESC) as posicion')
            )
                ->from('umbrales_randatosepisodio')
                ->whereIn('rde_estacion', $codigos);
        }, 'subconsulta')
            ->where('posicion', 1)
            ->pluck('rde_valor', 'rde_estacion');

        $estaciones = DB::table('umbrales_umbralesran')->whereIn('ur_codigo', $codigos)->get();
        $embalses = DB::table('umbrales_embalsesran')->whereIn('er_codigo', $codigos)->get();

        $niveles = [];

        foreach ($estaciones as $est) {
            $val = $mediciones->get($est->ur_codigo);
            $niveles[$est->ur_codigo] = $this->calcularNivelDinamico($val, $est, 'ur_');
        }
        foreach ($embalses as $emb) {
            $val = $mediciones->get($emb->er_codigo);
            $niveles[$emb->er_codigo] = $this->calcularNivelDinamico($val, $emb, 'er_');
        }

        return $niveles;
    }

    public function activosGlobal()
    {
        $episodios = DB::table('umbrales_ranepisodio')
            ->leftJoin('umbrales_ccaa', 'umbrales_ranepisodio.re_ccaa_id', '=', 'umbrales_ccaa.c_id')
            ->select('umbrales_ranepisodio.*', 'umbrales_ccaa.c_comunidad_autonoma as nombre_ccaa')
            ->whereNull('re_hora_fin')
            ->orderBy('re_hora_inicio', 'desc')
            ->get();

        $nivelesEstaciones = $this->obtenerNivelesAlertaVarios($episodios);

        return view('auth.episodios_lista', [
            'episodios' => $episodios,
            'titulo' => 'Cuenca del Tajo: Episodios Activos',
            'tipo' => 'Activos',
            'nivelesEstaciones' => $nivelesEstaciones,
        ]);
    }

    // Vista espisodios historicos
    public function historicoGlobal()
    {
        $episodios = DB::table('umbrales_ranepisodio')
            ->leftJoin('umbrales_ccaa', 'umbrales_ranepisodio.re_ccaa_id', '=', 'umbrales_ccaa.c_id')
            ->select('umbrales_ranepisodio.*', 'umbrales_ccaa.c_comunidad_autonoma as nombre_ccaa')
            ->whereNotNull('re_hora_fin')
            ->orderBy('re_hora_inicio', 'desc')
            ->get();

        $nivelesEstaciones = $this->obtenerNivelesAlertaVarios($episodios);

        return view('auth.episodios_lista', [
            'episodios' => $episodios,
            'titulo' => 'Cuenca del Tajo: Histórico de Episodios',
            'tipo' => 'Históricos',
            'nivelesEstaciones' => $nivelesEstaciones,
        ]);
    }

    // Vista para los episodios activos por CCAA
    public function activosPorCCAA($id)
    {
        $episodios = DB::table('umbrales_ranepisodio')
            ->leftJoin('umbrales_ccaa', 'umbrales_ranepisodio.re_ccaa_id', '=', 'umbrales_ccaa.c_id')
            ->select('umbrales_ranepisodio.*', 'umbrales_ccaa.c_comunidad_autonoma as nombre_ccaa')
            ->where('re_ccaa_id', $id)
            ->whereNull('re_hora_fin')
            ->orderBy('re_hora_inicio', 'desc')
            ->get();

        $ccaa = DB::table('umbrales_ccaa')->where('c_id', $id)->first();
        $nombreCcaa = $ccaa ? $ccaa->c_comunidad_autonoma : "CCAA $id";

        $nivelesEstaciones = $this->obtenerNivelesAlertaVarios($episodios);

        return view('auth.episodios_lista', [
            'episodios' => $episodios,
            'titulo' => "$nombreCcaa: Episodios Activos",
            'tipo' => 'Activos',
            'nivelesEstaciones' => $nivelesEstaciones,
        ]);
    }

    public function historicoPorCCAA($id)
    {
        $episodios = DB::table('umbrales_ranepisodio')
            ->leftJoin('umbrales_ccaa', 'umbrales_ranepisodio.re_ccaa_id', '=', 'umbrales_ccaa.c_id')
            ->select('umbrales_ranepisodio.*', 'umbrales_ccaa.c_comunidad_autonoma as nombre_ccaa')
            ->where('re_ccaa_id', $id)
            ->whereNotNull('re_hora_fin')
            ->orderBy('re_hora_inicio', 'desc')
            ->get();

        $ccaa = DB::table('umbrales_ccaa')->where('c_id', $id)->first();
        $nombreCcaa = $ccaa ? $ccaa->c_comunidad_autonoma : "CCAA $id";

        $nivelesEstaciones = $this->obtenerNivelesAlertaVarios($episodios);

        return view('auth.episodios_lista', [
            'episodios' => $episodios,
            'titulo' => "$nombreCcaa: Histórico de Episodios",
            'tipo' => 'Históricos',
            'nivelesEstaciones' => $nivelesEstaciones,
        ]);
    }

    // BUSCADOR
    public function buscarGlobal(Request $request, \App\Services\BuscadorService $servicioAlertas)
    {
        $query = trim((string) $request->input('query', $request->input('q', '')));
        if ($query === '') {
            return redirect()->route('inicio');
        }

        $resultados = $servicioAlertas->BuscarGlobal($query);

        return view('auth.busqueda_resultados', [
            'titulo' => 'Resultados de la búsqueda: "'.$query.'"',
            'query' => $query,
            'embalses' => $resultados['embalses'],
            'roeas' => $resultados['roeas'],
            'marcos_control' => $resultados['marcos_control'],
            'aforos' => $resultados['aforos'],
            'episodios' => $resultados['episodios'] ?? collect(),
            'meta' => $resultados['meta'] ?? ['total' => 0],
        ]);
    }

    // Detalle de un episodio
    public function detalle($id)
    {
        $episodio = DB::table('umbrales_ranepisodio')
            ->leftJoin('umbrales_ccaa', 'umbrales_ranepisodio.re_ccaa_id', '=', 'umbrales_ccaa.c_id')
            ->select('umbrales_ranepisodio.*', 'umbrales_ccaa.c_comunidad_autonoma as nombre_ccaa')
            ->where('re_id', $id)
            ->first();
        if (! $episodio) {
            abort(404);
        }

        $episodioActivo = empty($episodio->re_hora_fin);
        $campoEstaciones = $episodioActivo ? 're_estaciones_activas' : 're_estaciones_historicas';
        $codigosEstaciones = ! empty($episodio->{$campoEstaciones}) ? explode(',', $episodio->{$campoEstaciones}) : [];
        $codigos = array_values(array_unique(array_filter(array_map('trim', $codigosEstaciones))));

        $estaciones = collect();

        if (count($codigos) > 0) {

            $mediciones = DB::table(function ($query) use ($id, $codigos) {
                $query->select(
                    'rde_estacion',
                    'rde_valor',
                    'rde_valor_accesorio',
                    'rde_hora',
                    DB::raw('ROW_NUMBER() OVER (PARTITION BY rde_estacion ORDER BY rde_hora DESC) as posicion')
                )
                    ->from('umbrales_randatosepisodio')
                    ->where('rde_ran_episodio_id', $id)
                    ->whereIn('rde_estacion', $codigos);
            }, 'subconsulta')
                ->where('posicion', '<=', 2)
                ->get()
                ->groupBy('rde_estacion');

            // ROEAS / Aforos
            $ran = DB::table('umbrales_umbralesran')
                ->leftJoin('umbrales_ccaa', 'umbrales_umbralesran.ur_comunidad_autonoma_id', '=', 'umbrales_ccaa.c_id')
                ->whereIn('ur_codigo', $codigos)->get()
                ->map(function ($item) use ($mediciones, $episodio) {
                    $m = collect($mediciones->get($item->ur_codigo, []));
                    $actual = $m->where('posicion', 1)->first();
                    $anterior = $m->where('posicion', 2)->first();

                    return (object) [
                        'tipo' => 'aforo',
                        'codigo' => $item->ur_codigo,
                        'nombre' => $item->ur_nombre,
                        'rio' => $item->ur_rio,
                        'provincia' => ! empty($item->ur_provincia) ? $item->ur_provincia : 'Provincia no encontrada',
                        'ccaa' => $item->c_comunidad_autonoma ?? '---',
                        'tag_salida' => $item->ur_tag_ip21,
                        'tag_secundario' => $item->ur_tag_ip21_caudal ?? '---',
                        'valor' => $actual ? $actual->rde_valor : null,
                        'valor_acc' => $actual ? $actual->rde_valor_accesorio : null,
                        'hora' => $actual ? Carbon::parse($actual->rde_hora)->format('d/m/Y H:i:s') : '---',
                        'nivel_alerta' => $this->calcularNivelDinamico($actual ? $actual->rde_valor : null, $item, 'ur_'),
                        'ultimo_nivel_alerta' => $this->calcularNivelDinamico($anterior ? $anterior->rde_valor : null, $item, 'ur_'),
                        'episodio_id' => $episodio->re_id,
                        'umbral1' => $item->ur_umbral1 ?? 0,
                        'umbral2' => $item->ur_umbral2 ?? 0,
                        'umbral3' => $item->ur_umbral3 ?? 0,
                    ];
                });
            // Embalses
            $embalses = DB::table('umbrales_embalsesran')
                ->leftJoin('umbrales_ccaa', 'umbrales_embalsesran.er_comunidad_autonoma_id', '=', 'umbrales_ccaa.c_id')
                ->whereIn('er_codigo', $codigos)->get()
                ->map(function ($item) use ($mediciones, $episodio) {
                    $m = collect($mediciones->get($item->er_codigo, []));
                    $actual = $m->where('posicion', 1)->first();
                    $anterior = $m->where('posicion', 2)->first();

                    return (object) [
                        'tipo' => 'embalse',
                        'codigo' => $item->er_codigo,
                        'nombre' => $item->er_nombre,
                        'rio' => $item->er_rio ?? '---',
                        'provincia' => ! empty($item->er_provincia) ? $item->er_provincia : 'Provincia no encontrada',
                        'ccaa' => $item->c_comunidad_autonoma ?? '---',
                        'tag_salida' => $item->er_tag_ip21 ?? '---',
                        'tag_secundario' => $item->er_tag_volumen ?? '---',
                        'valor' => $actual ? $actual->rde_valor : null,
                        'valor_acc' => $actual ? $actual->rde_valor_accesorio : null,
                        'hora' => $actual ? Carbon::parse($actual->rde_hora)->format('d/m/Y H:i:s') : '---',
                        'nivel_alerta' => $this->calcularNivelDinamico($actual ? $actual->rde_valor : null, $item, 'er_'),
                        'ultimo_nivel_alerta' => $this->calcularNivelDinamico($anterior ? $anterior->rde_valor : null, $item, 'er_'),
                        'episodio_id' => $episodio->re_id,
                        'umbral1' => $item->er_umbral1 ?? 0,
                        'umbral2' => $item->er_umbral2 ?? 0,
                        'umbral3' => $item->er_umbral3 ?? 0,
                    ];
                });

            // Guarda todas las estaciones y lo ordena por nivel de alerta
            $estaciones = $ran->merge($embalses)->sortByDesc('nivel_alerta')->values();
        }

        $nombreEpisodio = $episodio->re_nombre ?? 'Episodio '.$episodio->re_id;
        $ccaa = $episodio->nombre_ccaa ?? 'CCAA Desconocida';
        $provinciaEpisodio = 'Provincia no encontrada';

        try {
            $provincias = DB::table('umbrales_provincias')
                ->where('c_id', $episodio->re_ccaa_id)
                ->pluck('p_provincia')
                ->filter(fn ($nombre) => ! empty(trim((string) $nombre)))
                ->map(fn ($nombre) => trim((string) $nombre))
                ->unique()
                ->values();

            if ($provincias->isNotEmpty()) {
                $provinciaEpisodio = $provincias->implode(', ');
            }
        } catch (QueryException $e) {
            Log::warning('No se pudo obtener la provincia del episodio', [
                'episodio_id' => $episodio->re_id,
                'ccaa_id' => $episodio->re_ccaa_id,
                'message' => $e->getMessage(),
            ]);
        }

        $horaIni = $episodio->re_hora_inicio ? Carbon::parse($episodio->re_hora_inicio)->format('d/m/Y') : '---';

        $horaFinText = '';
        if (! empty($episodio->re_hora_fin)) {
            $horaFinText = ' - Fin: '.Carbon::parse($episodio->re_hora_fin)->format('d/m/Y');
        } else {
            $horaFinText = ' - (Activo)';
        }

        $tituloDetalle = "Detalle: {$nombreEpisodio} | {$ccaa} | Inicio: {$horaIni}{$horaFinText}";

        return view('auth.episodios_detalle', [
            'episodio' => $episodio,
            'estaciones' => $estaciones,
            'titulo' => $tituloDetalle,
            'totalEstacionesEpisodio' => count($codigos),
            'etiquetaEstacionesDetalle' => $episodioActivo ? 'Estaciones activas' : 'Estaciones históricas',
            'provinciaEpisodio' => $provinciaEpisodio,
        ]);
    }

    private function calcularNivelDinamico($valor, $estacion, $prefijo)
    {
        if ($valor === null) {
            return 0;
        }

        $u3 = (float) ($estacion->{$prefijo.'umbral3'} ?? 0);
        $u2 = (float) ($estacion->{$prefijo.'umbral2'} ?? 0);
        $u1 = (float) ($estacion->{$prefijo.'umbral1'} ?? 0);

        if ($u3 > 0 && $valor >= $u3) {
            return 3;
        }
        if ($u2 > 0 && $valor >= $u2) {
            return 2;
        }
        if ($u1 > 0 && $valor >= $u1) {
            return 1;
        }

        return 0;
    }

    // Lógica para cerrar el episodio
    public function cerrarEpisodio($id)
    {
        $this->autorizarStaff();

        DB::table('umbrales_ranepisodio')
            ->where('re_id', $id)
            ->update([
                're_hora_fin' => now(),
            ]);

        return redirect()->route('inicio')->with('exito', 'El episodio ha sido cerrado');
    }

    public function renombrarEpisodio(Request $request, $id)
    {
        $this->autorizarStaff();

        $request->validate([
            'nuevo_nombre' => 'required|string|max:255',
        ]);

        DB::table('umbrales_ranepisodio')
            ->where('re_id', $id)
            ->update([
                're_nombre' => $request->nuevo_nombre,
            ]);

        return back()->with('exito', 'Nombre del episodio actualizado correctamente.');
    }

    public function mapaGlobal(EstadoActualService $estadoActualSyncService)
    {
        $this->sincronizarEstadoActualSiHaceFalta($estadoActualSyncService);

        $estacionesApi = $estadoActualSyncService->construirEstadoActual(
            $estadoActualSyncService->obtenerEstacionesEstadoActual()
        );

        $coordenadasPorCodigo = DB::table('umbrales_coordran')
            ->select('lr_codigo_txt', 'latitud', 'longitud')
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->get()
            ->mapWithKeys(function ($coord) {
                $codigo = strtoupper(trim((string) $coord->lr_codigo_txt));

                return [
                    $codigo => [
                        'latitud' => $coord->latitud,
                        'longitud' => $coord->longitud,
                    ],
                ];
            });

        $puntos = collect($estacionesApi)
            ->map(function ($estacion) use ($coordenadasPorCodigo) {
                $codigo = strtoupper(trim((string) ($estacion['codigo'] ?? '')));
                $coords = $coordenadasPorCodigo->get($codigo);

                if (! $coords) {
                    return null;
                }

                $tipoBase = strtoupper((string) ($estacion['tipo'] ?? 'AFORO'));
                $tipo = 'aforo';
                if ($tipoBase === 'EMBALSE') {
                    $tipo = 'embalse';
                } elseif (str_starts_with($codigo, 'R')) {
                    $tipo = 'roea';
                } elseif (str_starts_with($codigo, 'M')) {
                    $tipo = 'marco';
                }

                return (object) [
                    'codigo' => $codigo,
                    'nombre' => (string) ($estacion['nombre'] ?? 'Sin nombre'),
                    'latitud' => $coords['latitud'],
                    'longitud' => $coords['longitud'],
                    'ccaa' => (string) ($estacion['comunidad'] ?? 'Sin definir'),
                    'tipo' => $tipo,
                    'valor_actual' => $estacion['valor'] ?? null,
                    'nivel_alerta' => (int) ($estacion['alerta'] ?? 0),
                    'fecha' => (string) ($estacion['fecha'] ?? 'Sin conexión'),
                    'tendencia' => (string) ($estacion['tendencia'] ?? '→'),
                ];
            })
            ->filter()
            ->values();

        $listaCcaa = $puntos->pluck('ccaa')->unique()->sort()->values();

        return view('auth.mapa_global', [
            'titulo' => 'Mapa Global de la Cuenca',
            'puntos' => $puntos,
            'listaCcaa' => $listaCcaa,
        ]);
    }
}
