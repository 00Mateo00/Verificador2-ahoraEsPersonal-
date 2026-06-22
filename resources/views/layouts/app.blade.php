<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistema Verificado')</title>

    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="dashboard-layout-body">

    <!-- Navegación Principal CAJBIOBIO (Sumamente limpia, alineada a dashboard.png) -->
    <header class="header-nav-caj">
        <div class="header-inner-caj">
            <div class="header-logo-container-caj">
                <span class="logo-text-caj">
                    <strong>Intranet CAJBIOBIO</strong> <span style="font-weight: 300; opacity: 0.8; margin-left: 10px; font-size: 0.95rem; border-left: 1px solid rgba(255,255,255,0.3); padding-left: 10px;">Verificador de Actividades</span>
                </span>
            </div>

            <div style="display: flex; align-items: center; gap: 20px;">
            <div class="user-display-profile-badge" style="color: #ffffff; display: flex; align-items: center; gap: 8px; font-size: 0.9rem;">
                <span style="width: 8px; height: 8px; background-color: #2b8a3e; border-radius: 50%; display: inline-block;"></span>
                @if(Auth::user()->rol === 'admin')
                <span style="background-color: #ef3340; color: #ffffff; font-size: 0.75rem; font-weight: bold; padding: 2px 6px; border-radius: 4px; margin-right: 4px;">ADMIN</span>
                @endif
                <span style="font-weight: 500;">{{ Auth::user()->name }}</span>
            </div>

            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" style="background-color: transparent; border: 1px solid rgba(255,255,255,0.4); color: #ffffff; cursor: pointer; padding: 6px 14px; border-radius: 4px; font-weight: 600; font-size: 0.85rem; transition: all 0.2s ease;">
                    Cerrar Sesión
                </button>
            </form>
            </div>
        </div>
    </header>

    <!-- Layout Principal de Dos Columnas -->
    <main class="layout-dashboard-main">
        <aside>
            <div class="menu-sidebar-left">
                <div class="sidebar-title">
                    @if(Auth::user()->rol === 'admin')
                    Panel Central
                    @elseif(Auth::user()->rol === 'cargador')
                    Módulo Importación
                    @elseif(Auth::user()->rol === 'unidad')
                    Menú Unidad
                    @elseif(Auth::user()->rol === 'director')
                    Menú Dirección
                    @else
                    Menú Consultas
                    @endif
                </div>
                <ul>
                    <!-- Enlace dinámico al Dashboard según Rol -->
                    @if(Auth::user()->rol === 'admin')
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            Dashboard Principal
                        </a>
                    </li>
                    @elseif(Auth::user()->rol === 'auditor')
                    <li>
                        <a href="{{ route('auditor.dashboard') }}" class="{{ request()->routeIs('auditor.dashboard') ? 'active' : '' }}">
                            Dashboard Auditoría
                        </a>
                    </li>

                    @elseif(Auth::user()->rol === 'director')
                    <li>
                        <a href="{{ route('director.dashboard') }}" class="{{ request()->routeIs('director.dashboard') ? 'active' : '' }}">
                            Dashboard Regional
                        </a>
                    </li>
                    @endif

                    <!-- Secciones exclusivas de Administración -->
                    @if(Auth::user()->rol === 'admin')
                    <li>
                        <a href="{{ route('admin.usuarios') }}" class="{{ request()->routeIs('admin.usuarios') ? 'active' : '' }}">
                            Usuarios
                        </a>
                    </li>
                    @endif

                    <!-- Historial de Correos (Admin) y Correos Fallidos (Auditor) con indicador dinámico -->
                    @if(Auth::user()->rol === 'admin' || Auth::user()->rol === 'auditor')
                        @php
                            $pendingMailsCount = \App\Models\MailLog::whereIn('status', ['PENDING', 'FAILED'])->count();
                            $hasPendingMails = $pendingMailsCount > 0;
                            $routeActive = request()->routeIs('auditor.correos-fallidos');
                        @endphp
                        <li>
                            <a href="{{ route('auditor.correos-fallidos') }}" 
                               class="{{ $routeActive ? 'active' : '' }}"
                               style="@if($hasPendingMails && !$routeActive) background-color: rgba(239, 51, 64, 0.05); color: #ef3340 !important; font-weight: 700;  @endif display: flex; justify-content: space-between; align-items: center; width: 100%; box-sizing: border-box; transition: all 0.2s ease;">
                                <span>
                                    {{ Auth::user()->rol === 'admin' ? 'Historial de Correos' : 'Correos Fallidos' }}
                                </span>
                                <span style="background-color: {{ $hasPendingMails ? '#ef3340' : '#cbd5e1' }}; color: #ffffff; padding: 2px 7px; border-radius: 10px; font-size: 0.75rem; font-weight: 700; margin-left: auto; transition: all 0.2s ease;">
                                    {{ $pendingMailsCount }}
                                </span>
                            </a>
                        </li>
                    @endif

                    <!-- Enlaces dinámicos centralizados por Rol -->
                    @if(Auth::user()->rol === 'admin' || Auth::user()->rol === 'cargador')
                    <li>
                        <a href="{{ route('actividades.importar') }}" class="{{ request()->routeIs('actividades.importar') ? 'active' : '' }}">
                            Importar Planilla
                        </a>
                    </li>
                    @endif
                    

                    @if(Auth::user()->rol === 'unidad')
                    <li>
                        <a href="{{ route('unidad.dashboard') }}" class="{{ request()->routeIs('unidad.dashboard') ? 'active' : '' }}">
                            Verificar Pendientes
                        </a>
                    </li>
                    @endif

                    <li>
                        <a href="{{ route('actividades.historial') }}" class="{{ request()->routeIs('actividades.historial') ? 'active' : '' }}">
                            Historial de Actividades
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <section class="panel-dashboard-content">
            @if(session('password_warning_active'))
            <div style="background-color: #fffbeb; border: 1px solid #fef3c7; border-radius: 8px; padding: 20px; margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; gap: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); flex-wrap: wrap;">
                <div style="display: flex; align-items: flex-start; gap: 12px; flex: 1; min-width: 280px;">
                    <span style="font-size: 1.5rem; line-height: 1;">⚠️</span>
                    <div>
                        <strong style="color: #92400e; font-size: 1rem; display: block; margin-bottom: 4px;">Actualización Obligatoria de Contraseña Requerida</strong>
                        <p style="color: #b45309; font-size: 0.88rem; margin: 0; line-height: 1.5;">
                            Su contraseña de acceso institucional expira el día <strong>{{ session('password_warning_date') }}</strong> (en {{ session('password_warning_days') }} días). Para evitar la suspensión de su sesión, le sugerimos renovarla de forma segura y sin fricciones a través de su correo institucional.
                        </p>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <form action="{{ route('password.request-renewal') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn-primary-caj" style="padding: 10px 18px; font-size: 0.85rem; background-color: #d97706; width: auto; font-weight: 700; border-radius: 6px; cursor: pointer;">
                            Renovar contraseña ahora ✉️
                        </button>
                    </form>
                </div>
            </div>
            @endif

            @if (session('success'))
                <x-alert type="success" title="Éxito">
                    {{ session('success') }}
                </x-alert>
            @endif

            @if (session('error'))
                <x-alert type="error" title="Error">
                    {{ session('error') }}
                </x-alert>
            @endif

            @yield('content')
        </section>
    </main>

    <footer class="footer-credits-caj" style="margin-top: auto; background-color: #ffffff; border-top: 1px solid rgba(226, 232, 240, 0.8); padding: 15px 30px; text-align: center;">
        <p style="margin: 0; color: #64748b; font-size: 0.85rem;">© 2026 Corporación de Asistencia Judicial de la Región del Biobío. Todos los derechos reservados.</p>
    </footer>

     @stack('scripts')
    
    <!-- Sistema de Control Keep-Alive, Alertas y Heartbeat Multitestaña -->
    <script>
        (function() {

            function minutesToMs(m) {
                return m*60*1000;
            }
         
            // Lectura de SESSION_LIFETIME dinámico de Laravel
            const SESSION_LIFETIME = minutesToMs(parseInt(@json(config('session.lifetime', 30)), 10));

            // Parámetros de Configuración del Sistema Proporcionales
            const PING_INTERVAL = minutesToMs(10);
            const HEARTBEAT_ENDPOINT = '{{ route("session.keep-alive") }}';
            const CSRF_TOKEN = '{{ csrf_token() }}';


            // Claves de sincronización compartidas en LocalStorage
            const KEY_LAST_INTERACTION = 'caj_session_last_interaction';
            const KEY_LAST_PING = 'caj_session_last_ping';

            // Umbrales de cálculo de ventanas de Alerta en base a la última petición Keep-Alive
            const WARNING_THRESHOLD = SESSION_LIFETIME - minutesToMs(5)
            const EXPIRED_THRESHOLD = SESSION_LIFETIME+ 1000; 
            localStorage.setItem(KEY_LAST_PING, Date.now().toString());
            registerInteraction({ type: 'load' });
            // Log de depuración inicial estilizado
            console.log(
                '%c[Session Monitor] Inicializado %c\nLifetime: %s min (%s ms)\nCheck interval: %s ms\nActive User Window: %s ms\nWarning threshold: %s ms\nPing interval: %s ms',
                'background-color: #0F69C4; color: #ffffff; padding: 4px 8px; border-radius: 4px; font-weight: bold;',
                'color: inherit;',
                SESSION_LIFETIME, WARNING_THRESHOLD, PING_INTERVAL, EXPIRED_THRESHOLD
            );

            // Inyectar dinámicamente los elementos de ventana modal con estilos alineados a la intranet
            function ensureModalElements() {
                if (!document.getElementById('caj-session-warning-modal')) {
                    const warningHtml = `
                        <div id="caj-session-warning-modal" style="display:none; position: fixed; inset: 0; background-color: rgba(13, 27, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 99999; padding: 20px;">
                            <div style="background-color: #ffffff; border-radius: 12px; border: 1px solid #cbd5e1; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); padding: 30px; text-align: left;">
                                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                                    <span style="font-size: 2rem;">⚠️</span>
                                    <strong style="color: #9f1239; font-size: 1.25rem; font-weight: 700;">¿Sigue ahí? Su sesión va a expirar</strong>
                                </div>
                                <p style="color: #475569; font-size: 0.92rem; line-height: 1.6; margin: 0 0 25px 0;">
                                    Por motivos de seguridad y de acuerdo a las políticas de la intranet, su sesión de acceso caducará pronto debido a inactividad detectada. ¿Desea mantener su sesión activa?
                                </p>
                                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                                    <button id="caj-session-logout-btn" type="button" class="btn-acc" style="padding: 10px 18px; font-size: 0.85rem; border-color: #cbd5e1; border-radius: 6px; cursor: pointer; background: #f8fafc; color: #475569;">
                                        Cerrar sesión ahora
                                    </button>
                                    <button id="caj-session-extend-btn" type="button" class="btn-primary-caj" style="padding: 10px 20px; font-size: 0.85rem; border-radius: 6px; cursor: pointer; height: auto;">
                                        Sí, seguir activo
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    document.body.insertAdjacentHTML('beforeend', warningHtml);

                    document.getElementById('caj-session-extend-btn').addEventListener('click', () => {
                        console.log('%c[Session Monitor] Click en "Sí, seguir activo". Forzando keep-alive síncrono...', 'color: #2b8a3e; font-weight: bold;');
                        forceKeepAlive();
                    });

                    document.getElementById('caj-session-logout-btn').addEventListener('click', () => {
                        console.log('%c[Session Monitor] Click en "Cerrar sesión". Redirigiendo...', 'color: #ef3340; font-weight: bold;');
                        const logoutForm = document.querySelector('form[action$="/logout"]');
                        if (logoutForm) {
                            logoutForm.submit();
                        } else {
                            window.location.href = '/';
                        }
                    });
                }

                if (!document.getElementById('caj-session-expired-modal')) {
                    const expiredHtml = `
                        <div id="caj-session-expired-modal" style="display:none; position: fixed; inset: 0; background-color: rgba(13, 27, 42, 0.7); backdrop-filter: blur(5px); align-items: center; justify-content: center; z-index: 99999; padding: 20px;">
                            <div style="background-color: #ffffff; border-radius: 12px; border: 1px solid #cbd5e1; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); padding: 35px; text-align: center;">
                                <div style="font-size: 3rem; margin-bottom: 15px;">🔒</div>
                                <strong style="color: #ef3340; font-size: 1.30rem; font-weight: 700; display: block; margin-bottom: 10px;">Su sesión ha caducado</strong>
                                <p style="color: #64748b; font-size: 0.9rem; line-height: 1.6; margin: 0 0 25px 0;">
                                    Su sesión de acceso a la Intranet CAJBIOBIO ha expirado debido a inactividad prolongada. Por favor, vuelva a ingresar sus credenciales para continuar.
                                </p>
                                <button id="caj-session-relogin-btn" type="button" class="btn-primary-caj" style="padding: 12px 30px; font-size: 0.9rem; border-radius: 6px; cursor: pointer; width: 100%;">
                                    Ir al Inicio de Sesión
                                </button>
                            </div>
                        </div>
                    `;
                    document.body.insertAdjacentHTML('beforeend', expiredHtml);

                    document.getElementById('caj-session-relogin-btn').addEventListener('click', () => {
                        window.location.reload();
                    });
                }
            }

            // Control de presentación visual de las ventanas modales
            function updateModalsState() {
                const now = Date.now();
                const lastPing = parseInt(localStorage.getItem(KEY_LAST_PING) || '0', 10);

                if (lastPing === 0) {
                    document.getElementById('caj-session-warning-modal').style.display = 'none';
                    document.getElementById('caj-session-expired-modal').style.display = 'none';
                    return;
                }

                const elapsed = now - lastPing;
                console.log({elapsed,WARNING_THRESHOLD, "esmayor?":(elapsed>=WARNING_THRESHOLD)});
                

                if (elapsed >= EXPIRED_THRESHOLD) {
                    console.warn(`%c[Session Monitor] Expirado. Transcurrido desde último ping: ${Math.round(elapsed / 1000)}s >= ${WARNING_THRESHOLD / 1000}s`, 'color: #ef3340; font-weight: bold;');
                    document.getElementById('caj-session-warning-modal').style.display = 'none';
                    document.getElementById('caj-session-expired-modal').style.display = 'flex';
                } else if (elapsed >= WARNING_THRESHOLD ) {
                    console.log({elapsed,WARNING_THRESHOLD, "esmayor?":(elapsed>=WARNING_THRESHOLD)});

                    const segundosRestantes = Math.max(0, Math.round(((SESSION_LIFETIME) - elapsed) / 1000));
                    console.info(`%c[Session Monitor] Alerta Activa. Expiración inminente en: ${segundosRestantes} segundos.`, 'color: #d97706; font-weight: bold;');
                    document.getElementById('caj-session-expired-modal').style.display = 'none';
                    document.getElementById('caj-session-warning-modal').style.display = 'flex';
                } else {
                    document.getElementById('caj-session-warning-modal').style.display = 'none';
                    document.getElementById('caj-session-expired-modal').style.display = 'none';
                }
            }

            // Forzar refresco inmediato (Keep-Alive explícito) ante click en "Sí, seguir activo"
            async function forceKeepAlive() {
                try {
                    const response = await fetch(HEARTBEAT_ENDPOINT, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ _token: CSRF_TOKEN })
                    });

                    if (response.ok) {
                        const now = Date.now();
                        localStorage.setItem(KEY_LAST_PING, now.toString());
                        localStorage.setItem(KEY_LAST_INTERACTION, now.toString());
                        console.log('%c[Session Monitor] Ping forzado exitoso. Sesión refrescada en backend.', 'color: #2b8a3e; font-weight: bold;');
                        updateModalsState();
                    } else {
                        window.location.reload();
                    }
                } catch (error) {
                    console.error('[Session Monitor] Error en ping forzado:', error);
                }
            }

            // Actualizar el timestamp local en localStorage
            function registerInteraction(e) {
                localStorage.setItem(KEY_LAST_INTERACTION, Date.now().toString());
                console.log(`%c[Session Monitor] Actividad de UI detectada (${e.type}). Timestamp de interacción actualizado.`, 'color: #64748b; font-size: 0.75rem;');
            }

            // Throttling para prevenir sobrecarga de escrituras en localStorage
            function throttle(func, delay) {
                let lastCall = 0;
                return function(...args) {
                    const now = Date.now();
                    if (now - lastCall >= delay) {
                        lastCall = now;
                        func.apply(this, args);
                    }
                };
            }

            // Escuchar eventos globales de interfaz de usuario de forma pasiva
            const interactionEvents = ['mousemove', 'keydown', 'scroll', 'mousedown', 'touchstart'];
            interactionEvents.forEach(eventName => {
                window.addEventListener(eventName, throttle(registerInteraction, 1000), { passive: true });
            });

            // Bucle Heartbeat principal
            async function evaluateHeartbeat() {
                const now = Date.now();
                const lastInteraction = parseInt(localStorage.getItem(KEY_LAST_INTERACTION) || '0', 10);
                const lastPing = parseInt(localStorage.getItem(KEY_LAST_PING) || '0', 10);

                const elapsedInteraction = now - lastInteraction;
                const elapsedPing = now - lastPing;

                console.log(
                    `%c[Session Monitor] Tick de evaluación. Inactividad de UI: ${Math.round(elapsedInteraction / 1000)}s | Desde último ping: ${Math.round(elapsedPing / 1000)}s`,
                    'color: #0F69C4; font-size: 0.75rem; font-family: monospace;'
                );

                console.log({elapsedInteraction,PING_INTERVAL});
                

                // 1. Validar si el usuario registra actividad dentro de la ventana de actividad configurada
                if (elapsedInteraction >= PING_INTERVAL) {
                    console.warn(`%c[Session Monitor] Bypasseando ping: Usuario inactivo por más de ${Math.round(elapsedInteraction / 1000)}s (Límite: ${20000 / 1000}s)`, 'color: #64748b;');
                    updateModalsState();
                    return; 
                }

                // 2. Prevenir pings redundantes de múltiples pestañas abiertas.
                if (elapsedPing < PING_INTERVAL) {
                    console.log(`%c[Session Monitor] Bypasseando ping: Refresco reciente detectado en esta u otra pestaña hace ${Math.round(elapsedPing / 1000)}s`, 'color: #64748b;');
                    updateModalsState();
                    return;
                }

                // 3. Ejecutar el Ping síncrono al backend de Laravel
                try {
                    console.log('%c[Session Monitor] Iniciando petición keep-alive al servidor...', 'color: #0F69C4; font-weight: bold;');
                    const response = await fetch(HEARTBEAT_ENDPOINT, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ _token: CSRF_TOKEN })
                    });

                    if (response.ok) {
                        localStorage.setItem(KEY_LAST_PING, Date.now().toString());
                        console.log('%c[Session Monitor] Petición keep-alive exitosa. Sesión de base de datos refrescada.', 'color: #2b8a3e; font-weight: bold;');
                    }
                } catch (error) {
                    console.error('[Session Monitor] Error al comunicar con el servidor:', error);
                }

                updateModalsState();
            }

            // Registrar e inicializar timestamps síncronos sobre esta pestaña en carga inicial
            

            // Inicializar estructuras y bucles
            document.addEventListener('DOMContentLoaded', () => {
                ensureModalElements();
                updateModalsState();
            });

            // Programar el chequeo continuo
            setTimeout(() => {
                evaluateHeartbeat();
                setInterval(evaluateHeartbeat, PING_INTERVAL); 
            }, 3000);
        })();
    </script>
</body>

</html>