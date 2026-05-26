<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar - FirmaGob</title>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>
<body>

    <!-- Barra de Colores del Gobierno de Chile en el Tope -->
    <div class="header-bar-top"></div>

    <!-- Navegación Principal de FirmaGob -->
    <header class="header-nav">
        <div class="header-logo-container">
            <span class="logo-signature">
                <span class="logo-circle-marker"></span>
                firma.gob
            </span>
        </div>
        <ul class="nav-links-menu">
            <li><a href="#">Home</a></li>
            <li><a href="#">Biblioteca</a></li>
            <li><a href="#">¿Cómo utilizarla?</a></li>
            <li><a href="#">Capacitaciones</a></li>
            <li><a href="#">Ayuda</a></li>
        </ul>
        <div>
            <a href="#" class="btn-header-access">Acceso a FirmaGob</a>
        </div>
    </header>

    <!-- Barra de Accesibilidad Integrada -->
    <div class="accessibility-bar">
        <div class="breadcrumbs">
            <a href="#">Inicio</a>
            <span class="separator">/</span>
            <span>Ingreso</span>
        </div>
        <div class="accessibility-control-buttons">
            <button class="btn-acc" onclick="toggleContrast()" title="Alto Contraste">◐ Contraste</button>
            <button class="btn-acc" onclick="changeFontSize('small')" title="Reducir Fuente">-A</button>
            <button class="btn-acc" onclick="changeFontSize('large')" title="Aumentar Fuente">+A</button>
        </div>
    </div>

    <!-- Sección Hero de FirmaGob Adaptada para Login Centrado -->
    <div class="hero-signature" style="display: flex; justify-content: center; align-items: center; min-height: calc(85vh - 120px); padding: 50px 20px;">
        <!-- Card de Login Integrado y Centrado -->
        <div class="login-container-card" style="margin: 0; width: 100%; max-width: 480px;">
            <div class="login-header-banner">
                <h2 style="color: white">Iniciar Sesión</h2>
                <p style="margin: 5px 0 0; font-size: 0.85rem; opacity: 1;">Acceso seguro con clave institucional</p>
            </div>

            <form class="login-form-body" action="{{ route('login.post') }}" method="POST">
                @csrf

                @if (session('error'))
                    <div class="error-info-alert" style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                        <strong>Atención:</strong> {{ session('error') }}
                    </div>
                @endif

                <div class="form-group-item">
                    <label for="usuario_nombre">Usuario Institucional</label>
                    <input type="text" id="usuario_nombre" name="usuario_nombre" class="form-input-control" placeholder="ejemplo@institucion.gob.cl" required>
                </div>

                <div class="form-group-item">
                    <label for="usuario_pass">Contraseña</label>
                    <input type="password" id="usuario_pass" name="usuario_pass" class="form-input-control" placeholder="••••••••••••" required>
                </div>

                <div class="form-group-item" style="margin-top: 30px;">
                    <button type="submit" class="btn-primary btn-block-action" style="width: 100%; padding: 12px; background-color: var(--color-secondary); color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
                        Ingresar al Panel
                    </button>
                </div>

                <div style="text-align: center; margin-top: 15px;">
                    <a href="#" style="font-size: 0.85rem; color: var(--color-text-light);">¿Olvidó su contraseña institucional?</a>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer-credits-institution" style="text-align: center; padding: 20px; color: var(--color-text-light);">
        <p>© 2026 FirmaGob - Gobierno de Chile. Todos los derechos reservados.</p>
        <p style="font-size: 0.75rem; margin-top: 5px; opacity: 0.7;">Dirección de Tecnologías de la Información Institucional</p>
    </footer>

    <!-- Scripts de Accesibilidad -->
    <script>
        function toggleContrast() {
            document.body.classList.toggle('high-contrast');
            const isHigh = document.body.classList.contains('high-contrast');
            localStorage.setItem('high-contrast', isHigh ? 'true' : 'false');
        }

        function changeFontSize(size) {
            const html = document.documentElement;
            html.classList.remove('font-small', 'font-large', 'font-xlarge');
            if (size === 'small') {
                html.classList.add('font-small');
                localStorage.setItem('font-size', 'small');
            } else if (size === 'large') {
                html.classList.add('font-large');
                localStorage.setItem('font-size', 'large');
            } else if (size === 'xlarge') {
                html.classList.add('font-xlarge');
                localStorage.setItem('font-size', 'xlarge');
            } else {
                localStorage.setItem('font-size', 'normal');
            }
        }

        // Recuperar preferencias
        document.addEventListener('DOMContentLoaded', () => {
            if (localStorage.getItem('high-contrast') === 'true') {
                document.body.classList.add('high-contrast');
            }
            const savedSize = localStorage.getItem('font-size');
            if (savedSize) changeFontSize(savedSize);
        });
    </script>
</body>
</html>