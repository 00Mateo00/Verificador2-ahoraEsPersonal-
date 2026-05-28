<?php

use Livewire\Volt\Component;
use App\Models\Actividad;

use App\Models\Archivo;
use Livewire\WithFileUploads;

use App\Mail\ActividadRegistrada;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Auth;

new class extends Component {

    use WithFileUploads;

    public $archivos = [];

    public function removeFile($index)
        {
            array_splice($this->archivos, $index, 1);
        }

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

    protected function rules()
    {
        return [
            'region' => 'required|string',
            'tipo_unidad' => 'required|string',
            'unidad_operativa' => 'required|string|max:150',
            'tipo' => 'required|string',
            'nombre_actividad' => 'required|string|max:100',
            'objetivo' => 'required|string|max:200',
            'n_participantes' => 'required|integer|min:1',
            'fecha_actividad' => 'required|date',
            'ubicacion' => 'required|string|max:150',
            'observacion' => 'nullable|string|max:200',
        ];
    }

    public function save()
    {
        $this->validate();

        $actividad = Actividad::create([
            'usuario_id' => Auth::id(),
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

// Cargar relaciones necesarias para el correo (persona del usuario)
        $actividad->load('usuario.persona');

        // Envío de notificación por correo
        Mail::to('mateo.ossa.b@gmail.com')->send(new ActividadRegistrada($actividad));

session()->flash('success', 'Actividad registrada correctamente.');

        return redirect()->route('actividades.create');
    }
}; ?>

<div>
    <form wire:submit.prevent="save">
        <div class="form-grid-columns-2">
            <div class="form-group-item">
                <label for="region">Región</label>
                <select wire:model="region" id="region" class="form-select-control" required>
                    <option value="">Seleccione una región...</option>
                    <option value="Región Metropolitana">Región Metropolitana</option>
                    <option value="Región de Valparaíso">Región de Valparaíso</option>
                    <option value="Región del Biobío">Región del Biobío</option>
                    <option value="Región de Antofagasta">Región de Antofagasta</option>
                    <option value="Región de la Araucanía">Región de la Araucanía</option>
                </select>
                @error('region') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group-item">
                <label for="tipo_unidad">Tipo de Unidad</label>
                <select wire:model="tipo_unidad" id="tipo_unidad" class="form-select-control" required>
                    <option value="">Seleccione tipo unidad...</option>
                    <option value="División Tecnológica">División Tecnológica</option>
                    <option value="Departamento de Operaciones">Departamento de Operaciones</option>
                    <option value="Oficina de Atención Ciudadana">Oficina de Atención Ciudadana</option>
                    <option value="Dirección Nacional">Dirección Nacional</option>
                </select>
                @error('tipo_unidad') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-grid-columns-2">
            <div class="form-group-item">
                <label for="unidad_operativa">Unidad Operativa</label>
                <input type="text" wire:model="unidad_operativa" id="unidad_operativa" class="form-input-control" placeholder="Ej: Unidad de Ciberseguridad" required>
                @error('unidad_operativa') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group-item">
                <label for="tipo">Tipo de Actividad</label>
                <select wire:model="tipo" id="tipo" class="form-select-control" required>
                    <option value="">Seleccione un tipo...</option>
                    <option value="Capacitación Interna">Capacitación Interna</option>
                    <option value="Reunión Bilateral">Reunión Bilateral</option>
                    <option value="Auditoría de Control">Auditoría de Control</option>
                    <option value="Despliegue en Terreno">Despliegue en Terreno</option>
                </select>
                @error('tipo') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group-item">
            <label for="nombre_actividad">Nombre de la Actividad (Máx. 100 caracteres)</label>
            <input type="text" wire:model="nombre_actividad" id="nombre_actividad" class="form-input-control" maxlength="100" placeholder="Ingrese nombre descriptivo de la actividad" required>
            @error('nombre_actividad') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group-item">
            <label for="objetivo">Objetivo de la Actividad (La Firma Electrónica) (Máx. 200 caracteres)</label>
            <textarea wire:model="objetivo" id="objetivo" class="form-input-control" maxlength="200" rows="3" placeholder="Describa el objetivo principal del reporte..." required></textarea>
            @error('objetivo') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="form-grid-columns-2">
            <div class="form-group-item">
                <label for="n_participantes">Número de Participantes</label>
                <input type="number" wire:model="n_participantes" id="n_participantes" class="form-input-control" min="1" required>
                @error('n_participantes') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group-item">
                <label for="fecha_actividad">Fecha de Realización</label>
                <input type="date" wire:model="fecha_actividad" id="fecha_actividad" class="form-input-control" required>
                @error('fecha_actividad') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group-item">
            <label for="ubicacion">Ubicación física o Enlace Virtual (Máx. 150 caracteres)</label>
            <input type="text" wire:model="ubicacion" id="ubicacion" class="form-input-control" maxlength="150" placeholder="Ej: Auditorio del Piso 4 o Enlace de Teams" required>
            @error('ubicacion') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group-item">
            <label for="observacion">Observaciones Adicionales (Máx. 200 caracteres)</label>
            <textarea wire:model="observacion" id="observacion" class="form-input-control" maxlength="200" rows="3" placeholder="Ingrese cualquier observación pertinente..."></textarea>
            @error('observacion') <span class="error">{{ $message }}</span> @enderror
        </div>

<!-- Zona de Arrastrar y Soltar con Livewire + Alpine -->
        <div class="form-group-item" style="margin-top: 30px;" 
             x-data="{ isDragging: false }" 
             x-on:dragover.prevent="isDragging = true" 
             x-on:dragleave.prevent="isDragging = false" 
             x-on:drop.prevent="isDragging = false; @this.uploadMultiple('archivos', $event.dataTransfer.files)">
            
            <label>
                Archivos de Respaldo Firmados (PDF, DOCX, XLS, JPG, PNG - Máx. 5MB c/u)
            </label>

            <div id="dropzone" class="drag-drop-file-zone" 
                 :class="{ 'dragover': isDragging }"
                 onclick="document.getElementById('archivo').click()">
                <div class="file-zone-icon">⇪</div>
                <p style="margin: 0; font-weight: 600; font-size: 1rem; color: var(--color-primary);">Arrastre aquí sus documentos o haga clic para examinar</p>
                <div wire:loading wire:target="archivos" style="color: var(--color-secondary); font-weight: bold; margin-top: 10px;">
                    Subiendo archivos...
                </div>
                <input type="file" wire:model="archivos" id="archivo" multiple style="display: none;" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
            </div>

            @error('archivos.*') <span class="error">{{ $message }}</span> @enderror

            <!-- Lista de archivos seleccionados (Reactiva) -->
            <div id="file-list" style="margin-top: 15px;">
                @foreach($archivos as $index => $archivo)
                    <div class="file-selected-badge" style="margin-bottom: 10px;">
                        <span>Archivo: <strong>{{ $archivo->getClientOriginalName() }}</strong></span>
                        <button type="button" wire:click="removeFile({{ $index }})">×</button>
                    </div>
                @endforeach
            </div>
        </div>

<div class="buttons-row-action" style="display: flex; gap: 15px; margin-top: 30px; border-top: 1px solid var(--color-border); padding-top: 20px;">
            <button type="button" class="btn-secondary" onclick="confirmarVolver()" style="margin-right: auto; padding: 11px 20px; font-weight: 500; font-size: 0.95rem; border-radius: var(--border-radius); border: 1px solid var(--color-border); cursor: pointer;">Volver</button>
            <button type="reset" class="btn-secondary" style="padding: 11px 20px; font-weight: 500; font-size: 0.95rem; border-radius: var(--border-radius); border: 1px solid var(--color-border); cursor: pointer;">Limpiar Formulario</button>
            <button type="submit" class="btn-primary" style="background-color: var(--color-secondary); color: var(--color-white); border: none; padding: 13px 24px; border-radius: var(--border-radius); font-weight: 600; font-size: 1rem; cursor: pointer;">
                <span wire:loading.remove>Guardar Actividad</span>
                <span wire:loading>Procesando...</span>
            </button>
        </div>
    </form>
</div>