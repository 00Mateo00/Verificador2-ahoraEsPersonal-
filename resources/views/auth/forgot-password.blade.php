<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Verificador de Actividades</title>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
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
                @if($errors->any())
                    <div class="alert-error-top">
                        <strong>⚠ Error de autenticación</strong>
                        <p>{{ $errors->first() }}</p>
                    </div>
                    @endif
                <div class="login-card-header">
                    <h2>Recuperar Contraseña</h2>
                    <p>Ingrese su correo institucional para recibir un enlace de restablecimiento.</p>
                </div>

                @if (session('status'))
                <div style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid #c3e6cb;">
                    <strong>Éxito:</strong> {{ session('status') }}
                </div>
                @endif

                <form class="login-form-body-caj" action="{{ route('password.email') }}" method="POST">
                    @csrf

                    <div class="form-group-item-caj">
                        <label for="email">Correo Institucional</label>
                        <input type="email" id="email" name="email" class="form-input-control-caj" placeholder="ejemplo@cajbiobio.cl" value="{{ old('email') }}" required autofocus>
                    </div>

                    <div class="form-group-item-caj" style="margin-top: 25px;">
                        <button type="submit" class="btn-primary-caj">
                            Enviar Enlace de Recuperación
                        </button>
                    </div>

                    <div style="text-align: center; margin-top: 20px;">
                        <a href="{{ route('login') }}" style="font-size: 0.9rem; font-weight: 600; color: #0F69C4; text-decoration: none;">
                            Volver al Inicio de Sesión
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