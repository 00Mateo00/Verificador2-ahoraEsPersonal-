<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
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
        
        // Mapeo defensivo para evitar excepciones por desconfiguración de namespaces inexistentes (pages::)
        Fortify::verifyEmailView(fn () => view('auth.login'));
        Fortify::twoFactorChallengeView(fn () => view('auth.login'));
        Fortify::confirmPasswordView(fn () => view('auth.login'));
        Fortify::registerView(fn () => view('auth.login'));
        Fortify::resetPasswordView(fn () => view('auth.login'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.login'));
    }

    /**
     * Customiza la respuesta de autenticación exitosa para redirigir según el rol del usuario.
     */
    private function configureLoginResponse(): void
    {
        $this->app->singleton(
            \Laravel\Fortify\Contracts\LoginResponse::class,
            function () {
                return new class implements \Laravel\Fortify\Contracts\LoginResponse {
                    public function toResponse($request)
                    {
                        $user = \Illuminate\Support\Facades\Auth::user();

                        if (!$user) {
                            return redirect()->route('login');
                        }

                        // Bloquear sesión si la cuenta está deshabilitada administrativamente
                        if (!$user->estado) {
                            \Illuminate\Support\Facades\Auth::logout();
                            $request->session()->invalidate();
                            $request->session()->regenerateToken();
                            return redirect()->route('login')->with('error', 'Su cuenta se encuentra deshabilitada.');
                        }

                        $rol = $user->rol;

                        if ($rol === 'admin') {
                            return redirect()->route('admin.dashboard');
                        }
                        if ($rol === 'cargador') {
                            return redirect()->route('actividades.importar');
                        }
                        if ($rol === 'unidad') {
                            return redirect()->route('unidad.dashboard');
                        }

                        return redirect()->route('actividades.index');
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
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('passkeys', function (Request $request) {
            $credentialId = $request->input('credential.id');

            return Limit::perMinute(10)->by(
                ($credentialId ?: $request->session()->getId()).'|'.$request->ip(),
            );
        });
    }
}
