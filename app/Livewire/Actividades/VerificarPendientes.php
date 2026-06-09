<?php

namespace App\Livewire\Actividades;

use Livewire\Component;
use App\Models\Actividad;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class VerificarPendientes extends Component
{
    use WithPagination, WithFileUploads;

    // Almacena temporalmente los archivos de subida mapeados por ID de actividad
    public $verificadores = [];

    /**
     * Valida y procesa la verificación de una actividad específica.
     */
    public function verificarActividad($actividadId)
    {
        $this->validate([
            'verificadores.' . $actividadId => 'required|array|min:1',
            'verificadores.' . $actividadId . '.*' => 'file|max:5120', // Límite de 5MB por archivo
        ], [
            'verificadores.' . $actividadId . '.required' => 'Debe adjuntar al menos un archivo verificador para comprobar la realización.',
            'verificadores.' . $actividadId . '.*.max' => 'Los archivos no deben superar los 5MB.'
        ]);

        // Asegurar por integridad que la actividad pertenezca a la unidad del usuario y esté CARGADA
        $actividad = Actividad::where('estado', 'CARGADA')
            ->where('unidad_id_asignada', Auth::user()->unidad_id)
            ->findOrFail($actividadId);

        // Actualizar estado y asignar al funcionario de la unidad
        $actividad->update([
            'estado' => 'VERIFICADA',
        ]);

        // Guardar cada archivo adjunto de forma física y referencial
        foreach ($this->verificadores[$actividadId] as $archivo) {
            $path = $archivo->store('uploads', 'public');

            \App\Models\Archivo::create([
                'actividad_id' => $actividad->actividad_id,
                'archivo_nombre' => $archivo->getClientOriginalName(),
                'archivo_ruta' => $path,
                'archivo_tipo' => $archivo->getMimeType(),
                'archivo_size' => $archivo->getSize(),
            ]);
        }

        unset($this->verificadores[$actividadId]);
        session()->flash('success', 'La actividad #' . $actividadId . ' ha sido verificada y guardada con éxito.');
    }

    public function render()
    {
        $unidadId = Auth::user()->unidad_id;

        // Mostrar solo las actividades cargadas por el excel que pertenezcan a la unidad asignada
        $actividades = Actividad::where('estado', 'CARGADA')
            ->where('unidad_id_asignada', $unidadId)
            ->orderBy('FECHA', 'desc')
            ->paginate(10);

        return view('livewire.actividades.verificar-pendientes', [
            'actividades' => $actividades
        ]);
    }
}
