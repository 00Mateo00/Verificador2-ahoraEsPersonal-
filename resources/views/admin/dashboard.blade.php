@extends('layouts.app')

@section('title', 'Dashboard Administrador - Intranet CAJBIOBIO')

@section('content')
<div class="panel-header-section" style="margin-bottom: 30px;">
    <h2>Dashboard Administrativo</h2>
    <p style="margin: 5px 0 0; color: #64748b; font-size: 0.95rem;">
        Resumen general y control operativo del sistema de verificación.
    </p>
</div>

<!-- Alertas Dinámicas de Seguridad (Dashboard) -->
@if(session('modo_edicion'))
<div style="background-color: #fff1f2; border: 1px solid #fecdd3; border-radius: 8px; padding: 20px; margin-bottom: 30px; display: flex; align-items: flex-start; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.01);">
    <span style="font-size: 1.5rem; line-height: 1;">⚠️</span>
    <div>
        <strong style="color: #9f1239; font-size: 1rem; display: block; margin-bottom: 4px;">¡Cuidado! Modo Edición Activado</strong>
        <p style="color: #be123c; font-size: 0.85rem; margin: 0; line-height: 1.5;">
            Se encuentra en el modo interactivo de administración. Ahora los controles de edición en la vista de Unidades y las acciones para adjuntar o eliminar verificadores en el Historial están activos y usables. Por seguridad, este modo expirará automáticamente tras 10 minutos de inactividad.
        </p>
    </div>
</div>
@else
<div style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 20px; margin-bottom: 30px; display: flex; align-items: flex-start; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.01);">
    <span style="font-size: 1.5rem; line-height: 1;">🔒</span>
    <div>
        <strong style="color: #1e40af; font-size: 1rem; display: block; margin-bottom: 4px;">Modo Solo Lectura</strong>
        <p style="color: #1e3a8a; font-size: 0.85rem; margin: 0; line-height: 1.5;">
            Se encuentra visualizando la intranet en modo de lectura segura. Los controles críticos de cuentas de unidades y verificadores están bloqueados. Para habilitar las operaciones de edición, active el modo de edición crítica abajo.
        </p>
    </div>
</div>
@endif

<!-- Tarjetas de Indicadores Consolidados -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 35px;">
    
    <!-- Total Actividades -->
    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <span style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 0.5px;">Actividades Totales</span>
        <h2 style="font-size: 2.2rem; color: #0d1b2a; margin: 10px 0 0; font-weight: 800;">{{ $totalActividades }}</h2>
        <p style="margin: 5px 0 0; font-size: 0.8rem; color: #94a3b8;">Total acumulado histórico</p>
    </div>

    <!-- Pendientes -->
    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <span style="font-size: 0.75rem; text-transform: uppercase; color: #ef3340; font-weight: 700; letter-spacing: 0.5px;">Pendientes de Firma</span>
        <h2 style="font-size: 2.2rem; color: #ef3340; margin: 10px 0 0; font-weight: 800;">{{ $totalCargadas }}</h2>
        <p style="margin: 5px 0 0; font-size: 0.8rem; color: #fca5a5;">Requieren subir verificador</p>
    </div>

    <!-- Verificadas -->
    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <span style="font-size: 0.75rem; text-transform: uppercase; color: #2b8a3e; font-weight: 700; letter-spacing: 0.5px;">Verificadas con Éxito</span>
        <h2 style="font-size: 2.2rem; color: #2b8a3e; margin: 10px 0 0; font-weight: 800;">{{ $totalVerificadas }}</h2>
        <p style="margin: 5px 0 0; font-size: 0.8rem; color: #86efac;">Con respaldos archivados</p>
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
    <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 1.15rem; color: #0d1b2a; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px;">
        🗺️ Avance y Desempeño Territorial por Región
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

<!-- Grid Inferior: Cargas Recientes + Accesos Críticos -->
<div style="display: grid; grid-template-columns: 1.3fr 0.7fr; gap: 30px; flex-wrap: wrap;">
    
    <!-- Panel: Historial de Cargas Masivas -->
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

    <!-- Panel: Accesos y Acciones Críticas de Administración -->
    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 1.1rem; color: #0d1b2a; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px;">
                ⚙️ Configuración y Accesos
            </h3>
            <p style="color: #64748b; font-size: 0.85rem; line-height: 1.5; margin-bottom: 25px;">
                Gestione las actividades cargadas de forma masiva o ingrese al modo de configuración de credenciales críticas. Recuerde que las operaciones de modificación requieren confirmación de contraseña.
            </p>
        </div>

        <div style="display: flex; flex-direction: column; gap: 15px;">
            <a href="{{ route('actividades.historial') }}" class="btn-primary-caj" style="text-align: center; text-decoration: none; padding: 12px; font-size: 0.9rem;">
                Revisar Historial Completo
            </a>
            
            @if(session('modo_edicion'))
            <a href="{{ route('admin.salir-edicion') }}" class="btn-acc" style="text-align: center; border: 1px solid #2b8a3e; color: #2b8a3e !important; background-color: rgba(43, 138, 62, 0.05); font-weight: 700; padding: 12px; font-size: 0.9rem; border-radius: 6px;">
                Salir del Modo Edición 🔓
            </a>
            @else
            <a href="{{ route('admin.edicion') }}" class="btn-acc" style="text-align: center; border: 1px solid #ef3340; color: #ef3340 !important; background-color: rgba(239, 51, 64, 0.02); font-weight: 700; padding: 12px; font-size: 0.9rem; border-radius: 6px;">
                Modo Edición Crítica 🔐
            </a>
            @endif
        </div>
    </div>

</div>
@endsection