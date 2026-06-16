<?php

namespace App\Services;

use App\Models\FailedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Mailable;
use Throwable;

class MailService
{
    /**
     * Envía un correo síncronamente. Si falla, lo almacena en la tabla failed_mails.
     */
    public static function sendSafe(string $recipient, Mailable $mailable, array $payload): bool
    {
        try {
            Mail::to($recipient)->send($mailable);
            return true;
        } catch (Throwable $e) {
            $subject = 'Notificación CAJ';
            if (method_exists($mailable, 'envelope')) {
                $envelope = $mailable->envelope();
                if ($envelope && $envelope->subject) {
                    $subject = $envelope->subject;
                }
            }

            FailedMail::create([
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