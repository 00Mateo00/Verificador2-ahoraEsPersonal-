<?php

namespace App\Livewire\Auditor;

use App\Enums\MailStatus;
use App\Enums\UserRole;
use App\Models\MailLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class FailedMailsList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $activeTab = 'pending'; // 'pending' o 'sent'

    public function mount()
    {
        $user = Auth::user();

        if ($user->rol === UserRole::Admin) {
            // Lógica por defecto para el admin:
            // Si hay correos pendientes, la pestaña por defecto es 'pending'. De lo contrario, es 'sent'.
            $hasPending = MailLog::whereIn('status', [MailStatus::Pending, MailStatus::Failed])->exists();
            $this->activeTab = $hasPending ? 'pending' : 'sent';
        } else {
            // Auditor no tiene pestañas, siempre visualiza pendientes
            $this->activeTab = 'pending';
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function setTab($tab)
    {
        if (Auth::user()->rol === UserRole::Admin) {
            $this->activeTab = $tab;
            $this->resetPage();
        }
    }

    /**
     * Reenvía un correo individual.
     */
    public function resendIndividual($id)
    {
        $mail = MailLog::findOrFail($id);

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
        $pendingMails = MailLog::whereIn('status', [MailStatus::Pending, MailStatus::Failed])->get();

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
        if ($user->rol !== UserRole::Admin || ! session('modo_edicion')) {
            abort(403, 'Acción no autorizada. Solo un Administrador en Modo Edición puede eliminar registros de correos.');
        }

        $mail = MailLog::findOrFail($id);
        $recipient = $mail->recipient;
        $mail->delete();

        session()->flash('success', "Se eliminó el correo destinado a {$recipient} de forma administrativa.");
    }

    public function render()
    {
        $user = Auth::user();
        $isAdmin = $user->rol === UserRole::Admin;

        $query = MailLog::query()->with('user');

        if ($isAdmin) {
            if ($this->activeTab === 'sent') {
                $query->where('status', MailStatus::Sent);
            } else {
                $query->whereIn('status', [MailStatus::Pending, MailStatus::Failed]);
            }
        } else {
            // Auditor siempre ve pendientes/fallidos
            $query->whereIn('status', [MailStatus::Pending, MailStatus::Failed]);
        }

        $mails = $query->when($this->search, function ($q) {
            $q->where(function ($sub) {
                $sub->where('recipient', 'like', "%{$this->search}%")
                    ->orWhereHas('user', function ($uQ) {
                        $uQ->where('name', 'like', "%{$this->search}%");
                    });
            });
        })
            ->latest()
            ->paginate(15);

        return view('livewire.auditor.failed-mails-list', [
            'mails' => $mails,
            'isAdmin' => $isAdmin,
            'isModoEdicion' => ($isAdmin && session('modo_edicion')),
        ]);
    }
}
