<?php

namespace App\Services;

use App\Mail\PasswordRenewalMail;
use App\Models\MailLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

class PasswordPolicyService
{
    private const DAYS_EXPIRE = 90;

    private const WARNING_DAYS = 7;

    /**
     * Determina si la contraseña de un usuario ha expirado.
     */
    public function isExpired(User $user): bool
    {
        if ($user->rol === 'admin') {
            return false;
        }

        $changedAt = $user->password_changed_at ?: $user->created_at;
        if (! $changedAt) {
            return false;
        }

        return Carbon::parse($changedAt)->addDays(self::DAYS_EXPIRE)->isPast();
    }

    /**
     * Determina si el usuario se encuentra dentro de la ventana de advertencia de expiración.
     */
    public function isInWarningWindow(User $user): bool
    {
        if ($user->rol === 'admin') {
            return false;
        }

        if ($this->isExpired($user)) {
            return false;
        }

        $changedAt = $user->password_changed_at ?: $user->created_at;
        if (! $changedAt) {
            return false;
        }

        $daysLeft = $this->getDaysUntilExpiration($user);

        return $daysLeft >= 0 && $daysLeft <= self::WARNING_DAYS;
    }

    /**
     * Calcula los días restantes para que expire la contraseña.
     */
    public function getDaysUntilExpiration(User $user): int
    {
        $changedAt = $user->password_changed_at ?: $user->created_at;
        if (! $changedAt) {
            return self::DAYS_EXPIRE;
        }

        $expirationDate = Carbon::parse($changedAt)->addDays(self::DAYS_EXPIRE);

        return (int) now()->startOfDay()->diffInDays($expirationDate->startOfDay(), false);
    }

    /**
     * Obtiene la fecha exacta de expiración para un usuario.
     */
    public function getExpirationDate(User $user): Carbon
    {
        $changedAt = $user->password_changed_at ?: $user->created_at;

        return Carbon::parse($changedAt)->addDays(self::DAYS_EXPIRE);
    }

    /**
     * Verifica si existe un token de restablecimiento síncronamente vigente en la base de datos.
     */
    public function hasActiveToken(string $email): bool
    {
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if ($record) {
            $createdAt = Carbon::parse($record->created_at);
            // Reutiliza la vigencia por defecto de Laravel (60 minutos)
            if ($createdAt->addMinutes(60)->isFuture()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Genera un token de renovación usando el Password Broker por defecto.
     */
    public function generateRenewalToken(User $user): string
    {
        return Password::broker()->createToken($user);
    }

    /**
     * Recupera el último log de correo de renovación fallido (PENDING o FAILED) si se creó hace menos de 60 minutos.
     */
    public function getFailedRenewalMail(User $user): ?MailLog
    {
        $failedMail = MailLog::where('user_id', $user->id)
            ->where('mailable_class', PasswordRenewalMail::class)
            ->whereIn('status', ['PENDING', 'FAILED'])
            ->latest()
            ->first();

        if ($failedMail) {
            // Verificar que el correo fallido tenga menos de 60 minutos de antigüedad (vigencia del enlace de la URL)
            if ($failedMail->created_at->addMinutes(60)->isFuture()) {
                return $failedMail;
            }
        }

        return null;
    }
}
