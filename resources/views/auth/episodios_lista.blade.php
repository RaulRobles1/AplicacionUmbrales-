@extends('auth.plantilla')

@section('contenido')
    <style>
        .episodiosShell {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .cabeceraEpisodios {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            flex-wrap: wrap;
            padding: 14px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }

        .cabeceraEpisodios h2 {
            margin: 0;
            color: #1f2937;
            font-size: 1.35rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .subtituloEpisodios {
            margin: 4px 0 0;
            color: #64748b;
            font-size: 0.82rem;
        }

        .chipsResumen {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        .chipResumen {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid #d1d5db;
            border-radius: 999px;
            padding: 6px 10px;
            background: #fff;
            color: #334155;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .chipResumen.destacado {
            border-color: #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .tablaWrap {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
            background: #fff;
        }

        .tablaScroll {
            overflow: auto;
            max-height: calc(100vh - 290px);
        }

        .tablaEpisodios {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 1120px;
            font-size: 0.78rem;
        }

        .tablaEpisodios thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f8fafc;
            color: #334155;
            font-weight: 700;
            text-align: left;
            letter-spacing: 0.2px;
            border-bottom: 1px solid #e2e8f0;
            padding: 11px 10px;
            white-space: nowrap;
        }

        .tablaEpisodios tbody td {
            padding: 10px;
            border-bottom: 1px solid #eef2f7;
            vertical-align: top;
            color: #1f2937;
            line-height: 1.4;
        }

        .tablaEpisodios tbody tr:nth-child(even) td {
            background: #fcfdff;
        }

        .tablaEpisodios tbody tr:hover td {
            background: #f8fbff;
        }

        .codigoEpisodio {
            font-weight: 700;
            color: #0f172a;
        }

        .textoSecundario {
            color: #64748b;
            font-size: 0.73rem;
        }

        .ccaaTexto {
            font-weight: 600;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .chipsListado {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            max-width: 320px;
        }

        .celdaResumen {
            display: flex;
            flex-direction: column;
            gap: 7px;
            min-width: 220px;
        }

        .resumenLinea {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #475569;
            font-size: 0.73rem;
            font-weight: 600;
        }

        .badgeCantidad {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 24px;
            padding: 2px 7px;
            border-radius: 999px;
            border: 2px solid #cbd5e1;
            background: #e2e8f0;
            color: #1e293b;
            font-size: 0.72rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .badgeCantidad.alerta-1 {
            border-color: #eab308;
            background: #fefce8;
            color: #854d0e;
        }

        .badgeCantidad.alerta-2 {
            border-color: #f97316;
            background: #fff7ed;
            color: #9a3412;
        }

        .badgeCantidad.alerta-3 {
            border-color: #ef4444;
            background: #fef2f2;
            color: #991b1b;
        }

        .btnVerListaEstaciones {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            align-self: flex-start;
            padding: 4px 8px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #1e40af;
            font-size: 0.72rem;
            font-weight: 700;
            cursor: pointer;
            transition: all .2s ease;
        }

        .btnVerListaEstaciones:hover {
            background: #eff6ff;
            border-color: #93c5fd;
        }

        .chipCodigo {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid #dbe2ea;
            background: #f8fafc;
            color: #334155;
            padding: 2px 8px;
            font-size: 0.7rem;
            font-weight: 600;
            line-height: 1.3;
        }

        .chipCodigo.alerta-1 {
            border-color: #fde68a;
            background: #fffbeb;
            color: #92400e;
        }

        .chipCodigo.alerta-2 {
            border-color: #fdba74;
            background: #fff7ed;
            color: #9a3412;
        }

        .chipCodigo.alerta-3 {
            border-color: #fca5a5;
            background: #fef2f2;
            color: #b91c1c;
        }

        .valorBoletines {
            display: inline-flex;
            min-width: 28px;
            justify-content: center;
            border-radius: 999px;
            background: #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            font-size: 0.74rem;
            padding: 3px 8px;
        }

        .fechaCelda {
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .btnVerDetalle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 7px;
            padding: 7px 11px;
            background: #2563eb;
            color: #fff;
            text-decoration: none;
            font-size: 0.74rem;
            font-weight: 700;
            border: 1px solid #1d4ed8;
            transition: background .2s ease, transform .2s ease;
            white-space: nowrap;
        }

        .btnVerDetalle:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .vacioEpisodios {
            border: 1px dashed #cbd5e1;
            background: #f8fafc;
            color: #64748b;
            border-radius: 10px;
            padding: 26px;
            text-align: center;
            font-size: 0.88rem;
        }

        .overlayLista {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1300;
            padding: 20px;
        }

        .panelLista {
            width: min(680px, 100%);
            max-height: 82vh;
            overflow: auto;
            border-radius: 12px;
            background: #fff;
            border: 1px solid #dbe3ec;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.2);
        }

        .panelListaHeader {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 14px;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            background: #fff;
        }

        .panelListaHeader h3 {
            margin: 0;
            font-size: 0.95rem;
            color: #0f172a;
        }

        .btnCerrarPanelLista {
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #334155;
            border-radius: 8px;
            width: 28px;
            height: 28px;
            cursor: pointer;
            font-weight: 700;
        }

        .panelListaBody {
            padding: 14px;
        }

        .gridCodigos {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
    </style>

    <div class="episodiosShell">
        <div class="cabeceraEpisodios">
            <div>
                <h2>{{ $titulo }}</h2>
                <p class="subtituloEpisodios">Lista del detalle de los episodios </p>
            </div>
            <div class="chipsResumen">
                <span class="chipResumen destacado">{{ $tipo }}</span>
                <span class="chipResumen">{{ count($episodios) }} episodios</span>
            </div>
        </div>

        @if (count($episodios) > 0)
            <div class="tablaWrap">
                <div class="tablaScroll">
                    <table class="tablaEpisodios">
                        <thead>
                            <tr>
                                <th>Nº Episodio</th>
                                <th>Nombre</th>
                                <th>Comunidad Autónoma</th>
                                @if ($tipo !== 'Históricos')
                                    <th>Estaciones alarmadas actualmente</th>
                                @endif
                                <th>
                                    {{ $tipo === 'Históricos' ? 'Historico estaciones' : 'Historico estaciones' }}
                                </th>
                                <th>Iniciado</th>
                                <th>Finalizado</th>
                                <th>Boletines</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($episodios as $ep)
                                @php
                                    $nombreEpisodio =
                                        !empty($ep->re_nombre) && $ep->re_nombre !== 'None'
                                            ? $ep->re_nombre
                                            : 'Sin nombre';

                                    $estacionesHistoricas = !empty($ep->re_estaciones_historicas)
                                        ? array_values(
                                            array_unique(
                                                array_filter(array_map('trim', explode(',', $ep->re_estaciones_historicas))),
                                            ),
                                        )
                                        : [];

                                    $estacionesActivas = !empty($ep->re_estaciones_activas)
                                        ? array_values(
                                            array_unique(
                                                array_filter(array_map('trim', explode(',', $ep->re_estaciones_activas))),
                                            ),
                                        )
                                        : [];

                                    $estacionesAfectadas = ! empty($estacionesHistoricas)
                                        ? $estacionesHistoricas
                                        : $estacionesActivas;

                                    $alarmadasReales = empty($ep->re_hora_fin)
                                        ? array_values(array_filter($estacionesActivas, function ($codigo) use ($nivelesEstaciones) {
                                            return (int) ($nivelesEstaciones[$codigo] ?? 0) > 0;
                                        }))
                                        : [];

                                    $fechasEpisodio = $fechasEstaciones[$ep->re_id] ?? [];
                                    usort($estacionesAfectadas, function ($a, $b) use ($fechasEpisodio) {
                                        $fechaA = $fechasEpisodio[$a] ?? null;
                                        $fechaB = $fechasEpisodio[$b] ?? null;
                                        if ($fechaA && $fechaB) {
                                            return strtotime((string) $fechaB) <=> strtotime((string) $fechaA);
                                        }
                                        if ($fechaA && ! $fechaB) {
                                            return -1;
                                        }
                                        if ($fechaB && ! $fechaA) {
                                            return 1;
                                        }
                                        return strcmp((string) $a, (string) $b);
                                    });

                                    usort($alarmadasReales, function ($a, $b) use ($nivelesEstaciones) {
                                        $nivelA = (int) ($nivelesEstaciones[$a] ?? 0);
                                        $nivelB = (int) ($nivelesEstaciones[$b] ?? 0);
                                        if ($nivelA === $nivelB) {
                                            return strcmp((string) $a, (string) $b);
                                        }
                                        return $nivelB <=> $nivelA;
                                    });

                                    $maxNivelAfectadas = 0;
                                    foreach ($estacionesAfectadas as $codigoEstacion) {
                                        $maxNivelAfectadas = max($maxNivelAfectadas, (int) ($nivelesEstaciones[$codigoEstacion] ?? 0));
                                    }
                                    $claseBadgeAfectadas = $maxNivelAfectadas > 0 ? 'alerta-' . $maxNivelAfectadas : '';

                                    $maxNivelAlarmadas = 0;
                                    foreach ($alarmadasReales as $codigoAlarma) {
                                        $maxNivelAlarmadas = max($maxNivelAlarmadas, (int) ($nivelesEstaciones[$codigoAlarma] ?? 0));
                                    }
                                    $claseBadgeAlarmadas = $maxNivelAlarmadas > 0 ? 'alerta-' . $maxNivelAlarmadas : '';

                                    $previewEstaciones = array_slice($estacionesAfectadas, 0, 4);
                                    $restantesEstaciones = max(count($estacionesAfectadas) - count($previewEstaciones), 0);
                                    $previewAlarmadas = array_slice($alarmadasReales, 0, 4);
                                    $restantesAlarmadas = max(count($alarmadasReales) - count($previewAlarmadas), 0);
                                    $alarmadasConNivel = array_map(function ($codigoAlarma) use ($nivelesEstaciones) {
                                        return [
                                            'codigo' => $codigoAlarma,
                                            'nivel' => (int) ($nivelesEstaciones[$codigoAlarma] ?? 0),
                                        ];
                                    }, $alarmadasReales);

                                    $afectadasConNivel = array_map(function ($codigoEstacion) use ($nivelesEstaciones) {
                                        return [
                                            'codigo' => $codigoEstacion,
                                            'nivel' => (int) ($nivelesEstaciones[$codigoEstacion] ?? 0),
                                        ];
                                    }, $estacionesAfectadas);
                                @endphp

                                <tr>
                                    <td>
                                        <div class="codigoEpisodio">#{{ $ep->re_id }}</div>
                                    </td>

                                    <td>
                                        <div>{{ $nombreEpisodio }}</div>

                                    <td>
                                        <div class="ccaaTexto">{{ $ep->nombre_ccaa ?? '---' }}</div>
                                    </td>

                                    @if ($tipo === 'Activos')
                                        <td>
                                            @if (!empty($ep->re_hora_fin))
                                                <span class="textoSecundario">Episodio finalizado</span>
                                            @elseif (count($alarmadasReales) > 0)
                                                <div class="celdaResumen">
                                                    <span class="resumenLinea">
                                                        <span class="badgeCantidad {{ $claseBadgeAlarmadas }}">{{ count($alarmadasReales) }}</span>
                                                        alarmadas ahora
                                                    </span>
                                                    <div class="chipsListado">
                                                        @foreach ($previewAlarmadas as $codigoAlarma)
                                                            @php
                                                                $nivel = (int) ($nivelesEstaciones[$codigoAlarma] ?? 0);
                                                                $claseAlerta = $nivel > 0 ? 'alerta-' . $nivel : '';
                                                            @endphp
                                                            <span class="chipCodigo {{ $claseAlerta }}">{{ $codigoAlarma }}</span>
                                                        @endforeach
                                                        @if ($restantesAlarmadas > 0)
                                                            <span class="chipCodigo">+{{ $restantesAlarmadas }}</span>
                                                        @endif
                                                    </div>
                                                    <button type="button" class="btnVerListaEstaciones"
                                                        data-titulo="Estaciones alarmadas - Episodio #{{ $ep->re_id }}"
                                                        data-codigos='@json($alarmadasReales)'
                                                        data-estaciones='@json($alarmadasConNivel)'>
                                                        Ver todas
                                                    </button>
                                                </div>
                                            @else
                                                <span class="textoSecundario">Sin estaciones alarmadas ahora</span>
                                            @endif
                                        </td>
                                    @endif

                                    <td>
                                        @if (count($estacionesAfectadas) > 0)
                                            <div class="celdaResumen">
                                                <span class="resumenLinea">
                                                    <span class="badgeCantidad {{ $claseBadgeAfectadas }}">{{ count($estacionesAfectadas) }}</span>
                                                    estaciones vinculadas
                                                </span>
                                                <div class="chipsListado">
                                                    @foreach ($previewEstaciones as $codigoEstacion)
                                                        @php
                                                            $nivel = (int) ($nivelesEstaciones[$codigoEstacion] ?? 0);
                                                            $claseAlerta = $nivel > 0 ? 'alerta-' . $nivel : '';
                                                        @endphp
                                                        <span class="chipCodigo {{ $claseAlerta }}">{{ $codigoEstacion }}</span>
                                                    @endforeach
                                                    @if ($restantesEstaciones > 0)
                                                        <span class="chipCodigo">+{{ $restantesEstaciones }}</span>
                                                    @endif
                                                </div>
                                                <button type="button" class="btnVerListaEstaciones"
                                                    data-titulo="Estaciones afectadas - Episodio #{{ $ep->re_id }}"
                                                    data-codigos='@json($estacionesAfectadas)'
                                                    data-estaciones='@json($afectadasConNivel)'>
                                                    Ver todas
                                                </button>
                                            </div>
                                        @else
                                            <span class="textoSecundario">---</span>
                                        @endif
                                    </td>

                                    <td class="fechaCelda">
                                        {{ $ep->re_hora_inicio ? \Carbon\Carbon::parse($ep->re_hora_inicio)->format('d/m/Y H:i') : '---' }}
                                    </td>

                                    <td class="fechaCelda">
                                        {{ $ep->re_hora_fin ? \Carbon\Carbon::parse($ep->re_hora_fin)->format('d/m/Y H:i') : '—' }}
                                    </td>

                                    <td>
                                        <span class="valorBoletines">{{ $ep->re_boletines_generados ?? '0' }}</span>
                                    </td>

                                    <td>
                                        <a href="{{ route('episodios.detalle', $ep->re_id) }}" class="btnVerDetalle">Ver detalle</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="vacioEpisodios">
                No hay episodios {{ strtolower($tipo) }} registrados en esta sección.
            </div>
        @endif
    </div>

    <div id="overlayListaEstaciones" class="overlayLista">
        <div class="panelLista">
            <div class="panelListaHeader">
                <h3 id="tituloPanelLista">Estaciones</h3>
                <button type="button" class="btnCerrarPanelLista" id="btnCerrarPanelLista">×</button>
            </div>
            <div class="panelListaBody">
                <div id="contenidoPanelLista" class="gridCodigos"></div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.getElementById('overlayListaEstaciones');
            const titulo = document.getElementById('tituloPanelLista');
            const contenido = document.getElementById('contenidoPanelLista');
            const btnCerrar = document.getElementById('btnCerrarPanelLista');
            const botones = document.querySelectorAll('.btnVerListaEstaciones');

            function cerrarOverlay() {
                overlay.style.display = 'none';
                contenido.innerHTML = '';
            }

            botones.forEach(boton => {
                boton.addEventListener('click', function() {
                    const codigos = JSON.parse(this.dataset.codigos || '[]');
                    const estacionesConNivel = JSON.parse(this.dataset.estaciones || '[]');
                    const textoTitulo = this.dataset.titulo || 'Listado de estaciones';

                    titulo.textContent = textoTitulo;
                    contenido.innerHTML = '';

                    if (Array.isArray(estacionesConNivel) && estacionesConNivel.length > 0) {
                        estacionesConNivel.forEach(item => {
                            const codigo = typeof item === 'string' ? item : item.codigo;
                            const nivel = Number(typeof item === 'object' ? item.nivel : 0);
                            const chip = document.createElement('span');
                            chip.className = 'chipCodigo';
                            if (nivel > 0) {
                                chip.classList.add(`alerta-${nivel}`);
                            }
                            chip.textContent = codigo;
                            contenido.appendChild(chip);
                        });
                    } else if (!Array.isArray(codigos) || codigos.length === 0) {
                        const vacio = document.createElement('span');
                        vacio.className = 'textoSecundario';
                        vacio.textContent = 'No hay estaciones para mostrar.';
                        contenido.appendChild(vacio);
                    } else {
                        codigos.forEach(codigo => {
                            const chip = document.createElement('span');
                            chip.className = 'chipCodigo';
                            chip.textContent = codigo;
                            contenido.appendChild(chip);
                        });
                    }

                    overlay.style.display = 'flex';
                });
            });

            btnCerrar.addEventListener('click', cerrarOverlay);
            overlay.addEventListener('click', function(evento) {
                if (evento.target === overlay) cerrarOverlay();
            });
        });
    </script>
@endsection
