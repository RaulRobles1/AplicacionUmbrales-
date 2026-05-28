@extends('auth.plantilla')

@section('contenido')
    <style>
        .tabla-hidro {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .tabla-hidro th {
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #dee2e6;
            color: #555;
            font-size: 0.85rem;
            background: #f8f9fa;
            white-space: nowrap;
        }

        .tabla-hidro td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 0.85rem;
            vertical-align: middle;
        }

        .fila-alerta-3 {
            background-color: rgba(255, 0, 0, 0.15) !important;
            font-weight: bold;
        }

        .fila-alerta-2 {
            background-color: rgba(255, 140, 0, 0.15) !important;
        }

        .fila-alerta-1 {
            background-color: rgba(255, 215, 0, 0.15) !important;
        }

        .status-dot {
            height: 10px;
            width: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .dot-3 {
            background-color: red;
            box-shadow: 0 0 5px red;
        }

        .dot-2 {
            background-color: orange;
        }

        .dot-1 {
            background-color: gold;
        }

        .dot-0 {
            background-color: #bbb;
        }

        .pill-global {
            background-color: #455a64;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: bold;
        }

        .val-box {
            display: inline-block;
            margin-top: 4px;
            font-size: 0.8rem;
            color: #333;
        }
    </style>

    @php
        $estaciones = collect();

        foreach ($embalses as $e) {
            $estaciones->push((object) [
                'tipo' => 'EMBALSE',
                'activo' => (int) ($e->er_activo ?? 1),
                'codigo' => $e->er_codigo ?? '---',
                'nombre' => $e->er_nombre ?? '---',
                'rio' => $e->er_rio ?? '---',
                'provincia' => $e->er_provincia ?? 'Provincia no encontrada',
                'ccaa' => $e->ccaa_nombre ?? '---',
                'tag_salida' => $e->er_tag_ip21 ?? '---',
                'tag_secundario' => $e->er_tag_volumen ?? '---',
                'valor' => $e->rde_valor,
                'valor_acc' => $e->rde_valor_accesorio ?? null,
                'umbral1' => (float) ($e->er_umbral1 ?? 0),
                'umbral2' => (float) ($e->er_umbral2 ?? 0),
                'umbral3' => (float) ($e->er_umbral3 ?? 0),
                'nivel_alerta' => (int) ($e->nivel_alerta ?? 0),
                'hora' => $e->rde_hora ?? '---',
            ]);
        }

        foreach ($roeas as $r) {
            $estaciones->push((object) [
                'tipo' => 'ROEA',
                'activo' => (int) ($r->ur_activo ?? 1),
                'codigo' => $r->ur_codigo ?? '---',
                'nombre' => $r->ur_nombre ?? '---',
                'rio' => $r->ur_rio ?? '---',
                'provincia' => $r->ur_provincia ?? 'Provincia no encontrada',
                'ccaa' => $r->ccaa_nombre ?? '---',
                'tag_salida' => $r->ur_tag_ip21 ?? '---',
                'tag_secundario' => $r->ur_tag_ip21_caudal ?? '---',
                'valor' => $r->rde_valor,
                'valor_acc' => $r->rde_valor_accesorio ?? null,
                'umbral1' => (float) ($r->ur_umbral1 ?? 0),
                'umbral2' => (float) ($r->ur_umbral2 ?? 0),
                'umbral3' => (float) ($r->ur_umbral3 ?? 0),
                'nivel_alerta' => (int) ($r->nivel_alerta ?? 0),
                'hora' => $r->rde_hora ?? '---',
            ]);
        }

        foreach ($marcos_control as $mc) {
            $estaciones->push((object) [
                'tipo' => 'MARCO CONTROL',
                'activo' => (int) ($mc->ur_activo ?? 1),
                'codigo' => $mc->ur_codigo ?? '---',
                'nombre' => $mc->ur_nombre ?? '---',
                'rio' => $mc->ur_rio ?? '---',
                'provincia' => $mc->ur_provincia ?? 'Provincia no encontrada',
                'ccaa' => $mc->ccaa_nombre ?? '---',
                'tag_salida' => $mc->ur_tag_ip21 ?? '---',
                'tag_secundario' => $mc->ur_tag_ip21_caudal ?? '---',
                'valor' => $mc->rde_valor,
                'valor_acc' => $mc->rde_valor_accesorio ?? null,
                'umbral1' => (float) ($mc->ur_umbral1 ?? 0),
                'umbral2' => (float) ($mc->ur_umbral2 ?? 0),
                'umbral3' => (float) ($mc->ur_umbral3 ?? 0),
                'nivel_alerta' => (int) ($mc->nivel_alerta ?? 0),
                'hora' => $mc->rde_hora ?? '---',
            ]);
        }

        foreach ($aforos as $ar) {
            $estaciones->push((object) [
                'tipo' => 'AFORO',
                'activo' => (int) ($ar->ur_activo ?? 1),
                'codigo' => $ar->ur_codigo ?? '---',
                'nombre' => $ar->ur_nombre ?? '---',
                'rio' => $ar->ur_rio ?? '---',
                'provincia' => $ar->ur_provincia ?? 'Provincia no encontrada',
                'ccaa' => $ar->ccaa_nombre ?? '---',
                'tag_salida' => $ar->ur_tag_ip21 ?? '---',
                'tag_secundario' => $ar->ur_tag_ip21_caudal ?? '---',
                'valor' => $ar->rde_valor,
                'valor_acc' => $ar->rde_valor_accesorio ?? null,
                'umbral1' => (float) ($ar->ur_umbral1 ?? 0),
                'umbral2' => (float) ($ar->ur_umbral2 ?? 0),
                'umbral3' => (float) ($ar->ur_umbral3 ?? 0),
                'nivel_alerta' => (int) ($ar->nivel_alerta ?? 0),
                'hora' => $ar->rde_hora ?? '---',
            ]);
        }

        $estaciones = $estaciones
            ->sortByDesc('nivel_alerta')
            ->sortBy('codigo')
            ->values();
    @endphp

    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px;">
        <div>
            <h2 style="margin: 0;">{{ $titulo }}</h2>
            <div style="font-size: 0.8rem; color: #64748b; margin-top: 6px;">Búsqueda: "{{ $query ?? request('query') }}"</div>
        </div>
        <div style="text-align: right; margin-top: 5px;">
            <span class="pill-global">{{ count($estaciones) }} Estaciones</span>
           {{--   <div style="color: #666; font-size: 0.75rem; margin-top: 6px;">Última act: {{ now()->format('H:i') }}</div>--}}
        </div>
    </div>

    <table class="tabla-hidro">
        <thead>
            <tr>
                <th>Código / Nombre</th>
                <th>Río</th>
                <th>TagReferencia / Valor</th>
                <th>TagCaudal / Caudal o Vol.</th>
                <th>Límites (Umbrales)</th>
                <th>Estado Actual</th>
                <th>Fecha/Hora</th>
            </tr>
        </thead>
        <tbody>
            @forelse($estaciones as $e)
                <tr class="fila-alerta-{{ $e->nivel_alerta }}" style="{{ $e->activo ? '' : 'opacity: 0.6;' }}">
                    <td>
                        <strong>{{ $e->codigo }}</strong> - {{ $e->nombre }}<br>
                        <span style="font-size: 0.74rem; color: #6b7280;">
                            {{ $e->tipo }} | {{ $e->provincia }} | {{ $e->ccaa }}
                        </span>
                    </td>
                    <td>{{ $e->rio ?? '---' }}</td>
                    <td>
                        {{ $e->tag_salida ?? '---' }}<br>
                        <span class="val-box">Valor:
                            <strong>{{ is_numeric($e->valor) ? number_format((float) $e->valor, 3, ',', '.') : $e->valor ?? '---' }}</strong>
                        </span>
                    </td>
                    <td>
                        {{ $e->tag_secundario ?? '---' }}<br>
                        <span class="val-box">Valor:
                            <strong>{{ isset($e->valor_acc) && is_numeric($e->valor_acc) ? number_format((float) $e->valor_acc, 3, ',', '.') : $e->valor_acc ?? '---' }}</strong>
                        </span>
                    </td>
                    <td style="font-size: 0.8rem; line-height: 1.4;">
                        @if ((float) $e->umbral1 > 0)
                            <span style="color:gold"><b>A:</b> >{{ number_format((float) $e->umbral1, 2, ',', '.') }}</span><br>
                        @endif
                        @if ((float) $e->umbral2 > 0)
                            <span style="color: orange"><b>N:</b> >{{ number_format((float) $e->umbral2, 2, ',', '.') }}</span><br>
                        @endif
                        @if ((float) $e->umbral3 > 0)
                            <span style="color: red"><b>R:</b> >{{ number_format((float) $e->umbral3, 2, ',', '.') }}</span>
                        @endif
                        @if ((float) $e->umbral1 == 0 && (float) $e->umbral2 == 0 && (float) $e->umbral3 == 0)
                            <span style="color: #999;">Sin definir</span>
                        @endif
                    </td>
                    <td>
                        <span class="status-dot dot-{{ $e->nivel_alerta }}"></span>
                        <b>{{ $e->nivel_alerta == 3 ? 'ALERTA ROJA' : ($e->nivel_alerta == 2 ? 'ALERTA NARANJA' : ($e->nivel_alerta == 1 ? 'ALERTA AMARILLA' : 'NORMAL')) }}</b>
                    </td>
                    <td>{{ $e->hora ?? '---' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: #999;">
                        No se encontraron estaciones para esta búsqueda.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if (count($episodios ?? []) > 0)
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:30px;">
            <h3 style="margin:0;">Episodios relacionados</h3>
            <span class="pill-global">{{ count($episodios) }} encontrados</span>
        </div>
        <table class="tabla-hidro">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Comunidad</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($episodios as $ep)
                    <tr>
                        <td><strong>#{{ $ep->re_id }}</strong></td>
                        <td>{{ $ep->re_nombre ?? 'Episodio ' . $ep->re_id }}</td>
                        <td>{{ $ep->nombre_ccaa ?? '---' }}</td>
                        <td>{{ empty($ep->re_hora_fin) ? 'ACTIVO' : 'HISTÓRICO' }}</td>
                        <td><a href="{{ route('episodios.detalle', $ep->re_id) }}">Ver detalle</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
