<div>
    <!-- Stepper Progress Bar (Diseño dinámico basado en pasos declarativos) -->
    <div class="stepper-horizontal-container" style="margin-bottom: 40px; display: flex; align-items: center; justify-content: space-between; position: relative;">
        <div style="position: absolute; top: 20px; left: 0; right: 0; height: 3px; background-color: #e2e8f0; z-index: 1;"></div>
        <div style="position: absolute; top: 20px; left: 0; width: {{ (($currentStep - 1) / (count($this->steps()) - 1)) * 100 }}%; height: 3px; background-color: #0F69C4; z-index: 1; transition: width 0.3s ease;"></div>

        @foreach($this->steps() as $stepNumber => $stepData)
        <div class="stepper-step-item" style="z-index: 2; text-align: center; flex: 1; position: relative;">
            <div class="step-circle" style="
                    width: 40px; 
                    height: 40px; 
                    border-radius: 50%; 
                    margin: 0 auto 10px; 
                    display: flex; 
                    align-items: center; 
                    justify-content: center; 
                    font-weight: bold; 
                    font-size: 0.95rem;
                    transition: all 0.3s ease;
                    @if($currentStep == $stepNumber)
                        background-color: #0F69C4; border: 2px solid #0F69C4; color: #ffffff; box-shadow: 0 0 0 4px rgba(15, 105, 196, 0.2);
                    @elseif($currentStep > $stepNumber)
                        background-color: #2b8a3e; border: 2px solid #2b8a3e; color: #ffffff;
                    @else
                        background-color: #ffffff; border: 2px solid #cbd5e1; color: #64748b;
                    @endif
                ">
                @if($currentStep > $stepNumber)
                ✓
                @else
                {{ $stepNumber }}
                @endif
            </div>
            <div style="font-size: 0.8rem; font-weight: {{ $currentStep == $stepNumber ? '700' : '500' }}; color: {{ $currentStep == $stepNumber ? '#0d1b2a' : '#64748b' }};">
                {{ $stepData['label'] }}
            </div>
        </div>
        @endforeach
    </div>

    <!-- Bloques Condicionales del Formulario -->
    <form wire:submit.prevent="save">

        <!-- STEP 1: DATOS GENERALES -->
        @if($this->getCurrentStepLabel() === $this->steps()[1]['label'])
        <div class="form-grid-columns-2">
            <div class="form-group-item">
                <label for="region">Región</label>
                <select wire:model="region" id="region" class="form-select-control" style="width: 100%; padding: 12px 14px; border: 1px solid #dee2e6; border-radius: 4px;" required>
                    <option value="">Seleccione una región...</option>
                    <option value="Región Metropolitana">Región Metropolitana</option>
                    <option value="Región de Valparaíso">Región de Valparaíso</option>
                    <option value="Región del Biobío">Región del Biobío</option>
                    <option value="Región de Antofagasta">Región de Antofagasta</option>
                    <option value="Región de la Araucanía">Región de la Araucanía</option>
                </select>
                @error('region') <span style="color: #ef3340; font-size: 0.85rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group-item">
                <label for="tipo_unidad">Tipo de Unidad</label>
                <select wire:model="tipo_unidad" id="tipo_unidad" class="form-select-control" style="width: 100%; padding: 12px 14px; border: 1px solid #dee2e6; border-radius: 4px;" required>
                    <option value="">Seleccione tipo unidad...</option>
                    <option value="División Tecnológica">División Tecnológica</option>
                    <option value="Departamento de Operaciones">Departamento de Operaciones</option>
                    <option value="Oficina de Atención Ciudadana">Oficina de Atención Ciudadana</option>
                    <option value="Dirección Nacional">Dirección Nacional</option>
                </select>
                @error('tipo_unidad') <span style="color: #ef3340; font-size: 0.85rem;">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-grid-columns-2" style="margin-top: 15px;">
            <div class="form-group-item">
                <label for="unidad_operativa">Unidad Operativa</label>
                <input type="text" wire:model="unidad_operativa" id="unidad_operativa" class="form-input-control" placeholder="Ej: Unidad de Ciberseguridad" required>
                @error('unidad_operativa') <span style="color: #ef3340; font-size: 0.85rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group-item">
                <label for="tipo">Tipo de Actividad</label>
                <select wire:model="tipo" id="tipo" class="form-select-control" style="width: 100%; padding: 12px 14px; border: 1px solid #dee2e6; border-radius: 4px;" required>
                    <option value="">Seleccione un tipo...</option>
                    <option value="Capacitación Interna">Capacitación Interna</option>
                    <option value="Reunión Bilateral">Reunión Bilateral</option>
                    <option value="Auditoría de Control">Auditoría de Control</option>
                    <option value="Despliegue en Terreno">Despliegue en Terreno</option>
                </select>
                @error('tipo') <span style="color: #ef3340; font-size: 0.85rem;">{{ $message }}</span> @enderror
            </div>
        </div>
        @endif

        <!-- STEP 2: DETALLES DE LA ACTIVIDAD -->
        @if($this->getCurrentStepLabel() === $this->steps()[2]['label'])
        <div class="form-group-item">
            <label for="nombre_actividad">Nombre de la Actividad (Máx. 100 caracteres)</label>
            <input type="text" wire:model="nombre_actividad" id="nombre_actividad" class="form-input-control" maxlength="100" placeholder="Ingrese nombre descriptivo de la actividad" required>
            @error('nombre_actividad') <span style="color: #ef3340; font-size: 0.85rem;">{{ $message }}</span> @enderror
        </div>

        <div class="form-group-item" style="margin-top: 15px;">
            <label for="objetivo">Objetivo de la Actividad (Máx. 200 caracteres)</label>
            <textarea wire:model="objetivo" id="objetivo" class="form-input-control" maxlength="200" rows="3" placeholder="Describa el objetivo principal del reporte..." required></textarea>
            @error('objetivo') <span style="color: #ef3340; font-size: 0.85rem;">{{ $message }}</span> @enderror
        </div>

        <div class="form-grid-columns-2" style="margin-top: 15px;">
            <div class="form-group-item">
                <label for="n_participantes">Número de Participantes</label>
                <input type="number" wire:model="n_participantes" id="n_participantes" class="form-input-control" min="1" required>
                @error('n_participantes') <span style="color: #ef3340; font-size: 0.85rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group-item">
                <label for="fecha_actividad">Fecha de Realización</label>
                <input type="date" wire:model="fecha_actividad" id="fecha_actividad" class="form-input-control" required>
                @error('fecha_actividad') <span style="color: #ef3340; font-size: 0.85rem;">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group-item" style="margin-top: 15px;">
            <label for="ubicacion">Ubicación física o Enlace Virtual (Máx. 150 caracteres)</label>
            <input type="text" wire:model="ubicacion" id="ubicacion" class="form-input-control" maxlength="150" placeholder="Ej: Auditorio del Piso 4 o Enlace de Teams" required>
            @error('ubicacion') <span style="color: #ef3340; font-size: 0.85rem;">{{ $message }}</span> @enderror
        </div>

        <div class="form-group-item" style="margin-top: 15px;">
            <label for="observacion">Observaciones Adicionales (Máx. 200 caracteres)</label>
            <textarea wire:model="observacion" id="observacion" class="form-input-control" maxlength="200" rows="3" placeholder="Ingrese cualquier observación pertinente..."></textarea>
            @error('observacion') <span style="color: #ef3340; font-size: 0.85rem;">{{ $message }}</span> @enderror
        </div>
        @endif

        <!-- STEP 3: ARCHIVOS DE RESPALDO -->
        @if($this->getCurrentStepLabel() === $this->steps()[3]['label'])
        <div class="form-group-item"
            x-data="{ isDragging: false }"
            x-on:dragover.prevent="isDragging = true"
            x-on:dragleave.prevent="isDragging = false"
            x-on:drop.prevent="isDragging = false; @this.uploadMultiple('archivos', $event.dataTransfer.files)">

            <label>Archivos de Respaldo Firmados (PDF, DOCX, XLS, JPG, PNG - Máx. 5MB c/u)</label>

            <div id="dropzone" class="drag-drop-file-zone"
                :class="{ 'dragover': isDragging }"
                onclick="document.getElementById('archivo').click()">
                <div class="file-zone-icon">⇪</div>
                <p style="margin: 0; font-weight: 600; font-size: 1rem; color: #0F69C4;">Arrastre aquí sus documentos o haga clic para examinar</p>
                <div wire:loading wire:target="archivos" style="color: #2b8a3e; font-weight: bold; margin-top: 10px;">
                    Subiendo archivos...
                </div>
                <input type="file" wire:model="archivos" id="archivo" multiple style="display: none;" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
            </div>

            @error('archivos.*') <span style="color: #ef3340; font-size: 0.85rem;">{{ $message }}</span> @enderror

            <div id="file-list" style="margin-top: 15px;">
                @foreach($archivos as $index => $archivo)
                <div class="file-selected-badge" style="margin-bottom: 10px; padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; background-color: #f8fafc;">
                    <span>Archivo: <strong>{{ $archivo->getClientOriginalName() }}</strong></span>
                    <button type="button" wire:click="removeFile({{ $index }})" style="background: none; border: none; color: #ef3340; font-size: 1.25rem; cursor: pointer; line-height: 1;">×</button>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- STEP 4: RESUMEN Y CONFIRMACIÓN -->
        @if($this->getCurrentStepLabel() === $this->steps()[4]['label'])
        <div style="background-color: #f8fafc; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 25px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);">
            <h3 style="margin-top: 0; color: #0d1b2a; border-bottom: 2px solid #cbd5e1; padding-bottom: 10px; font-size: 1.15rem; font-weight: 700;">Auditoría de Registro</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-top: 15px;">
                <div>
                    <strong style="color: #64748b; font-size: 0.8rem; text-transform: uppercase;">Región</strong>
                    <p style="margin: 4px 0 15px; color: #0d1b2a; font-weight: 600;">{{ $region ?: 'N/A' }}</p>

                    <strong style="color: #64748b; font-size: 0.8rem; text-transform: uppercase;">Tipo de Unidad</strong>
                    <p style="margin: 4px 0 15px; color: #0d1b2a; font-weight: 600;">{{ $tipo_unidad ?: 'N/A' }}</p>

                    <strong style="color: #64748b; font-size: 0.8rem; text-transform: uppercase;">Unidad Operativa</strong>
                    <p style="margin: 4px 0 15px; color: #0d1b2a; font-weight: 600;">{{ $unidad_operativa ?: 'N/A' }}</p>

                    <strong style="color: #64748b; font-size: 0.8rem; text-transform: uppercase;">Tipo de Actividad</strong>
                    <p style="margin: 4px 0 15px; color: #0d1b2a; font-weight: 600;">{{ $tipo ?: 'N/A' }}</p>
                </div>
                <div>
                    <strong style="color: #64748b; font-size: 0.8rem; text-transform: uppercase;">Nombre de Actividad</strong>
                    <p style="margin: 4px 0 15px; color: #0d1b2a; font-weight: 600;">{{ $nombre_actividad ?: 'N/A' }}</p>

                    <strong style="color: #64748b; font-size: 0.8rem; text-transform: uppercase;">Fecha Realización</strong>
                    <p style="margin: 4px 0 15px; color: #0d1b2a; font-weight: 600;">{{ $fecha_actividad ? \Carbon\Carbon::parse($fecha_actividad)->format('d-m-Y') : 'N/A' }}</p>

                    <strong style="color: #64748b; font-size: 0.8rem; text-transform: uppercase;">Ubicación</strong>
                    <p style="margin: 4px 0 15px; color: #0d1b2a; font-weight: 600;">{{ $ubicacion ?: 'N/A' }}</p>

                    <strong style="color: #64748b; font-size: 0.8rem; text-transform: uppercase;">Participantes</strong>
                    <p style="margin: 4px 0 15px; color: #0d1b2a; font-weight: 600;">{{ $n_participantes }} personas</p>
                </div>
            </div>

            <div style="margin-top: 15px; border-top: 1px solid #cbd5e1; padding-top: 15px;">
                <strong style="color: #64748b; font-size: 0.8rem; text-transform: uppercase;">Objetivo o Descripción</strong>
                <p style="margin: 4px 0 15px; color: #0d1b2a; line-height: 1.5;">{{ $objetivo ?: 'N/A' }}</p>
            </div>

            @if($observacion)
            <div style="margin-top: 15px; border-top: 1px solid #cbd5e1; padding-top: 15px;">
                <strong style="color: #64748b; font-size: 0.8rem; text-transform: uppercase;">Observaciones</strong>
                <p style="margin: 4px 0 15px; color: #0d1b2a; line-height: 1.5;">{{ $observacion }}</p>
            </div>
            @endif

            @if(!empty($archivos))
            <div style="margin-top: 15px; border-top: 1px solid #cbd5e1; padding-top: 15px;">
                <strong style="color: #64748b; font-size: 0.8rem; text-transform: uppercase;">Documentos Adjuntos ({{ count($archivos) }})</strong>
                <div style="margin-top: 10px; display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 15px;">
                    @foreach($archivos as $archivo)
                    <div style="background-color: #ffffff; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: space-between; min-height: 160px;">

                        <!-- Previsualización si el archivo es imagen -->
                        @if(str_starts_with($archivo->getMimeType(), 'image/'))
                        <div style="width: 100%; height: 100px; overflow: hidden; border-radius: 4px; border: 1px solid #e2e8f0; margin-bottom: 8px;">
                            <img src="{{ $archivo->temporaryUrl() }}" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        @else
                        <!-- Placeholder elegante si es documento -->
                        <div style="height: 100px; display: flex; align-items: center; justify-content: center; background-color: #f1f5f9; border-radius: 4px; border: 1px solid #e2e8f0; margin-bottom: 8px; font-size: 2rem;">
                            📄
                        </div>
                        @endif

                        <span style="font-size: 0.75rem; color: #475569; font-weight: 500; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $archivo->getClientOriginalName() }}">
                            {{ $archivo->getClientOriginalName() }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Panel de Control / Acciones de Navegación del Wizard -->
        <div class="buttons-row-action" style="display: flex; gap: 15px; margin-top: 30px; border-top: 1px solid #dee2e6; padding-top: 20px;">
            @if($currentStep > 1)
            <button type="button" wire:click="previousStep" class="btn-secondary" style="margin-right: auto; padding: 11px 20px; font-weight: 500; font-size: 0.95rem; border-radius: 4px; border: 1px solid #dee2e6; cursor: pointer;">
                Atrás
            </button>
            @else
            <button type="button" class="btn-secondary" onclick="confirmarVolver()" style="margin-right: auto; padding: 11px 20px; font-weight: 500; font-size: 0.95rem; border-radius: 4px; border: 1px solid #dee2e6; cursor: pointer;">
                Volver
            </button>
            @endif

            @if($currentStep < $this->maxStep())
                <button type="button" wire:click="nextStep" class="btn-primary" style="background-color: #0F69C4; color: #ffffff; border: none; padding: 13px 24px; border-radius: 4px; font-weight: 600; font-size: 1rem; cursor: pointer;">
                    Siguiente
                </button>
                @else
                <button type="submit" class="btn-primary" style="background-color: #2b8a3e; color: #ffffff; border: none; padding: 13px 24px; border-radius: 4px; font-weight: 600; font-size: 1rem; cursor: pointer;">
                    <span wire:loading.remove>Guardar y Enviar Actividad</span>
                    <span wire:loading>Procesando...</span>
                </button>
                @endif
        </div>
    </form>
</div>