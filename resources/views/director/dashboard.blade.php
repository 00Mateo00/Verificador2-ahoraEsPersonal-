@extends('layouts.app')

@section('title', 'Dashboard Regional - Intranet CAJBIOBIO')

@section('content')
<div class="panel-header-section" style="margin-bottom: 25px;">
    <h2>Dashboard de Dirección Regional</h2>
    <p style="margin: 5px 0 0; color: #64748b; font-size: 0.95rem;">
        Consola de supervisión territorial para la región de <strong>{{ $region->region_nombre ?? 'Jurisdicción Asignada' }}</strong>.
        Vista del periodo estadístico: <strong>{{ date('m') }}/{{ date('Y') }} (Mes Actual)</strong>.
    </p>
</div>

<!-- Tarjeta Informativa de Rol -->
<div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; margin-bottom: 25px; display: flex; align-items: flex-start; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.01);">
    <span style="font-size: 1.5rem; line-height: 1;">🗺️</span>
    <div>
        <strong style="color: #166534; font-size: 1rem; display: block; margin-bottom: 4px;">Control de Operación Regional</strong>
        <p style="color: #15803d; font-size: 0.85rem; margin: 0; line-height: 1.5;">
            Supervise el avance en la subida de verificadores de las unidades operativas a su cargo para el mes corriente. Use la renotificación dinámica para recordar firmas rezagadas.
        </p>
    </div>
</div>

<!-- Tarjetas de Indicadores Consolidados -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 35px;">
    
    <!-- Total Actividades -->
    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <span style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 0.5px;">Actividades Totales</span>
        <h2 style="font-size: 2.2rem; color: #0d1b2a; margin: 10px 0 0; font-weight: 800;">{{ $totalActividades }}</h2>
        <p style="margin: 5px 0 0; font-size: 0.8rem; color: #94a3b8;">Asignadas este mes</p>
    </div>

    <!-- Pendientes -->
    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <span style="font-size: 0.75rem; text-transform: uppercase; color: #ef3340; font-weight: 700; letter-spacing: 0.5px;">Pendientes de Firma</span>
        <h2 style="font-size: 2.2rem; color: #ef3340; margin: 10px 0 0; font-weight: 800;">{{ $totalCargadas }}</h2>
        <p style="margin: 5px 0 0; font-size: 0.8rem; color: #fca5a5;">Faltan respaldos</p>
    </div>

    <!-- Verificadas -->
    <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <span style="font-size: 0.75rem; text-transform: uppercase; color: #2b8a3e; font-weight: 700; letter-spacing: 0.5px;">Verificadas con Éxito</span>
        <h2 style="font-size: 2.2rem; color: #2b8a3e; margin: 10px 0 0; font-weight: 800;">{{ $totalVerificadas }}</h2>
        <p style="margin: 5px 0 0; font-size: 0.8rem; color: #86efac;">Verificadores archivados</p>
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

<!-- 📢 Unidades Pendientes de Verificación (Exclusivo de su Región) -->
<div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); margin-bottom: 35px;">
    <h3 style="margin-top: 0; margin-bottom: 12px; font-size: 1.1rem; color: #0d1b2a; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px;">
        📢 Unidades Pendientes de Verificación ({{ $unidadesPendientes->count() }})
    </h3>
    <p style="color: #64748b; font-size: 0.85rem; line-height: 1.5; margin-bottom: 20px;">
        Las siguientes unidades regionales asignadas a su dirección registran una o más actividades sin respaldos para el mes estadístico en curso. Presione el botón para despacharles una renotificación automática de carga.
    </p>

    @if($unidadesPendientes->isEmpty())
    <div style="text-align: center; padding: 30px; color: #2b8a3e; background-color: rgba(43, 138, 62, 0.02); border: 1px dashed #2b8a3e; border-radius: 6px; font-size: 0.9rem; font-weight: 600;">
        ✅ ¡Enhorabuena! Todas sus unidades operativas tienen sus verificadores al día en este periodo.
    </div>
    @else
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 15px; max-height: 250px; overflow-y: auto;">
        @foreach($unidadesPendientes as $up)
        <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 15px; display: flex; justify-content: space-between; align-items: center; gap: 15px;">
            <div style="flex: 1;">
                <strong style="color: #0d1b2a; font-size: 0.9rem; display: block;">{{ $up->user->name }}</strong>
                <span style="font-size: 0.75rem; color: #64748b; display: block; margin-top: 2px;">
                    Email: {{ $up->user->email }}
                </span>
            </div>
            <div>
                <form action="{{ route('director.unidades.renotificar', $up->id) }}" method="POST">
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

<!-- 📋 Listado de Actividades del Periodo Actual -->
<div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); margin-bottom: 30px;">
    <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 1.15rem; color: #0d1b2a; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <span>📋 Listado de Actividades Registradas</span>
        <span style="font-size: 0.8rem; color: #64748b; font-weight: 500;">Filtro: Mes Estadístico Actual</span>
    </h3>

    @if($actividades->isEmpty())
    <div style="text-align: center; padding: 30px; color: #94a3b8; font-size: 0.9rem;">
        No se registran actividades para sus unidades en el mes corriente.
    </div>
    @else
    <div style="overflow-x: auto;">
        <table class="table-custom-data" style="width: 100%; border-collapse: collapse; min-width: 800px;">
            <thead>
                <tr>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: left; font-size: 0.8rem; font-weight: 700; color: #475569; width: 60px;">COD</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: left; font-size: 0.8rem; font-weight: 700; color: #475569;">Unidad</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: left; font-size: 0.8rem; font-weight: 700; color: #475569;">Tipo de Actividad</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: center; font-size: 0.8rem; font-weight: 700; color: #475569; width: 120px;">Fecha</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: center; font-size: 0.8rem; font-weight: 700; color: #475569; width: 140px;">Estado</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: right; font-size: 0.8rem; font-weight: 700; color: #475569; width: 160px;">Verificadores</th>
                </tr>
            </thead>
            <tbody>
                @foreach($actividades as $act)
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 14px 16px; font-size: 0.85rem; color: #64748b; font-family: monospace;">{{ $act->COD ?: 'N/A' }}</td>
                    <td style="padding: 14px 16px; font-size: 0.9rem; font-weight: 600; color: #0d1b2a;">{{ $act->UNIDAD }}</td>
                    <td style="padding: 14px 16px; font-size: 0.85rem; color: #475569;">
                        <strong>{{ $act->TIPO_ACTIVIDAD }}</strong><br>
                        <span style="font-size: 0.75rem; color: #94a3b8;">{{ $act->SUB_TIPO_ACTIVIDAD }}</span>
                    </td>
                    <td style="padding: 14px 16px; font-size: 0.85rem; color: #475569; text-align: center;">
                        {{ $act->FECHA ? \Carbon\Carbon::parse($act->FECHA)->format('d-m-Y') : 'N/A' }}
                    </td>
                    <td style="padding: 14px 16px; text-align: center;">
                        @if($act->estado === 'VERIFICADA')
                        <span style="background-color: rgba(43, 138, 62, 0.08); color: #2b8a3e; padding: 3px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase;">
                            Verificada
                        </span>
                        @else
                        <span style="background-color: rgba(239, 51, 64, 0.08); color: #ef3340; padding: 3px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase;">
                            Pendiente
                        </span>
                        @endif
                    </td>
                    <td style="padding: 14px 16px; text-align: right;">
                        @if($act->archivos->isNotEmpty())
                            @foreach($act->archivos as $archivo)
                            <a href="{{ route('archivos.descargar', $archivo->archivo_id) }}" style="font-size: 0.8rem; font-weight: 700; color: #0F69C4; display: block; margin-bottom: 2px;">
                                📥 Descargar {{ \Illuminate\Support\Str::limit($archivo->archivo_nombre, 12) }}
                            </a>
                            @endforeach
                        @else
                        <span style="font-size: 0.8rem; color: #94a3b8; font-style: italic;">Sin archivos</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div style="margin-top: 25px;">
        {{ $actividades->links() }}
    </div>
    @endif
</div>
@endsection