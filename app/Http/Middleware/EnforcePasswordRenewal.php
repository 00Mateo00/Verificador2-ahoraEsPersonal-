<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PasswordPolicyService;
use App\Mail\PasswordRenewalMail;
use App\Services\MailService;
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
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // 1. Evitar bucles de redirección en rutas de autenticación y recursos clave
        if ($request->routeIs('login', 'logout', 'password.*', 'verification.*')) {
            return $next($request);
        }

        // 2. Administradores exentos de expiración
        if ($user->rol === 'admin') {
            return $next($request);
        }

        // 3. Contraseña vencida (> 90 días): Cierre síncrono, despacho de correo y bloqueo
        if ($this->policyService->isExpired($user)) {
            $failedMail = $this->policyService->getFailedRenewalMail($user);

            if ($failedMail) {
                // Si existe un correo de renovación previo fallido síncronamente que siga vigente, lo reintentamos
                $failedMail->sendSynchronously();
            } elseif (!$this->policyService->hasActiveToken($user->email)) {
                // Si no hay intentos fallidos ni tokens activos, generamos uno nuevo de forma limpia
                $token = $this->policyService->generateRenewalToken($user);
                $url = url(route('password.reset', [
                    'token' => $token,
                    'email' => $user->email,
                ], false));

                $expirationString = $this->policyService->getExpirationDate($user)->format('d-m-Y');

                MailService::sendSafe(
                    $user->email,
                    new PasswordRenewalMail($user, $url, $expirationString),
                    [
                        'user_id' => $user->id,
                        'url' => $url,
                        'expiration_string' => $expirationString
                    ]
                );
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Su contraseña ha expirado debido a nuestra política de seguridad de 90 días. Se ha enviado automáticamente un enlace seguro de renovación a su correo electrónico institucional.');
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