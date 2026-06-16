<?php

namespace App\Providers;

use App\Listeners\LogFailedNotification;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerAuditorPolicies();
        $this->configurePasswordResetEmail();

        // Registrar listener global para capturar fallos de notificaciones (ej: Fortify resets)
        Event::listen(
            NotificationFailed::class,
            LogFailedNotification::class
        );
    }

    /**
     * Registrar políticas de defensa en profundidad para restringir mutaciones al rol auditor.
     */
    protected function registerAuditorPolicies(): void
    {
        // El Gate "mutate" evalúa si el usuario autenticado tiene permisos para realizar mutaciones de estado
        Gate::define('mutate', function ($user) {
            return $user->rol !== 'auditor';
        });
    }

    /**
     * Personalizar y forzar el diseño institucional para los correos de recuperación de contraseña.
     */
    protected function configurePasswordResetEmail(): void
    {
        ResetPassword::toMailUsing(function ($notifiable, $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('Enlace de restablecimiento de contraseña - Intranet CAJBIOBIO')
                ->view('emails.reset-password', [
                    'url' => $url,
                    'user' => $notifiable,
                ]);
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
