<?php

namespace App\Livewire\Actividades;

use App\Models\Actividad;
use App\Models\Archivo;
use App\Models\Region;
use App\Models\Unidad;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ConsultaList extends Component
{
    use WithFileUploads, WithPagination;

    // Propiedades para administración interactiva de verificadores (Modo Edición)
    public $nuevosVerificadores = [];

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

    /**
     * Aplica las restricciones de visibilidad de manera centralizada de acuerdo al rol del usuario.
     */
    private function applyRoleRestrictions($query, string $userRol): void
    {
        if ($userRol === 'unidad') {
            $unidad = Unidad::query()->where('user_id', Auth::id())->first();
            $userUnidadId = $unidad ? $unidad->id : null;
            $query->where('unidad_id_asignada', $userUnidadId);
        } elseif ($userRol === 'director') {
            $region = Region::query()->where('user_id', Auth::id())->first();
            $regionId = $region ? $region->id : null;
            $unidadIds = $regionId ? Unidad::query()->where('region_id', $regionId)->pluck('id')->toArray() : [];

            if (! empty($this->unidad_filtro) && in_array($this->unidad_filtro, $unidadIds)) {
                $query->where('unidad_id_asignada', $this->unidad_filtro);
            } else {
                $query->whereIn('unidad_id_asignada', $unidadIds);
            }
        } else {
            // Admin, Auditor, Cargador (Acceso global)
            if (! empty($this->unidad_filtro)) {
                $query->where('unidad_id_asignada', $this->unidad_filtro);
            }
        }
    }

    private function getFilteredActivitiesQuery()
    {
        $userRol = Auth::user()->rol;

        $query = Actividad::query()
            ->where('activo', true)
            ->where('estado', 'VERIFICADA');

        // Limitación jerárquica por unidad de acuerdo al rol usando el helper centralizado
        $this->applyRoleRestrictions($query, $userRol);

        if (! empty($this->actividad_id)) {
            $query->where('actividad_id', $this->actividad_id);
        }

        if (! empty($this->buscar)) {
            $query->where(function ($q) {
                $q->where('TIPO_ACTIVIDAD', 'like', '%'.$this->buscar.'%')
                    ->orWhere('SUB_TIPO_ACTIVIDAD', 'like', '%'.$this->buscar.'%')
                    ->orWhere('UNIDAD', 'like', '%'.$this->buscar.'%')
                    ->orWhere('DET_ACTIVIDAD', 'like', '%'.$this->buscar.'%');
            });
        }

        if (! empty($this->ano)) {
            $query->where('AÑO', $this->ano);
        }

        if (! empty($this->fecha_inicio)) {
            $query->where('FECHA', '>=', $this->fecha_inicio);
        }

        if (! empty($this->fecha_fin)) {
            $query->where('FECHA', '<=', $this->fecha_fin);
        }

        if (! empty($this->tipo)) {
            $query->where('TIPO_ACTIVIDAD', $this->tipo);
        }

        return $query->with(['archivos', 'unidadAsignada'])
            ->orderBy('FECHA', 'desc')
            ->orderBy('actividad_id', 'desc');
    }

    /**
     * Eliminar de forma permanente un archivo verificador (Solo Administradores en Modo Edición).
     */
    public function eliminarArchivo($archivoId)
    {
        // Defensa: Bloquear mutación si no es admin en modo edición
        if (Auth::user()->rol !== 'admin' || ! session('modo_edicion')) {
            abort(403, 'No autorizado para realizar esta acción.');
        }

        $archivo = Archivo::find($archivoId);
        if ($archivo) {
            // Eliminar físico del almacenamiento privado
            if (Storage::disk('local')->exists($archivo->archivo_ruta)) {
                Storage::disk('local')->delete($archivo->archivo_ruta);
            }

            $archivo->delete();
            session()->flash('success', 'El archivo verificador ha sido eliminado con éxito de forma administrativa.');
        }
    }

    /**
     * Adjuntar un nuevo archivo verificador a una actividad (Solo Administradores en Modo Edición).
     */
    public function adjuntarVerificadorAdministrativo($actividadId)
    {
        // Defensa: Bloquear mutación si no es admin en modo edición
        if (Auth::user()->rol !== 'admin' || ! session('modo_edicion')) {
            abort(403, 'No autorizado para realizar esta acción.');
        }

        $this->validate([
            'nuevosVerificadores' => 'required|array|min:1',
            'nuevosVerificadores.*' => 'file|mimes:pdf,doc,docx,png,jpg,jpeg|max:5120',
        ], [
            'nuevosVerificadores.required' => 'Debe adjuntar al menos un archivo.',
            'nuevosVerificadores.*.mimes' => 'Formato no permitido (Use PDF, Word o Imágenes).',
            'nuevosVerificadores.*.max' => 'Los archivos no deben superar los 5MB.',
        ]);

        $actividad = Actividad::find($actividadId);
        if (! $actividad) {
            session()->flash('error', 'Actividad no encontrada.');

            return;
        }

        // Si la actividad estaba en estado CARGADA, transiciona de forma automática a VERIFICADA
        if ($actividad->estado === 'CARGADA') {
            $actividad->update(['estado' => 'VERIFICADA']);
        }

        foreach ($this->nuevosVerificadores as $archivo) {
            $originalName = $archivo->getClientOriginalName();
            $mimeType = $archivo->getMimeType();
            $size = $archivo->getSize();

            $filename = pathinfo($originalName, PATHINFO_FILENAME);
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $sanitizedFilename = Str::slug($filename).'.'.$extension;

            // Guardar en el almacenamiento privado local
            $path = $archivo->store('uploads', 'local');

            Archivo::create([
                'actividad_id' => $actividad->actividad_id,
                'archivo_nombre' => $sanitizedFilename,
                'archivo_ruta' => $path,
                'archivo_tipo' => $mimeType,
                'archivo_size' => $size,
            ]);
        }

        $this->reset('nuevosVerificadores');
        session()->flash('success', 'El archivo verificador ha sido adjuntado e indexado con éxito de forma administrativa.');
    }

    public function render()
    {
        $perPage = 25;
        if (! empty($this->fecha_inicio) || ! empty($this->fecha_fin)) {
            $perPage = 100;
        } elseif (! empty($this->ano)) {
            $perPage = 50;
        }

        $query = $this->getFilteredActivitiesQuery();
        $totalResults = $query->count();
        $actividades = $query->paginate($perPage);

        $userRol = Auth::user()->rol;

        $monthQuery = Actividad::query()
            ->where('activo', true)
            ->where('estado', 'VERIFICADA');

        // Aplicar restricciones de rol centralizadas
        $this->applyRoleRestrictions($monthQuery, $userRol);

        $monthCounts = $monthQuery->selectRaw("SUBSTRING_INDEX(FECHA, '-', -2) as ym, count(*) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym')
            ->toArray();

        // Cargar las unidades asociadas al usuario autenticado para el filtro dinámico
        $unidadesAsignadas = [];
        if ($userRol === 'admin' || $userRol === 'auditor') {
            $unidadesAsignadas = Unidad::query()
                ->join('users', 'unidad.user_id', '=', 'users.id')
                ->orderBy('users.name', 'asc')
                ->select('unidad.*', 'users.name as unidad_nombre')
                ->get();
        } elseif ($userRol === 'director') {
            $region = Region::query()->where('user_id', Auth::id())->first();
            $regionId = $region ? $region->id : null;
            $unidadesAsignadas = Unidad::query()
                ->join('users', 'unidad.user_id', '=', 'users.id')
                ->where('unidad.region_id', $regionId)
                ->orderBy('users.name', 'asc')
                ->select('unidad.*', 'users.name as unidad_nombre')
                ->get();
        } else {
            // Rol Unidad: Solo ve su propia unidad
            $unidadesAsignadas = Unidad::query()
                ->join('users', 'unidad.user_id', '=', 'users.id')
                ->where('unidad.user_id', Auth::id())
                ->orderBy('users.name', 'asc')
                ->select('unidad.*', 'users.name as unidad_nombre')
                ->get();
        }

        return view('livewire.actividades.consulta-list', [
            'actividades' => $actividades,
            'monthCounts' => $monthCounts,
            'totalResults' => $totalResults,
            'unidadesAsignadas' => $unidadesAsignadas,
            'isDateRangeActive' => (! empty($this->fecha_inicio) || ! empty($this->fecha_fin)),
        ]);
    }
}
