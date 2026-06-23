<?php

namespace App\Services;

use App\Mail\ActividadRegistrada;
use App\Mail\NuevasActividadesPendientes;
use App\Mail\PasswordRenewalMail;
use App\Models\Actividad;
use App\Models\MailLog;
use App\Models\Unidad;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MailService
{
    /**
     * Reconstruye y envía un correo del log síncronamente por red.
     */
    public static function sendSynchronously(MailLog $mailLog): bool
    {
        try {
            $class = $mailLog->mailable_class;
            if (! class_exists($class)) {
                throw new \Exception("Clase mailable no encontrada: {$class}");
            }

            $mailable = null;
            if ($class === NuevasActividadesPendientes::class) {
                $unidadId = $mailLog->payload['unidad_id'] ?? null;
                $unidad = Unidad::find($unidadId);
                if (! $unidad) {
                    throw new \Exception("Unidad #{$unidadId} no encontrada para reconstruir correo.");
                }
                $mailable = new NuevasActividadesPendientes($unidad);
            } elseif ($class === ActividadRegistrada::class) {
                $actividadId = $mailLog->payload['actividad_id'] ?? null;
                $actividad = Actividad::find($actividadId);
                if (! $actividad) {
                    throw new \Exception("Actividad #{$actividadId} no encontrada para reconstruir correo.");
                }
                $mailable = new ActividadRegistrada($actividad);
            } elseif ($class === PasswordRenewalMail::class) {
                $userId = $mailLog->payload['user_id'] ?? null;
                $user = User::find($userId);
                if (! $user) {
                    throw new \Exception("Usuario #{$userId} no encontrado para reconstruir correo de renovación.");
                }
                $url = $mailLog->payload['url'] ?? '';
                $expirationString = $mailLog->payload['expiration_string'] ?? '';
                $mailable = new PasswordRenewalMail($user, $url, $expirationString);
            } else {
                throw new \Exception("Mailable no soportado para reconstrucción: {$class}");
            }

            Mail::to($mailLog->recipient)->send($mailable);
            $mailLog->update([
                'status' => UMailStatus::Sent,
                'attempts' => $mailLog->attempts + 1,
                'error_message' => null,
            ]);

            return true;
        } catch (Throwable $e) {
            $mailLog->update([
                'status' => UMailStatus::Failed,
                'attempts' => $mailLog->attempts + 1,
                'error_message' => $e->getMessage(),
            ]);

            return false;
        }
    }

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
