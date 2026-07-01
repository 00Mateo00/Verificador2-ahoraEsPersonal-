<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Enums\UserRole;
use App\Http\Responses\SuccessfulPasswordResetResponse as CustomSuccessfulPasswordResetResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            SuccessfulPasswordResetResponse::class,
            CustomSuccessfulPasswordResetResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
        $this->configureLoginResponse();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        // Enrutamiento de vistas de autenticación a plantillas existentes
        Fortify::loginView(fn () => view('auth.login'));

        Fortify::confirmPasswordView(fn () => view('auth.confirm-password'));
        Fortify::resetPasswordView(fn () => view('auth.reset-password'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
    }

    /**
     * Customiza la respuesta de autenticación exitosa para redirigir según el rol del usuario.
     */
    private function configureLoginResponse(): void
    {
        $this->app->singleton(
            LoginResponse::class,
            function () {
                return new class implements LoginResponse
                {
                    public function toResponse($request)
                    {
                        $user = Auth::user();

                        if (! $user) {
                            return redirect()->route('login');
                        }

                        // Bloquear sesión si la cuenta está deshabilitada administrativamente
                        if (! $user->activo) {
                            Auth::logout();
                            $request->session()->invalidate();
                            $request->session()->regenerateToken();

                            return redirect()->route('login')->with('error', 'Su cuenta se encuentra deshabilitada.');
                        }

                        return redirect()->route('dashboard');
                    }
                };
            }
        );
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

    }
}
