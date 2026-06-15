@extends('layouts.app')

@section('title', 'Historial de Actividades - Intranet CAJBIOBIO')

@section('breadcrumbs')
<a href="#">Actividades</a>
<span class="separator">‣</span>
<span>Historial</span>
@endsection


@section('content')
<div class="panel-header-section">
    <h2>Historial General de Actividades</h2>
    <p style="margin: 5px 0 0; color: #64748b; font-size: 0.95rem;">
        Consulte, filtre e investigue los reportes y actividades registradas en el sistema de forma centralizada.
    </p>
</div>

<livewire:actividades.consulta-list />
@endsection