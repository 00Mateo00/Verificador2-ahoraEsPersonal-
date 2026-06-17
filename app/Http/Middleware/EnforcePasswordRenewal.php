<?php

namespace App\Http\Middleware;

use App\Services\PasswordPolicyService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforcePasswordRenewal
{
    protected PasswordPolicyService $policyService;

    public function __construct(PasswordPolicyService $policyService)
    {
        $this->policyService = $policyService;
    }

    /**
     * Intercepta la petición para evaluar el estado de vigencia de la contraseña.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Si el usuario está autenticado e intenta acceder al formulario de restablecimiento de contraseña,
        // forzamos su cierre de sesión para que el middleware 'guest' de Fortify no rebote la petición.
        if ($request->routeIs('password.reset')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->to($request->fullUrl());
        }

        // 1. Evitar bucles de redirección en rutas de autenticación, la pantalla especial de expiración, el restablecimiento de contraseñas y reenvíos
        if ($request->routeIs('login', 'logout', 'password.expired', 'password.request-renewal', 'password.reset', 'password.update', 'verification.*')) {
            return $next($request);
        }
        // 2. Administradores exentos de expiración
        if ($user->rol === 'admin') {
            return $next($request);
        }

        // 3. Primer inicio de sesión histórico: si nunca ha cambiado su contraseña (es null), forzar redirección inmediata al formulario
        if (is_null($user->password_changed_at)) {
            $token = $this->policyService->generateRenewalToken($user);
            $email = $user->email;

            // Forzar cierre de sesión síncrono para que el formulario cargue sin colisión de sesión activa de Fortify
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('password.reset', [
                'token' => $token,
                'email' => $email,
                'reason' => 'first_login',
            ]);
        }

        // 4. Contraseña vencida (> 90 días): Almacenar datos en sesión, forzar deslogueo y redirección
        if ($this->policyService->isExpired($user)) {
            $email = $user->email;
            $name = $user->name;

            // Forzar cierre de sesión síncrono para prevenir que el middleware 'guest' de Fortify bloquee /reset-password
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Preservar el contexto de forma segura para la siguiente petición de renderizado de fallback
            session([
                'expired_user_email' => $email,
                'expired_user_name' => $name,
            ]);

            return redirect()->route('password.expired');
        }

        // 4. Ventana preventiva de advertencia (últimos 7 días antes de expirar)
        if ($this->policyService->isInWarningWindow($user)) {
            $daysLeft = $this->policyService->getDaysUntilExpiration($user);
            $expirationDate = $this->policyService->getExpirationDate($user)->format('d-m-Y');

            session([
                'password_warning_active' => true,
                'password_warning_days' => $daysLeft,
                'password_warning_date' => $expirationDate,
            ]);
        } else {
            session()->forget([
                'password_warning_active',
                'password_warning_days',
                'password_warning_date',
            ]);
        }

        return $next($request);
    }
}
