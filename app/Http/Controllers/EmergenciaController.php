<?php

namespace App\Http\Controllers;

use App\Models\SituacionEmergencia;
use App\Models\UmbralesCcaa;
use App\Models\UmbralesProvincia;
use App\Services\EstadoActualService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class EmergenciaController extends Controller
{
    public function crear()
    {
        $this->autorizarStaff();

        $ccaaParaFormulario = UmbralesCcaa::where('c_comunidad_autonoma', 'NOT ILIKE', '%Arag%')
            ->where('c_comunidad_autonoma', 'NOT ILIKE', '%Portugal%')
            ->where('c_comunidad_autonoma', 'NOT ILIKE', '%AY%')
            ->get();

        $provinciasParaFormulario = UmbralesProvincia::all();

        return view('auth.situacion_formulario', compact('ccaaParaFormulario', 'provinciasParaFormulario'));
    }

    public function guardar(Request $request)
    {
        $this->autorizarStaff();

        $esFlujoDesdePlan = $request->input('return_to') === 'plan';

        $reglasValidacion = [
            'ccaa_id' => 'required|exists:umbrales_ccaa,c_id',
            'nivel' => 'required|integer|min:0|max:5',
            'fecha' => 'required|date|before_or_equal:today',
            'hora' => 'required',
        ];

        if (! $esFlujoDesdePlan) {
            $reglasValidacion['tipo_documento'] = 'required|in:pdf_oficial,texto_correo';
            $reglasValidacion['texto_correo'] = 'required_if:tipo_documento,texto_correo';
            $reglasValidacion['archivo_pdf'] = 'required_if:tipo_documento,pdf_oficial|file|mimes:pdf|max:1900';
        } else {
            $reglasValidacion['tipo_documento'] = 'nullable|in:pdf_oficial,texto_correo';
            $reglasValidacion['texto_correo'] = 'nullable';
            $reglasValidacion['archivo_pdf'] = 'nullable|file|mimes:pdf|max:1900';
        }

        $request->validate($reglasValidacion, [
            'fecha.before_or_equal' => 'Error: La fecha de la emergencia no puede ser futura',
            'texto_correo.required_if' => 'Error: Debes pegar el contenido del correo para generar el PDF.',
            'archivo_pdf.required_if' => 'Error: Debes adjuntar un archivo PDF oficial.',
            'archivo_pdf.max' => 'Error: El archivo PDF supera el tamaño máximo permitido (1.9 MB).',
        ]);

        $hoy = \Carbon\Carbon::now()->format('Y-m-d');
        $horaActual = \Carbon\Carbon::now()->format('H:i');

        if ($request->fecha == $hoy && $request->hora > $horaActual) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['hora' => 'Error: La hora de la emergencia no puede ser futura']);
        }

        $provinciasIds = (array) $request->input('provincias_ids', []);
        $provinciasIds = array_values(array_unique(array_filter(array_map(function ($valor) {
            return is_numeric($valor) ? (int) $valor : null;
        }, $provinciasIds))));

        $provinciasDeLaCcaa = UmbralesProvincia::where('c_id', $request->ccaa_id)->get();
        $tieneProvincias = $provinciasDeLaCcaa->isNotEmpty();

        if ($tieneProvincias && empty($provinciasIds)) {
            if ($request->input('alcance') === 'global') {
                $provinciasIds = $provinciasDeLaCcaa->pluck('p_id')->map(fn ($id) => (int) $id)->all();
            } else {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['provincias_ids' => 'Debe marcar al menos una provincia o seleccionar todas.']);
            }
        }

        $textoProvincias = '';

        if (! $tieneProvincias) {
            $provinciasIds = [null];
            $textoProvincias = 'Toda la Comunidad Autónoma';
        } else {
            $idsValidosCcaa = $provinciasDeLaCcaa->pluck('p_id')->map(fn ($id) => (int) $id)->all();
            $provinciasIds = array_values(array_filter($provinciasIds, fn ($id) => in_array((int) $id, $idsValidosCcaa, true)));

            if (empty($provinciasIds)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['provincias_ids' => 'No se han detectado provincias válidas para la comunidad seleccionada.']);
            }

            if (count($provinciasIds) == $provinciasDeLaCcaa->count()) {
                $textoProvincias = 'Todas las provincias (Afectación a nivel autonómico)';
            } else {
                $nombresProvincias = $provinciasDeLaCcaa->whereIn('p_id', $provinciasIds)->pluck('p_provincia')->toArray();
                $textoProvincias = implode(', ', $nombresProvincias);
            }
        }

        $rutaPdf = null;
        $ccaaSeleccionada = UmbralesCcaa::find($request->ccaa_id);
        $nombreCcaa = $ccaaSeleccionada?->c_comunidad_autonoma ?? 'ccaa';

        if ($request->tipo_documento == 'pdf_oficial' && $request->hasFile('archivo_pdf')) {
            $archivo = $request->file('archivo_pdf');
            $nombrePdf = time().'_'.$archivo->getClientOriginalName();
            $rutaPdf = $archivo->storeAs('pdf_emergencia', $nombrePdf, 'public');
        }

        if ($request->tipo_documento == 'texto_correo' && $request->filled('texto_correo')) {
            $pdfCorreo = Pdf::loadView('auth.plantilla_pdf', [
                'texto' => $request->texto_correo,
                'fecha' => $request->fecha,
                'hora' => $request->hora,
                'ccaa' => $nombreCcaa,
                'provincias' => $textoProvincias,
            ]);

            $fechaPdf = \Carbon\Carbon::parse($request->fecha)->format('Y-m-d');
            $horaPdf = preg_replace('/[^0-9]/', '', (string) $request->hora);
            $horaPdf = $horaPdf !== '' ? $horaPdf : now()->format('His');
            $ccaaSlug = \Illuminate\Support\Str::slug($nombreCcaa, '_');
            $nombrePdf = $fechaPdf.'_'.$horaPdf.'_'.$ccaaSlug.'.pdf';
            Storage::disk('public')->put('pdf_emergencia/'.$nombrePdf, $pdfCorreo->output());
            $rutaPdf = 'pdf_emergencia/'.$nombrePdf;
        }

        $nombreProvinciaPorId = $provinciasDeLaCcaa
            ->pluck('p_provincia', 'p_id')
            ->mapWithKeys(fn ($nombre, $id) => [(int) $id => $nombre])
            ->all();

        DB::transaction(function () use ($request, $rutaPdf, $provinciasIds, $nombreProvinciaPorId, $textoProvincias) {
            foreach ($provinciasIds as $provId) {
                $nombreProvincia = $provId === null
                    ? $textoProvincias
                    : ($nombreProvinciaPorId[(int) $provId] ?? null);

                SituacionEmergencia::create([
                    'ccaa_id' => $request->ccaa_id,
                    'provincia' => $nombreProvincia,
                    'provincia_id' => $provId,
                    'nivel' => $request->nivel,
                    'fecha' => $request->fecha,
                    'hora' => $request->hora,
                    'descripcion' => $request->descripcion,
                    'ruta_pdf' => $rutaPdf,
                    'usuario_id' => session('id') ?? 1,
                ]);
            }
        });

        if ($request->input('return_to') === 'plan') {
            return redirect()->route('emergencias.vistaPlan')
                ->with('success', 'Situación de emergencia registrada correctamente');
        }

        return redirect()->back()->with('success', 'Situación de emergencia registrada correctamente');
    }

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

        try {
            $estadoActualSyncService->sincronizarCaches();
        } catch (Throwable $e) {
            Log::error('No se pudo refrescar la cache de estado actual desde la web', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function getEstadoActualPorCCAA($id, EstadoActualService $estadoActualSyncService)
    {
        $this->sincronizarEstadoActualSiHaceFalta($estadoActualSyncService);

        $nombreComunidad = DB::table('umbrales_ccaa')->where('c_id', $id)->value('c_comunidad_autonoma')
            ?? 'la comunidad seleccionada';

        $estaciones = Cache::get('api_estado_actual_ccaa_'.(int) $id, collect())
            ->sortByDesc('alerta')
            ->values();

        $resultadoFinal = $estaciones->isNotEmpty()
            ? collect([strtoupper($nombreComunidad) => $estaciones])
            : collect();

        return view('auth.vista_estadoActual', [
            'resultadoFinal' => $resultadoFinal,
            'nombreComunidadSinAlertas' => $nombreComunidad,
            'ultimaSincronizacion' => Cache::get('api_estado_actual_sync_at'),
        ]);
    }

    public function getEstadoActualDatos(EstadoActualService $estadoActualSyncService)
    {
        $this->sincronizarEstadoActualSiHaceFalta($estadoActualSyncService);

        $resultadoFinal = Cache::get('api_estado_actual_global', collect());

        return view('auth.vista_estadoActual', [
            'resultadoFinal' => $resultadoFinal,
            'nombreComunidadSinAlertas' => 'la Cuenca del Tajo',
            'ultimaSincronizacion' => Cache::get('api_estado_actual_sync_at'),
        ]);
    }

    public function vistaPlanEmergencia()
    {
        $ahora = \Carbon\Carbon::now();
        $meses = [$ahora->copy()->subMonth()->startOfMonth(), $ahora->copy()->startOfMonth()];
        $hoy = $ahora->format('Y-m-d');

        $ccaa = \App\Models\UmbralesCcaa::with('provincias')
            ->where('c_comunidad_autonoma', 'NOT ILIKE', '%Arag%')
            ->where('c_comunidad_autonoma', 'NOT ILIKE', '%Portugal%')
            ->where('c_comunidad_autonoma', 'NOT ILIKE', '%AY%')
            ->get();

        $registros = \App\Models\SituacionEmergencia::orderBy('fecha', 'asc')->orderBy('hora', 'asc')->get();

        $nombresNiveles = [
            0 => 'Normalidad',
            1 => 'Preemergencia',
            2 => 'Situación 0',
            3 => 'Situación 1',
            4 => 'Situación 2',
            5 => 'Situación 3',
        ];

        $matrizColores = [];

        foreach ($ccaa as $comunidad) {
            $registrosCcaa = $registros->filter(fn ($registro) => $registro->ccaa_id == $comunidad->c_id);

            foreach ($meses as $mes) {
                for ($dia = 1; $dia <= $mes->daysInMonth; $dia++) {
                    $fecha = $mes->copy()->day($dia)->format('Y-m-d');

                    if ($fecha > $hoy) {
                        continue;
                    }

                    $zonas = [null];
                    foreach ($comunidad->provincias as $provincia) {
                        $zonas[] = $provincia->p_id;
                    }

                    foreach ($zonas as $zonaId) {

                        $historialPrevio = $registrosCcaa->filter(function ($registro) use ($fecha, $zonaId) {
                            $fechaRegistro = substr((string) $registro->fecha, 0, 10);

                            return $registro->provincia_id == $zonaId && $fechaRegistro < $fecha;
                        })->sortByDesc(function ($registro) {
                            $fechaRegistro = substr((string) $registro->fecha, 0, 10);
                            $horaRegistro = trim((string) ($registro->hora ?? '00:00:00'));

                            return $fechaRegistro.' '.$horaRegistro;
                        });

                        $estadoAnterior = $historialPrevio->first();

                        $eventosHoy = $registrosCcaa->filter(function ($registro) use ($fecha, $zonaId) {
                            $fechaRegistro = substr((string) $registro->fecha, 0, 10);

                            return $registro->provincia_id == $zonaId && $fechaRegistro == $fecha;
                        })->sortBy('hora');

                        $ultimoEstado = $eventosHoy->last() ?? $estadoAnterior;

                        if ($ultimoEstado && $ultimoEstado->nivel > 0) {
                            $huboCambio = false;
                            $contadorEventos = 0;

                            if ($eventosHoy->count() > 0) {
                                $huboCambio = ($estadoAnterior && $estadoAnterior->nivel > 0) || ($eventosHoy->count() > 1);

                                if ($estadoAnterior && $estadoAnterior->nivel > 0) {
                                    $contadorEventos++;
                                }
                                $contadorEventos += $eventosHoy->count();

                                $historialDia = '<b>HISTORIAL DEL DÍA:</b><br><br>';

                                if ($estadoAnterior && $estadoAnterior->nivel > 0) {
                                    $nombreNivel = $nombresNiveles[$estadoAnterior->nivel] ?? '';

                                    $textoDesc = strpos($estadoAnterior->descripcion, ':') !== false
                                        ? trim(explode(':', $estadoAnterior->descripcion, 2)[1])
                                        : $estadoAnterior->descripcion;

                                    $fechaOriginal = \Carbon\Carbon::parse($estadoAnterior->fecha)->format('d/m/Y');
                                    $horaOriginal = \Carbon\Carbon::parse($estadoAnterior->hora)->format('H:i');

                                    $historialDia .= '<b>'.$nombreNivel.'</b> <span><i>Inicio: '.$fechaOriginal.' ('.$horaOriginal.')</i></span><br>'.$textoDesc.'<br>';

                                    if ($estadoAnterior->ruta_pdf) {
                                        $historialDia .= "<a href='/storage/".$estadoAnterior->ruta_pdf."' target='_blank' style='display:inline-block; margin-top:6px; padding:6px 12px; border:1px solid #d9534f; border-radius:5px; background-color:#fef2f2; color:#d9534f; font-size:0.85em; font-weight:bold; text-decoration:none;'>Ver / Descargar PDF {$fechaOriginal}</a><br>";
                                    }
                                    $historialDia .= "<hr style='border-top:1px dashed #ccc; margin: 10px 0;'>";
                                }

                                foreach ($eventosHoy as $evento) {
                                    $nombreNivel = $nombresNiveles[$evento->nivel] ?? '';

                                    $textoDesc = strpos($evento->descripcion, ':') !== false
                                        ? trim(explode(':', $evento->descripcion, 2)[1])
                                        : $evento->descripcion;

                                    $historialDia .= '<b>'.\Carbon\Carbon::parse($evento->hora)->format('H:i').'h - '.$nombreNivel.'</b><br>'.$textoDesc.'<br>';

                                    if ($evento->ruta_pdf) {
                                        $historialDia .= "<a href='/storage/".$evento->ruta_pdf."' target='_blank' style='display:inline-block; margin-top:6px; padding:6px 12px; border:1px solid #d9534f; border-radius:5px; background-color:#fef2f2; color:#d9534f; font-size:0.85em; font-weight:bold; text-decoration:none;'>Ver / Descargar PDF</a><br>";
                                    }
                                    $historialDia .= '<br>';
                                }
                            } else {
                                $fechaOriginal = \Carbon\Carbon::parse($estadoAnterior->fecha)->format('d/m/Y');
                                $horaOriginal = \Carbon\Carbon::parse($estadoAnterior->hora)->format('H:i');

                                $textoDesc = strpos($estadoAnterior->descripcion, ':') !== false
                                    ? trim(explode(':', $estadoAnterior->descripcion, 2)[1])
                                    : $estadoAnterior->descripcion;

                                $historialDia = '<span>(Situación iniciada el '.$fechaOriginal.' a las '.$horaOriginal.'h)</span><br><br>'.$textoDesc;
                            }

                            $claveZona = $zonaId === null ? 'global' : $zonaId;

                            $matrizColores[$comunidad->c_id][$claveZona][$fecha] = [
                                'nivel' => $ultimoEstado->nivel,
                                'hora' => $ultimoEstado->hora,
                                'desc' => $historialDia,
                                'pdf' => $ultimoEstado->ruta_pdf,
                                'cambio' => $huboCambio,
                                'num_eventos' => $contadorEventos,
                            ];
                        }
                    }
                }
            }
        }

        return view('auth.vista_PlanEmergencia', compact('ccaa', 'meses', 'matrizColores', 'hoy'));
    }
}
