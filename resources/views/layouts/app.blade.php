<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Intranet CAJBIOBIO')</title>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    @stack('styles')
</head>

<body class="dashboard-layout-body">

    <!-- Navegación Principal CAJBIOBIO (Sumamente limpia, alineada a dashboard.png) -->
    <header class="header-nav-caj">
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
    </header>

    <!-- Layout Principal de Dos Columnas -->
    <main class="layout-dashboard-main">
        <aside>
            <div class="menu-sidebar-left">
                <div style="padding: 10px 24px; font-size: 0.8rem; text-transform: uppercase; font-weight: 700; color: #0d1b2a; opacity: 0.7; letter-spacing: 0.5px;">
                    @if(Auth::user()->rol === 'admin')
                    Panel Central
                    @elseif(Auth::user()->rol === 'cargador')
                    Módulo Importación
                    @elseif(Auth::user()->rol === 'unidad')
                    Menú Unidad
                    @else
                    Menú Consultas
                    @endif
                </div>
                <ul>
                    <!-- Enlaces dinámicos centralizados por Rol -->
                    @if(Auth::user()->rol === 'admin' || Auth::user()->rol === 'cargador')
                    <li>
                        <a href="{{ route('actividades.importar') }}" class="{{ request()->routeIs('actividades.importar') ? 'active' : '' }}">
                            Importar Planilla
                        </a>
                    </li>
                    @endif

                    <li>
                        <a href="{{ route('actividades.index') }}" class="{{ request()->routeIs('actividades.index') ? 'active' : '' }}">
                            Consultar Actividades
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <section class="panel-dashboard-content">
            @if (session('success'))
            <div class="form-group-item" style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb; font-size: 0.9rem;">
                <strong>Éxito:</strong> {{ session('success') }}
            </div>
            @endif

            @if (session('error'))
            <div class="form-group-item" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb; font-size: 0.9rem;">
                <strong>Error:</strong> {{ session('error') }}
            </div>
            @endif

            @yield('content')
        </section>
    </main>

    <footer class="footer-credits-caj" style="margin-top: auto; background-color: #ffffff; border-top: 1px solid rgba(226, 232, 240, 0.8); padding: 15px 30px; text-align: center;">
        <p style="margin: 0; color: #64748b; font-size: 0.85rem;">© 2026 Corporación de Asistencia Judicial de la Región del Biobío. Todos los derechos reservados.</p>
    </footer>

    @stack('scripts')
</body>

</html>