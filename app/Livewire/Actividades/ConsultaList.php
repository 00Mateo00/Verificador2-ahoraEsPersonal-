<?php

namespace App\Livewire\Actividades;

use App\Enums\UserRole;
use App\Livewire\PaginatedComponent;
use App\Models\Actividad;
use App\Models\Archivo;
use App\Models\CargaExcel;
use App\Models\Region;
use App\Models\Scopes\StatisticalYearScope;
use App\Models\Unidad;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\WithFileUploads;

class ConsultaList extends PaginatedComponent
{
    use WithFileUploads;

    // Propiedades para administración interactiva de verificadores (Modo Edición)
    public $nuevosVerificadores = [];

    // Filtros de URL (Query Params)
    #[Url(as: 'q')]
    public string $buscar = '';

    #[Url(as: 'ano')]
    public string $ano = '';

    #[Url(as: 'mes')]
    public string $mes = '';

    #[Url(as: 'act')]
    public string $tipo = '';

    #[Url(as: 'uf')]
    public string $unidad_filtro = '';

    #[Url(as: 'dir')]
    public string $director_filtro = '';

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

    public function updatedMes()
    {
        $this->resetPage();
    }

    public function updatedUnidadFiltro()
    {
        $this->resetPage();
    }

    public function updatedDirectorFiltro()
    {
        $this->resetPage();
    }

    public function updatedTipo()
    {
        $this->resetPage();
    }

    private function getFilteredActivitiesQuery()
    {
        $user = Auth::user();

        $query = Actividad::query();

        // Si se selecciona un año específico en el filtro, omitimos el Scope del Año Estadístico actual
        if (! empty($this->ano)) {
            $query->withoutGlobalScope(StatisticalYearScope::class)->where('AÑO', $this->ano);
        }

        $query->where('activo', true)
            ->where('estado', 'VERIFICADA')
            ->forUser($user, (int) $this->unidad_filtro ?: null);

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

        if (! empty($this->mes)) {
            $query->where('MES', $this->mes);
        }

        if (! empty($this->tipo)) {
            $query->where('TIPO_ACTIVIDAD', $this->tipo);
        }

        if (! empty($this->director_filtro) && ($user->rol === UserRole::Admin || $user->rol === UserRole::Auditor)) {
            $region = Region::where('user_id', $this->director_filtro)->first();
            $unidadIds = $region ? Unidad::where('region_id', $region->id)->pluck('id')->toArray() : [];
            $query->whereIn('unidad_id_asignada', $unidadIds);
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
        if (Auth::user()->rol !== UserRole::Admin || ! session('modo_edicion')) {
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
     * Desactivar una actividad de forma permanente (Solo Administradores en Modo Edición).
     */
    public function desactivarActividad($actividadId)
    {
        if (Auth::user()->rol !== UserRole::Admin || ! session('modo_edicion')) {
            abort(403, 'No autorizado para realizar esta acción.');
        }

        $actividad = Actividad::find($actividadId);
        if ($actividad) {
            $actividad->update(['activo' => false]);
            session()->flash('success', 'La actividad ha sido desactivada del sistema con éxito.');
        }
    }

    /**
     * Adjuntar un nuevo archivo verificador a una actividad (Solo Administradores en Modo Edición).
     */
    public function adjuntarVerificadorAdministrativo($actividadId)
    {
        // Defensa: Bloquear mutación si no es admin en modo edición
        if (Auth::user()->rol !== UserRole::Admin || ! session('modo_edicion')) {
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
        $user = Auth::user();
        $userRol = $user->rol;

        // Si es Cargador, se muestra únicamente su historial de cargas agrupadas
        if ($userRol === UserRole::Cargador) {
            $cargasAgrupadas = CargaExcel::where('user_id', $user->id)
                ->with(['actividades' => function ($q) {
                    $q->where('activo', true);
                }])
                ->latest()
                ->paginate(10);

            return view('livewire.actividades.consulta-list', [
                'cargasAgrupadas' => $cargasAgrupadas,
            ]);
        }

        $perPage = 25;
        if (! empty($this->mes)) {
            $perPage = 100;
        } elseif (! empty($this->ano)) {
            $perPage = 50;
        }

        $query = $this->getFilteredActivitiesQuery();
        $totalResults = $query->count();
        $actividades = $query->paginate($perPage);

        $monthQuery = Actividad::query();

        if (! empty($this->ano)) {
            $monthQuery->withoutGlobalScope(StatisticalYearScope::class)->where('AÑO', $this->ano);
        }

        $monthQuery->where('activo', true)
            ->where('estado', 'VERIFICADA')
            ->forUser($user, (int) $this->unidad_filtro ?: null);

        $monthCounts = $monthQuery->selectRaw("SUBSTRING_INDEX(FECHA, '-', -2) as ym, count(*) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym')
            ->toArray();

        // Cargar las unidades asociadas al usuario autenticado para el filtro dinámico
        $unidadesAsignadas = [];
        if ($userRol === UserRole::Admin || $userRol === UserRole::Auditor) {
            $unidadesAsignadas = Unidad::query()
                ->join('users', 'unidad.user_id', '=', 'users.id')
                ->orderBy('users.name', 'asc')
                ->select('unidad.*', 'users.name as unidad_nombre')
                ->get();
        } elseif ($userRol === UserRole::Director) {
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

        // Cargar directores regionales para el filtro de Auditor / Admin
        $directoresRegionales = [];
        if ($userRol === UserRole::Admin || $userRol === UserRole::Auditor) {
            $directoresRegionales = User::where('rol', UserRole::Director)->orderBy('name', 'asc')->get();
        }

        return view('livewire.actividades.consulta-list', [
            'actividades' => $actividades,
            'monthCounts' => $monthCounts,
            'totalResults' => $totalResults,
            'unidadesAsignadas' => $unidadesAsignadas,
            'directoresRegionales' => $directoresRegionales,
            'isDateRangeActive' => ! empty($this->mes),
        ]);
    }
}
