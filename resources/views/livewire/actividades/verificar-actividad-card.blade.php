<div x-data="{ open: false }" 
     style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: border-color 0.2s ease; margin-bottom: 20px;"
     :style="open ? 'border-color: #0F69C4;' : ''">
    
    <!-- Cabecera de la Actividad (Visible Siempre) -->
    <div style="padding: 18px 20px; display: flex; justify-content: space-between; align-items: center; background-color: #ffffff; cursor: pointer;"
         @click="open = !open">
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <span style="background-color: rgba(239, 51, 64, 0.08); color: #ef3340; padding: 3px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase;">
                Pendiente
            </span>
            <strong style="font-size: 0.95rem; color: #0d1b2a;">{{ $act->TIPO_ACTIVIDAD }}</strong>
            <span style="font-size: 0.8rem; color: #64748b;">
                Código: <strong>{{ $act->COD ?: 'N/A' }}</strong> | Fecha: <strong>{{ $act->FECHA }}</strong>
            </span>
        </div>
        <button type="button" style="background: none; border: none; color: #0F69C4; font-size: 0.85rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px;">
            <span x-text="open ? 'Ocultar Resumen ▲' : 'Ver Detalles y Adjuntar ▼'"></span>
        </button>
    </div>

    <!-- Contenido Desplegable (Resumen + Carga de Verificador) -->
    <div x-show="open" x-transition style="border-top: 1px solid #e2e8f0; background-color: #fbfcfd; padding: 25px;">
        
        <!-- Tarjeta de Resumen Detallado -->
        <div style="background-color: #f8fafc; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 25px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.01); margin-bottom: 20px;">
            <h3 style="margin-top: 0; color: #0d1b2a; border-bottom: 2px solid #cbd5e1; padding-bottom: 10px; font-size: 1.15rem; font-weight: 700;">Auditoría de Registro</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-top: 15px;">
                <div>
                    <strong style="color: #64748b; font-size: 0.8rem; text-transform: uppercase;">Región</strong>
                    <p style="margin: 4px 0 15px; color: #0d1b2a; font-weight: 600;">{{ $act->REGION ?: 'N/A' }}</p>

                    <strong style="color: #64748b; font-size: 0.8rem; text-transform: uppercase;">Unidad Operativa</strong>
                    <p style="margin: 4px 0 15px; color: #0d1b2a; font-weight: 600;">{{ $act->UNIDAD ?: 'N/A' }}</p>

                    <strong style="color: #64748b; font-size: 0.8rem; text-transform: uppercase;">Tipo de Actividad</strong>
                    <p style="margin: 4px 0 15px; color: #0d1b2a; font-weight: 600;">{{ $act->TIPO_ACTIVIDAD ?: 'N/A' }}</p>
                </div>
                <div>
                    <strong style="color: #64748b; font-size: 0.8rem; text-transform: uppercase;">Fecha Realización</strong>
                    <p style="margin: 4px 0 15px; color: #0d1b2a; font-weight: 600;">{{ $act->FECHA ?: 'N/A' }}</p>

                    <strong style="color: #64748b; font-size: 0.8rem; text-transform: uppercase;">Participantes</strong>
                    <p style="margin: 4px 0 15px; color: #0d1b2a; font-weight: 600;">{{ $act->PARTICIPANTES ?: 0 }} personas</p>
                </div>
            </div>

            <div style="margin-top: 15px; border-top: 1px solid #cbd5e1; padding-top: 15px;">
                <strong style="color: #64748b; font-size: 0.8rem; text-transform: uppercase;">Objetivo o Descripción</strong>
                <p style="margin: 4px 0 15px; color: #0d1b2a; line-height: 1.5;">{{ $act->DET_ACTIVIDAD ?: 'Sin descripción provista.' }}</p>
            </div>
        </div>

        <!-- Formulario de Carga del Verificador -->
        <div style="background-color: #ffffff; border: 1px dashed #0F69C4; padding: 20px; border-radius: 8px; display: grid; grid-template-columns: 1fr auto; gap: 20px; align-items: center; flex-wrap: wrap;">
            <div>
                <label for="verificador-{{ $act->actividad_id }}" style="font-size: 0.85rem; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">
                    Adjuntar archivos respaldatorios firmados (PDF, Word, Excel, Imagen - Máx 5MB)
                </label>
                
                <input type="file" 
                       wire:model="verificador" 
                       id="verificador-{{ $act->actividad_id }}" 
                       multiple 
                       accept=".pdf,.doc,.docx,.png,.jpg,.jpeg"
                       style="font-size: 0.85rem; color: #475569;"
                       wire:loading.attr="disabled"
                       wire:target="verificador">
                
                @error('verificador')
                    <span style="color: #ef3340; font-size: 0.8rem; display: block; margin-top: 6px; font-weight: 600;">⚠️ {{ $message }}</span>
                @enderror
                @error('verificador.*')
                    <span style="color: #ef3340; font-size: 0.8rem; display: block; margin-top: 6px; font-weight: 600;">⚠️ {{ $message }}</span>
                @enderror
            </div>

            <div>
                <button type="button" 
                        wire:click="verificarActividad" 
                        class="btn-primary-caj" 
                        style="padding: 12px 24px;" 
                        wire:loading.attr="disabled" 
                        wire:target="verificarActividad">
                    <span wire:loading.remove wire:target="verificarActividad">Guardar Verificador</span>
                    <span wire:loading wire:target="verificarActividad">Guardando...</span>
                </button>
            </div>
        </div>

        <!-- Estados de Carga Locales de la Tarjeta -->
        <div wire:loading wire:target="verificador" style="color: #0F69C4; font-size: 0.8rem; font-weight: 600; margin-top: 10px;">
            ⏳ Subiendo archivos al servidor, por favor espere...
        </div>
        <div wire:loading wire:target="verificarActividad" style="color: #2b8a3e; font-size: 0.8rem; font-weight: 600; margin-top: 10px;">
            ⏳ Guardando y verificando actividad, por favor espere...
        </div>

        <!-- Mensajes Flash de Éxito / Error Locales en la Tarjeta -->
        @if (session()->has('success'))
            <div style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-top: 15px; font-size: 0.85rem; font-weight: 600;">
                {{ session('success') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-top: 15px; font-size: 0.85rem; font-weight: 600;">
                {{ session('error') }}
            </div>
        @endif
    </div>
</div>