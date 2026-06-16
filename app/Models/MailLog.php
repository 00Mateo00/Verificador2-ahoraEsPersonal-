<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

        if ($class === \App\Mail\NuevasActividadesPendientes::class) {
            return 'Aviso de Actividades Pendientes';
        }

        if ($class === \App\Mail\ActividadRegistrada::class) {
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
            if (!class_exists($class)) {
                throw new \Exception("Clase mailable no encontrada: {$class}");
            }

            $mailable = null;
            if ($class === \App\Mail\NuevasActividadesPendientes::class) {
                $unidadId = $this->payload['unidad_id'] ?? null;
                $unidad = \App\Models\Unidad::find($unidadId);
                if (!$unidad) {
                    throw new \Exception("Unidad #{$unidadId} no encontrada para reconstruir correo.");
                }
                $mailable = new \App\Mail\NuevasActividadesPendientes($unidad);
            } elseif ($class === \App\Mail\ActividadRegistrada::class) {
                $actividadId = $this->payload['actividad_id'] ?? null;
                $actividad = \App\Models\Actividad::find($actividadId);
                if (!$actividad) {
                    throw new \Exception("Actividad #{$actividadId} no encontrada para reconstruir correo.");
                }
                $mailable = new \App\Mail\ActividadRegistrada($actividad);
            } else {
                throw new \Exception("Mailable no soportado para reconstrucción: {$class}");
            }

            \Illuminate\Support\Facades\Mail::to($this->recipient)->send($mailable);
            
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