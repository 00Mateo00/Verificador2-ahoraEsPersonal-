@extends('layouts.app')

@section('title', (Auth::user()->rol === \App\Enums\UserRole::Admin ? 'Historial de Correos' : 'Correos Fallidos') . ' - Intranet CAJBIOBIO')

@section('breadcrumbs')
<a href="#">Administración</a>
<span class="separator">‣</span>
<span>{{ Auth::user()->rol === \App\Enums\UserRole::Admin ? 'Historial de Correos' : 'Correos Fallidos' }}</span>
@endsection

@section('content')
<div class="panel-header-section" style="margin-bottom: 25px;">
    <h2>{{ Auth::user()->rol === \App\Enums\UserRole::Admin ? 'Consola de Historial de Correos' : 'Módulo de Monitoreo de Correos Fallidos' }}</h2>
    <p style="margin: 5px 0 0; color: #64748b; font-size: 0.95rem;">
        @if(Auth::user()->rol === \App\Enums\UserRole::Admin)
            Consulte y gestione el historial de envíos exitosos y resuelva de forma manual o masiva los correos que quedaron pendientes o fallaron.
        @else
            Consulte, diagnostique y gestione de forma manual o masiva el reenvío de los correos automáticos que fallaron de forma síncrona en el sistema.
        @endif
    </p>
</div>

<!-- Advertencia Informativa de Seguridad -->
@if(Auth::user()->rol === \App\Enums\UserRole::Admin)
    @if(session('modo_edicion'))
        <x-alert type="danger" title="Modo Edición Activado (Administrador)">
            Se encuentra en modo interactivo de administración crítica. Puede eliminar registros de correos de forma permanente de la base de datos si así lo requiere.
        </x-alert>
    @else
        <x-alert type="info" title="Modo Solo Lectura (Administrador)">
            Está visualizando el listado en modo seguro. Los controles de eliminación de registros están bloqueados. Habilite el "Modo Edición Crítica" desde el Dashboard Principal si requiere realizar limpiezas permanentes.
        </x-alert>
    @endif
@endif

<livewire:auditor.failed-mails-list />
@endsection