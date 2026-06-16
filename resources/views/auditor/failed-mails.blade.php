@extends('layouts.app')

@section('title', (Auth::user()->rol === 'admin' ? 'Historial de Correos' : 'Correos Fallidos') . ' - Intranet CAJBIOBIO')

@section('breadcrumbs')
<a href="#">Administración</a>
<span class="separator">‣</span>
<span>{{ Auth::user()->rol === 'admin' ? 'Historial de Correos' : 'Correos Fallidos' }}</span>
@endsection

@section('content')
<div class="panel-header-section" style="margin-bottom: 25px;">
    <h2>{{ Auth::user()->rol === 'admin' ? 'Consola de Historial de Correos' : 'Módulo de Monitoreo de Correos Fallidos' }}</h2>
    <p style="margin: 5px 0 0; color: #64748b; font-size: 0.95rem;">
        @if(Auth::user()->rol === 'admin')
            Consulte y gestione el historial de envíos exitosos y resuelva de forma manual o masiva los correos que quedaron pendientes o fallaron.
        @else
            Consulte, diagnostique y gestione de forma manual o masiva el reenvío de los correos automáticos que fallaron de forma síncrona en el sistema.
        @endif
    </p>
</div>

<!-- Advertencia Informativa de Seguridad -->
@if(Auth::user()->rol === 'admin')
    @if(session('modo_edicion'))
    <div style="background-color: #fff1f2; border: 1px solid #fecdd3; border-radius: 8px; padding: 20px; margin-bottom: 25px; display: flex; align-items: flex-start; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.01);">
        <span style="font-size: 1.5rem; line-height: 1;">⚠️</span>
        <div>
            <strong style="color: #9f1239; font-size: 1rem; display: block; margin-bottom: 4px;">Modo Edición Activado (Administrador)</strong>
            <p style="color: #be123c; font-size: 0.85rem; margin: 0; line-height: 1.5;">
                Se encuentra en modo interactivo de administración crítica. Puede eliminar registros de correos de forma permanente de la base de datos si así lo requiere.
            </p>
        </div>
    </div>
    @else
    <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 20px; margin-bottom: 25px; display: flex; align-items: flex-start; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.01);">
        <span style="font-size: 1.5rem; line-height: 1;">🔒</span>
        <div>
            <strong style="color: #1e40af; font-size: 1rem; display: block; margin-bottom: 4px;">Modo Solo Lectura (Administrador)</strong>
            <p style="color: #1e3a8a; font-size: 0.85rem; margin: 0; line-height: 1.5;">
                Está visualizando el listado en modo seguro. Los controles de eliminación de registros están bloqueados. Habilite el "Modo Edición Crítica" desde el Dashboard Principal si requiere realizar limpiezas permanentes.
            </p>
        </div>
    </div>
    @endif
@endif

<livewire:auditor.failed-mails-list />
@endsection