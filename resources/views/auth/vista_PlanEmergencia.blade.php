@extends('auth.plantilla')

@section('contenido')
    @php
        $mesesPreparados = collect($meses)
            ->values()
            ->map(function ($mes, $indice) {
                $mesCarbon = null;
                if ($mes instanceof \Carbon\CarbonInterface) {
                    $mesCarbon = $mes->copy();
                } elseif ($mes instanceof \DateTimeInterface) {
                    $mesCarbon = \Carbon\Carbon::instance($mes);
                } elseif (is_string($mes)) {
                    $marcaTiempoMes = strtotime($mes);
                    if ($marcaTiempoMes !== false) {
                        $mesCarbon = \Carbon\Carbon::createFromTimestamp($marcaTiempoMes);
                    }
                }

                return [
                    'carbon' => $mesCarbon,
                    'dias' => $mesCarbon ? $mesCarbon->daysInMonth : 1,
                    'titulo' => $mesCarbon ? strtoupper($mesCarbon->translatedFormat('F Y')) : 'FECHA NO DISPONIBLE',
                    'clase' => $indice % 2 === 0 ? 'mes-impar' : 'mes-par',
                ];
            });
    @endphp

    <style>
        :root {
            --n0: #ffffff;
            --n1: #eeee7b;
            --n2: #ffff00;
            --n3: #ffbf00;
            --n4: #ff0000;
            --n5: #000000;
        }

        .panelPlan {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .cabeceraPlan {
            border: 1px solid #dbe4ef;
            border-radius: 10px;
            padding: 16px 18px;
            background: linear-gradient(135deg, #f8fbff 0%, #eef2ff 100%);
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
        }

        .cabeceraPlan h2 {
            margin: 0;
            color: #1e293b;
            font-size: 1.2rem;
        }

        .cabeceraPlan p {
            margin: 4px 0 0;
            color: #64748b;
            font-size: 0.8rem;
        }

        .mesesResumen {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .mesPill {
            border-radius: 999px;
            padding: 5px 11px;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.35px;
            border: 1px solid transparent;
        }

        .mesPill.mes-impar {
            background: #e7f3ec;
            border-color: #c4dfcf;
            color: #1f5138;
        }

        .mesPill.mes-par {
            background: #fff1e0;
            border-color: #ffd8a8;
            color: #9a3412;
        }

        .leyendas {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .leyenda {
            border-radius: 999px;
            font-size: 0.69rem;
            font-weight: 700;
            letter-spacing: 0.2px;
            padding: 3px 8px;
            border: 1px solid transparent;
        }

        .l0 {
            background: #f8fafc;
            color: #334155;
            border-color: #dbe2ea;
        }

        .l1 {
            background: var(--n1);
            color: #4b5563;
            border-color: #f3e17c;
        }

        .l2 {
            background: var(--n2);
            color: #374151;
            border-color: #f8e65a;
        }

        .l3 {
            background: var(--n3);
            color: #4a2f00;
            border-color: #f1ae2a;
        }

        .l4 {
            background: var(--n4);
            color: #ffffff;
            border-color: #d42a2a;
        }

        .l5 {
            background: var(--n5);
            color: #ffffff;
            border-color: #111827;
        }

        .matrizContainer {
            overflow: auto;
            border: 1px solid #cfd8e3;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
            background: #ffffff;
            max-height: calc(100vh - 230px);
        }

        .matriz {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 12px;
            table-layout: fixed;
        }

        .matriz th,
        .matriz td {
            border-right: 1px solid #d6dee8;
            border-bottom: 1px solid #d6dee8;
            height: 31px;
            text-align: center;
            min-width: 28px;
            position: relative;
        }

        .matriz thead th {
            position: sticky;
            top: 0;
            z-index: 9;
        }

        .colNombre {
            width: 250px;
            min-width: 250px;
            text-align: left;
            padding: 0 10px;
            position: sticky;
            left: 0;
            z-index: 11 !important;
            border-right: 2px solid #7a8798 !important;
            box-shadow: 6px 0 8px -8px rgba(15, 23, 42, 0.28);
        }

        .cabeceraNombre {
            background: #1f2937;
            color: #ffffff;
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        .cabeceraMes {
            font-weight: 700;
            letter-spacing: 0.55px;
            font-size: 0.72rem;
            text-transform: uppercase;
        }

        .cabeceraMes.mes-impar {
            background: linear-gradient(180deg, #dbefe4 0%, #c7e4d4 100%);
            color: #1f5138;
        }

        .cabeceraMes.mes-par {
            background: linear-gradient(180deg, #ffedd5 0%, #ffe1bd 100%);
            color: #9a3412;
        }

        .diaMes {
            font-size: 0.68rem;
            font-weight: 700;
            color: #334155;
            background: #f8fafc;
        }

        .diaMes.mes-impar {
            background: #eef8f2;
            color: #1f5138;
        }

        .diaMes.mes-par {
            background: #fff4e8;
            color: #9a3412;
        }

        .diaMes.finSemana {
            background-image: linear-gradient(180deg, rgba(15, 23, 42, 0.05), rgba(15, 23, 42, 0.02));
        }

        .inicioMes {
            border-left: 3px solid #475569 !important;
        }

        .finMes {
            border-right: 3px solid #475569 !important;
        }

        .filaComunidad {
            background: #e8edf4;
            font-weight: 700;
            border-top: 2px solid #6b7686;
        }

        .filaComunidad .colNombre {
            background: #dce5ef;
            color: #0f172a;
        }

        .filaProvincia {
            background: #ffffff;
        }

        .filaProvincia .colNombre {
            background: #f7f9fc;
            color: #334155;
        }

        .sangriaProvincia {
            padding-left: 30px !important;
            font-weight: 500;
        }

        .mes-impar.c0 {
            background: #f5fbf7;
        }

        .mes-par.c0 {
            background: #fffaf4;
        }

        .c0 {
            background-color: var(--n0);
        }

        .c1 {
            background-color: var(--n1);
            color: #000;
        }

        .c2 {
            background-color: var(--n2);
            color: #000;
        }

        .c3 {
            background-color: var(--n3);
            color: #000;
        }

        .c4 {
            background-color: var(--n4);
            color: #fff;
        }

        .c5 {
            background-color: var(--n5);
            color: #fff;
        }

        .matriz tbody td {
            transition: filter 0.16s ease, box-shadow 0.16s ease;
        }

        .celdaAccion {
            cursor: pointer;
        }

        .c0.celdaAccion::after {
            content: '+';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -55%);
            font-size: 0.8rem;
            color: rgba(71, 85, 105, 0.42);
            font-weight: 700;
            opacity: 0;
            transition: opacity 0.16s ease;
        }

        .c0.celdaAccion:hover::after {
            opacity: 1;
        }

        .rastroHorizontal::after,
        .rastroVertical::after {
            content: '';
            position: absolute;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.12);
            pointer-events: none;
            z-index: 5;
        }

        .rastroHorizontal::after {
            box-shadow: inset 0 2px 0 rgba(0, 0, 0, 0.5), inset 0 -2px 0 rgba(0, 0, 0, 0.5);
        }

        .rastroVertical::after {
            box-shadow: inset 2px 0 0 rgba(0, 0, 0, 0.5), inset -2px 0 0 rgba(0, 0, 0, 0.5);
        }

        .celdaFoco {
            box-shadow: inset 0 0 0 3px #0f172a !important;
            filter: brightness(1.1);
            z-index: 10 !important;
            cursor: crosshair;
        }

        .encabezadoFoco {
            background-color: #1f2937 !important;
            color: #fff !important;
            font-weight: 700;
            box-shadow: inset 0 0 0 2px #000;
            transform: scale(1.05);
            z-index: 10;
            position: relative;
        }

        .celdaClicable {
            cursor: pointer !important;
        }

        .celdaClicable:hover {
            filter: brightness(0.86) !important;
            box-shadow: inset 0 0 0 3px rgba(255, 255, 255, 0.95) !important;
        }

        .marcadorCambio::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 0 12px 12px 0;
            border-color: transparent #334155 transparent transparent;
            z-index: 3;
        }

        .modalOculto {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(15, 23, 42, 0.62);
            backdrop-filter: blur(2px);
        }

        .modalContenido {
            background-color: #ffffff;
            margin: 7% auto;
            padding: 24px;
            border: 1px solid #d4dde8;
            width: 470px;
            max-width: 92vw;
            border-radius: 10px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.25);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .cerrarModalBoton {
            color: #9aa5b1;
            float: right;
            font-size: 28px;
            font-weight: 700;
            cursor: pointer;
            line-height: 1;
        }

        .cerrarModalBoton:hover {
            color: #0f172a;
        }

        .modalSubtitulo {
            color: #475569;
            font-size: 0.88em;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 10px;
            margin-top: 8px;
        }

        .modalCajaDescripcion {
            background: #f8fafc;
            padding: 14px;
            border-left: 4px solid #334155;
            margin-top: 14px;
            border-radius: 5px;
            max-height: 250px;
            overflow-y: auto;
            color: #1e293b;
            font-size: 0.9rem;
        }

        .botonPdf {
            display: inline-block;
            background-color: #dc2626;
            color: #ffffff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 700;
            transition: background 0.2s ease;
        }

        .botonPdf:hover {
            background-color: #b91c1c;
            color: #ffffff;
        }

        .grupoAccionesModal {
            margin-top: 16px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .botonAccion {
            border: 0;
            border-radius: 7px;
            padding: 9px 12px;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
        }

        .botonPrimario {
            background: #2563eb;
            color: #ffffff;
        }

        .botonPrimario:hover {
            background: #1d4ed8;
            color: #ffffff;
        }

        .botonSecundario {
            background: #e2e8f0;
            color: #1e293b;
        }

        .botonSecundario:hover {
            background: #cbd5e1;
            color: #1e293b;
        }

        .botonGhost {
            background: #f8fafc;
            color: #475569;
            border: 1px solid #cbd5e1;
        }

        .botonGhost:hover {
            background: #f1f5f9;
            color: #334155;
        }

        .mensajeEstadoModal {
            margin-top: 10px;
            padding: 8px 10px;
            border-radius: 6px;
            font-size: 0.8rem;
            border: 1px dashed #cbd5e1;
            color: #475569;
            background: #f8fafc;
        }

        .bloquePdfModal {
            margin-top: 12px;
            padding: 10px;
            border: 1px solid #dbeafe;
            background: #f8fbff;
            border-radius: 6px;
        }

        .enlacePdfModal {
            display: inline-block;
            text-decoration: none;
            color: #1d4ed8;
            font-weight: 600;
            font-size: 0.82rem;
        }

        .enlacePdfModal:hover {
            text-decoration: underline;
            color: #1e40af;
        }

        .toastMovil {
            --toast-duracion: 300ms;
            position: fixed;
            top: 76px;
            right: 18px;
            z-index: 1400;
            min-width: 270px;
            max-width: 380px;
            background: #ffffff;
            color: #1f2937;
            border-radius: 8px;
            padding: 11px 12px 10px;
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.12);
            border: 1px solid #d1d5db;
            border-left: 4px solid #16a34a;
            opacity: 0;
            transform: translateY(-6px);
            transition: all 0.12s ease;
            pointer-events: none;
        }

        .toastMovil.visible {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .toastMovil.toastAdvertencia {
            --toast-duracion: 1000ms;
            border-left-color: #d97706;
        }

        .toastMovil.toastAdvertencia .toastTitulo {
            color: #92400e;
        }

        .toastCabecera {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 6px;
        }

        .toastTitulo {
            font-size: 0.79rem;
            font-weight: 700;
            letter-spacing: 0.2px;
            color: #166534;
        }

        .toastCerrar {
            border: 0;
            background: transparent;
            color: #64748b;
            font-size: 1rem;
            line-height: 1;
            cursor: pointer;
            padding: 2px 4px;
            border-radius: 4px;
        }

        .toastCerrar:hover {
            color: #334155;
            background: #f1f5f9;
        }

        .toastTexto {
            font-size: 0.82rem;
            line-height: 1.35;
            color: #334155;
        }

        .toastBarra {
            margin-top: 9px;
            width: 100%;
            height: 3px;
            border-radius: 999px;
            background: #e5e7eb;
            overflow: hidden;
        }

        .toastBarra::after {
            content: '';
            display: block;
            width: 100%;
            height: 100%;
            background: #16a34a;
            animation: toastTiempo var(--toast-duracion) linear forwards;
        }

        .toastMovil.toastAdvertencia .toastBarra::after {
            background: #d97706;
        }

        @keyframes toastTiempo {
            from { width: 100%; }
            to { width: 0%; }
        }

        @media (max-width: 980px) {
            .matrizContainer {
                max-height: calc(100vh - 280px);
            }

            .cabeceraPlan {
                align-items: flex-start;
            }

            .colNombre {
                width: 220px;
                min-width: 220px;
            }
        }
    </style>

    @if (session('success'))
        <div id="toastExitoPlan" class="toastMovil" role="status" aria-live="polite">
            <div class="toastCabecera">
                <span class="toastTitulo">Cambios guardados correctamente</span>
                <button type="button" class="toastCerrar" onclick="cerrarToastPlan()">&times;</button>
            </div>
            <div class="toastTexto">{{ session('success') }}</div>
            <div class="toastBarra"></div>
        </div>
    @endif

    <div class="panelPlan">
        <div class="cabeceraPlan">
            <div>
                <h2>PLAN DE EMERGENCIA</h2>
                </p>
            </div>

            <div class="mesesResumen">
                @foreach ($mesesPreparados as $mes)
                    <span class="mesPill {{ $mes['clase'] }}">{{ $mes['titulo'] }}</span>
                @endforeach
            </div>
        </div>

        <div class="leyendas">
            <span class="leyenda l0">Normalidad</span>
            <span class="leyenda l1">Preemergencia / Alerta</span>
            <span class="leyenda l2">Situación 0</span>
            <span class="leyenda l3">Situación 1</span>
            <span class="leyenda l4">Situación 2</span>
            <span class="leyenda l5">Situación 3</span>
        </div>

        <div class="matrizContainer">
            <table class="matriz">
                <thead>
                    <tr>
                        <th class="colNombre cabeceraNombre" rowspan="2">ZONA / PROVINCIA</th>
                        @foreach ($mesesPreparados as $mes)
                            <th class="cabeceraMes {{ $mes['clase'] }}" colspan="{{ $mes['dias'] }}">{{ $mes['titulo'] }}
                            </th>
                        @endforeach
                    </tr>

                    <tr>
                        @foreach ($mesesPreparados as $mes)
                            @for ($dia = 1; $dia <= $mes['dias']; $dia++)
                                @php
                                    $esFinSemana = false;
                                    if ($mes['carbon']) {
                                        $numeroDiaSemana = (int) $mes['carbon']->copy()->day($dia)->dayOfWeek;
                                        $esFinSemana = in_array($numeroDiaSemana, [0, 6], true);
                                    }
                                    $claseDia = trim($mes['clase'] . ' diaMes ' . ($esFinSemana ? 'finSemana' : ''));
                                    if ($dia === 1) {
                                        $claseDia .= ' inicioMes';
                                    }
                                    if ($dia === $mes['dias']) {
                                        $claseDia .= ' finMes';
                                    }
                                @endphp
                                <th class="{{ $claseDia }}">{{ $dia }}</th>
                            @endfor
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @foreach ($ccaa as $comunidad)
                        @php
                            $esComunidadSimple =
                                $comunidad->c_comunidad_autonoma == 'Madrid' ||
                                $comunidad->c_comunidad_autonoma == 'Portugal' ||
                                $comunidad->c_comunidad_autonoma == 'Ayto. Madrid';
                        @endphp

                        <tr class="filaComunidad">
                            <td class="colNombre">
                                {{ strtoupper($comunidad->c_comunidad_autonoma) }}
                                @if ($comunidad->plan_emergencia)
                                    <span style="color: #1f2937;">{{ '|  ' . $comunidad->plan_emergencia }}</span>
                                @endif
                            </td>

                            @foreach ($mesesPreparados as $mes)
                                @for ($dia = 1; $dia <= $mes['dias']; $dia++)
                                    @php
                                        $fechaDia = $mes['carbon']
                                            ? $mes['carbon']->copy()->day($dia)->format('Y-m-d')
                                            : 'fecha-no-disponible';
                                        $informacionCelda =
                                            $matrizColores[$comunidad->c_id]['global'][$fechaDia] ?? null;
                                        $nivelAlerta = $informacionCelda ? (int) $informacionCelda['nivel'] : 0;
                                        $claseCelda = $mes['clase'] . ' c' . $nivelAlerta . ' celdaAccion';
                                        if ($informacionCelda) {
                                            $claseCelda .= ' celdaClicable';
                                        }
                                        if (isset($informacionCelda['cambio']) && $informacionCelda['cambio']) {
                                            $claseCelda .= ' marcadorCambio';
                                        }
                                        if ($dia === 1) {
                                            $claseCelda .= ' inicioMes';
                                        }
                                        if ($dia === $mes['dias']) {
                                            $claseCelda .= ' finMes';
                                        }
                                    @endphp
                                    <td class="{{ trim($claseCelda) }}" data-fecha="{{ $fechaDia }}"
                                        data-zona="{{ $comunidad->c_comunidad_autonoma }}"
                                        data-hora="{{ $informacionCelda['hora'] ?? '' }}"
                                        data-desc="{{ $informacionCelda['desc'] ?? 'No hay descripción disponible' }}"
                                        data-pdf="{{ $informacionCelda['pdf'] ?? '' }}" data-nivel="{{ $nivelAlerta }}"
                                        data-ccaa-id="{{ $comunidad->c_id }}" data-provincia-id="" data-es-global="1"
                                        data-tiene-evento="{{ $informacionCelda ? 1 : 0 }}"
                                        data-num-eventos="{{ $informacionCelda['num_eventos'] ?? 0 }}">
                                    </td>
                                @endfor
                            @endforeach
                        </tr>

                        @if (!$esComunidadSimple)
                            @foreach ($comunidad->provincias as $provincia)
                                <tr class="filaProvincia">
                                    <td class="colNombre sangriaProvincia">{{ $provincia->p_provincia }}</td>

                                    @foreach ($mesesPreparados as $mes)
                                        @for ($dia = 1; $dia <= $mes['dias']; $dia++)
                                            @php
                                                $fechaDia = $mes['carbon']
                                                    ? $mes['carbon']->copy()->day($dia)->format('Y-m-d')
                                                    : 'fecha-no-disponible';
                                                $informacionCelda =
                                                    $matrizColores[$comunidad->c_id][$provincia->p_id][$fechaDia] ??
                                                    null;
                                                $nivelAlerta = $informacionCelda ? (int) $informacionCelda['nivel'] : 0;
                                                $claseCelda = $mes['clase'] . ' c' . $nivelAlerta . ' celdaAccion';
                                                if ($informacionCelda) {
                                                    $claseCelda .= ' celdaClicable';
                                                }
                                                if (isset($informacionCelda['cambio']) && $informacionCelda['cambio']) {
                                                    $claseCelda .= ' marcadorCambio';
                                                }
                                                if ($dia === 1) {
                                                    $claseCelda .= ' inicioMes';
                                                }
                                                if ($dia === $mes['dias']) {
                                                    $claseCelda .= ' finMes';
                                                }
                                            @endphp
                                            <td class="{{ trim($claseCelda) }}" data-fecha="{{ $fechaDia }}"
                                                data-zona="{{ $provincia->p_provincia }}"
                                                data-hora="{{ $informacionCelda['hora'] ?? '' }}"
                                                data-desc="{{ $informacionCelda['desc'] ?? 'No hay descripción disponible' }}"
                                                data-pdf="{{ $informacionCelda['pdf'] ?? '' }}"
                                                data-nivel="{{ $nivelAlerta }}" data-ccaa-id="{{ $comunidad->c_id }}"
                                                data-provincia-id="{{ $provincia->p_id }}" data-es-global="0"
                                                data-tiene-evento="{{ $informacionCelda ? 1 : 0 }}"
                                                data-num-eventos="{{ $informacionCelda['num_eventos'] ?? 0 }}">
                                            </td>
                                        @endfor
                                    @endforeach
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div id="modalDetalles" class="modalOculto">
        <div class="modalContenido">
            <h2 id="modalZona">ZONA</h2>
            <p class="modalSubtitulo">
                Fecha: <span id="modalFecha"></span> | Hora de la situación: <span id="modalHora"></span>
            </p>

            <div class="modalCajaDescripcion">
                <strong>Descripción de la situación:</strong>
                <p id="modalDescripcion"></p>
            </div>

            <div class="mensajeEstadoModal" id="modalInfoEventos"></div>

            <div id="modalBloquePdf" class="bloquePdfModal" style="display: none;">
                <a id="modalEnlacePdf" href="#" target="_blank" rel="noopener" class="enlacePdfModal">
                    Ver documento PDF asociado
                </a>
            </div>

            <div class="grupoAccionesModal">
                @if (session('is_staff') || session('is_superuser'))
                    <a id="botonEditarDesdeDetalle" href="#" class="botonAccion botonPrimario">Añadir emergencia a este día</a>
                @endif
                <button type="button" class="botonAccion botonGhost" onclick="cerrarModal()">Cerrar</button>
            </div>
        </div>
    </div>

    <div id="modalAcciones" class="modalOculto">
        <div class="modalContenido">
            <h2 id="modalAccionesZona">Nueva situación</h2>
            <p class="modalSubtitulo">
                Fecha seleccionada: <span id="modalAccionesFecha"></span>
            </p>

            <div class="modalCajaDescripcion">
                <strong>No hay ninguna emergencia registrada para este día.</strong>
            </div>

            <div class="grupoAccionesModal">
                @if (session('is_staff') || session('is_superuser'))
                    <a id="botonAccionAgregar" href="#" class="botonAccion botonPrimario">Añadir emergencia</a>
                @endif
                <button type="button" class="botonAccion botonGhost" onclick="cerrarModalAcciones()">Cerrar</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const TOAST_DURACION_MS = 300;
            const TOAST_DURACION_AVISO_MS = 1000;
            const toast = document.getElementById('toastExitoPlan');
            if (toast) {
                requestAnimationFrame(() => toast.classList.add('visible'));
                setTimeout(() => cerrarToastPlan(), TOAST_DURACION_MS);
            }

            const tablaMatriz = document.querySelector('.matriz');
            const cuerpoTabla = tablaMatriz.querySelector('tbody');
            const encabezadoDias = tablaMatriz.querySelector('thead tr:nth-child(2)');
            const rutaFormulario = @json(route('emergencias.crear'));
            const puedeEditarEmergencias = @json((bool) (session('is_staff') || session('is_superuser')));

            function construirUrlFormulario(celda, modo = 'crear') {
                const parametros = new URLSearchParams();
                if (celda.dataset.ccaaId) parametros.set('ccaa_id', celda.dataset.ccaaId);
                if (celda.dataset.provinciaId) parametros.set('provincia_id', celda.dataset.provinciaId);
                if (celda.dataset.esGlobal === '1') parametros.set('alcance', 'global');
                if (celda.dataset.fecha) parametros.set('fecha', celda.dataset.fecha);
                if (celda.dataset.hora) parametros.set('hora', celda.dataset.hora);
                const nivelActual = Number(celda.dataset.nivel || '0');
                const nivelSugerido = (modo === 'crear' && nivelActual === 0) ? 1 : nivelActual;
                parametros.set('nivel', String(nivelSugerido));
                parametros.set('modo', modo);
                parametros.set('zona', celda.dataset.zona || '');
                parametros.set('return_to', 'plan');
                return `${rutaFormulario}?${parametros.toString()}`;
            }

            function esFechaFutura(fechaTexto) {
                if (!/^\d{4}-\d{2}-\d{2}$/.test(fechaTexto || '')) return false;
                const [anio, mes, dia] = fechaTexto.split('-').map(Number);
                const fechaSeleccionada = new Date(anio, mes - 1, dia);
                const hoy = new Date();
                hoy.setHours(0, 0, 0, 0);
                return fechaSeleccionada > hoy;
            }

            function mostrarToastAvisoPlan(mensaje) {
                let toastAviso = document.getElementById('toastAvisoPlan');
                if (!toastAviso) {
                    toastAviso = document.createElement('div');
                    toastAviso.id = 'toastAvisoPlan';
                    toastAviso.className = 'toastMovil toastAdvertencia';
                    toastAviso.setAttribute('role', 'status');
                    toastAviso.setAttribute('aria-live', 'polite');
                    toastAviso.innerHTML = `
                        <div class="toastCabecera">
                            <span class="toastTitulo">Aviso</span>
                            <button type="button" class="toastCerrar" onclick="cerrarToastPlan('toastAvisoPlan')">&times;</button>
                        </div>
                        <div class="toastTexto"></div>
                        <div class="toastBarra"></div>
                    `;
                    document.body.appendChild(toastAviso);
                }

                toastAviso.querySelector('.toastTexto').textContent = mensaje;
                toastAviso.classList.remove('visible');
                void toastAviso.offsetWidth;
                toastAviso.classList.add('visible');

                if (window.temporizadorToastAvisoPlan) {
                    clearTimeout(window.temporizadorToastAvisoPlan);
                }
                window.temporizadorToastAvisoPlan = setTimeout(() => cerrarToastPlan('toastAvisoPlan'),
                    TOAST_DURACION_AVISO_MS);
            }

            tablaMatriz.addEventListener('mouseover', function(evento) {
                if (evento.target.tagName === 'TD' && evento.target.closest('tbody')) {
                    const celdaActual = evento.target;
                    const filaActual = celdaActual.parentElement;

                    document.querySelectorAll(
                            '.rastroHorizontal, .rastroVertical, .celdaFoco, .encabezadoFoco')
                        .forEach(elemento => {
                            elemento.classList.remove('rastroHorizontal', 'rastroVertical', 'celdaFoco',
                                'encabezadoFoco');
                        });

                    const indiceColumna = Array.from(filaActual.children).indexOf(celdaActual);
                    const indiceFila = Array.from(cuerpoTabla.children).indexOf(filaActual);
                    celdaActual.classList.add('celdaFoco');

                    for (let indice = 0; indice < indiceColumna; indice++) {
                        filaActual.children[indice].classList.add('rastroHorizontal');
                    }

                    const filasCuerpo = cuerpoTabla.children;
                    for (let indice = 0; indice < indiceFila; indice++) {
                        if (filasCuerpo[indice].children[indiceColumna]) {
                            filasCuerpo[indice].children[indiceColumna].classList.add('rastroVertical');
                        }
                    }

                    if (indiceColumna > 0) {
                        const encabezadoDia = encabezadoDias.children[indiceColumna - 1];
                        if (encabezadoDia) encabezadoDia.classList.add('encabezadoFoco');
                    }
                }
            });

            tablaMatriz.addEventListener('mouseleave', function() {
                document.querySelectorAll('.rastroHorizontal, .rastroVertical, .celdaFoco, .encabezadoFoco')
                    .forEach(elemento => {
                        elemento.classList.remove('rastroHorizontal', 'rastroVertical', 'celdaFoco',
                            'encabezadoFoco');
                    });
            });

            document.addEventListener('click', function(evento) {
                const celdaSeleccionada = evento.target.closest('td[data-fecha]');
                if (celdaSeleccionada) {
                    if (esFechaFutura(celdaSeleccionada.dataset.fecha)) {
                        cerrarModal();
                        cerrarModalAcciones();
                        mostrarToastAvisoPlan('No se puede seleccionar una fecha futura.');
                        return;
                    }

                    const hayEvento = celdaSeleccionada.dataset.tieneEvento === '1';

                    if (!hayEvento) {
                        if (!puedeEditarEmergencias) {
                            cerrarModal();
                            cerrarModalAcciones();
                            mostrarToastAvisoPlan('No tienes permisos para añadir emergencias.');
                            return;
                        }

                        cerrarModal();
                        document.getElementById('modalAccionesZona').innerText = celdaSeleccionada.dataset
                            .zona ||
                            'Zona sin emergencia';
                        document.getElementById('modalAccionesFecha').innerText = celdaSeleccionada.dataset
                            .fecha || '--';
                        const botonAccionAgregar = document.getElementById('botonAccionAgregar');
                        if (botonAccionAgregar) {
                            botonAccionAgregar.href = construirUrlFormulario(celdaSeleccionada, 'crear');
                        }
                        document.getElementById('modalAcciones').style.display = 'block';
                        return;
                    }

                    cerrarModalAcciones();
                    document.getElementById('modalZona').innerText = celdaSeleccionada.dataset.zona;
                    document.getElementById('modalFecha').innerText = celdaSeleccionada.dataset.fecha;
                    document.getElementById('modalHora').innerText = celdaSeleccionada.dataset.hora ||
                        '--:--';
                    document.getElementById('modalDescripcion').innerHTML = celdaSeleccionada.dataset.desc;
                    document.getElementById('modalInfoEventos').innerText =
                        `Eventos ocurridos en el dia: ${celdaSeleccionada.dataset.numEventos || 1}`;
                    const botonEditarDesdeDetalle = document.getElementById('botonEditarDesdeDetalle');
                    if (botonEditarDesdeDetalle && puedeEditarEmergencias) {
                        botonEditarDesdeDetalle.href = construirUrlFormulario(celdaSeleccionada, 'crear');
                        botonEditarDesdeDetalle.style.display = 'inline-flex';
                    } else if (botonEditarDesdeDetalle) {
                        botonEditarDesdeDetalle.style.display = 'none';
                    }

                    const bloquePdf = document.getElementById('modalBloquePdf');
                    const enlacePdf = document.getElementById('modalEnlacePdf');
                    if (celdaSeleccionada.dataset.pdf) {
                        enlacePdf.href = '/storage/' + celdaSeleccionada.dataset.pdf;
                        bloquePdf.style.display = 'block';
                    } else {
                        enlacePdf.href = '#';
                        bloquePdf.style.display = 'none';
                    }

                    document.getElementById('modalDetalles').style.display = 'block';
                }
            });

            document.addEventListener('keydown', function(evento) {
                if (evento.key === 'Escape') {
                    cerrarModal();
                    cerrarModalAcciones();
                }
            });
        });

        function cerrarModal() {
            document.getElementById('modalDetalles').style.display = 'none';
        }

        function cerrarModalAcciones() {
            document.getElementById('modalAcciones').style.display = 'none';
        }

        function cerrarToastPlan(idToast = 'toastExitoPlan') {
            const toast = document.getElementById(idToast);
            if (!toast) return;
            toast.classList.remove('visible');
            setTimeout(() => {
                if (toast && toast.parentNode) toast.parentNode.removeChild(toast);
            }, 260);
        }

        window.onclick = function(evento) {
            if (evento.target === document.getElementById('modalDetalles')) {
                cerrarModal();
            }
            if (evento.target === document.getElementById('modalAcciones')) {
                cerrarModalAcciones();
            }
        }
    </script>
@endsection
