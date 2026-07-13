@extends('auth.plantilla')

@section('contenido')
    <style>
        .panelEdicion {
            max-width: 720px;
            margin: 40px auto;
            background: #ffffff;
            padding: 35px;
            border-radius: 10px;
            border: 1px solid #e6e6e6;
            box-shadow: 0 6px 22px rgba(0, 0, 0, 0.05);
        }

        .cabeceraPanel {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .cabeceraPanel h2 {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 600;
            color: #1f2937;
        }

        .cabeceraPanel span {
            color: #2563eb;
            font-weight: 600;
        }

        .divisor {
            border: none;
            border-top: 1px solid #eee;
            margin-bottom: 25px;
        }

        .grupoFormulario {
            margin-bottom: 22px;
        }

        .etiquetaFormulario {
            display: block;
            margin-bottom: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .entradaFormulario {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: border .2s, box-shadow .2s;
        }

        .entradaFormulario:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .soloLectura {
            background: #f9fafb;
            color: #6b7280;
        }

        .cuadricula2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .rolBloqueado {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #f9fafb;
            color: #374151;
            font-size: 0.9rem;
        }

        .rolBloqueado small {
            display: block;
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 3px;
        }

        .botonGuardar {
            width: 100%;
            background: #2563eb;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s, transform .1s;
        }

        .botonGuardar:hover {
            background: #1d4ed8;
        }

        .botonGuardar:active {
            transform: scale(0.98);
        }
    </style>


    <div class="panelEdicion">

        <div class="cabeceraPanel">
            <h2>Estás editando a: <span>{{ $usuario->username }}</span></h2>
        </div>

        <hr class="divisor">
{{-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% --}}
{{-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% --}}
{{-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% --}}
{{-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% --}}
{{-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% --}}
{{-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% --}}
        <hr class="divisor">

        @if ($errors->any())
            <div style="background:#fee2e2; color:#b91c1c; padding:12px; margin-bottom:15px; border-radius:6px;">
                <ul style="margin:0; padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

{{-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% --}}
{{-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% --}}
{{-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% --}}
{{-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% --}}
{{-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% --}}
{{-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% --}}
        <form action="{{ route('actualizarUsuario', $usuario->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grupoFormulario">
                <label class="etiquetaFormulario">Username</label>
                <input type="text" value="{{ $usuario->username }}" class="entradaFormulario soloLectura" readonly>
            </div>

            <div class="cuadricula2">

                <div class="grupoFormulario">
                    <label class="etiquetaFormulario">Email</label>
                    <input type="email" name="email" value="{{ old('email', $usuario->email) }}"
                        class="entradaFormulario" required>
                </div>

                <div class="grupoFormulario">
                    <label class="etiquetaFormulario">Rol del usuario</label>

                    @if (auth()->id() == $usuario->id)
                        <div class="rolBloqueado">
                            {{ $usuario->is_superuser ? 'Superusuario' : ($usuario->is_staff ? 'Staff' : 'Normal') }}
                            <small>No puedes modificar tu propio rol</small>
                        </div>
                    @else
                        <select name="rol" class="entradaFormulario" required>
                            <option value="usuario"
                                {{ !$usuario->is_superuser && !$usuario->is_staff ? 'selected' : '' }}>Usuario</option>
                            <option value="staff" {{ !$usuario->is_superuser && $usuario->is_staff ? 'selected' : '' }}>
                                Staff</option>
                            <option value="superuser" {{ $usuario->is_superuser ? 'selected' : '' }}>Superusuario</option>
                        </select>
                    @endif

                </div>

            </div>

            <div class="cuadricula2">

                <div class="grupoFormulario">
                    <label class="etiquetaFormulario">Nombre</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $usuario->first_name) }}"
                        class="entradaFormulario" required>
                </div>

                <div class="grupoFormulario">
                    <label class="etiquetaFormulario">Apellidos</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $usuario->last_name) }}"
                        class="entradaFormulario" required>
                </div>

                <div class="grupoFormulario">
                    <label class="etiquetaFormulario">Contraseña</label>
                    <input type="password" id="campoPassword" name="password" value="" class="entradaFormulario"
                        placeholder="No rellenar para no cambiar la contraseña">
                </div>

                <div class="grupoFormulario">
                    <label class="etiquetaFormulario">Confirmar contraseña</label>
                    <input type="password" id="campoPasswordConfirm" name="password_confirmation" value="" class="entradaFormulario"
                        placeholder="Repite la contraseña">
                    <small id="mensajeError" style="display:none; color:#dc2626; font-size:0.8rem; margin-top:4px;">
                        Las contraseñas no coinciden
                    </small>
                </div>

            <button type="submit" class="botonGuardar">
                Guardar cambios
            </button>

        </form>

    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const password = document.getElementById('campoPassword');
        const confirmacion = document.getElementById('campoPasswordConfirm');
        const mensajeError = document.getElementById('mensajeError');
        const boton = document.querySelector('.botonGuardar');
        const formulario = document.querySelector('form');

        function validarCoincidencia() {
            // Comprueba que si los dos estan vacios no hay que validad nada por que no se quiere cambiar la contraseña
            if (password.value === '' && confirmacion.value === '') {
                mensajeError.style.display = 'none';
                confirmacion.style.borderColor = '';
                return true;
            }

            if (password.value !== confirmacion.value) {
                mensajeError.style.display = 'block';
                confirmacion.style.borderColor = '#dc2626';
                return false;
            } else {
                mensajeError.style.display = 'none';
                confirmacion.style.borderColor = '#16a34a';
                return true;
            }
        }

        password.addEventListener('input', validarCoincidencia);
        confirmacion.addEventListener('input', validarCoincidencia);

        formulario.addEventListener('submit', function (e) {
            if (!validarCoincidencia()) {
                e.preventDefault();
            }
        });
    });
</script>
@endsection
