
{{--
ESTO ES UNA PRUEBA PARA VERSI EN LA MAQUINA VIRTUAL TAMBIEN SE VE EN CUANTO LO GUARDE
-----------------------------------------------------------CODIGO EN DESUSO--------------------------

@extends('auth.plantilla')

@section('contenido')
    <style>
        /* Estilos de la tabla */
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
        }

        .tabla-hidro td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 0.85rem;
        }

        /* Clases para pintar las celdas según su nivel de alerta */
        .fila-alerta-3 {
            background-color: rgba(255, 0, 0, 0.15) !important;
            font-weight: bold;
        }

        /* Rojo */
        .fila-alerta-2 {
            background-color: rgba(255, 140, 0, 0.15) !important;
        }

        /* Naranja */
        .fila-alerta-1 {
            background-color: rgba(255, 215, 0, 0.15) !important;
        }

        /* Amarillo */

        /* Estilo para el punto segun el color de alerta */
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

        /* Estilo para el recuento de las estaciones de la tabla */
        .pill-global {
            background-color: #455a64;
            color: white;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
        }

        /*ESTILO PARA LOS GRÁFICOS*/
        .grafico-fila {
            background: rgba(255, 255, 255, 0.5);
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .grafico-fila:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .modal-overlay {
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

        .modal-content {
            background: white;
            padding: 24px;
            border-radius: 12px;
            width: min(1100px, 100%);
            max-height: calc(100vh - 48px);
            overflow-y: auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .btn-close {
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-close:hover {
            background: #dc2626;
        }
    </style>
--}}
    {{-- ENCABEZADO --}}
{{--
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>{{ $titulo }}</h2>
        <div>
            {{-- Recuenta dinamicamente el numero de aforos --}}
            {{--
            <span class="pill-global">{{ count($aforos) }} Estaciones</span>
            <small style="color: #666; margin-left: 10px;">Última actualización: {{ now()->format('H:i') }}</small>
        </div>
    </div>
 --}}
    {{-- Tabla principal --}}
    {{-- <table class="tabla-hidro">
        <thead>
            <tr>
                <th>Código / Nombre</th>
                <th>Río</th>
                <th>IP21</th>
                <th>Caudal</th>
                <th>Digital</th>
                <th>Valor</th>
                <th>Hora</th>
                <th>Último Estado</th>
                <th>Estado Actual</th>
                <th>Gráfico (7d)</th>
            </tr>
        </thead>
        <tbody>
             --}}
            {{-- Recorre todos los aforos
            @forelse($aforos as $ar)
                {{-- Cambia el color de la fila dependiendo del nivel de alerta calculado
                <tr class="fila-alerta-{{ $ar->nivel_alerta }}">

                    <td>
                        {{-- Si no encuentra el dato pone "---" para que no pete
                        <strong>{{ $ar->ur_codigo ?? '---' }}</strong><br>
                        {{ $ar->ur_nombre }}
                    </td>
                    <td>{{ $ar->ur_rio ?? '---' }}</td>
                    <td>{{ $ar->ur_tag_ip21 ?? '---' }}</td>
                    <td>{{ $ar->ur_tag_ip21_caudal ?? '---' }}</td>
                    <td>{{ $ar->ur_tag_digital_ip21 ?? '---' }}</td>

                    {{-- Solo se ven 2 decimales y punto para miles
                    <td style="font-weight: bold;">
                        {{ $ar->rde_valor ? number_format($ar->rde_valor, 2, ',', '.') : '---' }}
                    </td>

                    <td>{{ $ar->rde_hora ?? 'No hay hora disponible' }}</td>

                    {{-- ULTIMO NIVEL DE ALERTA
                    <td>
                        <span class="status-dot dot-{{ $ar->ur_ultimo_nivel_alerta ?? 0 }}"></span>
                        @if ($ar->ur_ultimo_nivel_alerta == 3)
                            <span style="color:red">ALERTA 3</span>
                        @elseif($ar->ur_ultimo_nivel_alerta == 2)
                            <span style="color:orange">ALERTA 2</span>
                        @elseif($ar->ur_ultimo_nivel_alerta == 1)
                            <span style="color:gold">ALERTA 1</span>
                        @else
                            <span style="color:gray">NORMAL</span>
                        @endif
                    </td>

                    {{-- ESTADO ACTUAL
                    <td>
                        <span class="status-dot dot-{{ $ar->nivel_alerta ?? 0 }}"></span>
                        @if ($ar->nivel_alerta == 3)
                            <b style="color:red">ALERTA 3</b>
                        @elseif($ar->nivel_alerta == 2)
                            <b style="color:orange">ALERTA 2</b>
                        @elseif($ar->nivel_alerta == 1)
                            <b style="color: gold">ALERTA 1</b>
                        @else
                            <span style="color:gray">NORMAL</span>
                        @endif
                    </td>

                    {{-- CONTENEDOR DEL MINI-GRÁFICO, (data-codigo sirve para saber a que estacion pedirle los datos a la API)

                    <td>
                        <div class="grafico-fila" data-codigo="{{ $ar->ur_codigo }}" style="width: 150px; height: 40px;">
                            <small style="color: #999; font-size: 10px; padding-left: 5px;">Cargando...</small>
                        </div>
                    </td>
                </tr>
            @empty
                {{-- Si la base de datos no devuelve ningún aforo
                <tr>
                    <td colspan="10" style="text-align: center; padding: 40px; color: #999;">
                        No hay Aforos en Ríos registrados para esta zona.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Estructuta del grafico ampliado, por defecto esta oculto, solo se muestra al clicar en el mini grafico
    <div id="graficoModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle" style="margin: 0; color: #333;">Detalle de Estación</h3>
                <button id="closeModal" class="btn-close">X</button>
            </div>
            <div class="modal-body">
                <div id="chartDetalle"
                    style="min-height: 360px; display: flex; align-items: center; justify-content: center; color: #666;">
                    Cargando datos detallados...
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Inicializamos los elementos para poder contolarlos
            const contenedores = document.querySelectorAll('.grafico-fila');
            const modal = document.getElementById('graficoModal');
            const closeModal = document.getElementById('closeModal');
            // Variable para guardar el grafico grande y asi poder borrarlo cuando se abra otro
            let chartAmpliado = null;

            // Eventos para cerrar elgrafico ampliado, se cierra al clicar la X o clicar fuera
            closeModal.addEventListener('click', () => modal.style.display = 'none');
            window.addEventListener('click', (e) => {
                if (e.target === modal) modal.style.display = 'none';
            });

            // Contenedores de cada mini grafico, uno a uno pide los datos a la API
            contenedores.forEach(async (div) => {
                const codigo = div.dataset.codigo;
                if (!codigo || codigo === '---') return;

                // URL de la api, en este caso es para 7 dias
                const url = `{{ url('/api/historial') }}/${codigo}?dias=7`;

                try {
                    // Pide los datos al servidor para convertirlos en JSON
                    const res = await fetch(url);
                    const json = await res.json();

                    if (json.valores && json.valores.length > 0) {
                        div.innerHTML = '';

                        // Configuracion del mini grafico
                        new ApexCharts(div, {
                            chart: {
                                type: 'area',
                                height: 40,
                                sparkline: {
                                    enabled: true
                                },
                                animations: {
                                    enabled: false
                                }
                            },
                            stroke: {
                                curve: 'smooth',
                                width: 2
                            },
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    opacityFrom: 0.4,
                                    opacityTo: 0
                                }
                            },
                            series: [{
                                name: 'Valor',
                                data: json.valores
                            }],
                            colors: ['#3b82f6'],
                            tooltip: {
                                fixed: {
                                    enabled: false
                                },
                                x: {
                                    show: false
                                },
                                y: {
                                    title: {
                                        formatter: () => ''
                                    }
                                }
                            }
                        }).render();

                        // Al clicar el mini grafico se abre el grafico grande se le pasan los datos obtenidos de la API
                        div.addEventListener('click', () => abrirGraficoAmpliado(codigo, json));
                    } else {
                        // Si no encuentra datos, muestra un mensaje en la parte del mini grafico
                        div.innerHTML =
                            '<small style="color:#ccc; font-size:9px;">No se han encontrado datos</small>';
                        div.style.cursor = 'default';
                    }
                } catch (err) {
                    // Para que no pete lo dejamos en blanco y asi no salta el error 500
                    div.innerHTML = '';
                }
            });

            function abrirGraficoAmpliado(codigo, json) {
                modal.style.display = 'flex';
                document.getElementById('modalTitle').innerText = `Historial Detallado: Estación ${codigo}`;

                const chartContenedor = document.getElementById('chartDetalle');
                chartContenedor.innerHTML = '';
                // Formatea las fechas para que ApexCharts las interprete correctamente
                const datosMapeados = json.fechas.map((f, i) => ({
                    x: f,
                    y: json.valores[i]
                }));

                // si hay un grafico anterior lo destruye para no tener varios uno encima del otro
                if (chartAmpliado) chartAmpliado.destroy();

                // (Esta parte no se si lo hace del todo bien pero se supone que si) Lo que hace es calcular las zonas del umbral para poner el color segun el nivel
                const umbrales = json.umbrales || [];
                umbrales.sort((a, b) => a.valor - b.valor);

                const zonasUmbrales = [];
                for (let i = 0; i < umbrales.length; i++) {
                    const u = umbrales[i];

                    const valorSiguiente = umbrales[i + 1] ? umbrales[i + 1].valor : 99999;

                    zonasUmbrales.push({
                        y: u.valor,
                        y2: valorSiguiente,
                        fillColor: u.color,
                        opacity: 0.15,
                        label: {
                            borderColor: u.color,
                            style: {
                                color: '#fff',
                                background: u.color,
                                fontWeight: 'bold',
                                padding: {
                                    left: 5,
                                    right: 5,
                                    top: 2,
                                    bottom: 2
                                }
                            },
                            text: `${u.texto} (> ${u.valor})`
                        }
                    });
                }
                // Configuraciond del grafico grande
                const options = {
                    chart: {
                        type: 'area',
                        height: 350,
                        zoom: {
                            enabled: true
                        }
                    },
                    series: [{
                        name: 'Nivel',
                        data: datosMapeados
                    }],

                    annotations: {
                        yaxis: zonasUmbrales
                    },

                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            opacityFrom: 0.6,
                            opacityTo: 0.1
                        }
                    },
                    colors: ['#2563eb'],

                    xaxis: {
                        type: 'datetime',
                        labels: {
                            datetimeUTC: false
                        },
                        title: {
                            text: 'Fecha y Hora'
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Valor Registrado'
                        }
                    },
                    tooltip: {
                        x: {
                            format: 'dd MMM yyyy - HH:mm'
                        }
                    },

                    dataLabels: {
                        enabled: false
                    },
                    markers: {
                        size: 0,
                        hover: {
                            size: 6
                        }
                    },
                    grid: {
                        borderColor: '#f1f1f1',
                        xaxis: {
                            lines: {
                                show: true
                            }
                        },
                        yaxis: {
                            lines: {
                                show: true
                            }
                        }
                    }
                };
                // Pinta el grafico grande
                chartAmpliado = new ApexCharts(chartContenedor, options);
                chartAmpliado.render();
            }
        });
    </script>
@endsection

