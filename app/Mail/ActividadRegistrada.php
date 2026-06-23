<?php

namespace App\Mail;

use App\Models\Actividad;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ActividadRegistrada extends Mailable
{
    use Queueable, SerializesModels;

    public $actividad;

    public function __construct(Actividad $actividad)
    {
        $this->actividad = $actividad;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva Actividad Registrada: ' . $this->actividad->nombre_actividad,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.actividad-registrada',
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        foreach ($this->actividad->archivos as $archivo) {
            $attachments[] = Attachment::fromStorageDisk('local', $archivo->archivo_ruta)
                ->as($archivo->archivo_nombre)
                ->withMime($archivo->archivo_tipo);
        }

        return $attachments;
    }
}