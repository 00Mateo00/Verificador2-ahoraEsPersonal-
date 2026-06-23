<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MailService;
use App\Services\PasswordPolicyService;
use App\Mail\PasswordRenewalMail;
use Illuminate\Http\Request;

class PasswordRenewalController extends Controller
{
    protected PasswordPolicyService $policyService;

    public function __construct(PasswordPolicyService $policyService)
    {
        $this->policyService = $policyService;
    }

    /**
     * Muestra la pantalla de contraseña expirada y envía el correo inicial si corresponde.
     */
    public function showExpired()
    {
        $email = session('expired_user_email');
        $name = session('expired_user_name');

        // Defensa: Si no hay datos temporales de expiración en sesión, denegar acceso inmediato
        if (!$email) {
            return redirect()->route('login');
        }

        $user = User::where('email', $email)->first();
        if (!$user || !$this->policyService->isExpired($user)) {
            return redirect()->route('login');
        }

        // Despachar de forma automática el correo de renovación al cargar la pantalla por primera vez
        $failedMail = $this->policyService->getFailedRenewalMail($user);
        $hasActiveToken = $this->policyService->hasActiveToken($user->email);

        if (!$failedMail && !$hasActiveToken) {
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
                    'expiration_string' => $expirationString,
                ]
            );
        }

        $expirationDate = $this->policyService->getExpirationDate($user)->format('d-m-Y');

        return view('auth.password-expired', [
            'user' => $user,
            'expirationDate' => $expirationDate,
        ]);
    }

    /**
     * Procesa la solicitud manual de reenvío del enlace de renovación.
     */
    public function requestRenewal()
    {
        $email = session('expired_user_email');

        // Defensa: Asegurar contexto síncrono de sesión
        if (!$email) {
            return redirect()->route('login');
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect()->route('login');
        }

        // 1. Identificar si existe una petición idéntica fallida para reintentarla de inmediato
        $failedMail = $this->policyService->getFailedRenewalMail($user);
        if ($failedMail) {
            if ($failedMail->sendSynchronously()) {
                return back()->with('success', 'Se ha reintentado enviar el enlace seguro de renovación a su correo electrónico institucional.');
            } else {
                return back()->with('error', 'El reintento de envío síncrono falló. Por favor, compruebe la conectividad del servidor SMTP.');
            }
        }

        // 2. Si no hay fallos pero el token sigue activo, significa que ya fue enviado correctamente
        if ($this->policyService->hasActiveToken($user->email)) {
            return back()->with('success', 'Revisa tu correo electrónico. Ya existe un enlace de renovación activo.');
        }

        // 3. De lo contrario, iniciar una petición limpia de renovación
        $token = $this->policyService->generateRenewalToken($user);
        $reason = is_null($user->password_changed_at) ? 'first_login' : 'renewal';

        $url = url(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
            'reason' => $reason,
        ], false));

        $expirationString = $this->policyService->getExpirationDate($user)->format('d-m-Y');

        $sent = MailService::sendSafe(
            $user->email,
            new PasswordRenewalMail($user, $url, $expirationString),
            [
                'user_id' => $user->id,
                'url' => $url,
                'expiration_string' => $expirationString,
            ]
        );

        if ($sent) {
            return back()->with('success', 'Se ha enviado un nuevo enlace seguro de renovación a su correo electrónico institucional.');
        }

        return back()->with('error', 'El envío de correo falló síncronamente. Por favor, intente nuevamente más tarde.');
    }
}