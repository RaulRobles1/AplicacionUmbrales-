<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);
        $usuario = DB::table('auth_user')
            ->where('username', $request->username)
            ->first();
        // Compara la contraseña cifrada
        if ($usuario && Hash::check($request->password, $usuario->password)) {
            $request->session()->regenerate();

            // Crea la sessión
            session([
                'id' => $usuario->id,
                'usuario' => $usuario->username,
                'email' => $usuario->email,
                'is_superuser' => $usuario->is_superuser,
                'is_staff' => $usuario->is_staff,
            ]);

            return redirect('/inicio');
        }

        // Si no es correcto muestra un mensaje de error
        return back()->withErrors([
            'username' => 'Usuario o contraseña incorrectos.',
        ])->withInput();
    }

    // CIerra la session y redirige al login
    public function cerrarSesion()
    {
        session()->invalidate();
        session()->regenerateToken();

        return redirect('/login');
    }
}
