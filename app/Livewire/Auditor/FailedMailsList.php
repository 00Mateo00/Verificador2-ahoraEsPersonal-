<?php

namespace App\Livewire\Auditor;

use App\Models\FailedMail;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class FailedMailsList extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    /**
     * Reenvía un correo individual.
     */
    public function resendIndividual($id)
    {
        $mail = FailedMail::findOrFail($id);

        if ($mail->sendSynchronously()) {
            session()->flash('success', "Correo para {$mail->recipient} reenviado con éxito.");
        } else {
            session()->flash('error', "Error al reenviar correo para {$mail->recipient}: {$mail->error_message}");
        }
    }

    /**
     * Reenvía todos los correos fallidos o pendientes.
     */
    public function resendAll()
    {
        $pendingMails = FailedMail::whereIn('status', ['PENDING', 'FAILED'])->get();

        if ($pendingMails->isEmpty()) {
            session()->flash('info', 'No hay correos pendientes o fallidos para reenviar.');

            return;
        }

        $successCount = 0;
        $failCount = 0;

        foreach ($pendingMails as $mail) {
            if ($mail->sendSynchronously()) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        if ($failCount === 0) {
            session()->flash('success', "Operación masiva completada: {$successCount} correos reenviados exitosamente.");
        } else {
            session()->flash('error', "Reenvío masivo parcial: {$successCount} exitosos, {$failCount} fallidos.");
        }
    }

    /**
     * Elimina un registro de correo fallido (Exclusivo Admin en Modo Edición).
     */
    public function deleteMail($id)
    {
        $user = Auth::user();

        // Defensa: Validar rol admin y que el Modo Edición esté activo en sesión
        if ($user->rol !== 'admin' || ! session('modo_edicion')) {
            abort(403, 'Acción no autorizada. Solo un Administrador en Modo Edición puede eliminar correos fallidos.');
        }

        $mail = FailedMail::findOrFail($id);
        $recipient = $mail->recipient;
        $mail->delete();

        session()->flash('success', "Se eliminó el correo fallido destinado a {$recipient} de forma administrativa.");
    }

    public function render()
    {
        $mails = FailedMail::query()
            ->when($this->search, function ($query) {
                $query->where('recipient', 'like', "%{$this->search}%")
                    ->orWhere('subject', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(15);

        return view('livewire.auditor.failed-mails-list', [
            'mails' => $mails,
            'isModoEdicion' => (Auth::user()->rol === 'admin' && session('modo_edicion')),
        ]);
    }
}
