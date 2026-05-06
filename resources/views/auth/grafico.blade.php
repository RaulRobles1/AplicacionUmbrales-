<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Estación {{ $codigo }}</title>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        :root {
            --fondo: #f1f5f9;
            --texto: #0f172a;
            --textoSecundario: #475569;
            --borde: #e2e8f0;
            --azul: #3b82f6;
            --azulClaro: #dbeafe;
            --rojo: #ef4444;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(180deg, #f8fafc 0%, var(--fondo) 100%);
            color: var(--texto);
            margin: 0;
            padding: 32px 20px;
        }

        .contenedorPagina {
            max-width: 1180px;
            margin: 0 auto;
        }

        #contenedorGrafico {
            background: white;
            padding: 28px;
            border-radius: 16px;
            border: 1px solid var(--borde);
            box-shadow: 0 20px 25px -18px rgba(15, 23, 42, 0.35);
            margin: 0 auto;
        }

        .cabeceraGrafico {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 14px;
        }

        .tituloGrafico {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        .subtituloGrafico {
            margin: 8px 0 0;
            color: var(--textoSecundario);
            font-size: 0.95rem;
        }

        .codigoEstacion {
            display: inline-flex;
            align-items: center;
            background: var(--azulClaro);
            color: #1e3a8a;
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 6px 11px;
        }

        #contenedorSelector {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
            margin: 0 0 18px;
            padding: 12px;
            background: #f8fafc;
            border: 1px solid var(--borde);
            border-radius: 10px;
        }

        #contenedorSelector label {
            font-weight: 600;
            color: #334155;
        }

        #rango {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 8px 12px;
            background: #ffffff;
            color: #0f172a;
            font-size: 0.92rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        #rango:focus {
            border-color: var(--azul);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        #sinDatos {
            text-align: center;
            color: var(--rojo);
            font-weight: 700;
            display: none;
            margin: 8px 0 14px;
            padding: 10px 14px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
        }

        #grafico {
            min-height: 390px;
            margin-top: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 8px;
            background: #ffffff;
        }

        @media (max-width: 768px) {
            body {
                padding: 14px;
            }

            #contenedorGrafico {
                padding: 16px;
            }

            .cabeceraGrafico {
                flex-direction: column;
                align-items: flex-start;
            }

            .tituloGrafico {
                font-size: 1.15rem;
            }
        }
    </style>
</head>

<body>
    <div class="contenedorPagina">
        <div id="contenedorGrafico">
            <div class="cabeceraGrafico">
                <div>
                    <h2 class="tituloGrafico">Historial de la estación</h2>
                    <p class="subtituloGrafico">Visualización temporal de valores medios y umbral máximo.</p>
                </div>
                <span class="codigoEstacion">{{ $codigo }}</span>
            </div>

            <div id="contenedorSelector">
                <label for="rango">Seleccionar rango:</label>
                <select id="rango">
                    <option value="7">Últimos 7 días</option>
                    <option value="30">Últimos 30 días</option>
                    <option value="365" selected>Último año</option>
                </select>
            </div>

            <div id="sinDatos">No hay datos disponibles para este rango.</div>
            <div id="grafico"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const selector = document.getElementById('rango');
            const divSinDatos = document.getElementById('sinDatos');
            let grafico;

            async function cargarGrafico() {
                const dias = selector.value;
                const url = "{{ route('api.grafico', ['codigo' => $codigo]) }}?dias=" + dias;

                try {
                    const res = await fetch(url);
                    const json = await res.json();

                    if (!json.fechas.length || !json.valores.length) {
                        if (grafico) grafico.updateSeries([{
                            data: []
                        }, {
                            data: []
                        }]);
                        divSinDatos.style.display = 'block';
                        return;
                    }
                    divSinDatos.style.display = 'none';

                    // Datos para ApexCharts
                    const datosArea = json.fechas.map((f, i) => ({
                        x: f,
                        y: json.valores[i]
                    }));

                    const umbral = datosArea.map(d => ({
                        x: d.x,
                        y: 75
                    }));

                    const opciones = {
                        chart: {
                            type: 'line',
                            height: 450,
                            stacked: false,
                            zoom: {
                                enabled: true
                            },
                            toolbar: {
                                show: true
                            }
                        },
                        series: [{
                                name: 'Valor Medio',
                                type: 'area',
                                data: datosArea
                            },
                            {
                                name: 'Umbral Máximo',
                                type: 'line',
                                data: umbral
                            }
                        ],
                        stroke: {
                            curve: 'smooth',
                            width: [3, 2]
                        },
                        fill: {
                            type: ['gradient', 'solid'],
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.7,
                                opacityTo: 0.3
                            }
                        },
                        colors: ['#3b82f6', '#ef4444'],
                        markers: {
                            size: 4
                        },
                        xaxis: {
                            type: 'datetime',
                            title: {
                                text: 'Fecha y Hora'
                            }
                        },
                        yaxis: {
                            title: {
                                text: 'Valor'
                            }
                        },
                        tooltip: {
                            shared: true,
                            intersect: false,
                            x: {
                                format: 'dd MMM yyyy - HH:mm'
                            }
                        },
                        grid: {
                            borderColor: '#e7e7e7',
                            row: {
                                colors: ['#f3f3f3', 'transparent'],
                                opacity: 0.5
                            }
                        }
                    };

                    if (grafico) {
                        grafico.updateOptions(opciones);
                        grafico.updateSeries(opciones.series);
                    } else {
                        grafico = new ApexCharts(document.querySelector("#grafico"), opciones);
                        grafico.render();
                    }

                } catch (err) {
                    console.error("Error cargando datos:", err);
                    divSinDatos.innerText = 'Error al cargar los datos.';
                    divSinDatos.style.display = 'block';
                }
            }

            selector.addEventListener('change', cargarGrafico);
            cargarGrafico();
        });
    </script>

</body>

</html>
