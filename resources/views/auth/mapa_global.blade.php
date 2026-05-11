@extends('auth.plantilla')

    @section('contenido')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />

    <style>
        #mapa-tajo { width: 100%; height: 70vh; border-radius: 6px; border: 1px solid #ced4da; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 1; }

        .panel-filtros { background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #e2e8f0; display: flex; gap: 15px; flex-wrap: wrap; align-items: center; }
        .filtro-control { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #f8fafc; color: #334155; font-size: 0.9rem; flex-grow: 1; min-width: 150px; }
        .filtro-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        .leyenda-alertas {
            margin: 0 0 12px 0;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px 14px;
            align-items: center;
        }
        .leyenda-titulo {
            font-size: 0.85rem;
            font-weight: 700;
            color: #334155;
            margin-right: 6px;
        }
        .leyenda-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.82rem;
            color: #334155;
        }
        .leyenda-punto {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            border: 1px solid rgba(15, 23, 42, 0.15);
        }

        /* Buscador de texto */
        .buscador-texto-container { flex-grow: 2; position: relative; }
        .buscador-texto-container i { position: absolute; left: 12px; top: 12px; color: #94a3b8; }
        .buscador-texto { padding-left: 35px !important; width: 100%; }

        /* Marcadores y Popups */
        .custom-div-icon { background: transparent; border: none; }
        .marker-pin { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: 700; box-shadow: 0 3px 6px rgba(0,0,0,0.4); border: 2px solid white; transition: all 0.2s;}
        .marker-pin.compacto { width: 26px; height: 26px; font-size: 11px; }

        .bg-alerta-0 { background-color: #10b981; }
        .bg-alerta-1 { background-color: #facc15; color: #1e293b; }
        .bg-alerta-2 { background-color: #f97316; }
        .bg-alerta-3 { background-color: #ef4444; animation: parpadeo-peligro 2s infinite; }

        @keyframes parpadeo-peligro { 0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); } 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); } }

        .leaflet-tooltip.tooltip-mapa {
            background: #ffffff;
            border: 1px solid #dbe2ea;
            border-radius: 8px;
            color: #1f2937;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.18);
            padding: 0;
        }
        .tooltip-mapa .leaflet-tooltip-content {
            margin: 0;
        }
        .tooltipCabecera {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 8px 10px;
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
        }
        .tooltipCuerpo {
            padding: 8px 10px;
            font-size: 12px;
            line-height: 1.45;
            color: #334155;
        }
        .tendencia-mapa {
            font-size: 2rem;
            font-weight: 700;
        }
        .textoAlertaRojo { color: #b91c1c; font-weight: 700; }
        .textoAlertaNaranja { color: #c2410c; font-weight: 700; }
        .textoAlertaAmarillo { color: #eab308; font-weight: 700; text-shadow: 0 1px 0 rgba(0, 0, 0, 0.08); }
        .textoAlertaNormal { color: #166534; font-weight: 700; }
        .cuenca-tajo-capa {
            pointer-events: none;
        }
    </style>

    <div class="container-fluid mb-4 mt-3">
        <h3 style="color: #475569; font-weight: 600; margin-bottom: 15px;"> {{ $titulo }}</h3>


        <div class="panel-filtros">

            <div class="buscador-texto-container">
                <i class="fas fa-search"></i>
                <input type="text" id="filtro-texto" class="filtro-control buscador-texto" placeholder="Buscar por nombre o código (ej: AR01, Bolarque)...">
            </div>

            <select id="filtro-tipo" class="filtro-control">
                <option value="todos">Todos los Tipos</option>
                <option value="embalse">Embalses</option>
                <option value="aforos_rio">Aforos en Río</option>
            </select>

            <select id="filtro-ccaa" class="filtro-control">
                <option value="todas">Todas las CCAA</option>
                @foreach($listaCcaa as $comunidad)
                    @if(strcasecmp(trim((string) $comunidad), 'AYTO. MADRID') !== 0)
                        <option value="{{ $comunidad }}">{{ $comunidad }}</option>
                    @endif
                @endforeach
            </select>

            <select id="filtro-alerta" class="filtro-control">
                <option value="todos">Cualquier Estado</option>
                <option value="0"> Normalidad</option>
                <option value="1"> Alerta Amarilla</option>
                <option value="2"> Alerta Naranja</option>
                <option value="3"> Alerta Roja</option>
            </select>
        </div>

        <div class="leyenda-alertas" aria-label="Leyenda de niveles de alerta del mapa">
            <span class="leyenda-titulo">Leyenda de alertas:</span>
            <span class="leyenda-item">
                <span class="leyenda-punto bg-alerta-0"></span>
                Normalidad
            </span>
            <span class="leyenda-item">
                <span class="leyenda-punto bg-alerta-1"></span>
                Alerta Amarilla
            </span>
            <span class="leyenda-item">
                <span class="leyenda-punto bg-alerta-2"></span>
                Alerta Naranja
            </span>
            <span class="leyenda-item">
                <span class="leyenda-punto bg-alerta-3"></span>
                Alerta Roja
            </span>
        </div>

        <div id="mapa-tajo"></div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var map = L.map('mapa-tajo').setView([39.8628, -4.0273], 7);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(map);

            var capaMarcadores = L.markerClusterGroup({
                chunkedLoading: true,
                showCoverageOnHover: false,
                spiderfyOnMaxZoom: true,
                disableClusteringAtZoom: 11,
                maxClusterRadius: 55,
                iconCreateFunction: function(cluster) {
                    var hijos = cluster.getAllChildMarkers();
                    var maxAlerta = 0;
                    hijos.forEach(function(m) {
                        maxAlerta = Math.max(maxAlerta, Number(m.options.nivelAlerta || 0));
                    });

                    var claseColor = 'bg-alerta-' + maxAlerta;
                    var html = `<div class="marker-pin compacto ${claseColor}">${hijos.length}</div>`;

                    return L.divIcon({
                        className: 'custom-div-icon',
                        html: html,
                        iconSize: [26, 26],
                        iconAnchor: [13, 13]
                    });
                }
            });
            map.addLayer(capaMarcadores);

            var todosLosPuntos = (@json($puntos) || []).map(function(p) {
                var lat = parseFloat(String(p.latitud).replace(',', '.'));
                var lng = parseFloat(String(p.longitud).replace(',', '.'));
                return Object.assign({}, p, {
                    latNum: lat,
                    lngNum: lng,
                    nivel_alerta: parseInt(p.nivel_alerta ?? 0, 10) || 0
                });
            }).filter(function(p) {
                return !isNaN(p.latNum) && !isNaN(p.lngNum);
            });
            var puntosActivos = todosLosPuntos.slice();
            var boundsCuenca = null;

            function etiquetaAlerta(nivel) {
                if (nivel === 3) return { texto: 'Rojo', clase: 'textoAlertaRojo' };
                if (nivel === 2) return { texto: 'Naranja', clase: 'textoAlertaNaranja' };
                if (nivel === 1) return { texto: 'Amarillo', clase: 'textoAlertaAmarillo' };
                return { texto: 'Normal', clase: 'textoAlertaNormal' };
            }

            function etiquetaPunto(punto) {
                return punto.tipo === 'embalse' ? 'E' : 'AR';
            }

            function valorFormateado(punto) {
                if (punto.valor_actual === null || punto.valor_actual === undefined || punto.valor_actual === '') {
                    return 'Sin datos';
                }
                var numero = Number(punto.valor_actual);
                if (!isNaN(numero)) {
                    return numero.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
                return String(punto.valor_actual);
            }

            function tooltipHtml(punto, totalAgrupados) {
                var alerta = etiquetaAlerta(punto.nivel_alerta);
                var extra = totalAgrupados > 1 ? `<div><strong>Agrupa:</strong> ${totalAgrupados} estaciones (máxima alerta aplicada)</div>` : '';
                return `
                    <div class="tooltipCabecera">${punto.nombre || 'Estación'}</div>
                    <div class="tooltipCuerpo">
                        <div><strong>Código:</strong> ${punto.codigo || '---'}</div>
                        ${extra}
                        <div><strong>Valor:</strong> ${valorFormateado(punto)}</div>
                        <div><strong>Alerta:</strong> <span class="${alerta.clase}">${alerta.texto}</span></div>
                        <div><strong>Tendencia:</strong> <span class="tendencia-mapa">${punto.tendencia || '---'}</span></div>
                    </div>
                `;
            }

            function marcadorCustom(lat, lng, nivelAlerta, textoEtiqueta, compacto) {
                var claseColor = 'bg-alerta-' + nivelAlerta;
                var claseCompacta = compacto ? 'compacto' : '';
                var iconoPersonalizado = L.divIcon({
                    className: 'custom-div-icon',
                    html: `<div class="marker-pin ${claseColor} ${claseCompacta}">${textoEtiqueta}</div>`,
                    iconSize: compacto ? [26, 26] : [32, 32],
                    iconAnchor: compacto ? [13, 13] : [16, 16]
                });
                return L.marker([lat, lng], { icon: iconoPersonalizado });
            }

            function cargarCuenca() {
                return fetch("{{ asset('geojson/CuencaTajoElegido.geojson') }}")
                    .then(function(response) {
                        if (!response.ok) {
                            throw new Error('No se pudo cargar la cuenca: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(function(geojson) {
                        var capaCuenca = L.geoJSON(geojson, {
                            style: {
                                color: '#989a9e',
                                weight: 2,
                                fillColor: '#cbd5e1',
                                fillOpacity: 0.5
                            },
                            className: 'cuenca-tajo-capa'
                        }).addTo(map);
                        boundsCuenca = capaCuenca.getBounds();
                        map.fitBounds(boundsCuenca, { padding: [20, 20] });
                    })
                    .catch(function(error) {
                        console.error(error.message);
                    });
            }

            function dibujarMapa(puntosA_Pintar) {
                capaMarcadores.clearLayers();

                puntosA_Pintar.forEach(function(punto) {
                    var marcador = marcadorCustom(
                        punto.latNum,
                        punto.lngNum,
                        punto.nivel_alerta,
                        etiquetaPunto(punto),
                        false
                    );

                    marcador.options.nivelAlerta = punto.nivel_alerta;

                    marcador.bindTooltip(tooltipHtml(punto, 1), {
                        direction: 'top',
                        sticky: true,
                        className: 'tooltip-mapa',
                        opacity: 1
                    });
                    marcador.on('mouseover', function() { marcador.openTooltip(); });
                    marcador.on('mouseout', function() { marcador.closeTooltip(); });
                    capaMarcadores.addLayer(marcador);
                });
            }

            // Filtros para el mapa
            function aplicarFiltros(aplicarZoomCcaa) {
                var textoFiltro = document.getElementById('filtro-texto').value.toLowerCase().trim();
                var tipoFiltro = document.getElementById('filtro-tipo').value;
                var ccaaFiltro = document.getElementById('filtro-ccaa').value;
                var alertaFiltro = document.getElementById('filtro-alerta').value;

                puntosActivos = todosLosPuntos.filter(function(p) {
                    var coincideTexto = true;
                    if(textoFiltro !== "") {
                        coincideTexto = p.nombre.toLowerCase().includes(textoFiltro) || p.codigo.toLowerCase().includes(textoFiltro);
                    }

                    var tipoPunto = String(p.tipo || '').toLowerCase();
                    var coincideTipo = (
                        tipoFiltro === 'todos' ||
                        (tipoFiltro === 'embalse' && tipoPunto === 'embalse') ||
                        (tipoFiltro === 'aforos_rio' && tipoPunto !== 'embalse')
                    );
                    var coincideCcaa = (ccaaFiltro === 'todas' || p.ccaa === ccaaFiltro);
                    var coincideAlerta = (alertaFiltro === 'todos' || p.nivel_alerta.toString() === alertaFiltro);

                    return coincideTexto && coincideTipo && coincideCcaa && coincideAlerta;
                });

                dibujarMapa(puntosActivos);

                if (aplicarZoomCcaa && ccaaFiltro !== 'todas') {
                    if (puntosActivos.length > 0) {
                        var bounds = L.latLngBounds(puntosActivos.map(function(p) {
                            return [p.latNum, p.lngNum];
                        }));
                        map.fitBounds(bounds, { padding: [30, 30], maxZoom: 11 });
                    } else if (boundsCuenca) {
                        map.fitBounds(boundsCuenca, { padding: [20, 20] });
                    }
                } else if (aplicarZoomCcaa && boundsCuenca) {
                    map.fitBounds(boundsCuenca, { padding: [20, 20] });
                }
            }

            document.getElementById('filtro-tipo').addEventListener('change', function() {
                aplicarFiltros(false);
            });
            document.getElementById('filtro-ccaa').addEventListener('change', function() {
                aplicarFiltros(true);
            });
            document.getElementById('filtro-alerta').addEventListener('change', function() {
                aplicarFiltros(false);
            });

            document.getElementById('filtro-texto').addEventListener('keyup', function() {
                aplicarFiltros(false);
            });

            cargarCuenca();
            dibujarMapa(todosLosPuntos);
        });
    </script>
@endsection
