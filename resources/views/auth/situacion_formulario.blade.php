@extends('auth.plantilla')

@section('contenido')
    @php
        $prefillCcaa = request()->query('ccaa_id');
        $prefillProvincia = request()->query('provincia_id');
        $prefillAlcance = request()->query('alcance');
        $prefillFecha = request()->query('fecha');
        $prefillHora = request()->query('hora');
        $prefillNivel = request()->query('nivel');
        $prefillModo = request()->query('modo');
        $prefillZona = request()->query('zona');
        $prefillReturnTo = request()->query('return_to');
        $fechaPreseleccionada = $prefillFecha ?: null;
        $modoEdicionDesdePlanActivo = in_array($prefillModo, ['editar', 'preconfigurar', 'crear'], true) && !empty($prefillCcaa);
        $ccaaSeleccionada = $ccaaParaFormulario->firstWhere('c_id', (int) $prefillCcaa);
        $nombreCcaaSeleccionada = $ccaaSeleccionada->c_comunidad_autonoma ?? 'No especificada';
        $debeBloquearCamposBase = $modoEdicionDesdePlanActivo && !empty($fechaPreseleccionada);
        if ($modoEdicionDesdePlanActivo && (empty($prefillNivel) || (int) $prefillNivel === 0)) {
            $prefillNivel = 1;
        }
        $prefillPayload = [
            'provincia_id' => old('provincias_ids.0', $prefillProvincia),
            'provincias_ids' => old('provincias_ids', []),
            'ccaa_id' => old('ccaa_id', $prefillCcaa),
            'alcance' => $prefillAlcance,
        ];
    @endphp

    <div class="contenedorEmergencias">

        <header class="cabeceraFormulario">
            <h2>PLANES EMERGENCIA CCAA</h2>
            <p>Formulario de registro de incidencias y situaciones de emergencia</p>
        </header>

        @if ($modoEdicionDesdePlanActivo)
            <div class="alertaContexto">
                <strong>Edición guiada desde el plan</strong>
                <span>
                    Zona: {{ $prefillZona ?: 'No especificada' }} |
                    Fecha: {{ $fechaPreseleccionada ?: date('Y-m-d') }}
                </span>
            </div>
        @endif

        @if (session('success'))
            <div class="alertaExito">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alertaError">
                <strong>Se encontraron los siguientes errores:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('emergencias.guardar') }}" enctype="multipart/form-data" class="formularioEmergencias">
            @csrf
            @if ($prefillReturnTo === 'plan')
                <input type="hidden" name="return_to" value="plan">
            @endif

            <div class="cuadriculaFormulario">

                <div class="grupoFormulario">
                    <label>Comunidad Autónoma</label>
                    @if ($debeBloquearCamposBase)
                        <input type="hidden" name="ccaa_id" value="{{ old('ccaa_id', $prefillCcaa) }}">
                        <div class="campoBloqueado">{{ $nombreCcaaSeleccionada }}</div>
                    @else
                        <select name="ccaa_id" id="selectorCcaa" required onchange="filtrarProvincias()">
                            <option value="">Seleccione una comunidad...</option>
                            @foreach ($ccaaParaFormulario as $comunidad)
                                <option value="{{ $comunidad->c_id }}"
                                    {{ old('ccaa_id', $prefillCcaa) == $comunidad->c_id ? 'selected' : '' }}>
                                    {{ $comunidad->c_comunidad_autonoma }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                    @error('ccaa_id')
                        <span class="mensajeError">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grupoFormulario">
                    <label>Nivel de emergencia</label>
                    <select name="nivel" required>
                        <option value="0" {{ old('nivel', $prefillNivel) == '0' ? 'selected' : '' }}>Normalidad</option>
                        <option value="1" {{ old('nivel', $prefillNivel) == '1' ? 'selected' : '' }}>Preemergencia / Alerta</option>
                        <option value="2" {{ old('nivel', $prefillNivel) == '2' ? 'selected' : '' }}>Situación 0</option>
                        <option value="3" {{ old('nivel', $prefillNivel) == '3' ? 'selected' : '' }}>Situación 1</option>
                        <option value="4" {{ old('nivel', $prefillNivel) == '4' ? 'selected' : '' }}>Situación 2</option>
                        <option value="5" {{ old('nivel', $prefillNivel) == '5' ? 'selected' : '' }}>Situación 3</option>
                    </select>
                    @error('nivel')
                        <span class="mensajeError">{{ $message }}</span>
                    @enderror
                </div>

                <div class="panelProvincias" id="grupoProvincias">
                    <label class="etiquetaPanel">Provincias afectadas</label>

                    <div class="contenedorSeleccionarTodas">
                        <label class="etiquetaCheckbox">
                            <input type="checkbox" id="checkTodos" onclick="marcarTodas(this)">
                            <span class="textoCheckbox">Seleccionar todas las provincias</span>
                        </label>
                    </div>

                    <div id="contenedorCheckboxes" class="cuadriculaCheckboxes">
                    </div>

                    @error('provincias_ids')
                        <span class="mensajeError">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grupoFormulario">
                    <label>Fecha</label>
                    @if ($debeBloquearCamposBase)
                        <input type="hidden" name="fecha" value="{{ old('fecha', $fechaPreseleccionada ?: date('Y-m-d')) }}">
                        <div class="campoBloqueado">{{ old('fecha', $fechaPreseleccionada ?: date('Y-m-d')) }}</div>
                    @else
                        <input type="date" name="fecha" required value="{{ old('fecha', $fechaPreseleccionada ?: date('Y-m-d')) }}">
                    @endif
                    @error('fecha')
                        <span class="mensajeError">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grupoFormulario">
                    <label>Hora</label>
                    <input type="time" name="hora" required value="{{ old('hora', $prefillHora) }}">
                    @error('hora')
                        <span class="mensajeError">{{ $message }}</span>
                    @enderror
                </div>

            </div>

            <section class="seccionDocumentacion">
                <h3>Documentación asociada</h3>

                <div class="selectorDocumento">
                    <label class="tarjetaRadio">
                        <input type="radio" name="tipo_documento" value="pdf_oficial" checked onchange="toggleDoc()">
                        <div class="contenidoRadio">
                            <strong>Documento PDF</strong>
                            <span>Adjuntar archivo PDF del comunicado</span>
                        </div>
                    </label>

                    <label class="tarjetaRadio">
                        <input type="radio" name="tipo_documento" value="texto_correo" onchange="toggleDoc()">
                        <div class="contenidoRadio">
                            <strong>Generar PDF automáticamente</strong>
                            <span>Copiar contenido del email recibido</span>
                        </div>
                    </label>
                </div>

                <div id="cajaPdf" class="cajaDocumento">
                    <label class="subirArchivo">
                        <span>Seleccionar archivo PDF</span>
                        <input type="file" name="archivo_pdf" accept=".pdf" onchange="updateFileName(this)">
                    </label>
                    <small class="textoAyudaArchivo">Tamaño máximo permitido: 1.9 MB.</small>
                    <div id="nombreArchivo" class="nombreArchivo"></div>
                    <div id="errorArchivoPdf" class="mensajeError" style="display:none;"></div>
                </div>

                <div id="cajaCorreo" class="cajaDocumento oculto">
                    <textarea name="texto_correo" rows="6" placeholder="Pegue aquí el contenido del correo para generar el PDF automáticamente"></textarea>
                </div>
            </section>

            <div class="grupoFormulario completo">
                <label>Descripción corta</label>
                <textarea name="descripcion" rows="3" placeholder="Descripcion...">{{ old('descripcion', $prefillZona ? 'Actualización de situación reportada para ' . $prefillZona . '.' : '') }}</textarea>
            </div>

            <div class="accionesFormulario">
                <button type="button" class="botonSecundario" onclick="volverAtrasSeguro()">
                    Volver atrás
                </button>
                <button type="submit" class="botonPrincipal">
                    Guardar y Actualizar Calendario
                </button>
            </div>

        </form>

    </div>

    <style>
        * {
            box-sizing: border-box;
        }

        .contenedorEmergencias {
            max-width: 850px;
            margin: 20px auto;
            background: #ffffff;
            padding: 35px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            font-family: Inter, system-ui, -apple-system, Segoe UI, sans-serif;
        }

        .cabeceraFormulario {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
        }

        .cabeceraFormulario h2 {
            margin: 0 0 5px 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
        }

        .cabeceraFormulario p {
            margin: 0;
            color: #64748b;
            font-size: 0.9rem;
        }

        .alertaExito, .alertaError {
            margin-bottom: 20px;
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 0.9rem;
            animation: deslizarAbajo 0.3s ease-out;
        }

        .alertaContexto {
            margin-bottom: 20px;
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 0.88rem;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1e3a8a;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .alertaContexto strong {
            font-size: 0.92rem;
        }

        .alertaExito {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .alertaError {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .alertaError strong { display: block; margin-bottom: 6px; }
        .alertaError ul { margin: 0; padding-left: 20px; }
        .alertaError li { margin: 3px 0; }

        @keyframes deslizarAbajo {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .cuadriculaFormulario {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 20px;
        }

        .grupoFormulario {
            display: flex;
            flex-direction: column;
        }

        .grupoFormulario.completo {
            grid-column: 1 / -1;
        }

        .grupoFormulario label {
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 0.85rem;
            color: #475569;
        }

        .grupoFormulario input,
        .grupoFormulario select,
        .grupoFormulario textarea {
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.9rem;
            color: #1e293b;
            background: #f8fafc;
            transition: all 0.2s ease;
        }

        .grupoFormulario input:focus,
        .grupoFormulario select:focus,
        .grupoFormulario textarea:focus {
            outline: none;
            border-color: #3b82f6;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .grupoFormulario textarea {
            resize: vertical;
        }

        .campoBloqueado {
            padding: 9px 12px;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            font-size: 0.9rem;
            color: #1e3a8a;
            background: #eff6ff;
            font-weight: 600;
        }

        .panelProvincias {
            grid-column: 1 / -1;
            background: #f8fafc;
            padding: 16px;
            border-radius: 6px;
            border: 1px dashed #cbd5e1;
            display: none;
            animation: aparecer 0.3s ease-out;
        }

        @keyframes aparecer {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .etiquetaPanel {
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 0.85rem;
            color: #334155;
            display: block;
        }

        .contenedorTodos {
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .etiquetaCheckbox {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 0.85rem;
            color: #475569;
        }

        .cuadriculaCheckboxes {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 8px;
        }

        .cuadriculaCheckboxes label {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
        }

        .cuadriculaCheckboxes label:hover {
            border-color: #94a3b8;
        }

        .seccionDocumentacion {
            margin-top: 25px;
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #fdfdfd;
        }

        .seccionDocumentacion h3 {
            margin: 0 0 15px 0;
            font-size: 1rem;
            color: #334155;
        }

        .selectorDocumento {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .tarjetaRadio { position: relative; cursor: pointer; }
        .tarjetaRadio input[type="radio"] { position: absolute; opacity: 0; }

        .contenidoRadio {
            padding: 12px;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .contenidoRadio strong { display: block; font-size: 0.9rem; color: #334155; }
        .contenidoRadio span { display: block; font-size: 0.8rem; color: #64748b; margin-top: 3px;}

        .tarjetaRadio input[type="radio"]:checked + .contenidoRadio {
            border-color: #3b82f6;
            background: #eff6ff;
        }

        .cajaDocumento { margin-top: 15px; }

        .subirArchivo {
            display: flex;
            justify-content: center;
            padding: 15px;
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            color: #475569;
        }
        .subirArchivo:hover { border-color: #3b82f6; color: #3b82f6; background: white; }
        .subirArchivo input[type="file"] { display: none; }

        .nombreArchivo {
            margin-top: 10px;
            padding: 8px 12px;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 4px;
            color: #065f46;
            font-size: 0.85rem;
            display: none;
        }
        .nombreArchivo.mostrar { display: inline-block; }

        .textoAyudaArchivo {
            display: block;
            margin-top: 8px;
            color: #64748b;
            font-size: 0.78rem;
        }

        .oculto { display: none; }
        .mensajeError { color: #dc2626; font-size: 0.8rem; margin-top: 5px; display: block; }

        .accionesFormulario {
            margin-top: 25px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .botonPrincipal {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 24px;
            font-size: 0.95rem;
            font-weight: 500;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .botonPrincipal:hover { background: #2563eb; }

        .botonSecundario {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 10px 18px;
            font-size: 0.92rem;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .botonSecundario:hover {
            background: #e2e8f0;
            border-color: #94a3b8;
        }

        @media (max-width: 600px) {
            .cuadriculaFormulario, .selectorDocumento { grid-template-columns: 1fr; }
            .contenedorEmergencias { padding: 20px; }
        }
    </style>

    <script>
        const listaProvincias = @json($provinciasParaFormulario);
        const prefill = @json($prefillPayload);

        function filtrarProvincias() {
            const selector = document.getElementById('selectorCcaa');
            const ccaaId = selector ? selector.value : String(prefill.ccaa_id || '');
            const contenedor = document.getElementById('contenedorCheckboxes');
            const grupoProv = document.getElementById('grupoProvincias');
            const checkTodos = document.getElementById('checkTodos');

            contenedor.innerHTML = '';
            checkTodos.checked = false;

            const filtradas = listaProvincias.filter(p => p.c_id == ccaaId);

            if (filtradas.length > 0) {
                grupoProv.style.display = 'block';

                filtradas.forEach(p => {
                    const provinciaMarcada = Array.isArray(prefill.provincias_ids) &&
                        prefill.provincias_ids.map(String).includes(String(p.p_id));
                    const checkedPorPrefill = (
                        provinciaMarcada ||
                        String(prefill.provincia_id || '') === String(p.p_id) ||
                        prefill.alcance === 'global'
                    ) ? 'checked' : '';

                    let label = document.createElement('label');
                    label.innerHTML = `
                        <input type="checkbox" name="provincias_ids[]" value="${p.p_id}" class="checkProvincia" ${checkedPorPrefill}>
                        ${p.p_provincia}
                    `;
                    contenedor.appendChild(label);
                });

                if (prefill.alcance === 'global') {
                    checkTodos.checked = true;
                }
            } else {
                grupoProv.style.display = 'none';
            }
        }

        function marcarTodas(fuente) {
            const checkboxes = document.querySelectorAll('.checkProvincia');
            checkboxes.forEach(c => c.checked = fuente.checked);
        }

        function toggleDoc() {
            const pdf = document.querySelector('input[value="pdf_oficial"]').checked;
            document.getElementById('cajaPdf').classList.toggle('oculto', !pdf);
            document.getElementById('cajaCorreo').classList.toggle('oculto', pdf);
        }

        function updateFileName(input) {
            const limiteBytesPdf = 1900 * 1024;
            const fileNameDiv = document.getElementById('nombreArchivo');
            const errorArchivoDiv = document.getElementById('errorArchivoPdf');
            errorArchivoDiv.style.display = 'none';
            errorArchivoDiv.textContent = '';

            if (input.files && input.files[0]) {
                if (input.files[0].size > limiteBytesPdf) {
                    input.value = '';
                    fileNameDiv.classList.remove('mostrar');
                    fileNameDiv.textContent = '';
                    errorArchivoDiv.textContent = 'Error: El archivo PDF supera el tamaño máximo permitido (1.9 MB).';
                    errorArchivoDiv.style.display = 'block';
                    return;
                }
                fileNameDiv.textContent = '📄 ' + input.files[0].name;
                fileNameDiv.classList.add('mostrar');
            } else {
                fileNameDiv.classList.remove('mostrar');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const selector = document.getElementById('selectorCcaa');
            if ((selector && selector.value) || prefill.ccaa_id) {
                filtrarProvincias();
            }
        });

        function volverAtrasSeguro() {
            if (window.history.length > 1) {
                window.history.back();
                return;
            }
            window.location.href = @json(url('/inicio'));
        }
    </script>
@endsection
