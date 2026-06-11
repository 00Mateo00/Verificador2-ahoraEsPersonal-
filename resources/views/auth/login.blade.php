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
        <div class="header-logo-container-caj">
            <span class="logo-text-caj">
                <strong>Intranet CAJBIOBIO</strong>
            </span>
        </div>
    </header>

    <!-- Contenedor Principal de Dos Columnas -->
    <div class="login-main-container">
        <div class="login-grid-wrapper">

            <!-- Columna Izquierda: Mensaje de Bienvenida Relacionado a la App -->
            <div class="login-welcome-column">
                <h1 class="welcome-title">Bienvenido al Registro de Actividades</h1>
                <p class="welcome-description">
                    Plataforma interna de la Corporación de Asistencia Judicial. Accede para registrar tus actividades, subir documentos de respaldo y automatizar las notificaciones, reemplazando de forma definitiva la antigua metodología de envío de correos con formularios manuales.
                </p>

                <!-- Botón ClaveÚnica Oficial -->
                <a class="btn-claveunica" aria-label="Continuar con ClaveÚnica" href="#">
                    <span class="cl-claveunica" aria-hidden="true"></span>
                    <span class="texto" aria-hidden="true">ClaveÚnica</span>
                </a>
            </div>

            <!-- Columna Derecha: Tarjeta de Acceso Institucional -->

            <div class="login-card-column">
                <div class="login-container-card-caj">
                    <div class="login-card-header">
                        <h2>Acceso al Sistema</h2>
                        <p>Gestión de verificadores centralizada</p>
                    </div>

                    <form class="login-form-body-caj" action="{{ route('login') }}" method="POST">
                        @csrf

                        @if (session('error'))
                        <div class="error-info-alert" style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; font-size: 0.9rem;">
                            <strong>Atención:</strong> {{ session('error') }}
                        </div>
                        @endif

                        <div class="form-group-item-caj">
                            <label for="email">Usuario Institucional</label>
                            <input type="text" id="email" name="email" class="form-input-control-caj" placeholder="ejemplo@cajbiobio.cl" required>
                            @error('email')
                            <span style="color: red;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group-item-caj">
                            <label for="password">Contraseña</label>
                            <input type="password" id="password" name="password" class="form-input-control-caj" placeholder="••••••••••••" required>
                            @error('password')
                            <span style="color: red;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group-item-caj" style="margin-top: 25px;">
                            <button type="submit" class="btn-primary-caj">
                                Ingresar al Panel
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <footer class="footer-credits-caj">
        <p>© 2026 Corporación de Asistencia Judicial de la Región del Biobío. Todos los derechos reservados.</p>
    </footer>

</body>

</html>