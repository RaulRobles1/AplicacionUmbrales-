<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión Umbrales</title>

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body,
        html {
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            height: 100%;
            background: #f5f5f5;
            color: #1f2937;
            overflow: hidden;
        }

        /* CABECERA */

        .cabecera {
            height: 88px;
            background: #2d3748;
            color: #ffffff;
            display: grid;
            grid-template-columns: auto 1fr auto auto;
            align-items: center;
            gap: 24px;
            padding: 0 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            border-bottom: 1px solid #1a202c;
        }

        .cabecera,
        .cabeceraIzquierda,
        .infoUsuario,
        .cabeceraDerecha {
            min-width: 0;
        }

        .cabeceraIzquierda {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .cabeceraBanner {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            min-width: 0;
            justify-self: end;
        }

        .cabeceraBanner img {
            height: 56px;
            max-height: 64px;
            width: 100%;
            max-width: 520px;
            object-fit: contain;
            display: block;
        }

        .textoLogo {
            font-size: 1.1rem;
            font-weight: 600;
            color: #ffffff;
        }

        .infoUsuario {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            color: #e2e8f0;
            padding-left: 24px;
            border-left: 1px solid rgba(255, 255, 255, 0.15);
        }

        .nombreUsuario {
            color: #ffffff;
            font-weight: 600;
        }

        .insigniaRol {
            padding: 3px 10px;
            background: rgba(59, 130, 246, 0.2);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
            color: #93c5fd;
        }

        .cabeceraDerecha {
            display: flex;
            align-items: center;
            gap: 16px;
            padding-left: 24px;
            justify-self: end;
        }

        .formBuscadorLateral {
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 10px 15px;
        }

        .entradaBuscadorLateral {
            width: 100%;
            border: 1px solid #4b5563;
            background: #1f2937;
            color: #f9fafb;
            border-radius: 6px;
            padding: 8px 10px;
            font-size: 0.85rem;
        }

        .entradaBuscadorLateral::placeholder {
            color: #9ca3af;
        }

        .botonBuscadorLateral {
            flex-shrink: 0;
            border: 1px solid rgba(59, 130, 246, 1);
            background: rgba(59, 130, 246, 0.9);
            color: #fff;
            border-radius: 6px;
            padding: 8px 9px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
        }

        .botonBuscadorLateral:hover {
            background: rgba(37, 99, 235, 1);
        }

        .textoEmail {
            font-size: 0.85rem;
            color: #cbd5e1;
            max-width: 260px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /*CERRAR SESION*/

        .botonCerrar {
            text-decoration: none;
            color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 6px 14px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: background 0.2s;
        }

        .botonCerrar:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        /* LAYOUT */

        .contenedor {
            display: flex;
            margin-top: 88px;
            height: calc(100vh - 88px);
            overflow: hidden;
        }

        /* BARRA LATERAL */

        .barraLateral {
            width: 260px;
            background: #2d3748;
            color: #e2e8f0;
            padding-top: 16px;
            overflow-y: auto;
            flex-shrink: 0;
            border-right: 1px solid #1a202c;
        }

        /* TITULOS */

        .tituloSeccion {
            padding: 12px 20px 8px;
            font-size: 0.7rem;
            text-transform: uppercase;
            color: #a0aec0;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-top: 12px;
        }

        .tituloSeccion:first-child {
            margin-top: 0;
        }

        /* LINKS */

        .enlaceDesplegable {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            font-size: 0.9rem;
            color: #cbd5e1;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            position: relative;
        }

        .enlaceDesplegable:hover {
            background: rgba(59, 130, 246, 0.1);
            border-left: 3px solid #3b82f6;
            color: #ffffff;
        }

        .enlaceActivo {
            background: rgba(59, 130, 246, 0.15);
            border-left: 3px solid #3b82f6;
            color: #ffffff;
            font-weight: 500;
        }

        /* SUBMENU */

        .submenu {
            max-height: 0;
            overflow: hidden;
            background: rgba(0, 0, 0, 0.15);
            transition: max-height 0.25s ease;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .submenu li a {
            display: block;
            padding: 8px 20px 8px 40px;
            font-size: 0.85rem;
            color: #a0aec0;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .submenu li a:hover {
            background: rgba(59, 130, 246, 0.1);
            color: #ffffff;
            border-left: 3px solid #3b82f6;
        }

        /* CHECK MENU */

        .checkMenu {
            display: none;
        }

        .checkMenu:checked+.enlaceDesplegable+.submenu {
            max-height: 500px;
            padding: 4px 0;
        }

        /* FLECHA */

        .flecha {
            font-size: 0.7rem;
            color: #a0aec0;
            transition: transform 0.2s;
        }

        .checkMenu:checked+.enlaceDesplegable .flecha {
            transform: rotate(90deg);
        }

        /*  BUSCADOR  */

        .barraLateral form {
            padding: 10px 16px 12px;
        }

        .barraLateral input {
            width: 100%;
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.85rem;
            outline: none;
            background: rgba(0, 0, 0, 0.2);
            color: #ffffff;
            transition: border-color 0.2s;
        }

        .barraLateral input:focus {
            border-color: #3b82f6;
        }

        .barraLateral input::placeholder {
            color: #a0aec0;
        }

        .barraLateral hr {
            border: 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin: 12px 16px;
        }

        /*  CONTENIDO  */

        .contenidoPrincipal {
            flex-grow: 1;
            padding: 28px;
            overflow-y: auto;
            height: 100%;
            background: #f5f5f5;
        }

        /* TARJETA */

        .tarjeta {
            background: white;
            padding: 28px;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            min-height: 200px;
            border: 1px solid #e5e7eb;
        }

        .bloqueVolverGlobal {
            margin-bottom: 14px;
        }

        .botonVolverGlobal {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 13px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #334155;
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .botonVolverGlobal:hover {
            background: #e2e8f0;
            border-color: #94a3b8;
            color: #0f172a;
        }

        /* SCROLL */

        .barraLateral::-webkit-scrollbar {
            width: 6px;
        }

        .barraLateral::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.2);
        }

        .barraLateral::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }

        .barraLateral::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .contenidoPrincipal::-webkit-scrollbar {
            width: 8px;
        }

        .contenidoPrincipal::-webkit-scrollbar-track {
            background: #f5f5f5;
        }

        .contenidoPrincipal::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 10px;
        }

        .contenidoPrincipal::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        @media (max-width: 1280px) {
            .cabecera {
                padding: 0 18px;
            }

            .cabeceraIzquierda {
                gap: 14px;
            }

            .infoUsuario {
                padding-left: 14px;
            }

            .cabeceraDerecha {
                gap: 10px;
                padding-left: 14px;
            }

            .textoEmail {
                max-width: 170px;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 1024px) {
            .textoEmail {
                display: none;
            }

            .cabecera {
                height: 72px;
            }

            .cabeceraBanner img {
                height: 44px;
                max-width: 360px;
            }

            .cabeceraDerecha,
            .infoUsuario {
                border-left: none;
                padding-left: 0;
            }

            .contenedor {
                margin-top: 72px;
                height: calc(100vh - 72px);
            }
        }
    </style>

</head>

<body>

    @inject('servicioEpisodios', 'App\Services\EpisodioService')

    <header class="cabecera">

        <div class="cabeceraIzquierda">
            <span class="textoLogo">Gestión de Umbrales</span>
            <div class="infoUsuario">
                <span class="nombreUsuario">{{ session('usuario') }}</span>
                <span class="insigniaRol">
                    @if (session('is_superuser'))
                        Superusuario
                    @elseif(session('is_staff'))
                        Staff
                    @else
                        Usuario
                    @endif
                </span>
            </div>
        </div>

        <div class="cabeceraDerecha">
            <span class="textoEmail">{{ session('email') }}</span>
            <a href="/cerrarSesion" class="botonCerrar">Cerrar Sesión</a>
        </div>

        <div class="cabeceraBanner">
            <img src="{{ asset('images/logo-web-CHT-centenario.png') }}" alt="Banner centenario CHT">
        </div>

    </header>

    <div class="contenedor">

        <nav class="barraLateral">

            <div class="tituloSeccion">TIEMPO REAL</div>

            <ul style="list-style:none;padding:0;margin:0;">

                <li>
                    <a href="/inicio" class="enlaceDesplegable">Inicio</a>
                </li>

                <li>
                    <a href="{{ route('mapa.global') }}"
                        class="enlaceDesplegable {{ request()->routeIs('mapa.global') ? 'enlaceActivo' : '' }}">
                        Mapa Cuenca del Tajo
                    </a>
                </li>
                <form action="{{ route('buscar.global') }}" method="GET" class="formBuscadorLateral">
                    <input type="text" name="query" class="entradaBuscadorLateral"
                        placeholder="Buscador..." value="{{ request('query') }}"
                        required>
                    <button type="submit" class="botonBuscadorLateral">Buscar</button>
                </form>
                <hr style="border:0;border-top:1px solid rgba(255,255,255,0.1);margin:10px 15px;">



                <li>
                    <div class="tituloSeccion">Gestión de Episodios</div>
                    <input type="checkbox" id="menuTajo" class="checkMenu">

                    <label for="menuTajo" class="enlaceDesplegable">
                        <span>Cuenca del Tajo (Global)</span>
                        <span class="flecha">▶</span>
                    </label>

                    <ul class="submenu">
                        <li><a href="{{ route('tajo.activos') }}">Episodios Activos</a></li>
                        <li><a href="{{ route('tajo.historico') }}">Histórico Episodios</a></li>
                    </ul>
                </li>

                @php
                    $comunidades = $servicioEpisodios->obtenerComunidadesConEpisodios();
                @endphp

                @foreach ($comunidades as $ccaa)
                    <li>

                        <input type="checkbox" id="menuCcaa{{ $ccaa->c_id }}" class="checkMenu">

                        <label for="menuCcaa{{ $ccaa->c_id }}" class="enlaceDesplegable">
                            <span>{{ $ccaa->nombre }}</span>
                            <span class="flecha">▶</span>
                        </label>
                        {{-- SUB MENUS POR CADA COMUNIDAD AUTONOMA --}}
                        <ul class="submenu">
                            </li>
                            <li><a href="{{ route('ccaa.activos', $ccaa->c_id) }}">Episodios Activos</a></li>
                            <li><a href="{{ route('ccaa.historico', $ccaa->c_id) }}">Histórico Episodios</a></li>
                        </ul>

                    </li>
                @endforeach
                <div class="tituloSeccion">Situaciones de Emergencia</div>
                <li>
                    <a href="{{ route('emergencias.vistaPlan') }}" class="enlaceDesplegable">
                        <span>Vista Plan Emergencia</span>
                    </a>
                </li>

                @if (session('is_staff') || session('is_superuser'))
                    <li>
                        <a href="{{ route('emergencias.crear') }}" class="enlaceDesplegable">
                            Formulario Emergencias
                        </a>
                    </li>
                @endif

                @if (session('is_superuser'))
                    <div class="tituloSeccion">Configuración</div>

                    <li>
                        <a href="{{ route('confUsuarios') }}" class="enlaceDesplegable">
                            Gestionar Usuarios
                        </a>
                    </li>
                @endif

            </ul>

        </nav>

        <main class="contenidoPrincipal">

            <div class="tarjeta">
                @if (!request()->is('inicio'))
                    <div class="bloqueVolverGlobal">
                        <a href="{{ url('/inicio') }}" class="botonVolverGlobal" data-volver-global="1">
                            <span aria-hidden="true">←</span>
                            Volver
                        </a>
                    </div>
                @endif

                @yield('contenido')

            </div>

        </main>

    </div>

    @vite(['resources/js/app.js'])

    <script type="module">
        const CLAVE_NAVEGACION = 'umbrales_navegacion_interna';

        function obtenerRutaActual() {
            return `${window.location.pathname}${window.location.search}`;
        }

        function normalizarRutaInterna(ruta) {
            if (!ruta || typeof ruta !== 'string') return '';
            if (!ruta.startsWith('/')) return '';
            if (ruta.startsWith('//')) return '';
            return ruta;
        }

        function leerPilaNavegacion() {
            try {
                const valor = sessionStorage.getItem(CLAVE_NAVEGACION);
                const pila = JSON.parse(valor || '[]');
                return Array.isArray(pila) ? pila.filter(normalizarRutaInterna) : [];
            } catch (_) {
                return [];
            }
        }

        function guardarPilaNavegacion(pila) {
            const limpia = (Array.isArray(pila) ? pila : []).filter(normalizarRutaInterna).slice(-60);
            sessionStorage.setItem(CLAVE_NAVEGACION, JSON.stringify(limpia));
        }

        function registrarRutaActual() {
            const actual = obtenerRutaActual();
            const pila = leerPilaNavegacion();
            if (pila[pila.length - 1] !== actual) {
                pila.push(actual);
                guardarPilaNavegacion(pila);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            registrarRutaActual();

            document.querySelectorAll('[data-volver-global]').forEach(enlace => {
                enlace.addEventListener('click', manejarVolverGlobal);
            });

            if (window.Echo) {
                window.Echo.channel('panel-alertas')
                    .listen('NuevoCambioRecibido', (e) => {
                        if (window.__umbralesRecargaPendiente) {
                            return;
                        }
                        window.__umbralesRecargaPendiente = true;
                        window.setTimeout(() => {
                            window.location.reload();
                        }, 900);

                    });

            }

        });

        function manejarVolverGlobal(event) {
            const actual = obtenerRutaActual();
            const pila = leerPilaNavegacion();
            while (pila.length > 0 && pila[pila.length - 1] === actual) {
                pila.pop();
            }

            const anteriorInterna = pila.pop();
            if (anteriorInterna) {
                event.preventDefault();
                guardarPilaNavegacion(pila);
                window.location.assign(anteriorInterna);
                return;
            }

            const tieneHistorial = window.history.length > 1;
            let mismaApp = false;
            if (document.referrer) {
                try {
                    const urlReferrer = new URL(document.referrer);
                    mismaApp = urlReferrer.origin === window.location.origin;
                } catch (_) {
                    mismaApp = false;
                }
            }
            if (tieneHistorial && mismaApp) {
                event.preventDefault();
                window.history.back();
            }
        }
    </script>

</body>

</html>
