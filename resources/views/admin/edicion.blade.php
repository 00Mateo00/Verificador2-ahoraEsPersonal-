@extends('layouts.app')

@section('title', 'Modo Edición Crítica - Intranet CAJBIOBIO')

@section('breadcrumbs')
<a href="{{ route('admin.dashboard') }}">Administrador</a>
<span class="separator">‣</span>
<span>Edición Crítica</span>
@endsection

@section('content')
<div class="panel-header-section" style="margin-bottom: 30px;">
    <h2>Edición de Configuración del Sistema</h2>
    <p style="margin: 5px 0 0; color: #64748b; font-size: 0.95rem;">
        Habilite o deshabilite accesos de usuarios y modifique parámetros operacionales de seguridad.
    </p>
</div>

<!-- Alerta de Advertencia de Zona Crítica -->
<div style="background-color: #fff1f2; border: 1px solid #fecdd3; border-radius: 8px; padding: 20px; margin-bottom: 30px; display: flex; align-items: flex-start; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.01);">
    <span style="font-size: 1.5rem; line-height: 1;">🔒</span>
    <div>
        <strong style="color: #9f1239; font-size: 1rem; display: block; margin-bottom: 4px;">Zona de Seguridad Crítica Protegida</strong>
        <p style="color: #be123c; font-size: 0.85rem; margin: 0; line-height: 1.5;">
            Cualquier modificación o alternancia de estados de cuentas de usuario en esta sección impactará de forma inmediata en las sesiones de los operadores del sistema. Estas operaciones de administración requieren la reconfirmación de su contraseña por motivos de auditoría y defensa.
        </p>
    </div>
</div>

<!-- Tabla de Usuarios y Control de Acceso -->
<div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
    <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 1.15rem; color: #0d1b2a; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px;">
        👤 Catálogo General de Usuarios y Control de Estado
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
                <tr style="border-bottom: 1px solid #e2e8f0; @if(!$usr->estado) background-color: #f8fafc; opacity: 0.8; @endif">
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
                            $colors = $roleColors[$usr->rol] ?? ['bg' => '#f1f5f9', 'text' => '#475569'];
                        @endphp
                        <span style="background-color: {{ $colors['bg'] }}; color: {{ $colors['text'] }}; padding: 3px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase;">
                            {{ $usr->rol }}
                        </span>
                    </td>
                    <td style="padding: 14px 16px; text-align: center;">
                        @if($usr->estado)
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
                        <form action="{{ route('admin.usuarios.toggle', $usr->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="btn-acc" 
                                    style="padding: 6px 12px; font-size: 0.8rem; font-weight: 700; border-radius: 4px; cursor: pointer; transition: all 0.15s ease;
                                           @if($usr->estado) border-color: #ef3340; color: #ef3340 !important; background-color: rgba(239, 51, 64, 0.02); @else border-color: #2b8a3e; color: #2b8a3e !important; background-color: rgba(43, 138, 62, 0.02); @endif">
                                {{ $usr->estado ? 'Deshabilitar' : 'Habilitar' }}
                            </button>
                        </form>
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