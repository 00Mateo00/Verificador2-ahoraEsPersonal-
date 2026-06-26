<?php

namespace App\Http\Responses;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetResponse as SuccessfulPasswordResetResponseContract;
use Symfony\Component\HttpFoundation\Response;

class SuccessfulPasswordResetResponse implements SuccessfulPasswordResetResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     * @return Response
     */
    public function toResponse($request)
    {
        $email = $request->email;
        $user = User::where('email', $email)->first();

        if ($user) {
            // Iniciar sesión de forma automática
            Auth::login($user);

            // Regenerar sesión para prevenir Session Fixation
            $request->session()->regenerate();

            // Redirigir según prioridades de permisos dinámicos
            if ($user->hasPermissionTo('usuarios.crear')) {
                return redirect()->route('admin.dashboard')->with('success', 'Contraseña restablecida con éxito. Sesión iniciada automáticamente.');
            }
            if ($user->hasPermissionTo('historial.ver-global')) {
                return redirect()->route('auditor.dashboard')->with('success', 'Contraseña restablecida con éxito. Sesión iniciada automáticamente.');
            }
            if ($user->hasPermissionTo('historial.ver-regional')) {
                return redirect()->route('director.dashboard')->with('success', 'Contraseña restablecida con éxito. Sesión iniciada automáticamente.');
            }
            if ($user->hasPermissionTo('actividades.verificar')) {
                return redirect()->route('unidad.dashboard')->with('success', 'Contraseña restablecida con éxito. Sesión iniciada automáticamente.');
            }
            if ($user->hasPermissionTo('actividades.importar')) {
                return redirect()->route('actividades.importar')->with('success', 'Contraseña restablecida con éxito. Sesión iniciada automáticamente.');
            }

            return redirect()->route('actividades.historial')->with('success', 'Contraseña restablecida con éxito. Sesión iniciada automáticamente.');
        }

        return redirect()->route('login')->with('success', 'Contraseña restablecida con éxito.');
    }
}
