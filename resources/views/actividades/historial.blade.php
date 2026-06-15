@extends('layouts.app')

@section('title', 'Historial de Actividades - Intranet CAJBIOBIO')

@section('breadcrumbs')
<a href="#">Actividades</a>
<span class="separator">‣</span>
<span>Historial</span>
@endsection


@section('content')
<div class="panel-header-section" style="margin-bottom: 25px;">
    <h2>Historial General de Actividades</h2>
    <p style="margin: 5px 0 0; color: #64748b; font-size: 0.95rem;">
        Consulte, filtre e investigue los reportes y actividades registradas en el sistema de forma centralizada.
    </p>
</div>

<!-- Alertas Dinámicas del Modo Edición para el Administrador -->
@if(Auth::user()->rol === 'admin')
    @if(session('modo_edicion'))
    <div style="background-color: #fff1f2; border: 1px solid #fecdd3; border-radius: 8px; padding: 20px; margin-bottom: 25px; display: flex; align-items: flex-start; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.01);">
        <span style="font-size: 1.5rem; line-height: 1;">⚠️</span>
        <div>
            <strong style="color: #9f1239; font-size: 1rem; display: block; margin-bottom: 4px;">Cuidado: Modo Edición Activado</strong>
            <p style="color: #be123c; font-size: 0.85rem; margin: 0; line-height: 1.5;">
                Se encuentra en el modo interactivo de administración. Ahora puede eliminar o adjuntar nuevos archivos verificadores directamente expandiendo las tarjetas de actividad abajo. Por seguridad, este modo edición expirará automáticamente tras 10 minutos de inactividad.
            </p>
        </div>
    </div>
    @else
    <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 20px; margin-bottom: 25px; display: flex; align-items: flex-start; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.01);">
        <span style="font-size: 1.5rem; line-height: 1;">🔒</span>
        <div>
            <strong style="color: #1e40af; font-size: 1rem; display: block; margin-bottom: 4px;">Modo Solo Lectura</strong>
            <p style="color: #1e3a8a; font-size: 0.85rem; margin: 0; line-height: 1.5;">
                Se encuentra visualizando el historial de actividades en modo de lectura segura. No se permiten realizar eliminaciones o cargas de nuevos respaldos. Para habilitar las acciones de edición sobre los verificadores, active el "Modo Edición Crítica" desde el Dashboard Principal.
            </p>
        </div>
    </div>
    @endif
@endif

<livewire:actividades.consulta-list />
@endsection