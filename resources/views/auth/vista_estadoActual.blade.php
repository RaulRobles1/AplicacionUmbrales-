@extends('auth.plantilla')

@section('contenido')
    <style>
        .tabla-hidro {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 40px 0;
        }

        .tabla-hidro th {
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #dee2e6;
            color: #555;
            font-size: 0.85rem;
        }

        .tabla-hidro td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 0.85rem;
        }


        .fila-alerta-3 { background-color: rgba(255, 0, 0, 0.12); font-weight: bold; }
        .fila-alerta-2 { background-color: rgba(255, 140, 0, 0.12); }
        .fila-alerta-1 { background-color: rgba(255, 215, 0, 0.12); }

        .pill-count {
            background-color: #455a64;
            color: white;
            padding: 2px 10px;
            border-radius: 10px;
            font-size: 0.75rem;
        }

        .cabecera-ccaa {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #2d3748;
            padding-bottom: 5px;
            margin-top: 30px;
        }
    </style>

    {{-- Encabezado principal  --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <h2 style="margin: 0; color: #1a202c;">Monitoreo Hidrográfico</h2>
        </div>

        <div style="text-align: right;">
            <button onclick="location.reload()"
                    style="background: #3b82f6; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600;">
                Actualizar Datos
            </button>
            <div style="color: #718096; font-size: 0.7rem; margin-top: 5px;">
                Sincronizado: {{ $ultimaSincronizacion ? \Carbon\Carbon::parse($ultimaSincronizacion)->format('H:i:s') : 'Sin datos' }}
            </div>
            <div id="autoRefreshInfo" style="color: #94a3b8; font-size: 0.68rem; margin-top: 3px;">
                Recarga automática en:
            </div>
        </div>
    </div>

    @forelse($resultadoFinal as $nombreComunidad => $estacionesEnAlerta)

        <div class="cabecera-ccaa">
            <h3 style="margin: 0; color: #2d3748;">{{ $nombreComunidad }}</h3>
            <span class="pill-count">{{ count($estacionesEnAlerta) }} estaciones</span>
        </div>

        <table class="tabla-hidro">
            <thead>
                <tr>
                    <th style="width: 40%;">Estación / Tag</th>
                    <th style="width: 15%;">Valor Actual</th>
                    <th style="width: 20%;">Tendencia</th>
                    <th style="width: 20%;">Umbrales (A/N/R)</th>
                    <th style="width: 20%;">Fecha y Hora</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($estacionesEnAlerta as $estacionAlerta)
                    @php
                        $nombreEstacion = $estacionAlerta['nombre'] ?? 'N/D';
                        $codigoEstacion = $estacionAlerta['codigo'] ?? '---';
                        $senalEstacion = $estacionAlerta['senal'] ?? '---';
                        $valorActual = $estacionAlerta['valor'] ?? null;
                        $nivelUmbral1 = $estacionAlerta['nivel1'] ?? null;
                        $nivelUmbral2 = $estacionAlerta['nivel2'] ?? null;
                        $nivelUmbral3 = $estacionAlerta['nivel3'] ?? null;
                        $fechaLectura = $estacionAlerta['fecha'] ?? '---';
                        $tendencia = $estacionAlerta['tendencia'] ?? '→';
                    @endphp

                    <tr class="fila-alerta-{{ $estacionAlerta['alerta'] ?? 0 }}">
                        {{-- Identificación de estación y señal --}}
                        <td>
                            <div style="font-weight: 600; color: #2d3748;">{{ $nombreEstacion }}</div>
                            <div style="font-size: 0.75rem; color: #718096;">
                                Cód: {{ $codigoEstacion }} |
                                {{ $senalEstacion }}
                            </div>
                        </td>

                        {{-- Valor leído en la última consulta --}}
                        <td style="font-size: 1rem;">
                            {{ is_numeric($valorActual) ? number_format((float) $valorActual, 3, ',', '.') : '---' }}
                        </td>

                        <td style="font-size: 1.15rem; font-weight: 700;">
                            {{ $tendencia }}
                        </td>

                        {{-- Umbrales alerta A/N/R --}}
                        <td style="font-size: 0.75rem;">
                            @if(isset($nivelUmbral1) && $nivelUmbral1 > 0)
                                <span style="color: #b8860b;">● {{ number_format($nivelUmbral1, 2) }}</span>
                            @endif
                            @if(isset($nivelUmbral2) && $nivelUmbral2 > 0)
                                <span style="color: #d97706;"> ● {{ number_format($nivelUmbral2, 2) }}</span>
                            @endif
                            @if(isset($nivelUmbral3) && $nivelUmbral3 > 0)
                                <span style="color: #dc2626;"> ● {{ number_format($nivelUmbral3, 2) }}</span>
                            @endif

                            @if(empty($nivelUmbral1) && empty($nivelUmbral2) && empty($nivelUmbral3))
                                <span style="color: #cbd5e0;">No definidos</span>
                            @endif
                        </td>

                        {{-- Fecha/hora de la lectura devuelta por API --}}
                        <td>
                            {{ $fechaLectura == 'Sin conexión' ? 'Error API' : $fechaLectura }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    @empty
        {{-- Caso sin alertas --}}
        <div style="padding: 40px; text-align: center; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e0;">
            <p style="color: #64748b;">No hay estaciones alarmadas en este momento.</p>
        </div>
    @endforelse

    <script>
        (() => {
            const INTERVALO_MS = 5 * 60 * 1000; // 5 minutos
            const info = document.getElementById('autoRefreshInfo');
            const inicio = Date.now();

            const timer = setInterval(() => {
                const transcurrido = Date.now() - inicio;
                const restante = Math.max(INTERVALO_MS - transcurrido, 0);
                const minutos = String(Math.floor(restante / 60000)).padStart(2, '0');
                const segundos = String(Math.floor((restante % 60000) / 1000)).padStart(2, '0');

                if (info) info.textContent = `Recarga automática en ${minutos}:${segundos}`;

                if (restante <= 0) {
                    clearInterval(timer);
                    const destino = new URL(window.location.href);
                    destino.searchParams.set('_autorefresh', Date.now().toString());
                    window.location.replace(destino.toString());
                }
            }, 250);
        })();
    </script>

@endsection
