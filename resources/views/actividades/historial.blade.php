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

<!-- Alertas Dinámicas del Modo Edición basadas en capacidades de mutación -->
@can('actividades.adjuntar-administrativo')
    @if(session('modo_edicion'))
        <x-alert type="danger" title="Cuidado: Modo Edición Activado">
            Se encuentra en el modo interactivo de administración. Ahora puede eliminar o adjuntar nuevos archivos verificadores directamente expandiendo las tarjetas de actividad abajo. Por seguridad, este modo edición expirará automáticamente tras 10 minutos de inactividad.
        </x-alert>
    @else
        <x-alert type="info" title="Modo Solo Lectura">
            Se encuentra visualizando el historial de actividades en modo de lectura segura. No se permiten realizar eliminaciones o cargas de nuevos respaldos. Para habilitar las acciones de edición sobre los verificadores, active el "Modo Edición Crítica" desde el Dashboard Principal.
        </x-alert>
    @endif
@endcan

<livewire:actividades.consulta-list />
@endsection