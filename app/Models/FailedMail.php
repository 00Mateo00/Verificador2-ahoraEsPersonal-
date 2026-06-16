<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedMail extends Model
{
    protected $table = 'failed_mails';

    protected $fillable = [
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