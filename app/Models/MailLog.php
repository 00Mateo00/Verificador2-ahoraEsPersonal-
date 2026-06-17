<?php

namespace App\Models;

use App\Mail\ActividadRegistrada;
use App\Mail\NuevasActividadesPendientes;
use App\Mail\PasswordRenewalMail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Mail;

class MailLog extends Model
{
    protected $table = 'mails';

    protected $fillable = [
        'user_id',
        'recipient',
        'subject',
        'mailable_class',
        'payload',
        'error_message',
        'status',
        'attempts',
    ];

    protected $casts = [
        'payload' => 'array',
        'attempts' => 'integer',
    ];

    /**
     * Relación con el usuario destinatario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Deduce de manera amigable el tipo de correo según la clase del mailable.
     */
    public function getMailTypeAttribute(): string
    {
        $class = $this->mailable_class;

        if ($class === NuevasActividadesPendientes::class) {
            return 'Aviso de Actividades Pendientes';
        }

        if ($class === ActividadRegistrada::class) {
            return 'Registro de Actividad';
        }

        if (str_contains(strtolower($class), 'resetpassword') || str_contains(strtolower($class), 'reset-password')) {
            return 'Restablecimiento de Contraseña';
        }

        return 'Notificación del Sistema';
    }

    /**
     * Reconstruye y envía el correo síncronamente.
     */
    public function sendSynchronously(): bool
    {
        try {
            $class = $this->mailable_class;
            if (! class_exists($class)) {
                throw new \Exception("Clase mailable no encontrada: {$class}");
            }

            $mailable = null;
            if ($class === NuevasActividadesPendientes::class) {
                $unidadId = $this->payload['unidad_id'] ?? null;
                $unidad = Unidad::find($unidadId);
                if (! $unidad) {
                    throw new \Exception("Unidad #{$unidadId} no encontrada para reconstruir correo.");
                }
                $mailable = new NuevasActividadesPendientes($unidad);
            } elseif ($class === ActividadRegistrada::class) {
                $actividadId = $this->payload['actividad_id'] ?? null;
                $actividad = Actividad::find($actividadId);
                if (! $actividad) {
                    throw new \Exception("Actividad #{$actividadId} no encontrada para reconstruir correo.");
                }
                $mailable = new ActividadRegistrada($actividad);
            } elseif ($class === PasswordRenewalMail::class) {
                $userId = $this->payload['user_id'] ?? null;
                $user = User::find($userId);
                if (! $user) {
                    throw new \Exception("Usuario #{$userId} no encontrado para reconstruir correo de renovación.");
                }
                $url = $this->payload['url'] ?? '';
                $expirationString = $this->payload['expiration_string'] ?? '';
                $mailable = new PasswordRenewalMail($user, $url, $expirationString);
            } else {
                throw new \Exception("Mailable no soportado para reconstrucción: {$class}");
            }

            Mail::to($this->recipient)->send($mailable);
            $this->update([
                'status' => 'SENT',
                'attempts' => $this->attempts + 1,
                'error_message' => null,
            ]);

            return true;
        } catch (\Throwable $e) {
            $this->update([
                'status' => 'FAILED',
                'attempts' => $this->attempts + 1,
                'error_message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
