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
        $maxEdadMinutos = (int) env('ESTADO_ACTUAL_MAX_AGE_MIN', 6);
        $fechaUltimaSync = null;
        if (! empty($ultimaSincronizacion)) {
            try {
                $fechaUltimaSync = Carbon::parse($ultimaSincronizacion);
            } catch (Throwable $e) {
                $fechaUltimaSync = null;
            }
        }
        if ($fechaUltimaSync !== null && $fechaUltimaSync->lt(now()->subMinutes($maxEdadMinutos))) {
            $requiereSincronizacion = true;
        }
        $modoGuardado = (string) Cache::get('api_estado_actual_modo', '');
        $modoEsperado = EstadoActualService::modoVisualizacionEsperado();

        if (! $requiereSincronizacion && $modoGuardado !== $modoEsperado) {
            $requiereSincronizacion = true;
        }

        if (! $requiereSincronizacion && $cacheGlobal instanceof \Illuminate\Support\Collection) {
            $faltanTendencias = $cacheGlobal
                ->flatMap(fn ($estaciones) => collect($estaciones))
                ->contains(fn ($estacion) => ! is_array($estacion) || ! array_key_exists('tendencia', $estacion));
            $requiereSincronizacion = $faltanTendencias || $cacheGlobal->isEmpty();
        }

        if (! $requiereSincronizacion && is_array($cacheGlobal) && empty($cacheGlobal)) {
            $requiereSincronizacion = true;
        }

        if (! $requiereSincronizacion && $modoEsperado === 'todas') {
            $totalActivas = $estadoActualSyncService->contarEstacionesActivas();
            $totalCache = 0;

            if ($cacheGlobal instanceof \Illuminate\Support\Collection) {
                $totalCache = $cacheGlobal->flatMap(fn ($estaciones) => collect($estaciones))->count();
            } elseif (is_array($cacheGlobal)) {
                $totalCache = collect($cacheGlobal)->flatMap(fn ($estaciones) => collect($estaciones))->count();
            }

            if ($totalActivas > 0 && $totalCache < $totalActivas) {
                $requiereSincronizacion = true;
            }
        }

        // La actualización periódica la hace el scheduler (api:sync-datos cada 5 min).
        // Evitamos recalcular desde petición web para no bloquear la respuesta.

        if (! $requiereSincronizacion) {
            return;
        }

        $lock = Cache::lock('api:sync-datos:web', 120);
        if (! $lock->get()) {
            return;
        }

        try {
            $estadoActualSyncService->sincronizarCaches(! app()->runningInConsole());
        } catch (Throwable $e) {
            Log::error('No se pudo refrescar la cache de estado actual para inicio', [
                'message' => $e->getMessage(),
            ]);
        } finally {
            optional($lock)->release();
        }
    }

    public function inicio(EstadoActualService $estadoActualSyncService)
    {
        $this->sincronizarEstadoActualSiHaceFalta($estadoActualSyncService);

        $resultadoFinal = Cache::get('api_estado_actual_global', collect());
        $ultimaSincronizacion = (string) Cache::get('api_estado_actual_sync_at', '');
        $todasApi = collect($resultadoFinal)->flatMap(function ($estaciones) {
            return collect($estaciones);
        })->values();

        $totalAforos = (int) DB::table('umbrales_umbralesran')
            ->where('ur_activo', 1)
            ->count();
        $totalEmbalses = (int) DB::table('umbrales_embalsesran')
            ->where('er_activo', 1)
            ->count();

        $alertasApi = $todasApi;
        $modoEsperado = EstadoActualService::modoVisualizacionEsperado();
        if ($modoEsperado === 'solo_emergencias') {
            $alertasApi = $alertasApi
                ->filter(fn ($estacion) => (int) data_get($estacion, 'alerta', 0) > 0)
                ->values();
        }
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
                'rio' => data_get($estacion, 'rio', '---'),
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

        $panelData = [
            'aforos' => $aforos,
            'embalses' => $embalses,
            'filtroCcaaAforos' => $aforos->pluck('ccaa')->filter()->unique()->sort()->values(),
            'filtroCcaaEmbalses' => $embalses->pluck('ccaa')->filter()->unique()->sort()->values(),
        ];

        return view('auth.inicio_umbrales', [
            'titulo' => 'Panel Principal - Estado Actual',
            'aforos' => $panelData['aforos'],
            'embalses' => $panelData['embalses'],
            'filtroCcaaAforos' => $panelData['filtroCcaaAforos'],
            'filtroCcaaEmbalses' => $panelData['filtroCcaaEmbalses'],
            'ultimaSincronizacion' => $ultimaSincronizacion,
            'totalAforos' => $totalAforos,
            'totalEmbalses' => $totalEmbalses,
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

    private function obtenerFechasEstacionesPorEpisodio($episodios): array
    {
        $ids = collect($episodios)->pluck('re_id')->filter()->values()->all();
        if (empty($ids)) {
            return [];
        }

        $registros = DB::table(function ($query) use ($ids) {
            $query->select(
                'rde_ran_episodio_id',
                'rde_estacion',
                'rde_hora',
                DB::raw('ROW_NUMBER() OVER (PARTITION BY rde_ran_episodio_id, rde_estacion ORDER BY rde_hora DESC) as posicion')
            )
                ->from('umbrales_randatosepisodio')
                ->whereIn('rde_ran_episodio_id', $ids);
        }, 'subconsulta')
            ->where('posicion', 1)
            ->get();

        $resultado = [];
        foreach ($registros as $fila) {
            $episodioId = (int) $fila->rde_ran_episodio_id;
            $codigo = trim((string) $fila->rde_estacion);
            if ($codigo === '') {
                continue;
            }
            if (! isset($resultado[$episodioId])) {
                $resultado[$episodioId] = [];
            }
            $resultado[$episodioId][$codigo] = $fila->rde_hora;
        }

        return $resultado;
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
        $fechasEstaciones = $this->obtenerFechasEstacionesPorEpisodio($episodios);

        return view('auth.episodios_lista', [
            'episodios' => $episodios,
            'titulo' => 'Cuenca del Tajo: Episodios Activos',
            'tipo' => 'Activos',
            'nivelesEstaciones' => $nivelesEstaciones,
            'fechasEstaciones' => $fechasEstaciones,
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
        $fechasEstaciones = $this->obtenerFechasEstacionesPorEpisodio($episodios);

        return view('auth.episodios_lista', [
            'episodios' => $episodios,
            'titulo' => 'Cuenca del Tajo: Histórico de Episodios',
            'tipo' => 'Históricos',
            'nivelesEstaciones' => $nivelesEstaciones,
            'fechasEstaciones' => $fechasEstaciones,
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
        $fechasEstaciones = $this->obtenerFechasEstacionesPorEpisodio($episodios);

        return view('auth.episodios_lista', [
            'episodios' => $episodios,
            'titulo' => "$nombreCcaa: Episodios Activos",
            'tipo' => 'Activos',
            'nivelesEstaciones' => $nivelesEstaciones,
            'fechasEstaciones' => $fechasEstaciones,
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
        $fechasEstaciones = $this->obtenerFechasEstacionesPorEpisodio($episodios);

        return view('auth.episodios_lista', [
            'episodios' => $episodios,
            'titulo' => "$nombreCcaa: Histórico de Episodios",
            'tipo' => 'Históricos',
            'nivelesEstaciones' => $nivelesEstaciones,
            'fechasEstaciones' => $fechasEstaciones,
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
        if ($episodioActivo) {
            $activas = ! empty($episodio->re_estaciones_activas) ? explode(',', $episodio->re_estaciones_activas) : [];
            $historicas = ! empty($episodio->re_estaciones_historicas) ? explode(',', $episodio->re_estaciones_historicas) : [];
            $codigosEstaciones = array_merge($activas, $historicas);
        } else {
            $codigosEstaciones = ! empty($episodio->re_estaciones_historicas) ? explode(',', $episodio->re_estaciones_historicas) : [];
        }
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

        $cacheGlobal = Cache::get('api_estado_actual_global');
        $estacionesApi = collect();

        if ($cacheGlobal instanceof \Illuminate\Support\Collection) {
            $estacionesApi = $cacheGlobal->flatMap(fn ($estaciones) => collect($estaciones))->values();
        } elseif (is_array($cacheGlobal)) {
            $estacionesApi = collect($cacheGlobal)->flatMap(fn ($estaciones) => collect($estaciones))->values();
        }

        if ($estacionesApi->isEmpty()) {
            $estacionesApi = $estadoActualSyncService->construirEstadoActual(
                $estadoActualSyncService->obtenerEstacionesEstadoActual()
            );
        }

        $coordRan = DB::table('umbrales_coordran')
            ->select('lr_codigo_txt as codigo', 'latitud', 'longitud', 'lr_utm_huso', 'lr_utm_x', 'lr_utm_y')
            ->where(function ($query) {
                $query
                    ->where(function ($subQuery) {
                        $subQuery->whereNotNull('latitud')->whereNotNull('longitud');
                    })
                    ->orWhere(function ($subQuery) {
                        $subQuery
                            ->whereNotNull('lr_utm_huso')
                            ->whereNotNull('lr_utm_x')
                            ->whereNotNull('lr_utm_y');
                    });
            })
            ->get();

        $coordenadasPorCodigo = $coordRan
            ->flatMap(function ($coord) {
                $codigo = strtoupper(trim((string) $coord->codigo));
                $codigoNormalizado = $this->normalizarCodigo($codigo);
                $latitud = $coord->latitud;
                $longitud = $coord->longitud;

                if ($latitud === null || $longitud === null) {
                    $convertido = $this->convertirUtmALatLong($coord->lr_utm_huso, $coord->lr_utm_x, $coord->lr_utm_y);
                    if ($convertido) {
                        $latitud = $convertido['latitud'];
                        $longitud = $convertido['longitud'];
                    }
                }

                if ($latitud === null || $longitud === null) {
                    return [];
                }

                $entrada = [
                    'latitud' => $latitud,
                    'longitud' => $longitud,
                ];

                $pares = [
                    $codigo => $entrada,
                ];

                if ($codigoNormalizado !== '' && $codigoNormalizado !== $codigo) {
                    $pares[$codigoNormalizado] = $entrada;
                }

                return $pares;
            });

        $puntos = collect($estacionesApi)
            ->map(function ($estacion) use ($coordenadasPorCodigo) {
                $codigo = strtoupper(trim((string) ($estacion['codigo'] ?? '')));
                $codigoNormalizado = $this->normalizarCodigo($codigo);
                $coords = $coordenadasPorCodigo->get($codigo)
                    ?? ($codigoNormalizado !== '' ? $coordenadasPorCodigo->get($codigoNormalizado) : null);

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

    private function convertirUtmALatLong($huso, $utmX, $utmY): ?array
    {
        $zona = is_numeric($huso) ? (int) $huso : null;
        $easting = is_numeric($utmX) ? (float) $utmX : (float) str_replace(',', '.', (string) $utmX);
        $northing = is_numeric($utmY) ? (float) $utmY : (float) str_replace(',', '.', (string) $utmY);

        if ($zona === null || $zona <= 0 || $easting <= 0 || $northing <= 0) {
            return null;
        }

        $a = 6378137.0;
        $e = 0.081819191;
        $e1sq = 0.006739497;
        $k0 = 0.9996;

        $x = $easting - 500000.0;
        $y = $northing;

        $m = $y / $k0;
        $mu = $m / ($a * (1 - ($e * $e / 4) - (3 * pow($e, 4) / 64) - (5 * pow($e, 6) / 256)));

        $e1 = (1 - sqrt(1 - $e * $e)) / (1 + sqrt(1 - $e * $e));

        $phi1 = $mu
            + (3 * $e1 / 2 - 27 * pow($e1, 3) / 32) * sin(2 * $mu)
            + (21 * pow($e1, 2) / 16 - 55 * pow($e1, 4) / 32) * sin(4 * $mu)
            + (151 * pow($e1, 3) / 96) * sin(6 * $mu)
            + (1097 * pow($e1, 4) / 512) * sin(8 * $mu);

        $n1 = $a / sqrt(1 - pow($e * sin($phi1), 2));
        $t1 = pow(tan($phi1), 2);
        $c1 = $e1sq * pow(cos($phi1), 2);
        $r1 = $a * (1 - $e * $e) / pow(1 - pow($e * sin($phi1), 2), 1.5);
        $d = $x / ($n1 * $k0);

        $lat = $phi1 - ($n1 * tan($phi1) / $r1) * (
            pow($d, 2) / 2
            - (5 + 3 * $t1 + 10 * $c1 - 4 * $c1 * $c1 - 9 * $e1sq) * pow($d, 4) / 24
            + (61 + 90 * $t1 + 298 * $c1 + 45 * $t1 * $t1 - 252 * $e1sq - 3 * $c1 * $c1) * pow($d, 6) / 720
        );

        $lon = (
            $d
            - (1 + 2 * $t1 + $c1) * pow($d, 3) / 6
            + (5 - 2 * $c1 + 28 * $t1 - 3 * $c1 * $c1 + 8 * $e1sq + 24 * $t1 * $t1) * pow($d, 5) / 120
        ) / cos($phi1);

        $lon0 = deg2rad(($zona - 1) * 6 - 180 + 3);

        $latDeg = rad2deg($lat);
        $lonDeg = rad2deg($lon0 + $lon);

        if (! is_finite($latDeg) || ! is_finite($lonDeg)) {
            return null;
        }

        return [
            'latitud' => round($latDeg, 6),
            'longitud' => round($lonDeg, 6),
        ];
    }

    private function normalizarCodigo(string $codigo): string
    {
        $codigoLimpio = strtoupper(trim($codigo));

        return preg_replace('/[^A-Z0-9]/', '', $codigoLimpio) ?? '';
    }
}
