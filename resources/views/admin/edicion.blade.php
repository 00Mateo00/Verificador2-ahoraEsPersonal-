@extends('layouts.app')

@section('title', 'Usuarios - Intranet CAJBIOBIO')

@section('breadcrumbs')
<a href="{{ route('admin.dashboard') }}">Administrador</a>
<span class="separator">‣</span>
<span>Usuarios</span>
@endsection

@section('content')
<div class="panel-header-section" style="margin-bottom: 30px;">
    <h2>Gestión de usuarios</h2>
    <p style="margin: 5px 0 0; color: #64748b; font-size: 0.95rem;">
        Consulte, busque y gestione el estado de las usuarios y operadores del sistema de forma centralizada.
    </p>
</div>

<!-- Alerta de Advertencia Dinámica de Seguridad -->
@if(session('modo_edicion'))
    <x-alert type="danger" title="Cuidado: Modo Edición Activado">
        Se encuentra en el modo interactivo de administración. Cualquier habilitación o deshabilitación de cuentas de usuario impactará de forma inmediata en las sesiones de los operadores del sistema. Por seguridad, este modo edición expirará automáticamente tras 10 minutos de inactividad.
    </x-alert>
@else
    <x-alert type="info" title="Modo Solo Lectura">
        Se encuentra visualizando el catálogo en modo de lectura segura. No se permite realizar modificaciones o alteraciones de estados de cuenta en esta vista. Para habilitar las acciones de edición, active el "Modo Edición Crítica" desde el Dashboard Principal.
    </x-alert>
@endif

<!-- Buscador de Usuarios -->
<div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); padding: 20px; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
    <form action="{{ route('admin.usuarios') }}" method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 250px;">
            <label for="search" style="font-size: 0.85rem; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Filtrar Unidades / Usuarios</label>
            <input type="text" name="search" id="search" value="{{ $search }}" class="form-input-control-caj" placeholder="Ej: Los Ángeles, Concepción, auditor, etc." style="width: 100%;">
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn-primary-caj" style="padding: 12px 24px; font-size: 0.9rem;">
                🔍 Filtrar
            </button>
            @if($search)
            <a href="{{ route('admin.usuarios') }}" class="btn-acc" style="text-align: center; padding: 12px 20px; text-decoration: none; border-color: #cbd5e1; font-weight: 600; font-size: 0.9rem; border-radius: 6px; display: inline-block;">
                Limpiar Filtros
            </a>
            @endif
        </div>
    </form>
</div>

<!-- Formularios de Creación (Solo en Modo Edición) -->
@if(session('modo_edicion'))
<div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 25px; margin-bottom: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);" x-data="{ activeForm: 'unidad' }">
    <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 1.15rem; color: #0d1b2a; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
        <span>Registrar Nuevos Accesos e Infraestructura</span>
    </h3>

    <!-- Pestañas internas de selección -->
    <div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid #cbd5e1; padding-bottom: 10px;">
        <button type="button" @click="activeForm = 'unidad'" :style="activeForm === 'unidad' ? 'background-color: #0F69C4; color: #ffffff;' : 'background-color: #f1f5f9; color: #475569;'" style="border: none; padding: 8px 16px; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 0.85rem;">
            🏢 Crear Unidad Operativa
        </button>
        <button type="button" @click="activeForm = 'region'" :style="activeForm === 'region' ? 'background-color: #0F69C4; color: #ffffff;' : 'background-color: #f1f5f9; color: #475569;'" style="border: none; padding: 8px 16px; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 0.85rem;">
            🗺️ Crear Región y Director
        </button>
        <button type="button" @click="activeForm = 'usuario'" :style="activeForm === 'usuario' ? 'background-color: #0F69C4; color: #ffffff;' : 'background-color: #f1f5f9; color: #475569;'" style="border: none; padding: 8px 16px; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 0.85rem;">
            👤 Crear Usuario Sistema
        </button>
    </div>

    <!-- Formulario: Crear Unidad -->
    <div x-show="activeForm === 'unidad'" x-cloak>
        <form action="{{ route('admin.crear-unidad') }}" method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; align-items: flex-end;" onsubmit="if(document.getElementById('unidad_id').value === '') { return confirm('¿Está seguro de que desea poner una ID automática para esta Unidad?'); }">
            @csrf
            <div>
                <label for="unidad_id" style="font-size: 0.8rem; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">ID de la Unidad (Opcional)</label>
                <input type="number" name="unidad_id" id="unidad_id" class="form-input-control-caj" placeholder="Ej: 801 (Vacío = Auto)">
            </div>
            <div>
                <label for="unidad_nombre" style="font-size: 0.8rem; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Nombre de la Unidad</label>
                <input type="text" name="unidad_nombre" id="unidad_nombre" class="form-input-control-caj" required placeholder="Ej: PMA Concepción">
            </div>
            <div>
                <label for="unidad_email" style="font-size: 0.8rem; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Correo del Operador</label>
                <input type="email" name="unidad_email" id="unidad_email" class="form-input-control-caj" required placeholder="Ej: concepcion@cajbiobio.cl">
            </div>
            <div>
                <label for="region_id" style="font-size: 0.8rem; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Región de pertenencia</label>
                <select name="region_id" id="region_id" class="form-input-control-caj" style="padding: 10px 14px; background-color: #ffffff;" required>
                    <option value="">Seleccione una región...</option>
                    @foreach($regiones as $reg)
                        <option value="{{ $reg->id }}">{{ $reg->region_nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="btn-primary-caj" style="width: 100%; height: 48px; border-radius: 6px; font-size: 0.9rem;">
                    Crear Unidad
                </button>
            </div>
        </form>
    </div>

    <!-- Formulario: Crear Región -->
    <div x-show="activeForm === 'region'" x-cloak>
        <form action="{{ route('admin.crear-region') }}" method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; align-items: flex-end;" onsubmit="if(document.getElementById('region_id').value === '') { return confirm('¿Está seguro de que desea poner una ID automática para esta Región?'); }">
            @csrf
            <div>
                <label for="region_id" style="font-size: 0.8rem; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">ID de la Región</label>
                <input type="number" name="region_id" id="region_id" class="form-input-control-caj"required placeholder="Ej: 8">
            </div>
            <div>
                <label for="region_nombre" style="font-size: 0.8rem; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Nombre de la Región</label>
                <input type="text" name="region_nombre" id="region_nombre" class="form-input-control-caj" required placeholder="Ej: Biobío">
            </div>
            <div>
                <label for="director_nombre" style="font-size: 0.8rem; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Nombre del Director</label>
                <input type="text" name="director_nombre" id="director_nombre" class="form-input-control-caj" required placeholder="Ej: Juan Pérez">
            </div>
            <div>
                <label for="director_email" style="font-size: 0.8rem; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Correo del Director</label>
                <input type="email" name="director_email" id="director_email" class="form-input-control-caj" required placeholder="Ej: director.biobio@cajbiobio.cl">
            </div>
            <div>
                <button type="submit" class="btn-primary-caj" style="width: 100%; height: 48px; border-radius: 6px; font-size: 0.9rem;">
                    Crear Región
                </button>
            </div>
        </form>
    </div>

    <!-- Formulario: Crear Usuario -->
    <div x-show="activeForm === 'usuario'" x-cloak>
        <form action="{{ route('admin.crear-usuario') }}" method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; align-items: flex-end;">
            @csrf
            <div>
                <label for="name" style="font-size: 0.8rem; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Nombre Completo</label>
                <input type="text" name="name" id="name" class="form-input-control-caj" required placeholder="Ej: Ana María Silva">
            </div>
            <div>
                <label for="email" style="font-size: 0.8rem; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Correo Institucional</label>
                <input type="email" name="email" id="email" class="form-input-control-caj" required placeholder="Ej: anasilva@cajbiobio.cl">
            </div>
            <div>
                <label for="rol" style="font-size: 0.8rem; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Rol asignado</label>
                <select name="rol" id="rol" class="form-input-control-caj" style="padding: 10px 14px; background-color: #ffffff;" required>
                    <option value="">Seleccione un rol...</option>
                    <option value="admin">Administrador</option>
                    <option value="auditor">Auditor</option>
                    <option value="cargador">Cargador</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn-primary-caj" style="width: 100%; height: 48px; border-radius: 6px; font-size: 0.9rem;">
                    Crear Usuario
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Tabla de Usuarios y Control de Acceso -->
<div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
    <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 1.15rem; color: #0d1b2a; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <span>👤 Catálogo General de Unidades y Operadores</span>
        @if(session('modo_edicion'))
        <span style="background-color: rgba(239, 51, 64, 0.08); color: #ef3340; font-size: 0.75rem; font-weight: bold; padding: 4px 10px; border-radius: 20px; border: 1px solid rgba(239, 51, 64, 0.2);">
            Modo Edición Activo 🔓
        </span>
        @else
        <span style="background-color: rgba(59, 130, 246, 0.08); color: #3b82f6; font-size: 0.75rem; font-weight: bold; padding: 4px 10px; border-radius: 20px; border: 1px solid rgba(59, 130, 246, 0.2);">
            Modo Solo Lectura 🔒
        </span>
        @endif
    </h3>

    <div style="overflow-x: auto;">
        <table class="table-custom-data" style="width: 100%; border-collapse: collapse; min-width: 800px;">
            <thead>
                <tr>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: left; font-size: 0.8rem; font-weight: 700; color: #475569; width: 60px;">ID</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: left; font-size: 0.8rem; font-weight: 700; color: #475569;">Nombre Completo</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: left; font-size: 0.8rem; font-weight: 700; color: #475569;">Correo Institucional</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: center; font-size: 0.8rem; font-weight: 700; color: #475569; width: 140px;">Rol asignado</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: center; font-size: 0.8rem; font-weight: 700; color: #475569; width: 140px;">Estado Cuenta</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: right; font-size: 0.8rem; font-weight: 700; color: #475569; width: 180px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usuarios as $usr)
               <tr style="border-bottom: 1px solid #e2e8f0; @if(!$usr->activo) background-color: #f8fafc; opacity: 0.8; @endif">
                    <td style="padding: 14px 16px; font-size: 0.85rem; color: #64748b; font-family: monospace;">#{{ $usr->id }}</td>
                    <td style="padding: 14px 16px; font-size: 0.9rem; font-weight: 600; color: #0d1b2a;">{{ $usr->name }}</td>
                    <td style="padding: 14px 16px; font-size: 0.85rem; color: #475569;">{{ $usr->email }}</td>
                    <td style="padding: 14px 16px; text-align: center;">
                        @php
                            $roleColors = [
                                'admin' => ['bg' => 'rgba(239, 51, 64, 0.08)', 'text' => '#ef3340'],
                                'director' => ['bg' => 'rgba(15, 105, 196, 0.08)', 'text' => '#0F69C4'],
                                'auditor' => ['bg' => 'rgba(100, 116, 139, 0.08)', 'text' => '#64748b'],
                                'cargador' => ['bg' => 'rgba(245, 158, 11, 0.08)', 'text' => '#d97706'],
                                'unidad' => ['bg' => 'rgba(16, 185, 129, 0.08)', 'text' => '#059669']
                            ];
                            $colors = $roleColors[$usr->rol->value] ?? ['bg' => '#f1f5f9', 'text' => '#475569'];
                        @endphp
                        <span style="background-color: {{ $colors['bg'] }}; color: {{ $colors['text'] }}; padding: 3px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase;">
                            {{ $usr->rol->value }}
                        </span>
                    </td>
                    <td style="padding: 14px 16px; text-align: center;">
                        @if($usr->activo)
                        <span style="color: #2b8a3e; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                            <span style="width: 6px; height: 6px; background-color: #2b8a3e; border-radius: 50%;"></span> Activo
                        </span>
                        @else
                        <span style="color: #64748b; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                            <span style="width: 6px; height: 6px; background-color: #64748b; border-radius: 50%;"></span> Inactivo
                        </span>
                        @endif
                    </td>
                    <td style="padding: 14px 16px; text-align: right;">
                        @if($usr->id === auth()->id())
                        <span style="font-size: 0.8rem; color: #94a3b8; font-style: italic;">Su Cuenta</span>
                        @else
                            @if(session('modo_edicion'))
                            <form action="{{ route('admin.usuarios.toggle', $usr->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="btn-acc" 
                                        style="padding: 6px 12px; font-size: 0.8rem; font-weight: 700; border-radius: 4px; cursor: pointer; transition: all 0.15s ease;
                                            @if($usr->activo) border-color: #ef3340; color: #ef3340 !important; background-color: rgba(239, 51, 64, 0.02); @else border-color: #2b8a3e; color: #2b8a3e !important; background-color: rgba(43, 138, 62, 0.02); @endif">
                                    {{ $usr->activo ? 'Deshabilitar' : 'Habilitar' }}
                                </button>
                            </form>
                            @else
                            <button type="button" 
                                    class="btn-acc" 
                                    disabled
                                    title="Debe activar el Modo Edición en el Dashboard para realizar modificaciones"
                                    style="padding: 6px 12px; font-size: 0.8rem; font-weight: 600; border-radius: 4px; cursor: not-allowed; border-color: #cbd5e1; color: #94a3b8 !important; background-color: #f1f5f9;">
                                Bloqueado 🔒
                            </button>
                            @endif
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Paginación de Usuarios -->
    <div style="margin-top: 25px;">
        {{ $usuarios->links() }}
    </div>
</div>
@endsection