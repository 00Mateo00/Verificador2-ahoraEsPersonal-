<?php

namespace App\Models;

use App\Enums\MailStatus;
use App\Mail\ActividadRegistrada;
use App\Mail\NuevasActividadesPendientes;
use App\Services\MailErrorParserService;
use App\Services\MailService;
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
        'status' => MailStatus::class,
    ];

    /**
     * Relación con el usuario destinatario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Accesor dinámico para obtener el mapeo de errores amigables según el fallo SMTP.
     */
    public function getFriendlyErrorAttribute(): array
    {
        return MailErrorParserService::parse($this->error_message);
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
     * Reconstruye y envía el correo síncronamente delegando en MailService.
     */
    public function sendSynchronously(): bool
    {
        return MailService::sendSynchronously($this);
    }
}
