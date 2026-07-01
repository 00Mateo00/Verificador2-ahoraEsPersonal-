@props([
    'unidades'
])

@php
// Calcular contadores síncronos en servidor para la carga inicial (sin queries extra de base de datos)
$unidadesColl = collect($unidades);
$totalCount = $unidadesColl->count();
$pendientesCount = $unidadesColl->where('status', 'pendientes')->count();
$alDiaCount = $unidadesColl->where('status', 'al_dia')->count();
$sinActividadesCount = $unidadesColl->where('status', 'sin_actividades')->count();
@endphp

<div data-unit-container data-current-filter="pendientes" style="width: 100%;">

    <!-- Barra de Búsqueda e Input JS -->
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 15px; margin-bottom: 25px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 280px;">
            <input type="text" 
                   class="form-input-control-caj js-unit-search" 
                   placeholder="🔍 Buscar unidad (coincidencia aproximada en memoria instantánea)..." 
                   style="width: 100%; height: 42px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 16px; font-size: 0.9rem;">
        </div>

        <!-- Botonera de Filtros JS -->
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <button type="button" 
                    class="js-unit-filter-btn"
                    data-filter="all"
                    data-active-color="#0F69C4"
                    style="background-color: #f8fafc; color: #475569; border: 1px solid #cbd5e1; padding: 8px 14px; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 0.8rem; height: 42px; transition: all 0.15s ease;">
                Todos ({{ $totalCount }})
            </button>
            <button type="button" 
                    class="js-unit-filter-btn active-filter"
                    data-filter="pendientes"
                    data-active-color="#ef3340"
                    style="background-color: #ef3340; color: #ffffff; border: none; padding: 8px 14px; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 0.8rem; height: 42px; transition: all 0.15s ease;">
                Pendientes ({{ $pendientesCount }})
            </button>
            <button type="button" 
                    class="js-unit-filter-btn"
                    data-filter="al_dia"
                    data-active-color="#2b8a3e"
                    style="background-color: #f8fafc; color: #475569; border: 1px solid #cbd5e1; padding: 8px 14px; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 0.8rem; height: 42px; transition: all 0.15s ease;">
                Al día ({{ $alDiaCount }})
            </button>
            <button type="button" 
                    class="js-unit-filter-btn"
                    data-filter="sin_actividades"
                    data-active-color="#64748b"
                    style="background-color: #f8fafc; color: #475569; border: 1px solid #cbd5e1; padding: 8px 14px; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 0.8rem; height: 42px; transition: all 0.15s ease;">
                Sin actividades ({{ $sinActividadesCount }})
            </button>
        </div>
    </div>

    <!-- Tabla JS -->
    <div style="overflow-x: auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px;">
        <table class="table-custom-data" style="width: 100%; border-collapse: collapse; min-width: 700px;">
            <thead>
                <tr>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: left; font-size: 0.8rem; font-weight: 700; color: #475569;">Unidad</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: center; font-size: 0.8rem; font-weight: 700; color: #475569; width: 120px;">Pendientes</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: center; font-size: 0.8rem; font-weight: 700; color: #475569; width: 120px;">Verificadas</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: center; font-size: 0.8rem; font-weight: 700; color: #475569; width: 180px;">Progreso</th>
                    <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: center; font-size: 0.8rem; font-weight: 700; color: #475569; width: 160px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($unidades as $stat)
                    <tr data-unit-row 
                        data-unit-name="{{ $stat['nombre'] }}" 
                        data-unit-status="{{ $stat['status'] }}"
                        style="border-bottom: 1px solid #e2e8f0; transition: all 0.1s ease;">
                        <td style="padding: 14px 16px; font-size: 0.9rem;">
                            <a href="{{ route('actividades.historial', ['uf' => $stat['id']]) }}" 
                               title="ir al historial de la unidad"
                               style="font-weight: 700; color: #0F69C4; text-decoration: none; display: inline-block; outline: none; border-bottom: 1px dashed rgba(15, 105, 196, 0.3);">
                                {{ $stat['nombre'] }}
                            </a>
                            <span style="display: block; font-size: 0.75rem; color: #64748b; font-weight: normal; margin-top: 2px;">{{ $stat['email'] }}</span>
                        </td>
                        <td style="padding: 14px 16px; font-size: 0.85rem; color: #ef3340; text-align: center; font-weight: 600;">{{ $stat['cargadas'] }}</td>
                        <td style="padding: 14px 16px; font-size: 0.85rem; color: #2b8a3e; text-align: center; font-weight: 600;">{{ $stat['verificadas'] }}</td>
                        <td style="padding: 14px 16px; text-align: center;">
                            @if($stat['status'] === 'sin_actividades')
                                <span style="font-size: 0.8rem; font-weight: 600; color: #64748b; font-style: italic;">Sin actividades</span>
                            @else
                                <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                                    <span style="font-size: 0.8rem; font-weight: 700; color: #0d1b2a;">{{ $stat['avance'] }}%</span>
                                    <div style="width: 80px; height: 8px; background-color: #e2e8f0; border-radius: 4px; overflow: hidden; display: inline-block;">
                                        <div style="width: {{ $stat['avance'] }}%; height: 100%; background-color: #2b8a3e;"></div>
                                    </div>
                                </div>
                            @endif
                        </td>
                        <td style="padding: 14px 16px; text-align: center;">
                            @if($stat['status'] === 'pendientes')
                                @php
                                    $notifyRoute = '#';
                                    if(Auth::user()->hasPermissionTo('historial.ver-global')) {
                                        $notifyRoute = route('auditor.unidades.renotificar', $stat['id']);
                                    } elseif(Auth::user()->hasPermissionTo('historial.ver-regional')) {
                                        $notifyRoute = route('director.unidades.renotificar', $stat['id']);
                                    }
                                    $isNotifiedToday = $stat['notificada_hoy'] ?? false;
                                @endphp
                                
                                @if($notifyRoute !== '#')
                                    <form action="{{ $notifyRoute }}" method="POST" style="margin: 0;" 
                                          onsubmit="if({{ $isNotifiedToday ? 'true' : 'false' }}) { if(!confirm('La unidad ya fue notificada anteriormente el día de hoy, ¿está seguro que desea volver a notificar a la unidad?')) return false; } this.querySelector('button').disabled = true; this.querySelector('button').innerHTML = '... ⏳';">
                                        @csrf
                                        <button type="submit" 
                                                class="btn-acc" 
                                                style="padding: 6px 12px; font-size: 0.8rem; font-weight: 700; border-radius: 4px; cursor: pointer; transition: all 0.15s ease;
                                                       {{ $isNotifiedToday ? 'border-color: #64748b; color: #64748b !important; background-color: #f1f5f9;' : 'border-color: #ef3340; color: #ef3340 !important; background-color: rgba(239, 51, 64, 0.02);' }}">
                                            {{ $isNotifiedToday ? 'Ya notificada' : 'Notificar' }} 
                                        </button>
                                    </form>
                                @endif
                            @else
                                <span style="font-size: 0.75rem; color: #94a3b8; font-style: italic; font-weight: 600;">Sin pendientes</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>