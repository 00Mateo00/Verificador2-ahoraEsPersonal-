<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Contraseña - Intranet CAJBIOBIO</title>
    @vite([
        'resources/scss/app.scss',
        'resources/js/app.js'
    ])
</head>

<body class="login-layout-body">

    <!-- Navegación Principal Institucional -->
    <header class="header-nav-caj">
        <div class="header-inner-caj">
            <div class="header-brand-sgv">
                <div class="header-brand-icon">CAJ</div>
                <div class="header-brand-text">
                    <h1>Intranet</h1>
                    <span>Corporación de Asistencia Judicial - Region del Biobió</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Contenedor Principal de Dos Columnas -->
    <div class="login-main-container">
        <div class="login-grid-wrapper" style="grid-template-columns: 1fr; max-width: 480px;">

            <!-- Tarjeta de Confirmación Segura -->
            <div class="login-container-card-caj">
                <div class="login-card-header">
                    <h2>🔐 Confirmar Identidad</h2>
                    <p style="margin-top: 8px; margin-bottom: 25px; font-size: 0.88rem; color: #64748b; line-height: 1.5;">
                        Está intentando ingresar a una sección protegida de configuración crítica. Por favor, reconfirme su contraseña institucional para continuar.
                    </p>
                </div>

                <form class="login-form-body-caj" action="{{ route('password.confirm') }}" method="POST">
                    @csrf

                    <div class="form-group-item-caj">
                        <label for="password">Contraseña Institucional</label>
                        <input type="password" id="password" name="password" class="form-input-control-caj" placeholder="••••••••••••" required autocomplete="current-password" autofocus>
                        @error('password')
                        <span style="color: #ef3340; font-size: 0.82rem; font-weight: 600; display: block; margin-top: 6px;">
                            ⚠️ {{ $message }}
                        </span>
                        @enderror
                    </div>

                    <div class="form-group-item-caj" style="margin-top: 25px; display: flex; gap: 15px; flex-direction: row-reverse;">
                        <button type="submit" class="btn-dashboard-primary">
                            Confirmar Contraseña
                        </button>
                        <a href="{{ route('admin.dashboard') }}" class="btn-acc" style="flex: 1; text-align: center; padding: 12px; border: 1px solid #cbd5e1; text-decoration: none; font-size: 0.95rem; font-weight: 600; border-radius: 6px;">
                            Cancelar
                        </a>
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