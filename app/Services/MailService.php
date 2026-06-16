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
     * Envía un correo síncronamente. Si falla, lo almacena en la tabla mails.
     * Si tiene éxito, registra un log de envío exitoso para el historial del administrador.
     */
    public static function sendSafe(string $recipient, Mailable $mailable, array $payload): bool
    {
        // Buscar el user_id correspondiente al destinatario por su correo electrónico
        $user = User::where('email', $recipient)->first();
        $userId = $user ? $user->id : null;

        $subject = 'Notificación';
        if (method_exists($mailable, 'envelope')) {
            $envelope = $mailable->envelope();
            if ($envelope && $envelope->subject) {
                $subject = $envelope->subject;
            }
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
