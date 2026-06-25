<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Establecer Nueva Contraseña - Verificador de Actividades</title>
    @vite([
        'resources/scss/app.scss',
        'resources/js/app.js'
    ])
    
    <!-- Script Vanilla JS independiente para validación de complejidad de contraseña en cliente -->
    <script src="{{ asset('assets/js/password-reset.js') }}" defer></script>
</head>

<body class="login-layout-body">

    <header class="header-nav-caj">
        <div class="header-inner-caj">
            <div class="header-brand-sgv">
                <div class="header-brand-icon">CAJ</div>
                <div class="header-brand-text">
                    <h1>Intranet</h1>
                    <span>Corporación de Asistencia Judicial - Region del  Biobió</span>
                </div>
            </div>
        </div>
    </header>

    <div class="login-main-container">
        <div class="login-grid-wrapper" style="grid-template-columns: 1fr; max-width: 480px;">

            <div class="login-container-card-caj">
                <div class="login-card-header">
                    @if(request('reason') === 'first_login')
                        <h2>🔐 Contraseña Inicial</h2>
                        <p style="margin-top: 8px; margin-bottom: 25px; font-size: 0.92rem; color: #475569; line-height: 1.6;">
                            ¡Bienvenido(a) a la Intranet CAJBIOBIO! Como medida de seguridad obligatoria para su <strong>primer inicio de sesión</strong>, es necesario que reemplace su clave temporal por una contraseña definitiva de uso personal.
                        </p>
                    @elseif(request('reason') === 'renewal')
                        <h2>🔄 Renovar Contraseña</h2>
                        <p style="margin-top: 8px; margin-bottom: 25px; font-size: 0.92rem; color: #475569; line-height: 1.6;">
                            Su contraseña de acceso ha expirado (política de seguridad de 90 días). Por favor, establezca una nueva contraseña segura para restablecer su acceso de forma inmediata y sin fricciones.
                        </p>
                    @else
                        <h2>Nueva Contraseña</h2>
                        <p>Por favor, ingrese sus nuevas credenciales de acceso para actualizar su cuenta.</p>
                    @endif
                </div>

                <form class="login-form-body-caj" 
                      action="{{ route('password.update') }}" 
                      method="POST">
                    @csrf

                    <!-- Token de Recuperación Proporcionado por Fortify -->
                    <input type="hidden" name="token" value="{{ request()->route('token') }}">

                    <div class="form-group-item-caj">
                        <label for="email" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                            <span>Correo Institucional</span>
                            <span style="font-size: 0.72rem; background-color: #f1f5f9; color: #64748b; padding: 2px 6px; border-radius: 4px; font-weight: 600;">🔒 No editable</span>
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-input-control-caj" 
                               value="{{ old('email', request()->email) }}" 
                               style="background-color: #f1f5f9; color: #64748b; border-color: #cbd5e1; cursor: not-allowed; font-weight: 500; pointer-events: none;"
                               required 
                               readonly
                               tabindex="-1">
                        @error('email')
                        <span style="color: #ef3340; font-size: 0.85rem; font-weight: 600; display: block; margin-top: 6px;">
                            ⚠️ {{ $message }}
                        </span>
                        @enderror
                    </div>

                    <!-- Reubicado por encima de ambos campos de contraseña -->
                    <div style="margin-bottom: 20px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px;">
                        <span style="font-size: 0.82rem; color: #475569; font-weight: 700; display: block; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
                            Requisitos de la contraseña:
                        </span>
                        <ul id="password-requirements" 
                            data-min-length="{{ config('password_policy.min_length', 8) }}"
                            data-require-mixed-case="{{ config('password_policy.require_mixed_case', true) ? 'true' : 'false' }}"
                            data-require-letters="{{ config('password_policy.require_letters', true) ? 'true' : 'false' }}"
                            data-require-numbers="{{ config('password_policy.require_numbers', true) ? 'true' : 'false' }}"
                            data-require-symbols="{{ config('password_policy.require_symbols', true) ? 'true' : 'false' }}"
                            style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                            
                            <li id="req-length" class="req-item" style="font-size: 0.82rem; display: flex; align-items: center; gap: 10px; color: #64748b; transition: all 0.2s ease;">
                                <span class="req-icon" style="display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; border-radius: 50%; font-size: 0.72rem; font-weight: bold; background-color: #e2e8f0; color: #64748b; transition: all 0.2s ease;">○</span>
                                <span>Tener al menos <span id="min-length-val">8</span> caracteres</span>
                            </li>
                            <li id="req-letters" class="req-item" style="font-size: 0.82rem; display: flex; align-items: center; gap: 10px; color: #64748b; transition: all 0.2s ease;">
                                <span class="req-icon" style="display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; border-radius: 50%; font-size: 0.72rem; font-weight: bold; background-color: #e2e8f0; color: #64748b; transition: all 0.2s ease;">○</span>
                                <span>Contener al menos una letra</span>
                            </li>
                            <li id="req-mixed" class="req-item" style="font-size: 0.82rem; display: flex; align-items: center; gap: 10px; color: #64748b; transition: all 0.2s ease;">
                                <span class="req-icon" style="display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; border-radius: 50%; font-size: 0.72rem; font-weight: bold; background-color: #e2e8f0; color: #64748b; transition: all 0.2s ease;">○</span>
                                <span>Contener mayúsculas y minúsculas</span>
                            </li>
                            <li id="req-numbers" class="req-item" style="font-size: 0.82rem; display: flex; align-items: center; gap: 10px; color: #64748b; transition: all 0.2s ease;">
                                <span class="req-icon" style="display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; border-radius: 50%; font-size: 0.72rem; font-weight: bold; background-color: #e2e8f0; color: #64748b; transition: all 0.2s ease;">○</span>
                                <span>Contener al menos un número</span>
                            </li>
                            <li id="req-symbols" class="req-item" style="font-size: 0.82rem; display: flex; align-items: center; gap: 10px; color: #64748b; transition: all 0.2s ease;">
                                <span class="req-icon" style="display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; border-radius: 50%; font-size: 0.72rem; font-weight: bold; background-color: #e2e8f0; color: #64748b; transition: all 0.2s ease;">○</span>
                                <span>Contener al menos un carácter especial (ej: ! @ # $ % & *)</span>
                            </li>
                            <li id="req-match" class="req-item" style="font-size: 0.82rem; display: flex; align-items: center; gap: 10px; color: #64748b; transition: all 0.2s ease;">
                                <span class="req-icon" style="display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; border-radius: 50%; font-size: 0.72rem; font-weight: bold; background-color: #e2e8f0; color: #64748b; transition: all 0.2s ease;">○</span>
                                <span>Las contraseñas coinciden</span>
                            </li>
                        </ul>
                    </div>

                    <div class="form-group-item-caj">
                        <label for="password">Nueva Contraseña</label>
                        <div class="password-wrapper">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="form-input-control-caj" 
                                   placeholder="Cargando requisitos..." 
                                   required 
                                   autofocus 
                                   autocomplete="new-password">
                            <button type="button" 
                                    id="toggle-password"
                                    class="password-toggle">
                                Mostrar
                            </button>
                        </div>

                        @error('password')
                        <span style="color: #ef3340; font-size: 0.85rem; font-weight: 600; display: block; margin-top: 8px;">
                            ⚠️ {{ $message }}
                        </span>
                        @enderror
                    </div>

                    <div class="form-group-item-caj">
                        <label for="password_confirmation">Confirmar Nueva Contraseña</label>
                        <div class="password-wrapper">
                            <input type="password" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   class="form-input-control-caj" 
                                   placeholder="Repita la contraseña" 
                                   required 
                                   autocomplete="new-password">
                            <button type="button" 
                                    id="toggle-confirm-password"
                                    class="password-toggle">
                                Mostrar
                            </button>
                        </div>
                    </div>

                    <div class="form-group-item-caj">
                        <button type="submit" class="btn-primary-caj">
                            Restablecer Contraseña
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <footer class="footer-credits-caj">
        <p>© 2026 Corporación de Asistencia Judicial de la Región del Biobío. Todos los derechos reservados.</p>
    </footer>

</body>

</html>