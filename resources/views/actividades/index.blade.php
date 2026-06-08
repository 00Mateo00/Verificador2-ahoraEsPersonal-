@extends('layouts.app')

@section('title', 'Mis Actividades - Intranet CAJBIOBIO')

@section('breadcrumbs')
<a href="#">Actividades</a>
<span class="separator">‣</span>
<span>Mis Consultas</span>
@endsection


@section('content')
<div class="panel-header-section">
    <h2>Mis Actividades Registradas</h2>
    <p style="margin: 5px 0 0; color: #64748b; font-size: 0.95rem;">
        Consulte e investigue los reportes que ha ingresado en el sistema de forma centralizada.
    </p>
</div>

<livewire:actividades.consulta-list />
@endsection