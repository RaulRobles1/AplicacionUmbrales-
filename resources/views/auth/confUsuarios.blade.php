@extends('auth.plantilla')

@section('contenido')
    <style>
        /* CONTENEDOR GENERAL */

        .panelAdmin {
            max-width: 1200px;
            margin: 40px auto;
        }

        /* CABECERA */

        .cabeceraPanel {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .izquierdaPanel {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .derechaPanel {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .cabeceraPanel h2 {
            margin: 0;
            font-size: 1.6rem;
            font-weight: 600;
            color: #1f2937;
        }

        .etiquetaTotal {
            background: #1f2937;
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .formBuscador {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .entradaBuscador {
            min-width: 260px;
            padding: 9px 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.85rem;
        }

        .botonLimpiar {
            color: #6b7280;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .botonLimpiar:hover {
            color: #111827;
        }

        /* BOTONES */

        .boton {
            border: none;
            border-radius: 6px;
            padding: 8px 14px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all .2s;
        }

        .botonAnadir {
            background: #2563eb;
            color: white;
        }

        .botonAnadir:hover {
            background: #1d4ed8;
        }

        .botonGuardar {
            background: #16a34a;
            color: white;
            padding: 10px 20px;
        }

        .botonGuardar:hover {
            background: #15803d;
        }

        .botonEditar {
            border: 1px solid #2563eb;
            color: #2563eb;
            background: white;
        }

        .botonEditar:hover {
            background: #2563eb;
            color: white;
        }

        .botonEliminar {
            border: 1px solid #dc2626;
            color: #dc2626;
            background: white;
        }

        .botonEliminar:hover {
            background: #dc2626;
            color: white;
        }

        /* FORMULARIO */

        #contenedorFormulario {
            display: none;
            margin-top: 15px;
            margin-bottom: 25px;
        }

        .tarjetaFormulario {
            background: white;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
            padding: 25px;
            animation: aparecerSuave .25s ease;
        }

        .cabeceraFormulario h3 {
            margin: 0;
            font-size: 1.2rem;
            color: #111827;
        }

        .cabeceraFormulario p {
            margin: 4px 0 18px 0;
            font-size: 0.85rem;
            color: #6b7280;
        }

        @keyframes aparecerSuave {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* GRID FORMULARIO */

        .cuadriculaFormulario {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
        }

        /* CAMPOS */

        .grupoFormulario {
            display: flex;
            flex-direction: column;
        }

        .etiquetaFormulario {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .entradaConf {
            padding: 9px 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.85rem;
            transition: border .2s, box-shadow .2s;
        }

        .entradaConf:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
        }

        /* FOOTER FORMULARIO */

        .pieFormulario {
            margin-top: 10px;
            display: flex;
            justify-content: flex-end;
        }

        /* TABLA */

        .tarjetaTabla {
            background: white;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .tablaHidro {
            width: 100%;
            border-collapse: collapse;
        }

        .tablaHidro thead {
            background: #f9fafb;
        }

        .tablaHidro th {
            text-align: left;
            padding: 14px;
            font-size: 0.75rem;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .6px;
        }

        .tablaHidro td {
            padding: 14px;
            font-size: 0.85rem;
            border-top: 1px solid #f1f5f9;
        }

        .tablaHidro tbody tr:hover {
            background: #f9fafb;
        }

        /* ROLES */

        .etiquetaRol {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .rolSuperusuario {
            background: #fee2e2;
            color: #b91c1c;
        }

        .rolStaff {
            background: #fef3c7;
            color: #92400e;
        }

        .rolNormal {
            background: #e2e8f0;
            color: #334155;
        }

        /* ALERTA */

        .alertaExito {
            background: #ecfdf5;
            border: 1px solid #bbf7d0;
            color: #065f46;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 0.85rem;
        }

        .mensajeError {
            color: #dc2626;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 4px;
        }
    </style>


    <div class="panelAdmin">


        {{-- CABECERA --}}

        <div class="cabeceraPanel">

            <div class="izquierdaPanel">
                <h2>Gestión de Usuarios</h2>
            </div>

            <div class="derechaPanel">
                <form action="{{ route('confUsuarios') }}" method="GET" class="formBuscador">
                    <input type="text" name="q" class="entradaBuscador"
                        placeholder="Buscar por usuario, nombre o email" value="{{ $busqueda ?? '' }}">
                    <button type="submit" class="boton botonEditar">Buscar</button>
                    @if (!empty($busqueda))
                        <a href="{{ route('confUsuarios') }}" class="botonLimpiar">Limpiar</a>
                    @endif
                </form>

                <span class="etiquetaTotal">
                    {{ count($usuarios) }} Usuarios
                </span>
            </div>

        </div>


        <button onclick="alternarFormulario()" class="boton botonAnadir" id="botonAlternar">
            Añadir nuevo usuario
        </button>


        {{-- FORMULARIO --}}

        <div id="contenedorFormulario">

            <div class="tarjetaFormulario">

                <div class="cabeceraFormulario">
                    <h3>Crear nuevo usuario</h3>
                    <p>Introduce la información necesaria para registrar un usuario.</p>
                </div>

                <form action="/crearUsuario" method="POST">
                    @csrf

                    <div class="cuadriculaFormulario">

                        <div class="grupoFormulario">
                            <label class="etiquetaFormulario">Username</label>
                            <input type="text" name="username" class="entradaConf" value="{{ old('username') }}"
                                required>
                        </div>

                        <div class="grupoFormulario">
                            <label class="etiquetaFormulario">Email</label>
                            <input type="email" name="email" class="entradaConf" value="{{ old('email') }}" required>
                        </div>

                        <div class="grupoFormulario">
                            <label class="etiquetaFormulario">Nombre</label>
                            <input type="text" name="first_name" class="entradaConf" value="{{ old('first_name') }}"
                                required>
                        </div>

                        <div class="grupoFormulario">
                            <label class="etiquetaFormulario">Apellidos</label>
                            <input type="text" name="last_name" class="entradaConf" value="{{ old('last_name') }}"
                                required>
                        </div>

                        <div class="grupoFormulario">
                            <label class="etiquetaFormulario">Contraseña</label>
                            <input type="password" name="password" class="entradaConf" required>
                            @error('password')
                                <span class="mensajeError">La contraseña no cumple los requisitos</span>
                            @enderror
                        </div>

                        <div class="grupoFormulario">
                            <label class="etiquetaFormulario">Confirmar contraseña</label>
                            <input type="password" name="password_confirmation" class="entradaConf" required>
                        </div>

                        <div class="grupoFormulario">
                            <label class="etiquetaFormulario">Rol</label>
                            <select name="rol" class="entradaConf">
                                <option value="usuario">Usuario</option>
                                <option value="staff">Staff</option>
                                <option value="superuser">Superusuario</option>
                            </select>
                        </div>

                    </div>

                    <div class="pieFormulario">
                        <button type="submit" class="boton botonGuardar">
                            Guardar usuario
                        </button>
                    </div>

                </form>

            </div>

        </div>


        {{-- TABLA --}}

        <div class="tarjetaTabla">

            @if (session('exito') || session('success'))
                <div class="alertaExito">
                    {{ session('exito') ?? session('success') }}
                </div>
            @endif


            <table class="tablaHidro">

                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($usuarios as $u)
                        <tr>

                            <td><strong>{{ $u->username }}</strong></td>

                            <td>{{ $u->first_name }} {{ $u->last_name }}</td>

                            <td>{{ $u->email }}</td>

                            <td>

                                @if ($u->is_superuser)
                                    <span class="etiquetaRol rolSuperusuario">Superusuario</span>
                                @elseif($u->is_staff)
                                    <span class="etiquetaRol rolStaff">Staff</span>
                                @else
                                    <span class="etiquetaRol rolNormal">Usuario</span>
                                @endif

                            </td>

                            <td style="text-align:center">

                                <div style="display:flex;gap:8px;justify-content:center">

                                    <a href="{{ route('editarUsuario', $u->id) }}" class="boton botonEditar">
                                        Editar
                                    </a>

                                    @if (auth()->id() !== $u->id)
                                        <form action="{{ route('eliminarUsuario', $u->id) }}" method="POST"
                                            onsubmit="return confirm('¿Seguro que quieres eliminar el usuario {{ $u->username }}?');">

                                            @csrf
                                            @method('DELETE')

                                            <button class="boton botonEliminar">
                                                Eliminar
                                            </button>

                                        </form>
                                    @else
                                        <small style="color:#9ca3af;">Sesión actual</small>
                                    @endif

                                </div>

                            </td>

                        </tr>
                    @endforeach

                </tbody>

            </table>

        </div>

    </div>


    <script>
        function alternarFormulario() {

            const div = document.getElementById('contenedorFormulario');
            const btn = document.getElementById('botonAlternar');

            if (div.style.display === 'none' || div.style.display === '') {

                div.style.display = 'block';
                btn.innerText = 'Cancelar';
                btn.style.background = '#6b7280';

            } else {

                div.style.display = 'none';
                btn.innerText = 'Añadir nuevo usuario';
                btn.style.background = '#2563eb';

            }

        }

        @if ($errors->any())
            document.addEventListener('DOMContentLoaded', () => {
                alternarFormulario();
            });
        @endif
    </script>
@endsection
