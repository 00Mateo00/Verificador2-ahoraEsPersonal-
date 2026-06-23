<?php

namespace App\Livewire\Actividades;

use App\Models\Actividad;
use App\Models\Archivo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class VerificarActividadCard extends Component
{
    use WithFileUploads;

    public Actividad $act;

    public $verificador = []; // local single array of files for this isolated card

    public function mount(Actividad $act)
    {
        $this->act = $act;
    }

    public function verificarActividad()
    {
        // Defensa en profundidad: Bloquear mutación si el rol del usuario es auditor
        Gate::authorize('mutate');

        // Validar autorización horizontal estricta sobre la actividad asignada
        Gate::authorize('update', $this->act);

        // 1. Candado atómico en servidor independiente
        $lockKey = 'lock_verificar_act_'.Auth::id().'_'.$this->act->actividad_id;
        if (! Cache::add($lockKey, true, 2)) {
            session()->add('error', 'Se está procesando otra solicitud. Por favor, espere.');

            return;
        }

        try {
            $this->validate([
                'verificador' => 'required|array|min:1',
                'verificador.*' => 'file|mimes:pdf,doc,docx,png,jpg,jpeg|max:5120',
            ], [
                'verificador.required' => 'Debe adjuntar al menos un archivo verificador.',
                'verificador.*.mimes' => 'El archivo debe tener un formato válido y seguro (PDF, Word, PNG, JPG).',
                'verificador.*.max' => 'Los archivos no deben superar los 5MB.',
            ]);

            // Validar de forma defensiva que todos los elementos subidos sean legibles
            foreach ($this->verificador as $archivo) {
                if (! $archivo || ! $archivo->isValid()) {
                    throw new \Exception('Uno de los archivos adjuntos se encuentra corrupto o la subida fue interrumpida.');
                }
            }

            // Actualizar estado de la actividad de forma aislada
            $this->act->update([
                'estado' => 'VERIFICADA',
            ]);

            // Persistencia segura de archivos en disco local (privado por defecto)
            foreach ($this->verificador as $archivo) {
                // 1. Obtener metadatos antes de mover o guardar el archivo temporal
                $originalName = $archivo->getClientOriginalName();
                $mimeType = $archivo->getMimeType();
                $size = $archivo->getSize();

                $filename = pathinfo($originalName, PATHINFO_FILENAME);
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                $sanitizedFilename = Str::slug($filename).'.'.$extension;

                // 2. Guardar el archivo físicamente en el storage local
                $path = $archivo->store('uploads', 'local');

                // 3. Registrar en base de datos
                Archivo::create([
                    'actividad_id' => $this->act->actividad_id,
                    'archivo_nombre' => $sanitizedFilename,
                    'archivo_ruta' => $path,
                    'archivo_tipo' => $mimeType,
                    'archivo_size' => $size,
                ]);
            }

            $this->reset('verificador');

            // Notificar al componente padre para refrescar la lista paginada de pendientes
            $this->dispatch('actividad-verificada');

            session()->flash('success', 'La actividad #'.$this->act->actividad_id.' ha sido verificada y guardada con éxito.');

        } catch (ValidationException $e) {
            $this->reset('verificador'); // Limpiar archivos corruptos tras fallar validación
            throw $e;
        } catch (\Throwable $e) {
            $this->reset('verificador'); // Limpiar estado de archivos para permitir reintentos
            session()->flash('error', 'Error al procesar archivos verificadores: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.actividades.verificar-actividad-card');
    }
}
