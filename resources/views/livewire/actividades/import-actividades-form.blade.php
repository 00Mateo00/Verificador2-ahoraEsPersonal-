<div class="panel-dashboard-content" style="margin-top: 20px;">
    <!-- Mensajes de Alerta -->
    @if (session()->has('success'))
        <x-alert type="success" title="Éxito">
            {{ session('success') }}
        </x-alert>
    @endif

    @if (session()->has('error'))
        <x-alert type="error" title="Error">
            {{ session('error') }}
        </x-alert>
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
                x-data="{ isDragging: false, dragCounter: 0 }"
                x-on:dragenter.prevent.stop="dragCounter++; isDragging = true"
                x-on:dragover.prevent.stop=""
                x-on:dragleave.prevent.stop="dragCounter--; if (dragCounter <= 0) { isDragging = false; dragCounter = 0; }"
                x-on:drop.prevent.stop="dragCounter = 0; isDragging = false; if ($event.dataTransfer.files.length) { $wire.upload('excelFile', $event.dataTransfer.files[0]) }"
                onclick="document.getElementById('excelFile').click()"
                :style="isDragging 
                    ? 'padding: 40px; border: 2px dashed #2b8a3e; border-radius: 8px; text-align: center; cursor: pointer; background-color: #ebfbee; transition: all 0.2s ease; transform: scale(1.01);' 
                    : 'padding: 40px; border: 2px dashed #b5c7e0; border-radius: 8px; text-align: center; cursor: pointer; background-color: #f8fafc; transition: all 0.2s ease;'"
                style="padding: 40px; border: 2px dashed #b5c7e0; border-radius: 8px; text-align: center; cursor: pointer; background-color: #f8fafc; transition: all 0.2s ease;">

                <div style="pointer-events: none; font-size: 3rem; margin-bottom: 15px;" :style="isDragging ? 'color: #2b8a3e;' : 'color: #0F69C4;'">
                    <span x-text="isDragging ? '🟢' : '📥'"></span>
                </div>
                <p style="pointer-events: none; margin: 0; font-weight: 600; font-size: 1.05rem;" :style="isDragging ? 'color: #2b8a3e;' : 'color: #0F69C4;'" x-text="isDragging ? '¡Suelta la planilla aquí para iniciar la subida!' : 'Arrastre su archivo Excel aquí o haga clic para buscar en su equipo'">
                    Arrastre su archivo Excel aquí o haga clic para buscar en su equipo
                </p>
                <p style="pointer-events: none; margin: 5px 0 0; font-size: 0.8rem; color: #64748b;">
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

        <!-- Selector Dinámico de Período según contenido del Excel -->
        <div style="background-color: #f1f5f9; border: 1px solid #cbd5e1; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
            <label for="periodoSeleccionado" style="font-size: 0.85rem; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">Seleccionar Período a Importar</label>
            <select wire:model.live="periodoSeleccionado" id="periodoSeleccionado" class="form-select-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; background-color: #ffffff; font-size: 0.95rem;">
                @foreach($periodosDisponibles as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <p style="margin: 8px 0 0; font-size: 0.8rem; color: #64748b; line-height: 1.4;">
                * Se muestran únicamente los períodos detectados de forma automática en el archivo Excel cargado. Al cambiar el período, la muestra y las advertencias se actualizarán de forma inmediata.
            </p>
        </div>

        @if($todoDuplicado)
            <!-- Alerta Crítica de Duplicación Total de Lote -->
            <div style="background-color: #fff1f2; border: 1px solid #fecdd3; border-radius: 8px; padding: 20px; margin-bottom: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                    <span style="font-size: 1.5rem;">🛑</span>
                    <strong style="color: #9f1239; font-size: 1.1rem;">Ya cargaste este contenido exacto anteriormente</strong>
                </div>
                <p style="color: #be123c; font-size: 0.9rem; margin: 0 0 15px 0; line-height: 1.5;">
                    Todas las actividades de la planilla cargada correspondientes al período seleccionado ya se encuentran registradas y vigentes en el sistema. A continuación puede visualizar y contrastar la planilla contra la información actual del programa.
                </p>
            </div>

            <!-- UI de Comparación Lado a Lado (Side-by-Side) -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                <!-- Columna Izquierda: Planilla Cargada -->
                <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.01);">
                    <h4 style="margin-top: 0; color: #0f69c4; font-size: 0.95rem; text-transform: uppercase; font-weight: 700; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 15px;">📥 Planilla Cargada</h4>
                    <div style="overflow-x: auto;">
                        <table class="table-custom-data" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th style="padding: 10px; font-size: 0.8rem; background-color: #f8fafc;">COD</th>
                                    <th style="padding: 10px; font-size: 0.8rem; background-color: #f8fafc;">Unidad</th>
                                    <th style="padding: 10px; font-size: 0.8rem; background-color: #f8fafc;">Actividad</th>
                                    <th style="padding: 10px; font-size: 0.8rem; background-color: #f8fafc;">Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($existingRowsComparison as $pair)
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 8px; font-size: 0.78rem; font-weight: bold; color: #334155;">{{ $pair['cargada']['COD'] ?? 'N/A' }}</td>
                                    <td style="padding: 8px; font-size: 0.78rem; color: #475569;">{{ \Illuminate\Support\Str::limit($pair['cargada']['UNIDAD'] ?? 'N/A', 15) }}</td>
                                    <td style="padding: 8px; font-size: 0.78rem; color: #475569;">{{ \Illuminate\Support\Str::limit($pair['cargada']['TIPO_MODIFICADO'] ?? 'N/A', 18) }}</td>
                                    <td style="padding: 8px; font-size: 0.78rem; color: #475569;">{{ $pair['cargada']['FECHA'] ?? 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Columna Derecha: Sistema Real -->
                <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.01);">
                    <h4 style="margin-top: 0; color: #2b8a3e; font-size: 0.95rem; text-transform: uppercase; font-weight: 700; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 15px;">✅ Registros en Plataforma</h4>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.78rem; text-align: left;">
                            <thead>
                                <tr style="background-color: #f8fafc; border-bottom: 1px solid #cbd5e1;">
                                    <th style="padding: 10px; font-size: 0.8rem; background-color: #f8fafc;">COD</th>
                                    <th style="padding: 10px; font-size: 0.8rem; background-color: #f8fafc;">Unidad</th>
                                    <th style="padding: 10px; font-size: 0.8rem; background-color: #f8fafc;">Actividad</th>
                                    <th style="padding: 10px; font-size: 0.8rem; background-color: #f8fafc;">Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($existingRowsComparison as $pair)
                                <tr style="border-bottom: 1px solid #f1f5f9; background-color: rgba(43, 138, 62, 0.02);">
                                    <td style="padding: 8px; font-size: 0.78rem; font-weight: bold; color: #2b8a3e;">{{ $pair['existente']['COD'] ?? 'N/A' }}</td>
                                    <td style="padding: 8px; font-size: 0.78rem; color: #475569;">{{ \Illuminate\Support\Str::limit($pair['existente']['UNIDAD'] ?? 'N/A', 15) }}</td>
                                    <td style="padding: 8px; font-size: 0.78rem; color: #475569;">{{ \Illuminate\Support\Str::limit($pair['existente']['TIPO_ACTIVIDAD'] ?? 'N/A', 18) }}</td>
                                    <td style="padding: 8px; font-size: 0.78rem; color: #475569;">{{ $pair['existente']['FECHA'] ?? 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <p style="color: #475569; font-size: 0.9rem; margin-bottom: 25px;">
                A continuación se presenta una muestra de los registros contenidos en el archivo Excel correspondientes al período seleccionado. Verifique que las columnas se correspondan con los datos esperados de actividades antes de persistir los datos.
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
        @endif

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
                    @if($todoDuplicado) disabled @endif
                    wire:target="startCountdown, resetForm">
                <span wire:loading.remove wire:target="startCountdown">Iniciar Confirmación e Importación</span>
                <span wire:loading wire:target="startCountdown">Preparando Confirmación...</span>
            </button>
        </div>
    </div>
    @endif

   <!-- PASO 3: CUENTA REGRESIVA DE ENVÍO -->
    @if($step === 3)
    <div x-data="timerComponent" style="text-align: center; padding: 30px 0;">

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

    <!-- PASO 4: PROCESAMIENTO ACTIVO Y ENVÍO DE CORREOS -->
    @if($step === 4)
    <div x-data x-init="$wire.processImport()" style="text-align: center; padding: 40px 0;">
        <div style="max-width: 550px; margin: 0 auto; text-align: left; background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: 35px; box-shadow: 0 10px 25px rgba(0,0,0,0.02);">
            <div style="text-align: center; margin-bottom: 30px;">
                <div class="animate-spin" style="font-size: 3.5rem; display: inline-block; margin-bottom: 12px;">⏳</div>
                <h3 style="color: #0F69C4; font-size: 1.5rem; margin: 0; font-weight: 700;">Enviando correos y Procesando registros</h3>
                <p style="color: #64748b; font-size: 0.88rem; margin-top: 6px;">El servidor se encuentra despachando los correos electrónicos y registrando las actividades. Por favor, <strong style="text-decoration: underline;">no cierre esta pestaña.</strong></p>
            </div>

            <div style="display: flex; flex-direction: column; gap: 20px;">
                
                <!-- Tarea 1: Validación previa -->
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 24px; height: 24px; border-radius: 50%; background-color: #2b8a3e; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: bold;">
                        <span>✓</span>
                    </div>
                    <div style="flex: 1;">
                        <strong style="font-size: 0.95rem; color: #0d1b2a;">Validando información</strong>
                        <p style="font-size: 0.8rem; margin: 2px 0 0; color: #475569;">Estructura de la planilla Excel analizada con éxito.</p>
                    </div>
                </div>

                <!-- Tarea 2: Procesar registros -->
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div class="animate-pulse" style="width: 24px; height: 24px; border-radius: 50%; background-color: #d97706; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: bold;">
                        <span>⏳</span>
                    </div>
                    <div style="flex: 1;">
                        <strong style="font-size: 0.95rem; color: #0d1b2a;">Procesando registros y Despachando notificaciones automáticas</strong>
                        <p style="font-size: 0.8rem; margin: 2px 0 0; color: #475569;">Escribiendo de forma masiva actividades y Enviando recordatorios a unidades...</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @endif

    <!-- PASO 5: ÉXITO -->
    @if($step === 5)
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