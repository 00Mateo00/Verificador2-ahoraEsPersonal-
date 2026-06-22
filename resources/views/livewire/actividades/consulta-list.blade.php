<div x-data="{ advancedOpen: false }" @if(Auth::user()->rol === 'auditor') wire:poll.600s @endif>
    <!-- 1. Filtros Básicos y Avanzados -->
    @include('livewire.actividades.partials.filtros')

    @if ($isDateRangeActive)
    <div
        style="margin-bottom: 20px; font-weight: 600; color: #0d1b2a; font-size: 0.95rem; background-color: #f1f5f9; padding: 12px 20px; border-radius: 6px;">
        🔍 Resultados en el Rango de Fechas: {{ $totalResults }} actividades encontradas.
    </div>
    @endif

    <!-- 3. Contenedor de Listado de Actividades -->
    <div id="actividades-container">
        @if ($actividades->isEmpty())
        <div class="dashoard-card empty-state-card">
            <div class="empty-state-icon">📁</div>
            <h3>No se encontraron actividades</h3>
            <p>
                No se encontraron reportes con los criterios de búsqueda
                seleccionados.
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
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December',
        ],
        [
        'Enero',
        'Febrero',
        'Marzo',
        'Abril',
        'Mayo',
        'Junio',
        'Julio',
        'Agosto',
        'Septiembre',
        'Octubre',
        'Noviembre',
        'Diciembre',
        ],
        $actDate->format('F'),
        );
        @endphp
        <div
            style="margin: 30px 0 15px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px dashed #cbd5e1; padding-bottom: 8px;">
            <span
                style="font-size: 0.85rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">
                📅 {{ $actDate->format('Y') }} - {{ $monthLabel }}
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
</div>