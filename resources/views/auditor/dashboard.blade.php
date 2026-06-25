@extends('layouts.app')

@section('title', 'Dashboard Auditoría - Intranet CAJBIOBIO')

@section('content')
<div class="panel-header-section" style="margin-bottom: 25px;">
    <h2>Dashboard de Auditoría</h2>
    <p style="margin: 5px 0 0; color: #64748b; font-size: 0.95rem;">
        Consola de monitoreo analítico de solo lectura (Vista actual: <strong>
            @if($view === 'mes')
                Este Mes ({{ $meses[$selectedMonth] ?? '' }} - {{ $selectedYear }})
            @elseif($view === 'ano')
                Todo el Año ({{ $selectedYear }})
            @else
                Estadísticas Globales de la Intranet
            @endif
        </strong>).
    </p>
</div>

<!-- Tarjeta Informativa de Solo Lectura -->
<div style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 20px; margin-bottom: 25px; display: flex; align-items: flex-start; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.01);">
    <span style="font-size: 1.5rem; line-height: 1;"></span>
    <div>
        <strong style="color: #1e40af; font-size: 1rem; display: block; margin-bottom: 4px;">Supervisión de Auditoría Global</strong>
        <p style="color: #1e3a8a; font-size: 0.85rem; margin: 0; line-height: 1.5;">
            Visualice los reportes a nivel nacional. Use las pestañas para alternar entre el mes, el año o las métricas históricas del sistema, y despache renotificaciones síncronas a las unidades rezagadas.
        </p>
    </div>
</div>

<!-- Control de Vistas Periodificadas (Pestañas/Tabs) -->
<div style="display: flex; gap: 12px; margin-bottom: 25px; border-bottom: 1px solid #cbd5e1; padding-bottom: 15px; flex-wrap: wrap;">
    <a href="{{ route('auditor.dashboard', ['view' => 'mes', 'mes' => $selectedMonth, 'ano' => $selectedYear]) }}" 
       style="padding: 10px 20px; font-size: 0.85rem; font-weight: 700; border-radius: 6px; text-decoration: none; transition: all 0.2s ease;
              @if($view === 'mes') background-color: #0F69C4; color: #ffffff !important; @else background-color: #ffffff; color: #475569 !important; border: 1px solid #cbd5e1; @endif">
         Visualizar Este Mes
    </a>
    <a href="{{ route('auditor.dashboard', ['view' => 'ano', 'ano' => $selectedYear]) }}" 
       style="padding: 10px 20px; font-size: 0.85rem; font-weight: 700; border-radius: 6px; text-decoration: none; transition: all 0.2s ease;
              @if($view === 'ano') background-color: #0F69C4; color: #ffffff !important; @else background-color: #ffffff; color: #475569 !important; border: 1px solid #cbd5e1; @endif">
         Visualizar Todo el Año
    </a>
    <a href="{{ route('auditor.dashboard', ['view' => 'global']) }}" 
       style="padding: 10px 20px; font-size: 0.85rem; font-weight: 700; border-radius: 6px; text-decoration: none; transition: all 0.2s ease;
              @if($view === 'global') background-color: #0F69C4; color: #ffffff !important; @else background-color: #ffffff; color: #475569 !important; border: 1px solid #cbd5e1; @endif">
         Estadísticas Globales
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
    <div style="background-color: #f1f5f9; border: 1px solid #cbd5e1; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
        <form action="{{ route('auditor.dashboard') }}" method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
            <input type="hidden" name="view" value="{{ $view }}">
            
            @if($view === 'mes')
            <div style="flex: 1; min-width: 150px;">
                <label for="mes" style="font-size: 0.85rem; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Seleccionar Mes Estadístico</label>
                <select name="mes" id="mes" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; background-color: #ffffff; font-size: 0.95rem;">
                    @for($m = $maxMonth; $m >= 1; $m--)
                        <option value="{{ $m }}" @if($m === $selectedMonth) selected @endif>{{ $meses[$m] }}</option>
                    @endfor
                </select>
            </div>
            @endif

            <div style="flex: 1; min-width: 150px;">
                <label for="ano" style="font-size: 0.85rem; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Seleccionar Año Estadístico</label>
                <select name="ano" id="ano" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; background-color: #ffffff; font-size: 0.95rem;">
                    @for($y = $currentYear; $y >= 2020; $y--)
                        <option value="{{ $y }}" @if($y === $selectedYear) selected @endif>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn-dashboard-primary">
                    Filtrar
                </button>
                @if($selectedMonth !== $currentMonth || $selectedYear !== $currentYear)
                <a href="{{ route('auditor.dashboard', ['view' => $view, 'mes' => $currentMonth, 'ano' => $currentYear]) }}" class="btn-acc" style="text-align: center; padding: 10px 15px; text-decoration: none; border-color: #cbd5e1; font-weight: 600; font-size: 0.9rem; border-radius: 6px; display: inline-flex; align-items: center;">
                    Volver al Periodo Actual 
                </a>
                @endif
            </div>
        </form>
    </div>
@endif

<!-- Tarjetas de Indicadores Consolidados -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 35px;">
    
    <!-- Total Actividades -->
    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <span style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 0.5px;">Actividades Totales</span>
        <h2 style="font-size: 2.2rem; color: #0d1b2a; margin: 10px 0 0; font-weight: 800;">{{ $totalActividades }}</h2>
        <p style="margin: 5px 0 0; font-size: 0.8rem; color: #94a3b8;">
            @if($view === 'mes') Periodo de carga del mes @elseif($view === 'ano') Acumulado año {{ $selectedYear }} @else Total acumulado histórico @endif
        </p>
    </div>

    <!-- Pendientes -->
    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <span style="font-size: 0.75rem; text-transform: uppercase; color: #ef3340; font-weight: 700; letter-spacing: 0.5px;">Pendientes de Firma</span>
        <h2 style="font-size: 2.2rem; color: #ef3340; margin: 10px 0 0; font-weight: 800;">{{ $totalCargadas }}</h2>
        <p style="margin: 5px 0 0; font-size: 0.8rem; color: #fca5a5;">
            @if($view === 'mes') Pendientes este mes @elseif($view === 'ano') Pendientes año {{ $selectedYear }} @else Pendientes globales @endif
        </p>
    </div>

    <!-- Verificadas -->
    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <span style="font-size: 0.75rem; text-transform: uppercase; color: #2b8a3e; font-weight: 700; letter-spacing: 0.5px;">Verificadas con Éxito</span>
        <h2 style="font-size: 2.2rem; color: #2b8a3e; margin: 10px 0 0; font-weight: 800;">{{ $totalVerificadas }}</h2>
        <p style="margin: 5px 0 0; font-size: 0.8rem; color: #86efac;">
            @if($view === 'mes') Con firmas este mes @elseif($view === 'ano') Con firmas año {{ $selectedYear }} @else Firmadas globales @endif
        </p>
    </div>

    <!-- Tasa de Avance -->
    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <span style="font-size: 0.75rem; text-transform: uppercase; color: #0F69C4; font-weight: 700; letter-spacing: 0.5px;">Porcentaje de Avance</span>
        <h2 style="font-size: 2.2rem; color: #0F69C4; margin: 10px 0 0; font-weight: 800;">{{ $porcentajeVerificacion }}%</h2>
        <div style="margin-top: 8px; width: 100%; height: 6px; background-color: #e2e8f0; border-radius: 3px; overflow: hidden;">
            <div style="width: {{ $porcentajeVerificacion }}%; height: 100%; background-color: #0F69C4;"></div>
        </div>
    </div>

</div>

<!-- Sección Central: Estadísticas Territoriales por Región -->
<div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 25px; margin-bottom: 35px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
    <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 1.15rem; color: #0d1b2a; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <span> Avance y Desempeño Territorial por Región</span>
        <span style="font-size: 0.8rem; color: #64748b; font-weight: 500;">Periodo: @if($view === 'mes') {{ $meses[$selectedMonth] }} - {{ $selectedYear }} @elseif($view === 'ano') Año {{ $selectedYear }} @else Histórico Global @endif</span>
    </h3>
    
    <div style="overflow-x: auto;">
        <table class="table-custom-data" style="width: 100%; border-collapse: collapse; min-width: 700px;">
            <thead>
                <tr>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: left; font-size: 0.8rem; font-weight: 700; color: #475569;">Región</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: left; font-size: 0.8rem; font-weight: 700; color: #475569;">Director Regional</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: center; font-size: 0.8rem; font-weight: 700; color: #475569;">Unidades</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: center; font-size: 0.8rem; font-weight: 700; color: #475569;">Cargadas</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: center; font-size: 0.8rem; font-weight: 700; color: #475569;">Verificadas</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: right; font-size: 0.8rem; font-weight: 700; color: #475569; width: 180px;">Progreso</th>
                </tr>
            </thead>
            <tbody>
                @foreach($regionesEstadisticas as $stat)
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 14px 16px; font-weight: 700; color: #0F69C4; font-size: 0.9rem;">{{ $stat['nombre'] }}</td>
                    <td style="padding: 14px 16px; font-size: 0.85rem; color: #475569;">{{ $stat['director'] }}</td>
                    <td style="padding: 14px 16px; font-size: 0.85rem; color: #334155; text-align: center; font-weight: 600;">{{ $stat['unidades_count'] }}</td>
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
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Grid Ocultado en la pestaña "Estadísticas Globales" -->
@if($view !== 'global')
    <!-- Panel: Unidades Pendientes con Reenvío de Notificación -->
    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); margin-bottom: 35px;">
        <h3 style="margin-top: 0; margin-bottom: 12px; font-size: 1.1rem; color: #0d1b2a; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <span>📢 Unidades Pendientes de Verificación ({{ $unidadesPendientes->count() }})</span>
            <span style="font-size: 0.8rem; color: #64748b; font-weight: 500;">Filtro: @if($view === 'mes') {{ $meses[$selectedMonth] }} - {{ $selectedYear }} @else Año {{ $selectedYear }} @endif</span>
        </h3>
        <p style="color: #64748b; font-size: 0.85rem; line-height: 1.5; margin-bottom: 20px;">
            A continuación se listan las unidades operativas que registran actividades en estado <strong>CARGADA</strong> para el periodo seleccionado. Como auditor, puede enviarles una renotificación por correo directamente para agilizar sus firmas de verificador.
        </p>

        @if($unidadesPendientes->isEmpty())
        <div style="text-align: center; padding: 30px; color: #2b8a3e; background-color: rgba(43, 138, 62, 0.02); border: 1px dashed #2b8a3e; border-radius: 6px; font-size: 0.9rem; font-weight: 600;">
             ¡Excelente! No existen unidades con actividades pendientes de verificación para este periodo.
        </div>
        @else
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 15px; max-height: 350px; overflow-y: auto; padding-right: 5px;">
            @foreach($unidadesPendientes as $up)
            <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 15px; display: flex; justify-content: space-between; align-items: center; gap: 15px;">
                <div style="flex: 1;">
                    <strong style="color: #0d1b2a; font-size: 0.9rem; display: block;">{{ $up->user->name }}</strong>
                    <span style="font-size: 0.8rem; color: #64748b; display: block; margin-top: 2px;">
                        Región: <strong>{{ $up->region->region_nombre }}</strong>
                    </span>
                    <span style="font-size: 0.75rem; color: #94a3b8; display: block;">
                        Email: {{ $up->user->email }}
                    </span>
                </div>
                <div>
                    <form action="{{ route('auditor.unidades.renotificar', $up->id) }}" method="POST" onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').innerHTML = 'Enviando... ⏳';">
                        @csrf
                        <button type="submit" class="btn-acc" style="padding: 8px 14px; font-size: 0.8rem; font-weight: 700; border-color: #0F69C4; color: #0F69C4 !important; background-color: rgba(15, 105, 196, 0.02); border-radius: 4px; cursor: pointer;">
                            Renotificar ✉️
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Panel: Historial de Cargas Masivas -->
    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); margin-bottom: 30px;">
        <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 1.1rem; color: #0d1b2a; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px;">
            📋 Historial Reciente de Planillas Excel Cargadas
        </h3>
        
        @if($cargasRecientes->isEmpty())
        <div style="text-align: center; padding: 30px; color: #94a3b8; font-size: 0.9rem;">
            No se registran importaciones recientes de planillas.
        </div>
        @else
        <div style="display: flex; flex-direction: column; gap: 15px;">
            @foreach($cargasRecientes as $carga)
            <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 15px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong style="color: #0d1b2a; font-size: 0.9rem; display: block;">{{ $carga->nombre_archivo }}</strong>
                    <span style="font-size: 0.8rem; color: #64748b;">
                        Subido por <strong>{{ $carga->usuario->name }}</strong> el {{ $carga->created_at->format('d-m-Y H:i') }}
                    </span>
                </div>
                <div style="text-align: right;">
                    <span style="background-color: rgba(43, 138, 62, 0.1); color: #2b8a3e; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 4px;">
                        {{ $carga->estado }}
                    </span>
                    <span style="font-size: 0.8rem; color: #475569; font-weight: 600;">
                        {{ $carga->total_filas }} filas
                    </span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
@endif
@endsection