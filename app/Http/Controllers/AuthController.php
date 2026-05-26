<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'usuario_nombre' => 'required|string',
            'usuario_pass'   => 'required|string',
        ]);

        // Autenticación manual con MD5 para compatibilidad con el sistema legado
        $user = User::where('usuario_nombre', $request->usuario_nombre)
                    ->where('usuario_pass', md5($request->usuario_pass))
                    ->where('usuario_estado_id', 1)
                    ->first();

        if (!$user) {
            return back()->with('error', 'Usuario o contraseña incorrecta, o cuenta deshabilitada.');
        }

        Auth::login($user);

// Redirección por rol (asegurando minúsculas y sin espacios)
        if ($user->usuario_rol === 'admin') {
            return redirect()->route('admin.actividades');
        }

return redirect()->route('actividades.create');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}