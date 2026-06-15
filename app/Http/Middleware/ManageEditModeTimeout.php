<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManageEditModeTimeout
{
    /**
     * Controla el tiempo de expiración por inactividad de 10 minutos para el Modo Edición.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session('modo_edicion')) {
            $lastActivity = session('modo_edicion_last_activity', 0);
            if (time() - $lastActivity > 600) { // 10 minutos (600 segundos)
                session()->forget([
                    'modo_edicion',
                    'modo_edicion_last_activity',
                    'auth.password_confirmed_at' // Invalida la confirmación de password en caché de Laravel ante inactividad
                ]);
                session()->flash('error', 'Su sesión de Modo Edición Administrativa ha expirado automáticamente por inactividad de 10 minutos.');
            } else {
                session(['modo_edicion_last_activity' => time()]);
            }
        }

        return $next($request);
    }
}