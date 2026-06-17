<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contraseña Expirada - Intranet CAJBIOBIO</title>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>

<body class="login-layout-body">

    <!-- Navegación Principal Institucional -->
    <header class="header-nav-caj">
        <div class="header-logo-container-caj">
            <span class="logo-text-caj">
                <strong>Intranet CAJBIOBIO</strong> <span style="font-weight: 300; opacity: 0.8; margin-left: 10px; font-size: 0.95rem; border-left: 1px solid rgba(255,255,255,0.3); padding-left: 10px;">Verificador de Actividades</span>
            </span>
        </div>
    </header>

    <!-- Contenedor Principal de Dos Columnas -->
    <div class="login-main-container">
        <div class="login-grid-wrapper" style="grid-template-columns: 1fr; max-width: 520px;">

            <!-- Tarjeta de Contraseña Expirada -->
            <div class="login-container-card-caj" style="border-top-color: #ef3340;">
                <div class="login-card-header" style="text-align: center;">
                    <div style="font-size: 3.5rem; margin-bottom: 15px;">🔒</div>
                    <h2 style="color: #ef3340;">Contraseña Expirada</h2>
                    <p style="margin-top: 10px; margin-bottom: 25px; font-size: 0.92rem; color: #475569; line-height: 1.6; text-align: left;">
                        Estimado(a) <strong>{{ $user->name }}</strong>, de acuerdo con la política de seguridad corporativa de la Corporación de Asistencia Judicial de la Región del Biobío, las contraseñas deben ser renovadas obligatoriamente cada 90 días.
                    </p>
                    <p style="margin-top: 10px; margin-bottom: 25px; font-size: 0.92rem; color: #475569; line-height: 1.6; text-align: left;">
                        Su clave de acceso expiró el día <strong>{{ $expirationDate }}</strong>. Para resguardar la confidencialidad de la información y recuperar el acceso total, es mandatorio que realice el cambio de clave.
                    </p>
                </div>

                @if (session('success'))
                <div style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid #c3e6cb; font-weight: 600;">
                    ✓ {{ session('success') }}
                </div>
                @endif

                @if (session('error'))
                <div style="background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid #f5c6cb; font-weight: 600;">
                    ⚠ {{ session('error') }}
                </div>
                @endif

                <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 15px; margin-bottom: 25px; font-size: 0.88rem; color: #1e3a8a; line-height: 1.5;">
                    📧 <strong>Enlace enviado:</strong> Hemos enviado un enlace de renovación seguro a tu correo institucional <strong>{{ $user->email }}</strong>. Sigue las instrucciones del mensaje para restablecer tu contraseña.
                </div>

                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <form action="{{ route('password.request-renewal') }}" method="POST" style="margin: 0; width: 100%;">
                        @csrf
                        <button type="submit" class="btn-primary-caj" style="background-color: #0F69C4; width: 100%; padding: 12px;">
                            Reenviar correo de renovación ✉️
                        </button>
                    </form>

                    <a href="{{ route('login') }}" class="btn-acc" style="width: 100%; padding: 12px; border-color: #cbd5e1; font-weight: 600; cursor: pointer; text-align: center; text-decoration: none; box-sizing: border-box; display: block; color: #475569;">
                        Volver al inicio de sesión ↩
                    </a>
                </div>
            </div>

        </div>
    </div>

    <footer class="footer-credits-caj">
        <p>© 2026 Corporación de Asistencia Judicial de la Región del Biobío. Todos los derechos reservados.</p>
    </footer>

</body>

</html>