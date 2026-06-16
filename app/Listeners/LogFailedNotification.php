<?php

namespace App\Listeners;

use App\Models\MailLog;
use App\Models\User;
use Illuminate\Notifications\Events\NotificationFailed;
use Throwable;

class LogFailedNotification
{
    /**
     * Captura y registra cualquier fallo en el canal mail de las notificaciones del sistema.
     */
    public function handle(NotificationFailed $event): void
    {
        // Registrar únicamente fallos del canal mail
        if ($event->channel !== 'mail') {
            return;
        }

        $notifiable = $event->notifiable;
        $recipient = method_exists($notifiable, 'routeNotificationFor')
            ? $notifiable->routeNotificationFor('mail')
            : ($notifiable->email ?? null);

        if (!$recipient) {
            return;
        }

        $userId = ($notifiable instanceof User) ? $notifiable->id : null;
        if (!$userId) {
            $user = User::where('email', $recipient)->first();
            $userId = $user ? $user->id : null;
        }

        $notification = $event->notification;
        $subject = 'Notificación del Sistema';
        if (method_exists($notification, 'toMail')) {
            try {
                $mailMessage = $notification->toMail($notifiable);
                if ($mailMessage && $mailMessage->subject) {
                    $subject = $mailMessage->subject;
                }
            } catch (Throwable $e) {
                // Conservar subject de respaldo si la previsualización falla
            }
        }

        MailLog::create([
            'user_id' => $userId,
            'recipient' => $recipient,
            'subject' => $subject,
            'mailable_class' => get_class($notification),
            'payload' => [
                'notifiable_id' => $userId,
                'notification_class' => get_class($notification)
            ],
            'error_message' => $event->exception ? $event->exception->getMessage() : 'Error de transporte de notificación',
            'status' => 'PENDING',
            'attempts' => 1,
        ]);
    }
}