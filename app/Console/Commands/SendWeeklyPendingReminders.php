<?php

namespace App\Console\Commands;

use App\Mail\NuevasActividadesPendientes;
use App\Models\Unidad;
use App\Services\MailService;
use Illuminate\Console\Command;

class SendWeeklyPendingReminders extends Command
{
    /**
     * El nombre y firma del comando de consola.
     *
     * @var string
     */
    protected $signature = 'actividades:send-weekly-reminders';

    /**
     * La descripción del comando de consola.
     *
     * @var string
     */
    protected $description = 'Envía recordatorios de verificación semanales a las unidades que presentan actividades pendientes del año en curso';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle(): int
    {
        $this->info('Iniciando proceso de re-notificación semanal de actividades pendientes...');

        $currentYear = (int) now()->year;

        // Recuperar unidades que poseen al menos una actividad con estado 'CARGADA' en el año actual
        $unidades = Unidad::query()
            ->whereHas('actividadesAsignadas', function ($query) use ($currentYear) {
                $query->where('estado', 'CARGADA')
                      ->where('AÑO', $currentYear);
            })
            ->with(['user'])
            ->get();

        if ($unidades->isEmpty()) {
            $this->info("No se encontraron unidades operativas con actividades pendientes para el año {$currentYear}.");
            return self::SUCCESS;
        }

        $this->info("Se identificaron {$unidades->count()} unidades con pendientes de verificación.");

        $sentEmails = [];
        $successCount = 0;
        $failCount = 0;

        foreach ($unidades as $unidad) {
            if (!$unidad->user || empty($unidad->user->email)) {
                $this->warn("La unidad con ID #{$unidad->id} no cuenta con un operador de sistema asignado o carece de email.");
                continue;
            }

            $email = $unidad->user->email;

            // Evitar envíos múltiples a la misma casilla de correo si existieran cruces de datos en la ejecución actual
            if (in_array($email, $sentEmails, true)) {
                $this->line("Omitiendo duplicado de recordatorio para: {$email}");
                continue;
            }

            $this->line("Enviando correo de re-notificación a: {$email}");

            // Envío a través de MailService para registrar fallos en mails (failed_mails) ante caídas de red
            $sent = MailService::sendSafe(
                $email,
                new NuevasActividadesPendientes($unidad),
                ['unidad_id' => $unidad->id]
            );

            if ($sent) {
                $successCount++;
                $this->info("✓ Correo enviado con éxito a {$email}.");
            } else {
                $failCount++;
                $this->error("✕ Error síncrono al despachar a {$email}. Registrado en historial local de correos.");
            }

            $sentEmails[] = $email;
        }

        $this->info("Operación finalizada. Exitosos: {$successCount}, Fallidos: {$failCount}.");

        return self::SUCCESS;
    }
}