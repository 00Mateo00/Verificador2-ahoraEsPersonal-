<?php

namespace App\Livewire\Actividades;

use Livewire\Component;
use App\Models\Actividad;
use App\Models\Archivo;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasStepWizard;

class CreateForm extends Component
{
    use WithFileUploads, HasStepWizard;

    public $archivos = [];

    // Propiedades del formulario
    public string $region = '';
    public string $tipo_unidad = '';
    public string $unidad_operativa = '';
    public string $tipo = '';
    public string $nombre_actividad = '';
    public string $objetivo = '';
    public int $n_participantes = 1;
    public string $fecha_actividad = '';
    public string $ubicacion = '';
    public string $observacion = '';

    // Paso declarativo central (Fuente de verdad del Wizard)
    public function steps(): array
    {
        return [
            1 => [
                'label' => 'Datos generales',
                'rules' => [
                    'region' => 'required|string',
                    'tipo_unidad' => 'required|string',
                    'unidad_operativa' => 'required|string|max:150',
                    'tipo' => 'required|string',
                ],
            ],
            2 => [
                'label' => 'Detalles de actividad',
                'rules' => [
                    'nombre_actividad' => 'required|string|max:100',
                    'objetivo' => 'required|string|max:200',
                    'n_participantes' => 'required|integer|min:1',
                    'fecha_actividad' => 'required|date',
                    'ubicacion' => 'required|string|max:150',
                    'observacion' => 'nullable|string|max:200',
                ],
            ],
            3 => [
                'label' => 'Archivos de respaldo',
                'rules' => [
                    'archivos' => 'nullable|array',
                    'archivos.*' => 'nullable|file|max:5120',
                ],
            ],
            4 => [
                'label' => 'Resumen y Confirmación',
                'rules' => [],
            ],
        ];
    }

    public function mount()
    {
        $this->initializeStepWizard();
    }

    public function removeFile($index)
    {
        array_splice($this->archivos, $index, 1);
    }

    public function save()
    {
        // Validación completa de seguridad
        $allRules = [];
        foreach ($this->steps() as $step) {
            $allRules = array_merge($allRules, $step['rules']);
        }
        $this->validate($allRules);

        $actividad = Actividad::create([
            'region' => $this->region,
            'tipo_unidad' => $this->tipo_unidad,
            'unidad_operativa' => $this->unidad_operativa,
            'tipo' => $this->tipo,
            'nombre_actividad' => $this->nombre_actividad,
            'objetivo' => $this->objetivo,
            'n_participantes' => $this->n_participantes,
            'ubicacion' => $this->ubicacion,
            'observacion' => $this->observacion,
            'activo' => true,
            'fecha_actividad' => $this->fecha_actividad,
        ]);

        foreach ($this->archivos as $archivo) {
            $path = $archivo->store('uploads', 'public');

            Archivo::create([
                'actividad_id' => $actividad->actividad_id,
                'archivo_nombre' => $archivo->getClientOriginalName(),
                'archivo_ruta' => $path,
                'archivo_tipo' => $archivo->getMimeType(),
                'archivo_size' => $archivo->getSize(),
            ]);
        }

        $actividad->load('usuarioAsignado');

        session()->flash('success', 'Actividad registrada correctamente.');

        return redirect()->route('actividades.create');
    }

    public function render()
    {
        return view('livewire.actividades.create-form');
    }
}
