@extends('layouts.app')

@section('title', 'Dashboard Regional - Sistema de Gestion Verificador')

@section('content')
<div x-data="{ showModal: false, activeUnit: null }">
<div class="dashboard-page-header">
    <h2>Dashboard de Dirección Regional</h2>
    <p style="margin: 5px 0 0; color: #64748b; font-size: 0.95rem;">
        Consola de supervisión territorial para la región de <strong>{{ $region->region_nombre ?? 'Jurisdicción Asignada' }}</strong>.
        Vista del periodo estadístico: <strong>
            @if($view === 'mes')
                {{ $selectedMonth }}/{{ $selectedYear }} (Mes Seleccionado)
            @elseif($view === 'ano')
                Año {{ $selectedYear }}
            @else
                Histórico Global
            @endif
        </strong>.
    </p>
</div>

<!-- Control de Vistas Periodificadas (Pestañas/Tabs) -->
<div style="display: flex; gap: 12px; margin-bottom: 25px; border-bottom: 1px solid #cbd5e1; padding-bottom: 15px; flex-wrap: wrap;">
    <a href="{{ route('director.dashboard', ['view' => 'mes', 'mes' => $selectedMonth, 'ano' => $selectedYear]) }}" 
       style="padding: 10px 20px; font-size: 0.85rem; font-weight: 700; border-radius: 6px; text-decoration: none; transition: all 0.2s ease;
              @if($view === 'mes') background-color: #0F69C4; color: #ffffff !important; @else background-color: #ffffff; color: #475569 !important; border: 1px solid #cbd5e1; @endif">
        📅 Visualizar Este Mes
    </a>
    <a href="{{ route('director.dashboard', ['view' => 'ano', 'ano' => $selectedYear]) }}" 
       style="padding: 10px 20px; font-size: 0.85rem; font-weight: 700; border-radius: 6px; text-decoration: none; transition: all 0.2s ease;
              @if($view === 'ano') background-color: #0F69C4; color: #ffffff !important; @else background-color: #ffffff; color: #475569 !important; border: 1px solid #cbd5e1; @endif">
        📆 Visualizar Todo el Año
    </a>
    <a href="{{ route('director.dashboard', ['view' => 'global']) }}" 
       style="padding: 10px 20px; font-size: 0.85rem; font-weight: 700; border-radius: 6px; text-decoration: none; transition: all 0.2s ease;
              @if($view === 'global') background-color: #0F69C4; color: #ffffff !important; @else background-color: #ffffff; color: #475569 !important; border: 1px solid #cbd5e1; @endif">
        📊 Estadísticas Globales
    </a>
</div>

<!-- Filtros de Selección de Periodo (Mes/Año) - Ocultos en Vista Global -->
@if($view !== 'global')
    @php
        $maxMonth = ($selectedYear == $currentYear) ? $currentMonth : 12;
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
    @endphp
    <div class="dashboard-filter-stat-card">
        <div class="dashboard-filter-stat-header">
            <div>
                <h3>Filtros Estadísticos</h3>
                <p>Selecciona el período que deseas visualizar.</p>
            </div>
        </div>
        <form action="{{ route('director.dashboard') }}" method="GET" class="estadistica-filtros">
            <input type="hidden" name="view" value="{{ $view }}">
            
            @if($view === 'mes')
            <div class="estadistica-form-group">
                <label for="mes">Seleccionar Mes Estadístico</label>
                <select name="mes" id="mes" class="estadistica-select">
                    @for($m = $maxMonth; $m >= 1; $m--)
                        <option value="{{ $m }}" @if($m === $selectedMonth) selected @endif>{{ $meses[$m] }}</option>
                    @endfor
                </select>
            </div>
            @endif

            <div class="estadistica-form-group">
                <label for="ano">Seleccionar Año Estadístico</label>
                <select name="ano" id="ano" class="estadistica-select">
                    @for($y = $currentYear; $y >= 2020; $y--)
                        <option value="{{ $y }}" @if($y === $selectedYear) selected @endif>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="estadistica-actions">
                <button type="submit" class="btn-primary-caj">
                    Filtrar 🔍
                </button>
                @if($selectedMonth !== $currentMonth || $selectedYear !== $currentYear)
                <a href="{{ route('director.dashboard', ['view' => $view, 'mes' => $currentMonth, 'ano' => $currentYear]) }}" class="btn-acc estadistica-btn-reset">
                    Volver al Periodo Actual 🔄
                </a>
                @endif
            </div>
        </form>
    </div>
@endif

<!-- Tarjeta Informativa de Rol -->
<div class="dashboard-alert-card">
    <span>🗺️</span>
    <div>
        <strong>Control de Operación Regional</strong>
        <p>
            Supervise el avance en la subida de verificadores de las unidades operativas a su cargo. Utilice los filtros superiores para segmentar estadísticas de forma mensual, anual o histórica.
        </p>
    </div>
</div>

<!-- Tarjetas de Indicadores Consolidados -->
<div class="dashboard-kpi-grid">
    <!-- Total Actividades -->
    <div class="dashboard-kpi-card">
        <span class="dashboard-kpi-label">Actividades Totales</span>
        <div class="dashboard-kpi-value">{{ $totalActividades }}</div>
        <div class="dashboard-kpi-text">
            @if($view === 'mes') Periodo de carga del mes @elseif($view === 'ano') Acumulado año {{ $selectedYear }} @else Total acumulado histórico @endif
        </div>
    </div>

    <!-- Pendientes -->
    <div class="dashboard-kpi-card kpi-red">
        <span class="dashboard-kpi-label">Pendientes de Firma</span>
        <div class="dashboard-kpi-value">{{ $totalCargadas }}</div>
        <div class="dashboard-kpi-text">Faltan respaldos</div>
    </div>

    <!-- Verificadas -->
    <div class="dashboard-kpi-card kpi-green">
        <span class="dashboard-kpi-label">Verificadas con Éxito</span>
        <div class="dashboard-kpi-value">{{ $totalVerificadas }}</div>
        <div class="dashboard-kpi-text">Verificadores archivados</div>
    </div>

    <!-- Tasa de Avance -->
    <div class="dashboard-kpi-card kpi-blue">
        <span class="dashboard-kpi-label">Porcentaje de Avance</span>
        <div class="dashboard-kpi-value">{{ $porcentajeVerificacion }}%</div>
        <div class="dashboard-progress-bar">
            <div class="dashboard-progress-fill" style="width: {{ $porcentajeVerificacion }}%;"></div>
        </div>
    </div>
</div>

<!-- 🗺️ Avance Individual de Unidades Operativas (Exclusivo de su Región) -->
<div class="dashboard-card">
    <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 1.15rem; color: #0d1b2a; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <span>🗺️ Avance Individual de Unidades Asignadas</span>
        <span style="font-size: 0.8rem; color: #64748b; font-weight: 500;">Ordenado por menor porcentaje de avance</span>
    </h3>
    
    <div style="overflow-x: auto;">
        <table class="table-custom-data" style="width: 100%; border-collapse: collapse; min-width: 700px;">
            <thead>
                <tr>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: left; font-size: 0.8rem; font-weight: 700; color: #475569;">Unidad</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: center; font-size: 0.8rem; font-weight: 700; color: #475569; width: 120px;">Pendientes</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: center; font-size: 0.8rem; font-weight: 700; color: #475569; width: 120px;">Verificadas</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: right; font-size: 0.8rem; font-weight: 700; color: #475569; width: 180px;">Progreso</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: right; font-size: 0.8rem; font-weight: 700; color: #475569; width: 160px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
               @foreach($unidadesEstadisticas as $stat)
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 14px 16px; font-weight: 700; color: #0F69C4; font-size: 0.9rem;">
                            {{ $stat['nombre'] }}
                            <span style="display: block; font-size: 0.75rem; color: #64748b; font-weight: normal; margin-top: 2px;">{{ $stat['email'] }}</span>
                        </td>
                        <td style="padding: 14px 16px; font-size: 0.85rem; color: #ef3340; text-align: center; font-weight: 600;">{{ $stat['cargadas'] }}</td>
                        <td style="padding: 14px 16px; font-size: 0.85rem; color: #2b8a3e; text-align: center; font-weight: 600;">{{ $stat['verificadas'] }}</td>
                        <td style="padding: 14px 16px; text-align: right;">
                            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
                                <span style="font-size: 0.8rem; font-weight: 700; color: #0d1b2a;">{{ $stat['avance'] }}%</span>
                                <div style="width: 80px; height: 8px; background-color: #e2e8f0; border-radius: 4px; overflow: hidden; display: inline-block;">
                                    <div style="width: {{ $stat['avance'] }}%; height: 100%; background-color: #2b8a3e;"></div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 14px 16px; text-align: right;">
                            <div style="display: flex; gap: 8px; justify-content: flex-end; align-items: center;">
                                @if($stat['avance'] < 100.0)
                                    <form action="{{ route('director.unidades.renotificar', $stat['id']) }}" method="POST" onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').innerHTML = 'Enviando... ⏳';">
                                        @csrf
                                        <button type="submit" class="btn-acc" style="padding: 6px 12px; font-size: 0.8rem; font-weight: 700; border-color: #0F69C4; color: #0F69C4 !important; background-color: rgba(15, 105, 196, 0.02); border-radius: 4px; cursor: pointer; height: auto;">
                                            Renotificar ✉️
                                        </button>
                                    </form>
                                
                                @else
                                    <p>al dia✅</p>

                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection