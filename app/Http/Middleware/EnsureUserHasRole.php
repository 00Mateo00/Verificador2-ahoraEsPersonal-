<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Maneja la solicitud entrante evaluando el rol del usuario autenticado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Bloquear cuentas deshabilitadas administrativamente
        if (!$user->activo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('error', 'Su cuenta se encuentra deshabilitada.');
        }

        // Si el usuario pertenece a uno de los roles permitidos, continuar
        if (empty($roles) || in_array($user->rol, $roles)) {
            return $next($request);
        }

        abort(403, 'No tiene permisos para acceder a esta sección.');
    }
}