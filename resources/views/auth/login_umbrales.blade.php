<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Inicio de sesion Gestion umbrales</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --bg: #f8fafc;
            --text: #1e293b;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .tarjetaLogin {
            background: white;
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .cabecera {
            text-align: center;
            margin-bottom: 2rem;
        }

        .cabecera h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .cabecera p {
            color: #64748b;
            font-size: 0.875rem;
        }

        .grupoFormulario {
            margin-bottom: 1.25rem;
        }

        label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            ring: 2px solid var(--primary);
        }

        .boton {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem;
            width: 100%;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            font-size: 1rem;
        }

        .boton:hover {
            background: var(--primary-hover);
        }

        .mensajeError {
            color: #dc2626;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }
    </style>
</head>

<body>
{{-- Logica de inicio de sesión --}}
    <div class="tarjetaLogin">
        <div class="cabecera">
            <h1>Acceso al Sistema de Umbrales</h1>

        </div>

        <form action="/login" method="POST">
            @csrf <div class="grupoFormulario">
                <label for="username">Nombre de Usuario</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus>
                @error('username')
                    <div class="mensajeError">{{ $message }}</div>
                @enderror
            </div>

            <div class="grupoFormulario">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
                @error('password')
                    <div class="mensajeError">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="boton">Entrar al Sistema</button>
        </form>
    </div>

</body>

</html>
