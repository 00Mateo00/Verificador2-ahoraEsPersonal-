<div x-data="{ advancedOpen: false }" @if(Auth::user()->rol === \App\Enums\UserRole::Auditor) wire:poll.600s @endif>
    @if(Auth::user()->rol === \App\Enums\UserRole::Cargador)
        <!-- Vista Historial de Cargas Masivas Agrupadas (Cargador) -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            @if($cargasAgrupadas->isEmpty())
                <div class="empty-state-card">
                    <div class="empty-state-icon">📋</div>
                    <h3>Sin cargas masivas registradas</h3>
                    <p>Usted no registra importaciones de planillas en el sistema todavía.</p>
                </div>
            @else
                @foreach($cargasAgrupadas as $carga)
                    <div class="dashboard-card" style="margin-bottom: 20px; padding: 25px;" x-data="{ expanded: false }">
                        <div style="display: flex; justify-content: space-between; align-items: center; cursor: pointer;" @click="expanded = !expanded">
                            <div>
                                <h4 style="margin: 0; color: #0F69C4; font-size: 1.15rem; font-weight: 700;">{{ $carga->nombre_archivo }}</h4>
                                <p style="margin: 6px 0 0; font-size: 0.85rem; color: #64748b;">
                                    Importado el <strong>{{ $carga->created_at->format('d-m-Y H:i:s') }}</strong> | Total filas: <strong style="color: #2b8a3e;">{{ $carga->total_filas }}</strong>
                                </p>
                            </div>
                            <button type="button" class="filter-toggle-btn" style="padding: 8px 16px; font-size: 0.8rem; cursor: pointer;">
                                <span x-text="expanded ? 'Ocultar Detalle ▲' : 'Ver Detalle ▼'"></span>
                            </button>
                        </div>

                        <!-- Listado de actividades del bache de subida -->
                        <div x-show="expanded" x-transition style="margin-top: 20px; border-top: 1px dashed #cbd5e1; padding-top: 15px;" x-cloak>
                            <h5 style="margin: 0 0 12px 0; color: #475569; font-size: 0.85rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Actividades en este bache</h5>
                            <div style="overflow-x: auto;">
                                <table class="table-custom-data" style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr>
                                            <th style="padding: 10px; font-size: 0.8rem;">COD</th>
                                            <th style="padding: 10px; font-size: 0.8rem;">Unidad</th>
                                            <th style="padding: 10px; font-size: 0.8rem;">Actividad</th>
                                            <th style="padding: 10px; font-size: 0.8rem;">Modalidad</th>
                                            <th style="padding: 10px; font-size: 0.8rem; text-align: center;">Fecha</th>
                                            <th style="padding: 10px; font-size: 0.8rem; text-align: center;">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($carga->actividades as $act)
                                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                                <td style="padding: 10px; font-size: 0.8rem; font-weight: bold; color: #334155;">{{ $act->COD }}</td>
                                                <td style="padding: 10px; font-size: 0.8rem; color: #475569;">{{ $act->UNIDAD }}</td>
                                                <td style="padding: 10px; font-size: 0.8rem; color: #475569;">{{ $act->TIPO_ACTIVIDAD }}</td>
                                                <td style="padding: 10px; font-size: 0.8rem; color: #475569;">{{ $act->MODALIDAD }}</td>
                                                <td style="padding: 10px; font-size: 0.8rem; text-align: center; color: #475569;">{{ $act->FECHA ? $act->FECHA->format('d-m-Y') : 'N/A' }}</td>
                                                <td style="padding: 10px; text-align: center;">
                                                    <span style="background-color: {{ $act->estado === 'VERIFICADA' ? 'rgba(43, 138, 62, 0.08)' : 'rgba(239, 51, 64, 0.08)' }}; color: {{ $act->estado === 'VERIFICADA' ? '#2b8a3e' : '#ef3340' }}; padding: 2px 6px; border-radius: 4px; font-size: 0.72rem; font-weight: 700;">
                                                        {{ $act->estado }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div style="margin-top: 20px;">
                    {{ $cargasAgrupadas->links() }}
                </div>
            @endif
        </div>
    @else
        <!-- 1. Filtros Básicos y Avanzados -->
        @include('livewire.actividades.partials.filtros')

        @if ($isDateRangeActive)
        <div
            style="margin-bottom: 20px; font-weight: 600; color: #0d1b2a; font-size: 0.95rem; background-color: #f1f5f9; padding: 12px 20px; border-radius: 6px;">
             Resultados del Mes Seleccionado: {{ $totalResults }} actividades encontradas.
        </div>
        @endif

        <!-- 3. Contenedor de Listado de Actividades -->
        <div id="actividades-container">
            @if ($actividades->isEmpty())
            <div class="dashoard-card empty-state-card">
                <div class="empty-state-icon">📁</div>
                <h3>No se encontraron actividades</h3>
                <p>
                    No se encontraron reportes con los criterios de búsqueda seleccionados.
                </p>
            </div>
            @else
            @php $lastMonthYear = null; @endphp
            @foreach ($actividades as $act)
            @php
            $actDate = $act->FECHA ?? now();
            $monthYearKey = $actDate->format('Y-m');
            @endphp

            <!-- Separador de Línea Temporal (Mes / Año) -->
            @if (!$isDateRangeActive && $lastMonthYear !== $monthYearKey)
            @php
            $lastMonthYear = $monthYearKey;
            $totalMonthCount = $monthCounts[$monthYearKey] ?? 0;
            $monthLabel = str_replace(
                [
                    'January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December',
                ],
                [
                    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
                ],
                $actDate->format('F'),
            );
            @endphp
            <div
                style="margin: 30px 0 15px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px dashed #cbd5e1; padding-bottom: 8px;">
                <span
                    style="font-size: 0.85rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">
                     {{ $actDate->format('Y') }} - {{ $monthLabel }}
                </span>
                <span
                    style="font-size: 0.75rem; font-weight: 600; color: #64748b; background-color: #f1f5f9; padding: 3px 8px; border-radius: 20px;">
                    {{ $totalMonthCount }} {{ $totalMonthCount == 1 ? 'actividad' : 'actividades' }}
                </span>
            </div>
            @endif

            <!-- Tarjeta Individual de Actividad (Acordeón) -->
            @include('livewire.actividades.partials.actividad-card', [
            'act' => $act,
            'actDate' => $actDate,
            ])
            @endforeach
            @endif
        </div>

        <!-- Paginación Laravel -->
        <div style="margin-top: 25px;">
            {{ $actividades->links() }}
        </div>
    @endif
</div>