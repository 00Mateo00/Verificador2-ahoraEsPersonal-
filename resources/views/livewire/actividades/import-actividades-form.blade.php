<div class="panel-dashboard-content" style="margin-top: 20px;">
    <!-- Mensajes de Alerta -->
    @if (session()->has('success'))
    <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #c3e6cb; font-size: 0.9rem;">
        <strong>Éxito:</strong> {{ session('success') }}
    </div>
    @endif

    @if (session()->has('error'))
    <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #f5c6cb; font-size: 0.9rem;">
        <strong>Error:</strong> {{ session('error') }}
    </div>
    @endif

    <!-- PASO 1: SUBIDA DE ARCHIVO -->
    @if($step === 1)
    <div class="form-group-item"
        x-data="{ isUploading: false, progress: 0 }"
        x-on:livewire-upload-start="isUploading = true"
        x-on:livewire-upload-finish="isUploading = false"
        x-on:livewire-upload-error="isUploading = false"
        x-on:livewire-upload-progress="progress = $event.detail.progress">

        <h3 style="margin-bottom: 10px; color: #0d1b2a; font-size: 1.3rem;">Importar Planilla Masiva de Actividades</h3>
        <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 20px;">
            Cargue el archivo Excel (.xlsx) estructurado bajo las cabeceras institucionales requeridas para poblar automáticamente el verificador de actividades.
        </p>

        <!-- 1. Estado: Subiendo archivo (Centralizado con barra de progreso) -->
        <div x-show="isUploading" style="background-color: #f8fafc; border: 2px dashed #2b8a3e; border-radius: 8px; padding: 50px; text-align: center; box-shadow: inset 0 2px 4px rgba(0,0,0,0.01);" x-cloak>
            <div style="font-size: 3.5rem; margin-bottom: 15px; animation: pulse 1.5s infinite;">⏳</div>
            <p style="margin: 0; font-weight: 700; font-size: 1.2rem; color: #2b8a3e;">
                Cargando archivo...
            </p>
            <!-- Barra de Progreso Dinámica -->
            <div style="width: 250px; height: 8px; background-color: #e2e8f0; border-radius: 4px; margin: 20px auto 0; overflow: hidden;">
                <div style="width: 100%; height: 100%; background-color: #2b8a3e; transition: width 0.3s ease;" :style="{ width: progress + '%' }"></div>
            </div>
            <p style=" margin: 8px 0 0; font-size: 0.85rem; color: #64748b; font-weight: 600;" x-text="progress + '%'" style="font-family: monospace;">
            </p>
        </div>

        <!-- 2. Estado: Sin archivo seleccionado e inactivo (No cargando) -->
        <div x-show="!$wire.excelFile && !isUploading">
            <div class="drag-drop-file-zone"
                x-data="{ isDragging: false }"
                x-on:dragover.prevent="isDragging = true"
                x-on:dragleave.prevent="isDragging = false"
                x-on:drop.prevent="isDragging = false; $wire.upload('excelFile', $event.dataTransfer.files[0])"
                onclick="document.getElementById('excelFile').click()"
                style="padding: 40px; border: 2px dashed #b5c7e0; border-radius: 8px; text-align: center; cursor: pointer; background-color: #f8fafc; transition: all 0.2s ease;">

                <div style="font-size: 3rem; margin-bottom: 15px; color: #0F69C4;">📥</div>
                <p style="margin: 0; font-weight: 600; font-size: 1.05rem; color: #0F69C4;">
                    Arrastre su archivo Excel aquí o haga clic para buscar en su equipo
                </p>
                <p style="margin: 5px 0 0; font-size: 0.8rem; color: #64748b;">
                    Formatos permitidos: .xlsx, .xls (Máx. 10MB)
                </p>
            </div>
        </div>

        <!-- 3. Estado: Archivo cargado con éxito en el cliente (No cargando) -->
        <div x-show="$wire.excelFile && !isUploading" x-cloak>
            <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 30px; text-align: center; box-shadow: inset 0 2px 4px rgba(0,0,0,0.01);">
                <div style="font-size: 3.5rem; margin-bottom: 15px;">📊</div>
                <p style="margin: 0; font-weight: 700; color: #0d1b2a; font-size: 1.15rem;">
                    {{ $excelFile ? $excelFile->getClientOriginalName() : '' }}
                </p>
                <p style="margin: 5px 0 20px; font-size: 0.85rem; color: #64748b;">
                    Tamaño: {{ $excelFile ? number_format($excelFile->getSize() / 1024, 1) . ' KB' : '' }} | Listo para validación
                </p>

                <button type="button" wire:click="$set('excelFile', null)" class="btn-acc" style="border: 1px solid #cbd5e1; padding: 8px 16px; font-size: 0.85rem; cursor: pointer; border-radius: 4px; background-color: #ffffff; display: inline-flex; align-items: center; gap: 6px; font-weight: 600;">
                    ✕ Cambiar Archivo
                </button>
            </div>
        </div>

        <input type="file" wire:model="excelFile" id="excelFile" style="display: none;" accept=".xlsx,.xls">

        @error('excelFile')
        <span style="color: #ef3340; font-size: 0.85rem; display: block; margin-top: 10px; font-weight: 600;">
            ⚠️ {{ $message }}
        </span>
        @enderror

        @if($excelFile)
        <div x-show="!isUploading" style="margin-top: 25px; display: flex; justify-content: flex-end;" x-cloak>
            <button type="button" 
                    wire:click="uploadFile" 
                    class="btn-primary-caj" 
                    style="padding: 12px 24px;" 
                    wire:loading.attr="disabled" 
                    wire:target="excelFile, uploadFile">
                <span wire:loading.remove wire:target="uploadFile">Procesar Planilla</span>
                <span wire:loading wire:target="uploadFile">Validando Planilla...</span>
            </button>
        </div>
        @endif
    </div>
    @endif

    <!-- PASO 2: PREVISUALIZACIÓN DE FILAS -->
    @if($step === 2)
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #cbd5e1; padding-bottom: 15px; margin-bottom: 20px;">
            <h3 style="margin: 0; color: #0d1b2a; font-size: 1.4rem;">Previsualización de Carga</h3>
            <span style="background-color: rgba(15, 105, 196, 0.1); color: #0F69C4; font-weight: 700; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem;">
                {{ $totalRows }} registros detectados
            </span>
        </div>

        <p style="color: #475569; font-size: 0.9rem; margin-bottom: 25px;">
            A continuación se presenta una muestra representativa con los primeros 10 registros contenidos en el archivo Excel. Verifique que las columnas se correspondan con los datos esperados de actividades antes de persistir los datos.
        </p>

        <!-- Bloque Dinámico de Advertencias (Warnings Panel) -->
        @if(!empty($warnings))
        <div style="background-color: #fffbeb; border: 1px solid #fef3c7; border-radius: 8px; padding: 20px; margin-bottom: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                <span style="font-size: 1.25rem;">⚠️</span>
                <strong style="color: #92400e; font-size: 1rem;">Advertencias de consistencia de datos detectadas ({{ count($warnings) }})</strong>
            </div>
            <p style="color: #b45309; font-size: 0.85rem; margin: 0 0 12px 0;">
                Se detectaron inconsistencias menores en la planilla. Las filas afectadas se omitirán automáticamente del proceso final de inserción masiva para resguardar la integridad estructural de la base de datos, mientras que el resto de las filas elegibles se importará normalmente.
            </p>
            <div style="max-height: 180px; overflow-y: auto; background-color: #ffffff; border: 1px solid #fde68a; border-radius: 6px; padding: 12px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.01);">
                <ul style="margin: 0; padding-left: 20px; font-size: 0.85rem; color: #78350f; display: flex; flex-direction: column; gap: 6px;">
                    @foreach($warnings as $warning)
                    <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <div style="overflow-x: auto; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; margin-bottom: 25px; background: #ffffff;">
            <table class="table-custom-data" style="width: 100%; border-collapse: collapse; min-width: 800px;">
                <thead>
                    <tr>
                        <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: left; font-size: 0.8rem; font-weight: 700; color: #475569;">MODALIDAD</th>
                        <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: left; font-size: 0.8rem; font-weight: 700; color: #475569;">TIPO ACTIVIDAD</th>
                        <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: left; font-size: 0.8rem; font-weight: 700; color: #475569;">SUB TIPO ACTIVIDAD</th>
                        <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: left; font-size: 0.8rem; font-weight: 700; color: #475569;">COD</th>
                        <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: left; font-size: 0.8rem; font-weight: 700; color: #475569;">FECHA</th>
                        <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: left; font-size: 0.8rem; font-weight: 700; color: #475569;">UNIDAD</th>
                        <th style="padding: 12px 16px; background-color: #f1f5f9; text-align: left; font-size: 0.8rem; font-weight: 700; color: #475569;">FUNCIONARIO</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($previewRows as $row)
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 12px 16px; font-size: 0.85rem; color: #334155;">{{ $row['MODALIDAD_MODIFICADO'] ?? 'N/A' }}</td>
                        <td style="padding: 12px 16px; font-size: 0.85rem; color: #334155;">{{ $row['TIPO_MODIFICADO'] ?? 'N/A' }}</td>
                        <td style="padding: 12px 16px; font-size: 0.85rem; color: #334155;">{{ $row['SUB_TIPO_MODIFICADO'] ?? 'N/A' }}</td>
                        <td style="padding: 12px 16px; font-size: 0.85rem; color: #334155;">{{ $row['COD'] ?? 'N/A' }}</td>
                        <td style="padding: 12px 16px; font-size: 0.85rem; color: #334155;">{{ $row['FECHA'] ?? 'N/A' }}</td>
                        <td style="padding: 12px 16px; font-size: 0.85rem; color: #334155; font-weight: 600;">{{ $row['UNIDAD'] ?? 'N/A' }}</td>
                        <td style="padding: 12px 16px; font-size: 0.85rem; color: #334155;">{{ $row['FUNCIONARIO'] ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="display: flex; gap: 15px; justify-content: flex-end; border-top: 1px solid #dee2e6; padding-top: 20px;">
            <button type="button" 
                    wire:click="resetForm" 
                    class="btn-acc" 
                    style="border: 1px solid #cbd5e1; padding: 12px 20px;"
                    wire:loading.attr="disabled"
                    wire:target="resetForm, startCountdown">
                Cancelar y Volver
            </button>
            <button type="button" 
                    wire:click="startCountdown" 
                    class="btn-primary-caj" 
                    style="padding: 12px 24px; background-color: #2b8a3e;"
                    wire:loading.attr="disabled"
                    wire:target="startCountdown, resetForm">
                <span wire:loading.remove wire:target="startCountdown">Iniciar Confirmación e Importación</span>
                <span wire:loading wire:target="startCountdown">Preparando Confirmación...</span>
            </button>
        </div>
    </div>
    @endif

    <!-- PASO 3: CUENTA REGRESIVA (CANCELACIÓN ACTIVA) -->
    @if($step === 3)
    <div x-data="{ 
            timeLeft: 10, 
            timerInterval: null,
            init() {
                this.timeLeft = 10;
                this.timerInterval = setInterval(() => {
                    if (this.timeLeft > 1) {
                        this.timeLeft--;
                    } else {
                        clearInterval(this.timerInterval);
                        $wire.processImport();
                    }
                }, 1000);
            },
            cancel() {
                clearInterval(this.timerInterval);
                $wire.cancelSend();
            }
        }" style="text-align: center; padding: 30px 0;">

        <h3 style="color: #0d1b2a; font-size: 1.6rem; margin-bottom: 10px;">Enviando Notificaciones Automáticas</h3>
        <p style="color: #475569; font-size: 0.95rem; margin-bottom: 30px;">
            El sistema iniciará la persistencia de las actividades y enviará las notificaciones por correo a las unidades correspondientes en:
        </p>

        <div style="margin: 0 auto 30px; width: 120px; height: 120px; border-radius: 50%; border: 6px solid #ef3340; display: flex; align-items: center; justify-content: center; background-color: rgba(239, 51, 64, 0.05); box-shadow: 0 4px 10px rgba(239, 51, 64, 0.15);">
            <span x-text="timeLeft" style="font-size: 3rem; font-weight: 800; color: #ef3340; font-family: monospace;">10</span>
        </div>

        <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 35px; max-width: 500px; margin-left: auto; margin-right: auto;">
            Si ha detectado algún error de último momento, puede suspender la importación antes de que expire el tiempo haciendo clic en el botón de abajo de manera inmediata.
        </p>

        <div style="display: flex; justify-content: center;">
            <button type="button" @click="cancel" class="btn-primary-caj" style="background-color: #ef3340; padding: 14px 40px; font-weight: 700;">
                ⛔ CANCELAR OPERACIÓN
            </button>
        </div>
    </div>
    @endif

    <!-- PASO 4: ÉXITO -->
    @if($step === 4)
    <div style="text-align: center; padding: 40px 0;">
        <div style="font-size: 4rem; margin-bottom: 20px;">🎉</div>
        <h3 style="color: #2b8a3e; font-size: 1.8rem; margin-bottom: 10px; font-weight: 700;">Planilla Importada con Éxito</h3>
        <p style="color: #475569; font-size: 1rem; margin-bottom: 35px; max-width: 600px; margin-left: auto; margin-right: auto;">
            Se han procesado, validado e ingresado de forma íntegra las <strong>{{ $totalRows }}</strong> actividades en la base de datos de Intranet. Los correos de notificación a las unidades ya se encuentran agendados en la cola de procesamiento.
        </p>

        <div style="display: flex; justify-content: center; gap: 15px;">
            <button type="button" wire:click="resetForm" class="btn-primary-caj" style="padding: 12px 30px;">
                Cargar Nueva Planilla
            </button>
        </div>
    </div>
    @endif
</div>