<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Establecer Nueva Contraseña - Verificador de Actividades</title>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <!-- Alpine.js inyectado de forma segura para dar soporte reactivo e interactivo en tiempo real -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="login-layout-body">

    <header class="header-nav-caj">
        <div class="header-logo-container-caj">
            <span class="logo-text-caj">
                <strong>Intranet CAJBIOBIO</strong>
            </span>
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
                      method="POST"
                      x-data="{
                          password: '',
                          submitted: @json($errors->has('password')),
                          config: {
                              minLength: @json(config('password_policy.min_length')),
                              requireMixedCase: @json(config('password_policy.require_mixed_case')),
                              requireLetters: @json(config('password_policy.require_letters')),
                              requireNumbers: @json(config('password_policy.require_numbers')),
                              requireSymbols: @json(config('password_policy.require_symbols'))
                          },
                          get rules() {
                              let items = [];
                              items.push({
                                  id: 'length',
                                  text: `Tener al menos ${this.config.minLength} caracteres`,
                                  valid: this.password.length >= this.config.minLength
                              });
                              if (this.config.requireLetters) {
                                  items.push({
                                      id: 'letters',
                                      text: 'Contener al menos una letra',
                                      valid: /[a-zA-Z]/.test(this.password)
                                  });
                              }
                              if (this.config.requireMixedCase) {
                                  items.push({
                                      id: 'mixed',
                                      text: 'Contener mayúsculas y minúsculas',
                                      valid: /[a-z]/.test(this.password) && /[A-Z]/.test(this.password)
                                  });
                              }
                              if (this.config.requireNumbers) {
                                  items.push({
                                      id: 'numbers',
                                      text: 'Contener al menos un número',
                                      valid: /\d/.test(this.password)
                                  });
                              }
                              if (this.config.requireSymbols) {
                                  items.push({
                                      id: 'symbols',
                                      text: 'Contener al menos un carácter especial (ej: ! @ # $ % & *)',
                                      valid: /[^a-zA-Z0-9]/.test(this.password)
                                  });
                              }
                              return items;
                          },
                          get allValid() {
                              return this.rules.every(r => r.valid);
                          },
                          handleSubmit(e) {
                              this.submitted = true;
                              if (!this.allValid) {
                                  e.preventDefault();
                              }
                          }
                      }"
                      @submit="handleSubmit($event)">
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

                    <!-- Mensaje de Error Consolidado y Dinámico (UX Faltante) -->
                    <div x-show="submitted && !allValid" 
                         style="background-color: #fff5f5; border: 1px solid #feb2b2; border-radius: 8px; padding: 15px; margin-bottom: 20px;" 
                         x-cloak>
                        <strong style="color: #c53030; font-size: 0.88rem; display: block; margin-bottom: 6px;">
                            La contraseña debe cumplir los siguientes requisitos:
                        </strong>
                        <ul style="margin: 0; padding-left: 20px; font-size: 0.82rem; color: #9b2c2c; display: flex; flex-direction: column; gap: 4px;">
                            <template x-for="rule in rules" :key="rule.id">
                                <li x-show="!rule.valid" x-text="rule.text"></li>
                            </template>
                        </ul>
                    </div>

                    <div class="form-group-item-caj">
                        <label for="password">Nueva Contraseña</label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               x-model="password"
                               class="form-input-control-caj" 
                               :placeholder="'Mínimo ' + config.minLength + ' caracteres'" 
                               :minlength="config.minLength"
                               required 
                               autofocus 
                               autocomplete="new-password">

                        <!-- Checklist de validación en tiempo real -->
                        <div style="margin-top: 12px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px;">
                            <span style="font-size: 0.82rem; color: #475569; font-weight: 700; display: block; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
                                Checklist de Seguridad:
                            </span>
                            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                                <template x-for="rule in rules" :key="rule.id">
                                    <li style="font-size: 0.82rem; display: flex; align-items: center; gap: 10px; transition: all 0.2s ease;"
                                        :style="rule.valid 
                                            ? 'color: #2b8a3e;' 
                                            : (submitted ? 'color: #ef3340;' : 'color: #64748b;')">
                                        
                                        <!-- Círculo indicador con estados: Válido (Verde), Inválido tras envío (Rojo) o Neutral (Gris) -->
                                        <span style="display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; border-radius: 50%; font-size: 0.72rem; font-weight: bold; transition: all 0.2s ease;"
                                              :style="rule.valid 
                                                  ? 'background-color: rgba(43, 138, 62, 0.1); color: #2b8a3e;' 
                                                  : (submitted ? 'background-color: rgba(239, 51, 64, 0.1); color: #ef3340;' : 'background-color: #e2e8f0; color: #64748b;')">
                                            <span x-text="rule.valid ? '✓' : (submitted ? '✗' : '○')"></span>
                                        </span>
                                        <span x-text="rule.text" style="line-height: 1.2;"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        @error('password')
                        <span style="color: #ef3340; font-size: 0.85rem; font-weight: 600; display: block; margin-top: 8px;">
                            ⚠️ {{ $message }}
                        </span>
                        @enderror
                    </div>

                    <div class="form-group-item-caj">
                        <label for="password_confirmation">Confirmar Nueva Contraseña</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-input-control-caj" placeholder="Repita la contraseña" required autocomplete="new-password">
                    </div>

                    <div class="form-group-item-caj" style="margin-top: 25px;">
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