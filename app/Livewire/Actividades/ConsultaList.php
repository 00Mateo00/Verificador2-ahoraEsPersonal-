<?php

namespace App\Livewire\Actividades;

use Livewire\Component;
use App\Models\Actividad;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class ConsultaList extends Component
{
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
        $userUnidadId = Auth::user()->unidad_id;
        $userRol = Auth::user()->rol;

        $query = Actividad::query()->where('activo', true);

        // Limitación jerárquica por unidad de acuerdo al rol
        if ($userRol !== 'admin' && $userRol !== 'auditor') {
            if (!empty($this->unidad_filtro) && $this->unidad_filtro == $userUnidadId) {
                $query->where('unidad_id_asignada', $this->unidad_filtro);
            } else {
                $query->where('unidad_id_asignada', $userUnidadId);
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

        $userUnidadId = Auth::user()->unidad_id;
        $userRol = Auth::user()->rol;

        $query = Actividad::whereIn('actividad_id', $this->selectedIds);

        if ($userRol !== 'admin' && $userRol !== 'auditor') {
            $query->where('unidad_id_asignada', $userUnidadId);
        }

        $actividades = $query->orderBy('FECHA', 'desc')->get();

        $filename = 'reporte_actividades_' . now()->format('Ymd_His') . '.xls';

        return response()->streamDownload(function () use ($actividades) {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head><meta http-equiv="Content-type" content="text/html;charset=utf-8" /></head><body><table border="1"><tr>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">ID</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">Fecha Realización</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">Unidad Operativa</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">Tipo Actividad</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">Subtipo Actividad</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">Detalle / Descripción</th>';
            echo '<th style="background-color: #0F69C4; color: #ffffff;">N° Participantes</th>';
            echo '</tr>';

            foreach ($actividades as $act) {
                echo '<tr>';
                echo '<td>' . $act->actividad_id . '</td>';
                echo '<td>' . htmlspecialchars($act->FECHA) . '</td>';
                echo '<td>' . htmlspecialchars($act->REGION) . '</td>';
                echo '<td>' . htmlspecialchars($act->UNIDAD) . '</td>';
                echo '<td>' . htmlspecialchars($act->TIPO_ACTIVIDAD) . '</td>';
                echo '<td>' . htmlspecialchars($act->SUB_TIPO_ACTIVIDAD) . '</td>';
                echo '<td>' . htmlspecialchars($act->DET_ACTIVIDAD) . '</td>';
                echo '<td>' . $act->PARTICIPANTES . '</td>';
                echo '</tr>';
            }
            echo '</table></body></html>';
        }, $filename);
    }

    public function render()
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

        return view('livewire.actividades.consulta-list', [
            'actividades' => $actividades,
            'monthCounts' => $monthCounts,
            'totalResults' => $totalResults,
            'unidadesAsignadas' => $unidadesAsignadas,
            'isDateRangeActive' => (!empty($this->fecha_inicio) || !empty($this->fecha_fin)),
        ]);
    }
}
