@extends('layouts.app')

@section('title', 'Dashboard - Intranet CAJBIOBIO')

@section('content')
<div class="panel-header-section" style="margin-bottom: 30px;">
    <h2>Dashboard Institucional</h2>
    <p style="margin: 5px 0 0; color: #64748b; font-size: 0.95rem;">
        Bienvenido(a) a la consola operativa de la intranet de la Corporación de Asistencia Judicial de la Región del Biobío.
    </p>
</div>

<!-- Alertas de Edición según Permisos de Mutación Administrativos -->
@can('actividades.adjuntar-administrativo')
    @if(session('modo_edicion'))
        <x-alert type="danger" title="¡Cuidado! Modo Edición Activado">
            Se encuentra en el modo interactivo de administración. Ahora los controles de edición en la vista de Unidades y las acciones para adjuntar o eliminar verificadores en el Historial están activos y usables.
        </x-alert>
    @else
        <x-alert type="info" title="Modo Solo Lectura">
            Se encuentra visualizando la intranet en modo de lectura segura. Los controles críticos de cuentas de unidades y verificadores están bloqueados. Para habilitar las operaciones de edición, active el modo de edición crítica abajo.
        </x-alert>
    @endif
@endcan

<!-- KPIs Consolidados (Visibles para Supervisión Global o Regional) -->
@if(Gate::allows('historial.ver-global') || Gate::allows('historial.ver-regional') || Gate::allows('usuarios.crear'))
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 35px;">
    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <span style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 0.5px;">Actividades Totales</span>
        <h2 style="font-size: 2.2rem; color: #0d1b2a; margin: 10px 0 0; font-weight: 800;">{{ $totalActividades }}</h2>
        <p style="margin: 5px 0 0; font-size: 0.8rem; color: #94a3b8;">Periodo estadístico seleccionado</p>
    </div>

    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <span style="font-size: 0.75rem; text-transform: uppercase; color: #ef3340; font-weight: 700; letter-spacing: 0.5px;">Pendientes de Firma</span>
        <h2 style="font-size: 2.2rem; color: #ef3340; margin: 10px 0 0; font-weight: 800;">{{ $totalCargadas }}</h2>
        <p style="margin: 5px 0 0; font-size: 0.8rem; color: #fca5a5;">Requieren subir verificador</p>
    </div>

    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <span style="font-size: 0.75rem; text-transform: uppercase; color: #2b8a3e; font-weight: 700; letter-spacing: 0.5px;">Verificadas con Éxito</span>
        <h2 style="font-size: 2.2rem; color: #2b8a3e; margin: 10px 0 0; font-weight: 800;">{{ $totalVerificadas }}</h2>
        <p style="margin: 5px 0 0; font-size: 0.8rem; color: #86efac;">Con respaldos archivados</p>
    </div>

    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <span style="font-size: 0.75rem; text-transform: uppercase; color: #0F69C4; font-weight: 700; letter-spacing: 0.5px;">Porcentaje de Avance</span>
        <h2 style="font-size: 2.2rem; color: #0F69C4; margin: 10px 0 0; font-weight: 800;">{{ $porcentajeVerificacion }}%</h2>
        <div style="margin-top: 8px; width: 100%; height: 6px; background-color: #e2e8f0; border-radius: 3px; overflow: hidden;">
            <div style="width: {{ $porcentajeVerificacion }}%; height: 100%; background-color: #0F69C4;"></div>
        </div>
    </div>
</div>
@endif

<!-- Bloque: Verificar Pendientes de la Unidad (Widget Autónomo) -->
@can('actividades.verificar')
    <!-- Si no tiene accesos jerárquicos superiores, se asume que es una unidad pura -->
    @if(!Gate::allows('usuarios.crear') && !Gate::allows('historial.ver-regional') && !Gate::allows('historial.ver-global'))
    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 25px; margin-bottom: 35px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <h3 style="margin-top: 0; color: #0d1b2a; font-size: 1.2rem; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; margin-bottom: 20px;">
             📥 Actividades Asignadas Pendientes de Respaldo
        </h3>
        <livewire:actividades.verificar-pendientes />
    </div>
    @endif
@endcan

<!-- Selector de Periodo y Filtros Estadísticos (Supervisión Global o Regional) -->
@if(Gate::allows('historial.ver-global') || Gate::allows('historial.ver-regional'))
    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 25px; margin-bottom: 35px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <form action="{{ route('dashboard') }}" method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 150px;">
                <label for="ano" style="font-size: 0.85rem; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Seleccionar Año Estadístico</label>
                <select name="ano" id="ano" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; background-color: #ffffff; font-size: 0.95rem;">
                    @for($y = (int)date('Y') + 1; $y >= 2020; $y--)
                        <option value="{{ $y }}" @if($y === $selectedYear) selected @endif>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn-dashboard-primary" style="height: 42px;">
                    Filtrar por Año
                </button>
                @if($selectedYear !== $activeYear)
                <a href="{{ route('dashboard', ['ano' => $activeYear]) }}" class="btn-acc" style="text-align: center; padding: 10px 15px; text-decoration: none; border-color: #cbd5e1; font-weight: 600; font-size: 0.9rem; border-radius: 6px; display: inline-flex; align-items: center; background: #fff; height: 42px; box-sizing: border-box;">
                    Volver al Año Activo ({{ $activeYear }})
                </a>
                @endif
            </div>
        </form>
    </div>
@endif

<!-- Bloque: Auditoría / Regiones Desplegables (Widget de Supervisión Global) -->
@can('historial.ver-global')
<div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 25px; margin-bottom: 35px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
    <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 1.15rem; color: #0d1b2a; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <span> Avance y Desempeño Territorial por Región</span>
    </h3>
    <div style="overflow-x: auto;">
        <table class="table-custom-data" style="width: 100%; border-collapse: collapse; min-width: 700px;">
            <thead>
                <tr>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: left; font-size: 0.8rem; font-weight: 700; color: #475569;">Región</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: left; font-size: 0.8rem; font-weight: 700; color: #475569;">Director Regional</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: center; font-size: 0.8rem; font-weight: 700; color: #475569; width: 100px;">Unidades</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: center; font-size: 0.8rem; font-weight: 700; color: #475569; width: 100px;">Cargadas</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: center; font-size: 0.8rem; font-weight: 700; color: #475569; width: 100px;">Verificadas</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: center; font-size: 0.8rem; font-weight: 700; color: #475569; width: 180px;">Progreso</th>
                </tr>
            </thead>
            <tbody>
                @foreach($regionesEstadisticas as $stat)
                <tr class="js-region-toggle" 
                    data-target="region-detail-{{ $stat['id'] }}"
                    style="border-bottom: 1px solid #e2e8f0; cursor: pointer; transition: background-color 0.15s ease;">
                    <td style="padding: 14px 16px; font-weight: 700; color: #0F69C4; font-size: 0.9rem;" title="expandir unidades">
                        <span class="js-accordion-icon" >▼</span>
                        {{ $stat['nombre'] }} 
                    </td>
                    <td style="padding: 14px 16px; font-size: 0.85rem; color: #475569;">{{ $stat['director'] }}</td>
                    <td style="padding: 14px 16px; font-size: 0.85rem; color: #334155; text-align: center; font-weight: 600;">{{ $stat['unidades_count'] }}</td>
                    <td style="padding: 14px 16px; font-size: 0.85rem; color: #ef3340; text-align: center; font-weight: 600;">{{ $stat['cargadas'] }}</td>
                    <td style="padding: 14px 16px; font-size: 0.85rem; color: #2b8a3e; text-align: center; font-weight: 600;">{{ $stat['verificadas'] }}</td>
                    <td style="padding: 14px 16px; text-align: center;">
                        <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                            <span style="font-size: 0.8rem; font-weight: 700; color: #0d1b2a;">{{ $stat['avance'] }}%</span>
                            <div style="width: 80px; height: 8px; background-color: #e2e8f0; border-radius: 4px; overflow: hidden; display: inline-block;">
                                <div style="width: {{ $stat['avance'] }}%; height: 100%; background-color: #2b8a3e;"></div>
                            </div>
                        </div>
                    </td>
                </tr>

                <tr id="region-detail-{{ $stat['id'] }}" 
                    style="background-color: #f8fafc; display: none;">
                    <td colspan="6" style="padding: 25px; border-bottom: 1px solid #cbd5e1;">
                        <div style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 12px; padding: 25px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);">
                            <h4 style="margin-top: 0; margin-bottom: 20px; color: #0d1b2a; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">
                                Unidades Operativas - Región {{ $stat['nombre'] }}
                            </h4>
                            <x-unidades-list :unidades="$stat['unidades']" />
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endcan

<!-- Bloque: Director Regional (Widget de Supervisión Territorial) -->
@can('historial.ver-regional')
    @if(!Gate::allows('historial.ver-global'))
    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 25px; margin-bottom: 35px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 1.15rem; color: #0d1b2a; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px;">
             Control y Monitoreo de Unidades - Región {{ $region->region_nombre ?? 'Jurisdicción' }}
        </h3>
        <x-unidades-list :unidades="$unidadesEstadisticas" />
    </div>
    @endif
@endcan

<!-- Bloque Inferior Grid: Cargas Recientes + Configuración (Visible para Roles con permisos de Gestión) -->
@if(Gate::allows('historial.ver-global') || Gate::allows('usuarios.crear'))
<div style="display: grid; grid-template-columns: 1.3fr 0.7fr; gap: 30px; flex-wrap: wrap;">
    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
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
                    <span style="background-color: rgba(43, 138, 62, 0.1); color: #2b8a3e; padding: 3px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 4px;">
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

    <!-- Panel de accesos rápidos -->
    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 1.1rem; color: #0d1b2a; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px;">
                ⚙️ Configuración y Accesos Rápidos
            </h3>
            <p style="color: #64748b; font-size: 0.85rem; line-height: 1.5; margin-bottom: 25px;">
                Gestione las actividades cargadas o active el modo de edición crítica en el catálogo. Las operaciones de mutación de cuentas y archivos requieren confirmación de contraseña.
            </p>
        </div>

        <div style="display: flex; flex-direction: column; gap: 15px;">
            @if(Gate::allows('historial.ver-global') || Gate::allows('historial.ver-regional') || Gate::allows('historial.ver-unidad'))
            <a href="{{ route('actividades.historial') }}" class="btn-dashboard-primary" style="text-align: center; text-decoration: none; padding: 12px; font-size: 0.9rem;">
                Revisar Historial Completo
            </a>
            @endif
            
            @can('actividades.adjuntar-administrativo')
                @if(session('modo_edicion'))
                <a href="{{ route('admin.salir-edicion') }}" class="btn-acc" style="text-align: center; border: 1px solid #2b8a3e; color: #2b8a3e !important; background-color: rgba(43, 138, 62, 0.05); font-weight: 700; padding: 12px; font-size: 0.9rem; border-radius: 6px;">
                    Salir del Modo Edición 🔓
                </a>
                @else
                <a href="{{ route('admin.edicion') }}" class="btn-acc" style="text-align: center; border: 1px solid #ef3340; color: #ef3340 !important; background-color: rgba(239, 51, 64, 0.02); font-weight: 700; padding: 12px; font-size: 0.9rem; border-radius: 6px;">
                    Modo Edición Crítica 🔐
                </a>
                @endif
            @endcan
        </div>
    </div>
</div>
@endif
@endsection