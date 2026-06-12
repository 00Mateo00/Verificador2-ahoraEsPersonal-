<?php

namespace App\Livewire\Actividades;

use App\Models\Actividad;
use App\Models\Archivo;
use App\Models\Unidad;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class VerificarPendientes extends Component
{
    use WithFileUploads, WithPagination;

    // Almacena temporalmente los archivos de subida mapeados por ID de actividad
    public $verificadores = [];

    /**
     * Valida y procesa la verificación de una actividad específica.
     */
    public function verificarActividad($actividadId)
    {
        $this->validate([
            'verificadores.'.$actividadId => 'required|array|min:1',
            'verificadores.'.$actividadId.'.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg|max:5120', // Límite de 5MB y formatos seguros
        ], [
            'verificadores.'.$actividadId.'.required' => 'Debe adjuntar al menos un archivo verificador para comprobar la realización.',
            'verificadores.'.$actividadId.'.*.mimes' => 'El archivo debe tener un formato válido y seguro (PDF, Word, Excel, PNG, JPG).',
            'verificadores.'.$actividadId.'.*.max' => 'Los archivos no deben superar los 5MB.',
        ]);

        // Recuperar la unidad asociada al usuario autenticado usando query() explícito
        $unidad = Unidad::query()->where('user_id', Auth::id())->first();
        $unidadId = $unidad ? $unidad->id : null;

        // Asegurar por integridad que la actividad pertenezca a la unidad del usuario y esté CARGADA
        $actividad = Actividad::query()->where('estado', 'CARGADA')
            ->where('unidad_id_asignada', $unidadId)
            ->findOrFail($actividadId);

        // Actualizar estado
        $actividad->update([
            'estado' => 'VERIFICADA',
        ]);

        // Guardar cada archivo adjunto de forma física y referencial
        foreach ($this->verificadores[$actividadId] as $archivo) {
            // Laravel hashea el nombre del archivo en disco de forma segura por defecto para prevenir Directory Traversal
            $path = $archivo->store('uploads', 'public');

            // Sanitizar el nombre original del archivo para mitigar ataques XSS persistentes al renderizar el nombre
            $originalName = $archivo->getClientOriginalName();
            $filename = pathinfo($originalName, PATHINFO_FILENAME);
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $sanitizedFilename = Str::slug($filename).'.'.$extension;

            Archivo::create([
                'actividad_id' => $actividad->actividad_id,
                'archivo_nombre' => $sanitizedFilename,
                'archivo_ruta' => $path,
                'archivo_tipo' => $archivo->getMimeType(),
                'archivo_size' => $archivo->getSize(),
            ]);
        }

        unset($this->verificadores[$actividadId]);
        session()->flash('success', 'La actividad #'.$actividadId.' ha sido verificada y guardada con éxito.');
    }

    public function render()
    {
        // Recuperar la unidad asociada al usuario autenticado usando query() explícito
        $unidad = Unidad::query()->where('user_id', Auth::id())->first();
        $unidadId = $unidad ? $unidad->id : null;

        // Mostrar solo las actividades cargadas por el excel que pertenezcan a la unidad asignada
        $actividades = $unidadId
            ? Actividad::query()->where('estado', 'CARGADA')
                ->where('unidad_id_asignada', $unidadId)
                ->orderBy('FECHA', 'desc')
                ->paginate(10)
            : collect(); // Retornar colección vacía si el usuario no tiene una unidad operativa asignada

        return view('livewire.actividades.verificar-pendientes', [
            'actividades' => $actividades,
        ]);
    }
}
