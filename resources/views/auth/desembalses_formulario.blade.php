@extends('auth.plantilla')

@section('contenido')
    <div class="contenedorEmergencias desembalseLayout">
        <section class="desembalseFormulario">
        <header class="cabeceraFormulario">
            <h2>ENVÍO DESEMBALSES</h2>
            <p><strong> Indica los datos del desembalse previsto</strong></p>
        </header>

        @if (session('success'))
            <div class="alertaExito">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alertaError">
                <strong>Ha ocurrido un error, revisa los campos:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('desembalses.guardar') }}" class="formularioEmergencias">
            @csrf

            <div class="cuadriculaFormulario">
                <div class="grupoFormulario">
                    <label for="embalse">Embalse</label>
                    <select id="embalse" name="embalse" required>
                        <option value="">Selecciona un embalse</option>
                        @foreach ($embalses as $embalse)
                            <option value="{{ $embalse->er_codigo }}" data-nombre="{{ $embalse->er_nombre }}" data-ccaa="{{ $embalse->er_comunidad_autonoma_id }}" @selected(old('embalse') === $embalse->er_codigo)>
                                {{ $embalse->er_codigo }} - {{ $embalse->er_nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grupoFormulario">
                    <label for="caudal">Caudal a desembalsar</label>
                    <input type="number" id="caudal" name="caudal" value="{{ old('caudal') }}" min="0" step="0.01" inputmode="decimal" required>
                </div>

                <div class="grupoFormulario">
                    <label for="fecha_hora_prevista">Fecha y hora prevista</label>
                    <input type="datetime-local" id="fecha_hora_prevista" name="fecha_hora_prevista" value="{{ old('fecha_hora_prevista') }}" required>
                </div>
            </div>

            <div>
                <h3><strong>Vista de correo</strong></h3>
                <hr>
                <p><strong>ASUNTO</strong></p>
                <p id="vista-asunto">Previsión desembalse</p>

                <p><strong>CUERPO</strong></p>
                <p id="vista-cuerpo" style="white-space: pre-line;">Por la presente les informamos de que en esta fecha está previsto empezar a desembalsar a razón de caudal m3/s.

Sin otro particular, reciba un cordial saludo.</p>
            </div>

            <button type="submit" class="botonPrincipal">Validar envío</button>
        </form>
        </section>

        <aside class="panelDestinatarios">
            <header class="cabeceraDestinatarios">
                <h3>Destinatarios</h3>
                <span id="contador-destinatarios">0</span>
            </header>
            <p class="textoAyudaDestinatarios">Se incluyen siempre los destinatarios generales y los de la CCAA del embalse:</p>

            <div class="listaDestinatarios">
                @forelse ($destinatarios as $destinatario)
                    <div class="destinatarioCorreo" data-ccaa="{{ $destinatario->rde_ccaa }}">
                        <span class="indicadorDestinatario" aria-hidden="true"></span>
                        <div>
                            @if ($destinatario->rde_nombre)
                                <strong>{{ $destinatario->rde_nombre }}</strong>
                            @endif
                            <span>{{ $destinatario->rde_direccion }}</span>
                        </div>
                    </div>
                @empty
                    <p class="destinatariosVacios">No hay destinatarios activos configurados para desembalses.</p>
                @endforelse
            </div>
            <p id="destinatarios-sin-coincidencias" class="destinatariosVacios" hidden>
                No hay destinatarios específicos para la CCAA seleccionada.
            </p>
        </aside>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const embalse = document.getElementById('embalse');
            const caudal = document.getElementById('caudal');
            const fecha = document.getElementById('fecha_hora_prevista');
            const vistaAsunto = document.getElementById('vista-asunto');
            const vistaCuerpo = document.getElementById('vista-cuerpo');
            const contadorDestinatarios = document.getElementById('contador-destinatarios');
            const destinatarios = [...document.querySelectorAll('.destinatarioCorreo')];
            const sinCoincidencias = document.getElementById('destinatarios-sin-coincidencias');

            const actualizarDestinatarios = () => {
                const opcion = embalse.options[embalse.selectedIndex];
                const ccaaSeleccionada = opcion?.dataset.ccaa || '';
                let visibles = 0;

                destinatarios.forEach((destinatario) => {
                    const esGeneral = destinatario.dataset.ccaa === '0';
                    const perteneceALaCcaa = destinatario.dataset.ccaa === ccaaSeleccionada;
                    const mostrar = Boolean(ccaaSeleccionada) && (esGeneral || perteneceALaCcaa);

                    destinatario.hidden = !mostrar;
                    destinatario.style.display = mostrar ? 'flex' : 'none';
                    visibles += mostrar ? 1 : 0;
                });

                contadorDestinatarios.textContent = visibles;
                sinCoincidencias.hidden = destinatarios.length === 0 || visibles > 0;
            };

            const actualizarVistaCorreo = () => {
                const opcion = embalse.options[embalse.selectedIndex];
                const codigoEmbalse = opcion?.value || '{Código}';
                const nombreEmbalse = opcion?.dataset.nombre || '{Embalse}';
                const valorCaudal = caudal.value || '{Caudal}';
                const valorFecha = fecha.value
                    ? new Intl.DateTimeFormat('es-ES', {
                        dateStyle: 'short',
                        timeStyle: 'short',
                    }).format(new Date(fecha.value))
                    : '{Fecha y hora}';

                vistaAsunto.textContent = `Previsión desembalse ${codigoEmbalse} - ${nombreEmbalse}`;
                vistaCuerpo.textContent = `Por la presente les informamos de que en esta ${valorFecha} está previsto empezar a desembalsar el embalse ${codigoEmbalse} - ${nombreEmbalse} a razón de ${valorCaudal}m3/s.\n\nSin otro particular, reciba un cordial saludo.`;
            };

            [embalse, caudal, fecha].forEach((campo) => {
                campo.addEventListener('input', actualizarVistaCorreo);
                campo.addEventListener('change', actualizarVistaCorreo);
            });

            embalse.addEventListener('change', actualizarDestinatarios);

            actualizarVistaCorreo();
            actualizarDestinatarios();
        });
    </script>
@endsection
