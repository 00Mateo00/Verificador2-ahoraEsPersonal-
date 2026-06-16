<?php

namespace App\Services;

use App\Models\MailLog;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MailService
{
    /**
     * Fusible de conexión (Circuit Breaker).
     * Si se detecta que el servidor SMTP está fuera de línea durante la transacción,
     * se bloquean los intentos físicos restantes para resguardar el hilo de PHP contra caídas de Timeout.
     */
    protected static bool $smtpIsDown = false;

    /**
     * Envía un correo síncronamente. Si falla, lo almacena en la tabla mails.
     * Si tiene éxito, registra un log de envío exitoso para el historial del administrador.
     */
    public static function sendSafe(string $recipient, Mailable $mailable, array $payload): bool
    {
        // Buscar el user_id correspondiente al destinatario por su correo electrónico
        $user = User::where('email', $recipient)->first();
        $userId = $user ? $user->id : null;

        $subject = 'Notificación CAJ';
        if (method_exists($mailable, 'envelope')) {
            $envelope = $mailable->envelope();
            if ($envelope && $envelope->subject) {
                $subject = $envelope->subject;
            }
        }

        // Si el fusible está activo, guardar directamente como PENDING sin intentar conexión de red
        if (self::$smtpIsDown) {
            MailLog::create([
                'user_id' => $userId,
                'recipient' => $recipient,
                'subject' => $subject,
                'mailable_class' => get_class($mailable),
                'payload' => $payload,
                'error_message' => 'Envío omitido preventivamente: Fusible de conexión SMTP activado (Servidor fuera de línea).',
                'status' => 'PENDING',
                'attempts' => 1,
            ]);

            return false;
        }

        try {
            Mail::to($recipient)->send($mailable);

            // Log de envío exitoso
            MailLog::create([
                'user_id' => $userId,
                'recipient' => $recipient,
                'subject' => $subject,
                'mailable_class' => get_class($mailable),
                'payload' => $payload,
                'error_message' => null,
                'status' => 'SENT',
                'attempts' => 1,
            ]);

            return true;
        } catch (Throwable $e) {
            // Activar fusible preventivo de conexión caída para los correos restantes de esta transacción
            self::$smtpIsDown = true;

            // Log de envío fallido
            MailLog::create([
                'user_id' => $userId,
                'recipient' => $recipient,
                'subject' => $subject,
                'mailable_class' => get_class($mailable),
                'payload' => $payload,
                'error_message' => $e->getMessage(),
                'status' => 'PENDING',
                'attempts' => 1,
            ]);

            return false;
        }
    }
}
