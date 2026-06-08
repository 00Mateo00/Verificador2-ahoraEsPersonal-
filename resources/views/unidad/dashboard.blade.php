@extends('layouts.app')

@section('title', 'Dashboard Unidad - Intranet CAJBIOBIO')

@section('content')
<div class="panel-header-section">
    <h2>Dashboard de Unidad Operativa</h2>
    <p style="margin: 5px 0 0; color: #64748b; font-size: 0.95rem;">
        Resumen operativo de actividades pendientes y verificadas de la unidad.
    </p>
</div>

<div class="panel-dashboard-content" style="margin-top: 20px;">
    <h3 style="margin-top: 0; color: #0d1b2a; font-size: 1.2rem; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; margin-bottom: 20px;">
        📥 Actividades Pendientes de Respaldo
    </h3>
    
    <!-- Componente Operativo de Verificación para la Unidad -->
    <livewire:actividades.verificar-pendientes />
</div>
@endsection