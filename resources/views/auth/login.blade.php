<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar - Verificador de Actividades</title>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>

<body class="login-layout-body">

    <!-- Navegación Principal adaptada a Intranet CAJBIOBIO -->
    <header class="header-nav-caj">
        <div class="header-brand-sgv">
            <div class="header-brand-icon">
                SGV
            </div>
            <div class="header-brand-text">
                <h1>Sistema de Gestión Verificador</h1>
                <span>Corporación de Asistencia Judicial · Región del Biobío</span>
            </div>
        </div>
    </header>

    <!-- Contenedor Principal de Dos Columnas -->
    <div class="login-main-container">
        <div class="login-grid-wrapper">

            <!-- Columna Izquierda: Mensaje de Bienvenida Relacionado a la App -->
            <div class="login-welcome-column">
                <span class="welcome-badge">
                    Plataforma Institucional
                </span>
                <h1 class="welcome-title">
                    Sistema de Gestión Verificador
                </h1>
                <p class="welcome-description">
                    Centraliza el registro de actividades, la gestión documental
                    y el seguimiento de procesos internos en una plataforma segura,
                    eficiente y orientada a la trazabilidad de la información.
                </p>
                <div class="system-info-box">
                    <div class="system-info-title">
                        Funcionalidades principales
                    </div>
                    <div><strong>01.</strong>&nbsp; Registro de Actividades</div>
                    <div><strong>02.</strong>&nbsp; Gestión Documental</div>
                    <div><strong>03.</strong>&nbsp; Seguimiento Institucional</div>
                    <div><strong>04.</strong>&nbsp; Notificaciones Automáticas</div>
                </div>
            </div>

            <!-- Columna Derecha: Tarjeta de Acceso Institucional -->

            <div class="login-card-column">
                <div class="login-container-card-caj">
                    @if($errors->any())
                    <div class="alert-error-top">
                        <strong>⚠ Error de autenticación</strong>
                        <p>{{ $errors->first() }}</p>
                    </div>
                    @endif
                    <div class="login-card-header">
                        <h2>Acceso al Sistema</h2>
                        <p>Gestión de verificadores centralizada</p>
                    </div>

                    <form class="login-form-body-caj" action="{{ route('login') }}" method="POST">
                        @csrf
                        <div class="form-group-item-caj">
                            <label for="email">Usuario Institucional</label>
                            <input type="text" id="email" name="email" class="form-input-control-caj" placeholder="ejemplo@cajbiobio.cl" autocomplete="username" required>
                        </div>
                        <div class="form-group-item-caj">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <label for="password" style="margin: 0;">Contraseña</label>
                                <a href="{{ route('password.request') }}" style="font-size: 0.82rem; font-weight: 600; color: #0F69C4; text-decoration: none;">
                                    ¿Olvidó su contraseña?
                                </a>
                            </div>
                            <div class="password-wrapper">
                                <input type="password" id="password" name="password" class="form-input-control-caj" autocomplete="current-password" placeholder="••••••••••••" required>
                                <button
                                    type="button"
                                    id="togglePassword"
                                    class="password-toggle"
                                    aria-label="Mostrar contraseña"
                                    onclick="togglePasswordVisibility()">
                                    Mostrar
                                </button>
                            </div>
                        </div>

                        <div class="form-group-item-caj" style="margin-top: 25px;">
                            <button type="submit" class="btn-primary-caj">
                                Acceder al Sistema
                            </button>
                            <p class="login-security-note">
                                Acceso restringido a personal autorizado.
                            </p>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <footer class="footer-credits-caj">
        <p>© 2026 Corporación de Asistencia Judicial de la Región del Biobío. Todos los derechos reservados.</p>
    </footer>
<script>
    function togglePasswordVisibility() {

        const password = document.getElementById("password");
        const button = document.getElementById("togglePassword");

        if (password.type === "password") {

            password.type = "text";
            button.textContent = "Ocultar";
            button.setAttribute("aria-label", "Ocultar contraseña");

        } else {

            password.type = "password";
            button.textContent = "Mostrar";
            button.setAttribute("aria-label", "Mostrar contraseña");

        }
    }
</script>
</body>

</html>