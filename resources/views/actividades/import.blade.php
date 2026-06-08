@extends('layouts.app')

@section('title', 'Importar Actividades - Intranet CAJBIOBIO')

@section('breadcrumbs')
<a href="#">Actividades</a>
<span class="separator">‣</span>
<span>Importar Planilla</span>
@endsection

@section('sidebar_menu')
@if(Auth::user()->rol === 'cargador' || Auth::user()->rol === 'admin')
<li>
    <a href="{{ route('actividades.importar') }}" class="active">Importar Planilla</a>
</li>
@endif
<li>
    <a href="{{ route('actividades.index') }}">Consultar Actividades</a>
</li>
@endsection

@section('content')
<div class="panel-header-section">
    <h2>Módulo de Carga Masiva (Excel)</h2>
    <p style="margin: 5px 0 0; color: var(--color-text-light); font-size: 0.95rem;">
        Cargue y distribuya de manera centralizada las actividades programadas para las diferentes unidades regionales de la corporación.
    </p>
</div>

<livewire:actividades.import-actividades-form />
@endsection