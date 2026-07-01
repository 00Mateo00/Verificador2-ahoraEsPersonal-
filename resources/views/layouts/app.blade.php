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
            <div class="header-brand-sgv">
                <div class="header-brand-icon">CAJ</div>
                <div class="header-brand-text">
                    <h1>Intranet<h1>
                    <span>Corporación de Asistencia Judicial - Región del Biobió</span>
                </div>
            </div>
            <div class="header-actions-caj">
                <div class="header-user-badge">
                    <span class="header-user-dot"></span>
                    @if(Auth::user()->rol === \App\Enums\UserRole::Admin)
                    <span class="header-role-badge">ADMIN</span>
                    @endif
                    <span>{{ Auth::user()->name }}</span>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="header-logout-btn">
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
                    Menú Intranet
                </div>
                <ul>
                    <!-- Enlace Único Dinámico al Dashboard Adaptable -->
                    <li>
                        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            @can('usuarios.crear')
                                Dashboard Principal
                            @else
                                @can('historial.ver-global')
                                    Dashboard Auditoría
                                @else
                                    @can('historial.ver-regional')
                                        Dashboard Regional
                                    @else
                                        Dashboard Unidad
                                    @endcan
                                @endcan
                            @endcan
                        </a>
                    </li>

                    @can('usuarios.ver-catalogo')
                    <li>
                        <a href="{{ route('admin.usuarios') }}" class="{{ request()->routeIs('admin.usuarios') ? 'active' : '' }}">
                            Usuarios
                        </a>
                    </li>
                    @endcan

                    <!-- Historial y logs de correspondencia -->
                    @can('correos.ver-historial')
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
                                    Historial de Correos
                                </span>
                                <span style="background-color: {{ $hasPendingMails ? '#ef3340' : '#cbd5e1' }}; color: #ffffff; padding: 2px 7px; border-radius: 10px; font-size: 0.75rem; font-weight: 700; margin-left: auto; transition: all 0.2s ease;">
                                    {{ $pendingMailsCount }}
                                </span>
                            </a>
                        </li>
                    @endcan

                    @can('actividades.importar')
                    <li>
                        <a href="{{ route('actividades.importar') }}" class="{{ request()->routeIs('actividades.importar') ? 'active' : '' }}">
                            Importar Planilla
                        </a>
                    </li>
                    @endcan

                    @if(Gate::allows('historial.ver-global') || Gate::allows('historial.ver-regional') || Gate::allows('historial.ver-unidad'))
                    <li>
                        <a href="{{ route('actividades.historial') }}" class="{{ request()->routeIs('actividades.historial') ? 'active' : '' }}">
                            Historial de Actividades
                        </a>
                    </li>
                    @endif
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
                        <button type="submit" class="btn-dashboard-primary">
                            Renovar contraseña ahora 
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

    <!-- Caso 2: Modales estáticos integrados desde plantilla de Blade parcial -->
    @include('layouts.partials.session-modals')

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
            const KEY_LAST_INTERACTION = 'caj_verificador_last_interaction';
            const KEY_LAST_PING = 'caj_verificador_last_ping';

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

            // Asignar controladores a elementos estáticos de Blade
            function attachModalListeners() {
                const extendBtn = document.getElementById('caj-session-extend-btn');
                if (extendBtn) {
                    extendBtn.addEventListener('click', () => {
                        console.log('%c[Session Monitor] Click en "Sí, seguir activo". Forzando keep-alive síncrono...', 'color: #2b8a3e; font-weight: bold;');
                        forceKeepAlive();
                    });
                }

                const logoutBtn = document.getElementById('caj-session-logout-btn');
                if (logoutBtn) {
                    logoutBtn.addEventListener('click', () => {
                        console.log('%c[Session Monitor] Click en "Cerrar sesión". Redirigiendo...', 'color: #ef3340; font-weight: bold;');
                        const logoutForm = document.querySelector('form[action$="/logout"]');
                        if (logoutForm) {
                            logoutForm.submit();
                        } else {
                            window.location.href = '/';
                        }
                    });
                }

                const reloginBtn = document.getElementById('caj-session-relogin-btn');
                if (reloginBtn) {
                    reloginBtn.addEventListener('click', () => {
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
                attachModalListeners();
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