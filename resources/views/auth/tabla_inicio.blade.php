<table class="tablaHidro">
    <thead>
        <tr>
            <th>Código / Nombre</th>
            <th>Río</th>
            <th>Comunidad Autónoma</th>
            <th>Valor Referencia</th>
            {{-- Cabecera dinámica por si es caudal o volumen --}}
            <th>{{ $valorElegido ?? 'Caudal o Vol.' }}</th>
            <th>Límites (Umbrales)</th>
            <th>Tendencia</th>
            <th>Estado Actual</th>
            <th>
                Fecha/Hora
                <div class="iconoInfo">
                    ?
                    <span class="bocadilloTexto">Muestra la fecha y hora exacta del último dato recibido por esta estación</span>
                </div>
            </th>
        </tr>
    </thead>
    <tbody>
        @forelse($estaciones as $e)
            @php
                $ccaaFila = strtoupper(trim((string) ($e->ccaa ?? '---')));
                $nivelAlertaFila = (int) ($e->nivel_alerta ?? 0);
            @endphp
            {{-- Estilos dinámicos segun su nivel de alerta --}}
            <tr class="filaAlerta{{ $e->nivel_alerta }} filaEstacion" data-ccaa="{{ $ccaaFila }}" data-alerta="{{ $nivelAlertaFila }}">
                <td>
                    <strong>{{ $e->codigo ?? '---' }}</strong><br>
                    {{ $e->nombre }}
                </td>
                <td>{{ $e->rio ?? '---' }}</td>

                <td>{{ $e->ccaa ?? '---' }}</td>

                <td>
                    {{ $e->tag_salida ?? '---' }}<br>
                    <span class="cajaValor">
                        Valor:
                        <strong>{{ is_numeric($e->valor) ? number_format((float) $e->valor, 3, ',', '.') : $e->valor ?? '---' }}</strong>
                    </span>
                </td>

                <td>
                    {{ $e->tag_secundario ?? '---' }}<br>
                    <span class="cajaValor">
                        Valor:
                        <strong>{{ isset($e->valor_acc) && is_numeric($e->valor_acc) ? number_format((float) $e->valor_acc, 3, ',', '.') : $e->valor_acc ?? '---' }}</strong>
                    </span>
                </td>

                <td style="font-size: 0.8rem; line-height: 1.4;">
                    @if ((float) ($e->umbral1 ?? 0) > 0)
                        <span style="color: gold"><b>A:</b> >{{ number_format((float) $e->umbral1, 2, ',', '.') }}</span><br>
                    @endif
                    @if ((float) ($e->umbral2 ?? 0) > 0)
                        <span style="color: orange"><b>N:</b> >{{ number_format((float) $e->umbral2, 2, ',', '.') }}</span><br>
                    @endif
                    @if ((float) ($e->umbral3 ?? 0) > 0)
                        <span style="color: red"><b>R:</b> >{{ number_format((float) $e->umbral3, 2, ',', '.') }}</span>
                    @endif
                    @if ((float) ($e->umbral1 ?? 0) == 0 && (float) ($e->umbral2 ?? 0) == 0 && (float) ($e->umbral3 ?? 0) == 0)
                        <span style="color: #999;">Sin definir</span>
                    @endif
                </td>

                <td style="font-size: 2rem; font-weight: 700;">
                    {{ $e->tendencia ?? '→' }}
                </td>

                {{-- Estado Actual --}}
                <td>
                    <span class="puntEstado punt{{ $e->nivel_alerta }}"></span>
                    <b>{{ $e->nivel_alerta == 3 ? 'ALERTA ROJA' : ($e->nivel_alerta == 2 ? 'ALERTA NARANJA' : ($e->nivel_alerta == 1 ? 'ALERTA AMARILLA' : 'NORMAL')) }}</b>
                </td>

                <td>{{ $e->hora ?? '---' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 40px; color: #999;">
                    No hay estaciones en estado de emergencia en este momento.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
