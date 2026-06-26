<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    /**
     * Evalúa si el usuario autenticado tiene el permiso especificado.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
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

        if ($user->hasPermissionTo($permission)) {
            return $next($request);
        }

        abort(403, 'No tiene los permisos necesarios para realizar esta acción.');
    }
}