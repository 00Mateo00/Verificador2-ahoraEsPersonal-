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

    #[Url(as: 'reg')]
    public string $region = '';

    #[Url(as: 'uni')]
    public string $tipo_unidad = '';

    #[Url(as: 'act')]
    public string $tipo = '';

    // ID seleccionado desde Deep Link
    #[Url(as: 'id')]
    public string $actividad_id = '';

    // Selección múltiple para exportación
    public array $selectedIds = [];
    public bool $selectAll = false;

    // Reiniciar paginación al cambiar filtros
    public function updatedBuscar() { $this->resetPage(); }
    public function updatedAno() { $this->resetPage(); }
    public function updatedFechaInicio() { $this->resetPage(); }
    public function updatedFechaFin() { $this->resetPage(); }
    public function updatedRegion() { $this->resetPage(); }
    public function updatedTipoUnidad() { $this->resetPage(); }
    public function updatedTipo() { $this->resetPage(); }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedIds = $this->getFilteredActivitiesQuery()
                ->pluck('actividad_id')
                ->map(fn($id) => (string)$id)
                ->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    private function getFilteredActivitiesQuery()
    {
        $query = Actividad::where('usuario_id', Auth::id())
            ->where('activo', true);

        if (!empty($this->actividad_id)) {
            $query->where('actividad_id', $this->actividad_id);
        }

        if (!empty($this->buscar)) {
            $query->where(function($q) {
                $q->where('nombre_actividad', 'like', '%' . $this->buscar . '%')
                  ->orWhere('unidad_operativa', 'like', '%' . $this->buscar . '%')
                  ->orWhere('objetivo', 'like', '%' . $this->buscar . '%');
            });
        }

        if (!empty($this->ano)) {
            $query->whereYear('fecha_actividad', $this->ano);
        }

        if (!empty($this->fecha_inicio)) {
            $query->where('fecha_actividad', '>=', $this->fecha_inicio);
        }

        if (!empty($this->fecha_fin)) {
            $query->where('fecha_actividad', '<=', $this->fecha_fin);
        }

        if (!empty($this->region)) {
            $query->where('region', $this->region);
        }

        if (!empty($this->tipo_unidad)) {
            $query->where('tipo_unidad', $this->tipo_unidad);
        }

        if (!empty($this->tipo)) {
            $query->where('tipo', $this->tipo);
        }

        return $query->orderBy('fecha_actividad', 'desc')->orderBy('actividad_id', 'desc');
    }

    public function exportSelected()
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'Debe seleccionar al menos una actividad para exportar.');
            return;
        }

        $actividades = Actividad::whereIn('actividad_id', $this->selectedIds)
            ->where('usuario_id', Auth::id())
            ->orderBy('fecha_actividad', 'desc')
            ->get();

        $filename = "reporte_actividades_" . now()->format('Ymd_His') . ".xls";

        return response()->streamDownload(function () use ($actividades) {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head><meta http-equiv="Content-type" content="text/html;charset=utf-8" /></head><body><table border="1"><tr>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">ID</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">Fecha Realización</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">Región</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">Tipo Unidad</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">Unidad Operativa</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">Tipo Actividad</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">Nombre Actividad</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">Objetivo</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">N° Participantes</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">Ubicación</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">Observaciones</th>';
            echo '</tr>';

            foreach ($actividades as $act) {
                echo '<tr>';
                echo '<td>' . $act->actividad_id . '</td>';
                echo '<td>' . \Carbon\Carbon::parse($act->fecha_actividad)->format('d-m-Y') . '</td>';
                echo '<td>' . htmlspecialchars($act->region) . '</td>';
                echo '<td>' . htmlspecialchars($act->tipo_unidad) . '</td>';
                echo '<td>' . htmlspecialchars($act->unidad_operativa) . '</td>';
                echo '<td>' . htmlspecialchars($act->tipo) . '</td>';
                echo '<td>' . htmlspecialchars($act->nombre_actividad) . '</td>';
                echo '<td>' . htmlspecialchars($act->objetivo) . '</td>';
                echo '<td>' . $act->n_participantes . '</td>';
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

        $monthCounts = Actividad::where('usuario_id', Auth::id())
            ->where('activo', true)
            ->selectRaw("DATE_FORMAT(fecha_actividad, '%Y-%m') as ym, count(*) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym')
            ->toArray();

        return [
            'actividades' => $actividades,
            'monthCounts' => $monthCounts,
            'totalResults' => $totalResults,
            'isDateRangeActive' => (!empty($this->fecha_inicio) || !empty($this->fecha_fin)),
        ];
    }
}; ?>

<div x-data="{ advancedOpen: false }">
    <!-- 1. Filtros Básicos y Avanzados -->
    @include('livewire.actividades.partials.filtros')

    <!-- 2. Barra de Control y Acciones Masivas -->
    @include('livewire.actividades.partials.barra-acciones')

    @if($isDateRangeActive)
        <div style="margin-bottom: 20px; font-weight: 600; color: #0d1b2a; font-size: 0.95rem; background-color: #f1f5f9; padding: 12px 20px; border-radius: 6px;">
            🔍 Resultados en el Rango de Fechas: {{ $totalResults }} actividades encontradas.
        </div>
    @endif

    <!-- 3. Contenedor de Listado de Actividades -->
    <div id="actividades-container">
        @if($actividades->isEmpty())
            <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 40px; text-align: center; color: #64748b;">
                <span style="font-size: 1.5rem;">📁</span>
                <p style="margin: 10px 0 0; font-weight: 500;">No se encontraron reportes con los criterios de búsqueda seleccionados.</p>
            </div>
        @else
            @php $lastMonthYear = null; @endphp
            @foreach($actividades as $act)
                @php
                    $actDate = \Carbon\Carbon::parse($act->fecha_actividad);
                    $monthYearKey = $actDate->format('Y-m');
                @endphp

                <!-- Separador de Línea Temporal (Mes / Año) -->
                @if(!$isDateRangeActive && $lastMonthYear !== $monthYearKey)
                    @php
                        $lastMonthYear = $monthYearKey;
                        $totalMonthCount = $monthCounts[$monthYearKey] ?? 0;
                        $monthLabel = str_replace(
                            ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                            ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                            $actDate->format('F')
                        );
                    @endphp
                    <div style="margin: 30px 0 15px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px dashed #cbd5e1; padding-bottom: 8px;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">
                            📅 {{ $actDate->format('Y') }} - {{ $monthLabel }}
                        </span>
                        <span style="font-size: 0.75rem; font-weight: 600; color: #64748b; background-color: #f1f5f9; padding: 3px 8px; border-radius: 20px;">
                            {{ $totalMonthCount }} {{ $totalMonthCount == 1 ? 'actividad' : 'actividades' }}
                        </span>
                    </div>
                @endif

                <!-- Tarjeta Individual de Actividad (Acordeón) -->
                @include('livewire.actividades.partials.actividad-card', ['act' => $act, 'actDate' => $actDate])
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