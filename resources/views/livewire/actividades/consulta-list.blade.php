<?php

use Livewire\Volt\Component;
use App\Models\Actividad;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

new class extends Component {
    use WithPagination;

    // Filtros de URL (Query Params)
    #[Url(as: 'q')]
    public string $buscar = '';

    #[Url(as: 'ano')]
    public string $ano = '';

    #[Url(as: 'desde')]
    public string $fecha_inicio = '';

    #[Url(as: 'hasta')]
    public string $fecha_fin = '';

    #[Url(as: 'act')]
    public string $tipo = '';

    #[Url(as: 'uf')]
    public string $unidad_filtro = '';

    // ID seleccionado desde Deep Link
    #[Url(as: 'id')]
    public string $actividad_id = '';

    // Selección múltiple para exportación
    public array $selectedIds = [];
    public bool $selectAll = false;

    // Reiniciar paginación al cambiar filtros
    public function updatedBuscar()
    {
        $this->resetPage();
    }
    public function updatedAno()
    {
        $this->resetPage();
    }
    public function updatedFechaInicio()
    {
        $this->resetPage();
    }
    public function updatedFechaFin()
    {
        $this->resetPage();
    }
    public function updatedUnidadFiltro()
    {
        $this->resetPage();
    }
    public function updatedTipo()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedIds = $this->getFilteredActivitiesQuery()->pluck('actividad_id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    private function getFilteredActivitiesQuery()
    {
        $unidadIds = \Illuminate\Support\Facades\DB::table('unidad_persona')
            ->where('persona_id', Auth::user()->persona_id)
            ->pluck('unidad_id')
            ->toArray();

        $query = Actividad::query()->where('activo', true);

        // Limitación jerárquica por unidad de acuerdo al rol
        if (Auth::user()->usuario_rol !== 'admin' && Auth::user()->usuario_rol !== 'auditor') {
            if (!empty($this->unidad_filtro) && in_array($this->unidad_filtro, $unidadIds)) {
                $query->where('unidad_id_asignada', $this->unidad_filtro);
            } else {
                $query->whereIn('unidad_id_asignada', $unidadIds);
            }
        } elseif (!empty($this->unidad_filtro)) {
            $query->where('unidad_id_asignada', $this->unidad_filtro);
        }

        if (!empty($this->actividad_id)) {
            $query->where('actividad_id', $this->actividad_id);
        }

        if (!empty($this->buscar)) {
            $query->where(function ($q) {
                $q->where('TIPO_ACTIVIDAD', 'like', '%' . $this->buscar . '%')
                    ->orWhere('SUB_TIPO_ACTIVIDAD', 'like', '%' . $this->buscar . '%')
                    ->orWhere('UNIDAD', 'like', '%' . $this->buscar . '%')
                    ->orWhere('DET_ACTIVIDAD', 'like', '%' . $this->buscar . '%');
            });
        }

        if (!empty($this->ano)) {
            $query->where('AÑO', $this->ano);
        }

        if (!empty($this->fecha_inicio)) {
            $query->where('FECHA', '>=', $this->fecha_inicio);
        }

        if (!empty($this->fecha_fin)) {
            $query->where('FECHA', '<=', $this->fecha_fin);
        }

        if (!empty($this->tipo)) {
            $query->where('TIPO_ACTIVIDAD', $this->tipo);
        }

        return $query->orderBy('FECHA', 'desc')->orderBy('actividad_id', 'desc');
    }

    public function exportSelected()
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'Debe seleccionar al menos una actividad para exportar.');
            return;
        }

        $unidadIds = \Illuminate\Support\Facades\DB::table('unidad_persona')
            ->where('persona_id', Auth::user()->persona_id)
            ->pluck('unidad_id')
            ->toArray();

        $query = Actividad::whereIn('actividad_id', $this->selectedIds);

        if (Auth::user()->usuario_rol !== 'admin' && Auth::user()->usuario_rol !== 'auditor') {
            $query->whereIn('unidad_id_asignada', $unidadIds);
        }

        $actividades = $query->orderBy('FECHA', 'desc')->get();

        $filename = 'reporte_actividades_' . now()->format('Ymd_His') . '.xls';

        return response()->streamDownload(function () use ($actividades) {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head><meta http-equiv="Content-type" content="text/html;charset=utf-8" /></head><body><table border="1"><tr>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">ID</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">Fecha Realización</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">Región</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">Tipo Unidad</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">Unidad Operativa</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">Tipo Actividad</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">Subtipo Actividad</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">Detalle / Descripción</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">N° Participantes</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">Ubicación</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">Observaciones</th>';
            echo '</tr>';

            foreach ($actividades as $act) {
                echo '<tr>';
                echo '<td>' . $act->actividad_id . '</td>';
                echo '<td>' . htmlspecialchars($act->FECHA) . '</td>';
                echo '<td>' . htmlspecialchars($act->REGION) . '</td>';
                echo '<td>' . htmlspecialchars($act->TIPO_UNIDAD) . '</td>';
                echo '<td>' . htmlspecialchars($act->UNIDAD) . '</td>';
                echo '<td>' . htmlspecialchars($act->TIPO_ACTIVIDAD) . '</td>';
                echo '<td>' . htmlspecialchars($act->SUB_TIPO_ACTIVIDAD) . '</td>';
                echo '<td>' . htmlspecialchars($act->DET_ACTIVIDAD) . '</td>';
                echo '<td>' . $act->PARTICIPANTES . '</td>';
                echo '<td>' . htmlspecialchars($act->ubicacion) . '</td>';
                echo '<td>' . htmlspecialchars($act->observacion) . '</td>';
                echo '</tr>';
            }
            echo '</table></body></html>';
        }, $filename);
    }

    public function with(): array
    {
        $perPage = 25;
        if (!empty($this->fecha_inicio) || !empty($this->fecha_fin)) {
            $perPage = 100;
        } elseif (!empty($this->ano)) {
            $perPage = 50;
        }

        $query = $this->getFilteredActivitiesQuery();
        $totalResults = $query->count();
        $actividades = $query->paginate($perPage);

        $userUnidadId = Auth::user()->unidad_id;
        $userRol = Auth::user()->rol;

        $monthQuery = Actividad::where('activo', true);
        if ($userRol !== 'admin' && $userRol !== 'auditor') {
            $monthQuery->where('unidad_id_asignada', $userUnidadId);
        }

        $monthCounts = $monthQuery->selectRaw("SUBSTRING_INDEX(FECHA, '-', -2) as ym, count(*) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym')
            ->toArray();

        // Cargar las unidades asociadas al usuario autenticado para el filtro dinámico
        $unidadesAsignadas = [];
        if ($userRol === 'admin' || $userRol === 'auditor') {
            $unidadesAsignadas = \App\Models\Unidad::orderBy('unidad_nombre', 'asc')->get();
        } else {
            $unidadesAsignadas = \App\Models\Unidad::where('unidad_id', $userUnidadId)->orderBy('unidad_nombre', 'asc')->get();
        }

        return [
            'actividades' => $actividades,
            'monthCounts' => $monthCounts,
            'totalResults' => $totalResults,
            'unidadesAsignadas' => $unidadesAsignadas,
            'isDateRangeActive' => (!empty($this->fecha_inicio) || !empty($this->fecha_fin)),
        ];
    }
}; ?>

<div x-data="{ advancedOpen: false }">
    <!-- 1. Filtros Básicos y Avanzados -->
    @include('livewire.actividades.partials.filtros')

    <!-- 2. Barra de Control y Acciones Masivas -->
    @include('livewire.actividades.partials.barra-acciones')

    @if ($isDateRangeActive)
    <div
        style="margin-bottom: 20px; font-weight: 600; color: #0d1b2a; font-size: 0.95rem; background-color: #f1f5f9; padding: 12px 20px; border-radius: 6px;">
        🔍 Resultados en el Rango de Fechas: {{ $totalResults }} actividades encontradas.
    </div>
    @endif

    <!-- 3. Contenedor de Listado de Actividades -->
    <div id="actividades-container">
        @if ($actividades->isEmpty())
        <div
            style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 40px; text-align: center; color: #64748b;">
            <span style="font-size: 1.5rem;">📁</span>
            <p style="margin: 10px 0 0; font-weight: 500;">No se encontraron reportes con los criterios de búsqueda
                seleccionados.</p>
        </div>
        @else
        @php $lastMonthYear = null; @endphp
        @foreach ($actividades as $act)
        @php
        $actDate = \Carbon\Carbon::parse($act->fecha_actividad);
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

    <!-- Scripts de Portapapeles -->
    @include('livewire.actividades.partials.scripts-copiado')
</div>