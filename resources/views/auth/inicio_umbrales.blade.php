@extends('auth.plantilla')

@section('contenido')
    <style>
        .tablaHidro {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .tablaHidro th {
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #dee2e6;
            color: #555;
            font-size: 0.85rem;
        }

        .tablaHidro td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 0.85rem;
        }

        .filaAlerta3 {
            background-color: rgba(255, 0, 0, 0.15) !important;
            font-weight: bold;
        }

        .filaAlerta2 {
            background-color: rgba(255, 140, 0, 0.15) !important;
        }

        .filaAlerta1 {
            background-color: rgba(255, 215, 0, 0.15) !important;
        }

        .puntEstado {
            height: 10px;
            width: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .punt3 {
            background-color: red;
            box-shadow: 0 0 5px red;
        }

        .punt2 {
            background-color: orange;
        }

        .punt1 {
            background-color: gold;
        }

        .punt0 {
            background-color: #bbb;
        }

        .etiquetaGlobal {
            background-color: #455a64;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: bold;
        }

        .cajaValor {
            display: inline-block;
            margin-top: 4px;
            font-size: 0.8rem;
            color: #333;
        }

        .filaGrafico {
            background: rgba(255, 255, 255, 0.5);
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .filaGrafico:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .contenedorPestanas {
            margin-bottom: 20px;
            border-bottom: 2px solid #dee2e6;
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .botonPestana {
            padding: 10px 20px;
            border: none;
            background: #f8f9fa;
            cursor: pointer;
            font-weight: bold;
            border-radius: 8px 8px 0 0;
            color: #666;
            transition: 0.3s;
        }

        .botonPestana.activo {
            background: #0d6efd;
            color: white;
        }

        .contenidoPestana {
            display: none;
        }

        .contenidoPestana.activo {
            display: block;
        }

        .capaModal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            display: none;
            justify-content: center;
            align-items: center;
            padding: 24px;
        }

        .contenidoModal {
            background: white;
            padding: 24px;
            border-radius: 12px;
            width: min(1100px, 100%);
            max-height: calc(100vh - 48px);
            overflow-y: auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .cabeceraModal {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .botonCerrarModal {
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            font-weight: bold;
            cursor: pointer;
        }

        .botonCerrarModal:hover {
            background: #dc2626;
        }

        /* Botones de Filtro de Tiempo */
        .filtrosTiempoGrafico {
            padding: 12px 16px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 8px;
            border-radius: 10px;
        }

        .cuerpoModal {
            margin-top: 12px;
        }

        .botonFiltroTiempo {
            background: white;
            border: 1px solid #ced4da;
            padding: 4px 14px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            color: #495057;
        }

        .botonFiltroTiempo:hover {
            background: #e9ecef;
        }

        .botonFiltroTiempo.activo {
            background: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }

        /* --- Estilos de Información --- */
        .iconoInfo {
            position: relative;
            display: inline-block;
            cursor: help;
            color: #0d6efd;
            margin-left: 5px;
            font-weight: bold;
            font-size: 0.85rem;
            background: #e9ecef;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            text-align: center;
            line-height: 18px;
        }

        .iconoInfo .bocadilloTexto {
            visibility: hidden;
            width: 260px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 8px 10px;
            position: absolute;
            z-index: 100;
            bottom: 130%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 0.75rem;
            font-weight: normal;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }

        .iconoInfo .bocadilloTexto::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #333 transparent transparent transparent;
        }

        .iconoInfo:hover .bocadilloTexto {
            visibility: visible;
            opacity: 1;
        }

        .resumenSuperior {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .resumenDerecha {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .resumenDerechaCol {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 4px;
        }

        .metaActualizacion {
            color: #666;
            font-size: 0.78rem;
        }

        .resumenEstados {
            width: auto;
            border-collapse: collapse;
            margin: 2px 0 0;
            font-size: 0.74rem;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
        }

        .resumenEstados td {
            padding: 3px 6px;
            text-align: left;
            vertical-align: middle;
            white-space: nowrap;
            border-bottom: 1px solid #eef2f7;
        }

        .resumenEstados tr:last-child td {
            border-bottom: none;
        }

        .resumenEstados tr:last-child td {
            border-bottom: none;
        }

        .resumenItem {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            min-width: 42px;
            padding: 0;
            border-radius: 0;
            background: transparent;
            color: #374151;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }

        .resumenDot {
            height: 10px;
            width: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .resumenLeyenda {
            display: grid;
            grid-template-columns: 110px repeat(4, minmax(42px, auto));
            align-items: center;
            column-gap: 10px;
        }

        .resumenTipo {
            font-weight: 700;
            color: #111827;
            margin-right: 0;
        }

        .bloqueFiltroCcaa {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0 0 12px;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
        }

        .bloqueFiltroCcaa label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #334155;
        }

        .selectorFiltroCcaa {
            min-width: 220px;
            padding: 6px 10px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #1e293b;
        }

        @media (max-width: 900px) {
            .resumenDerecha {
                justify-content: flex-start;
            }
        }
    </style>

    @php
        $resumenAforos = [
            'normal' => $aforos->where('nivel_alerta', 0)->count(),
            'amarilla' => $aforos->where('nivel_alerta', 1)->count(),
            'naranja' => $aforos->where('nivel_alerta', 2)->count(),
            'roja' => $aforos->where('nivel_alerta', 3)->count(),
        ];
        $resumenEmbalses = [
            'normal' => $embalses->where('nivel_alerta', 0)->count(),
            'amarilla' => $embalses->where('nivel_alerta', 1)->count(),
            'naranja' => $embalses->where('nivel_alerta', 2)->count(),
            'roja' => $embalses->where('nivel_alerta', 3)->count(),
        ];
        $totalAforosVista = $totalAforos ?? count($aforos);
        $totalEmbalsesVista = $totalEmbalses ?? count($embalses);
        $mostrarAforosNormal = ($resumenAforos['amarilla'] + $resumenAforos['naranja'] + $resumenAforos['roja']) === 0
            ? $totalAforosVista
            : $resumenAforos['normal'];
        $mostrarEmbalsesNormal = ($resumenEmbalses['amarilla'] + $resumenEmbalses['naranja'] + $resumenEmbalses['roja']) === 0
            ? $totalEmbalsesVista
            : $resumenEmbalses['normal'];
    @endphp

    <div class="resumenSuperior">
        <div style="display: flex; align-items: center; gap: 15px;">
            <h2 style="margin: 0;">{{ $titulo }}</h2>
        </div>

        <div class="resumenDerechaCol">
            <span class="etiquetaGlobal">{{ count($aforos) + count($embalses) }} Estaciones</span>
            <table class="resumenEstados" aria-label="Resumen de alertas">
                <tbody>
                    <tr aria-label="Resumen de alertas en aforos">
                        <td>
                            <span class="resumenLeyenda">
                                <span class="resumenTipo">Aforos en Río</span>
                                <span class="resumenItem"><span class="resumenDot punt0"></span>{{ $mostrarAforosNormal }}</span>
                                <span class="resumenItem"><span class="resumenDot punt1"></span>{{ $resumenAforos['amarilla'] }}</span>
                                <span class="resumenItem"><span class="resumenDot punt2"></span>{{ $resumenAforos['naranja'] }}</span>
                                <span class="resumenItem"><span class="resumenDot punt3"></span>{{ $resumenAforos['roja'] }}</span>
                            </span>
                        </td>
                    </tr>
                    <tr aria-label="Resumen de alertas en embalses">
                        <td>
                            <span class="resumenLeyenda">
                                <span class="resumenTipo">Embalses</span>
                                <span class="resumenItem"><span class="resumenDot punt0"></span>{{ $mostrarEmbalsesNormal }}</span>
                                <span class="resumenItem"><span class="resumenDot punt1"></span>{{ $resumenEmbalses['amarilla'] }}</span>
                                <span class="resumenItem"><span class="resumenDot punt2"></span>{{ $resumenEmbalses['naranja'] }}</span>
                                <span class="resumenItem"><span class="resumenDot punt3"></span>{{ $resumenEmbalses['roja'] }}</span>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
            {{--  <small class="metaActualizacion">
                Última actualización:
                {{ !empty($ultimaSincronizacion) ? \Carbon\Carbon::parse($ultimaSincronizacion)->format('H:i:s') : 'Sin datos' }}
            </small>
            --}}
        </div>
    </div>

    <div class="contenedorPestanas">
        <button class="botonPestana activo" onclick="abrirPestana(event, 'pestanaAforos')">Aforos en Río
            ({{ count($aforos) }})</button>
        <button class="botonPestana" onclick="abrirPestana(event, 'pestanaEmbalses')">Embalses
            ({{ count($embalses) }})</button>
    </div>

    {{-- CONTENIDO AFOROS --}}
    <div id="pestanaAforos" class="contenidoPestana activo">
        <div class="bloqueFiltroCcaa">
            <label for="filtroCcaaAforos">Filtrar por CCAA:</label>
            <select id="filtroCcaaAforos" class="selectorFiltroCcaa" data-tabla="pestanaAforos">
                <option value="">Todas</option>
                @foreach ($filtroCcaaAforos as $ccaa)
                    <option value="{{ strtoupper($ccaa) }}">{{ $ccaa }}</option>
                @endforeach
            </select>
            <label for="filtroAlertaAforos">Filtrar por alerta:</label>
            <select id="filtroAlertaAforos" class="selectorFiltroAlerta selectorFiltroCcaa" data-tabla="pestanaAforos">
                <option value="">Todas</option>
                <option value="1">Alerta Amarilla</option>
                <option value="2">Alerta Naranja</option>
                <option value="3">Alerta Roja</option>
            </select>
        </div>
        @include('auth.tabla_inicio', ['estaciones' => $aforos, 'valorElegido' => 'Caudal Asociado'])
    </div>

    {{-- CONTENIDO EMBALSES --}}
    <div id="pestanaEmbalses" class="contenidoPestana">
        <div class="bloqueFiltroCcaa">
            <label for="filtroCcaaEmbalses">Filtrar por CCAA:</label>
            <select id="filtroCcaaEmbalses" class="selectorFiltroCcaa" data-tabla="pestanaEmbalses">
                <option value="">Todas</option>
                @foreach ($filtroCcaaEmbalses as $ccaa)
                    <option value="{{ strtoupper($ccaa) }}">{{ $ccaa }}</option>
                @endforeach
            </select>
            <label for="filtroAlertaEmbalses">Filtrar por alerta:</label>
            <select id="filtroAlertaEmbalses" class="selectorFiltroAlerta selectorFiltroCcaa" data-tabla="pestanaEmbalses">
                <option value="">Todas</option>
                <option value="1">Alerta Amarilla</option>
                <option value="2">Alerta Naranja</option>
                <option value="3">Alerta Roja</option>
            </select>
        </div>
        @include('auth.tabla_inicio', ['estaciones' => $embalses, 'valorElegido' => 'Volumen Asociado'])
    </div>
    <script>
        function abrirPestana(evt, nombrePestana) {
            let i, contenidoPestana, enlacesPestana;
            contenidoPestana = document.getElementsByClassName("contenidoPestana");
            for (i = 0; i < contenidoPestana.length; i++) {
                contenidoPestana[i].style.display = "none";
                contenidoPestana[i].classList.remove("activo");
            }
            enlacesPestana = document.getElementsByClassName("botonPestana");
            for (i = 0; i < enlacesPestana.length; i++) {
                enlacesPestana[i].classList.remove("activo");
            }
            document.getElementById(nombrePestana).style.display = "block";
            document.getElementById(nombrePestana).classList.add("activo");
            evt.currentTarget.classList.add("activo");
        }

        function aplicarFiltrosTabla(tablaId) {
            const tabla = document.getElementById(tablaId);
            if (!tabla) return;

            const selectorCcaa = document.querySelector(`.selectorFiltroCcaa[data-tabla="${tablaId}"]:not(.selectorFiltroAlerta)`);
            const selectorAlerta = document.querySelector(`.selectorFiltroAlerta[data-tabla="${tablaId}"]`);
            const valorCcaa = ((selectorCcaa && selectorCcaa.value) || '').toUpperCase();
            const valorAlerta = (selectorAlerta && selectorAlerta.value) || '';

            const filas = tabla.querySelectorAll('tbody .filaEstacion');
            filas.forEach(fila => {
                const ccaaFila = (fila.dataset.ccaa || '').toUpperCase();
                const alertaFila = (fila.dataset.alerta || '').trim();
                const coincideCcaa = !valorCcaa || ccaaFila === valorCcaa;
                const coincideAlerta = !valorAlerta || alertaFila === valorAlerta;
                const mostrar = coincideCcaa && coincideAlerta;
                fila.style.display = mostrar ? '' : 'none';
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.selectorFiltroCcaa, .selectorFiltroAlerta').forEach(selector => {
                selector.addEventListener('change', function() {
                    aplicarFiltrosTabla(this.dataset.tabla);
                });
            });
        });
    </script>
@endsection
