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
        info("(from login) login in..", $request->all());

        // hay que validar por token tambien
        $request->validate([
            '_token' => 'required|string',
            'email' => 'required|email',
            'password'   => 'required|string',
        ], [
            'email.required' => 'El campo de correo electrónico es obligatorio.',
            'email.email' => 'El campo de correo electrónico debe ser una dirección de correo válida.',
            'password.required' => 'El campo de contraseña es obligatorio.',
        ]);
        info("(from login) login validated for " . $request->email);

        $credentials = [
            'email'    => $request->email,
            'password' => $request->password,
            'estado'   => true,
        ];

        if (!Auth::attempt($credentials)) {
            info("(from login) login failed for " . $request->email);
            return back()->with('error', 'Usuario o contraseña incorrecta, o cuenta deshabilitada.'); //TO-DO: mensaje más específico según:[no autorizado, cuenta deshabilitada, etc]
        }

        $user = Auth::user();
        if ($user->rol === 'admin') {
            info("(from login) login successful for admin " . $user->email);
            return redirect()->route('admin.dashboard');
        }
        if ($user->rol === 'cargador') {
            info("(from login) login successful for cargador " . $user->email);
            return redirect()->route('actividades.importar');
        }
        if ($user->rol === 'unidad') {
            info("(from login) login successful for unidad " . $user->email);
            return redirect()->route('unidad.dashboard');
        }
        info("(from login) login successful for user " . $user->email);
        return redirect()->route('actividades.index');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
