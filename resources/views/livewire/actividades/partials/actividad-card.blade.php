<div x-data="{ open: @json($act->actividad_id == $actividad_id) }" 
     class="accordion-activity-panel" 
     style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; margin-bottom: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02); transition: all 0.2s ease;" 
     :style="open ? 'border-color: #0F69C4; box-shadow: 0 4px 12px rgba(15, 105, 196, 0.05);' : ''">
    
    <div class="accordion-activity-header" style="padding: 18px 20px; display: flex; align-items: center; justify-content: space-between; gap: 15px;">
        
        <div @click="open = !open" style="cursor: pointer; flex: 1; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <span style="background-color: rgba(15, 105, 196, 0.08); color: #0F69C4; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">
                {{ $act->REGION }}
            </span>
            <strong style="color: #0d1b2a; font-size: 0.95rem;">{{ $act->TIPO_ACTIVIDAD }}</strong>
            <span style="color: #64748b; font-size: 0.8rem; font-weight: 500;">
                Realizado el {{ $actDate->format('d-m-Y') }}
            </span>
        </div>

        <button type="button" @click="open = !open" style="background: none; border: none; cursor: pointer; color: #64748b; font-size: 0.9rem; padding: 5px;">
            <span x-text="open ? '▲' : '▼'"></span>
        </button>
    </div>

    <!-- Contenido Desplegable (Cuerpo de la Tarjeta) -->
    <div x-show="open" x-transition style="border-top: 1px solid #f1f5f9; padding: 20px 24px; background-color: #fbfcfd;">

        <!-- Ficha de Datos Técnicos -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; background-color: #ffffff; border: 1px solid #e2e8f0; padding: 15px; border-radius: 6px;">
            <div>
                <strong style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; display: block; margin-bottom: 4px;">Unidad Operativa</strong>
                <span style="font-size: 0.9rem; color: #0d1b2a; font-weight: 600;">{{ $act->UNIDAD }}</span>
            </div>
            <div>
                <strong style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; display: block; margin-bottom: 4px;">Subtipo de Actividad</strong>
                <span style="font-size: 0.9rem; color: #0d1b2a; font-weight: 600;">{{ $act->SUB_TIPO_ACTIVIDAD }}</span>
            </div>
            <div>
                <strong style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; display: block; margin-bottom: 4px;">Participantes</strong>
                <span style="font-size: 0.9rem; color: #0d1b2a; font-weight: 600;">{{ $act->PARTICIPANTES }} personas</span>
            </div>
            <div>
                <strong style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; display: block; margin-bottom: 4px;">Modalidad</strong>
                <span style="font-size: 0.9rem; color: #0d1b2a; font-weight: 600;">{{ $act->MODALIDAD }}</span>
            </div>
        </div>

        <!-- Objetivo -->
        <div style="margin-bottom: 15px;">
            <h4 style="margin: 0 0 6px 0; font-size: 0.85rem; color: #475569; text-transform: uppercase; font-weight: 700;">Objetivo / Descripción</h4>
            <p style="margin: 0; font-size: 0.95rem; color: #0d1b2a; line-height: 1.5;">{{ $act->DET_ACTIVIDAD }}</p>
        </div>

        <!-- Funcionario Responsable -->
        @if($act->FUNCIONARIO)
            <div style="margin-bottom: 20px; border-top: 1px dashed #e2e8f0; padding-top: 15px;">
                <h4 style="margin: 0 0 6px 0; font-size: 0.85rem; color: #475569; text-transform: uppercase; font-weight: 700;">Funcionario Responsable</h4>
                <p style="margin: 0; font-size: 0.95rem; color: #475569; line-height: 1.5;">{{ $act->FUNCIONARIO }}</p>
            </div>
        @endif

        <!-- Documentos de Respaldo Firmados -->
        @if($act->archivos->isNotEmpty() || (Auth::user()->rol === \App\Enums\UserRole::Admin && session('modo_edicion')))
            <div style="border-top: 1px dashed #e2e8f0; padding-top: 15px;">
                <h4 style="margin: 0 0 10px 0; font-size: 0.85rem; color: #0F69C4; text-transform: uppercase; font-weight: 700;">Documentos de Respaldo</h4>
                @if($act->archivos->isNotEmpty())
                <div style="overflow-x: auto;">
                    <table class="table-custom-data" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Nombre del Documento</th>
                                <th>Tipo</th>
                                <th>Tamaño</th>
                                <th style="text-align: right;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($act->archivos as $archivo)
                                <tr>
                                    <td><strong>{{ $archivo->archivo_nombre }}</strong></td>
                                    <td>{{ $archivo->archivo_tipo }}</td>
                                    <td>
                                        @php
                                            $sizeKB = $archivo->archivo_size / 1024;
                                            echo ($sizeKB >= 1024) ? number_format($sizeKB / 1024, 2) . ' MB' : number_format($sizeKB, 1) . ' KB';
                                        @endphp
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="{{ route('archivos.descargar', $archivo->archivo_id) }}" style="font-weight: 700; color: #0F69C4; margin-right: 15px;">Descargar</a>
                                        @if(Auth::user()->rol === \App\Enums\UserRole::Admin && session('modo_edicion'))
                                        <button type="button" 
                                                wire:click="eliminarArchivo({{ $archivo->archivo_id }})" 
                                                wire:confirm="¿Está seguro de que desea eliminar permanentemente este archivo verificador de forma administrativa?"
                                                style="background: none; border: none; color: #ef3340; font-weight: 700; cursor: pointer; padding: 0;">
                                            Eliminar 🗑️
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p style="margin: 0; font-size: 0.9rem; color: #64748b; font-style: italic;">Esta actividad no posee archivos verificadores cargados.</p>
                @endif

                <!-- Formulario Administrativo para Adjuntar Nuevos Verificadores -->
                @if(Auth::user()->rol === \App\Enums\UserRole::Admin && session('modo_edicion'))
                    <div style="margin-top: 20px; background-color: rgba(15, 105, 196, 0.02); border: 1px dashed #0F69C4; padding: 20px; border-radius: 8px; display: grid; grid-template-columns: 1fr auto; gap: 20px; align-items: center; flex-wrap: wrap;">
                        <div>
                            <label for="nuevosVerificadores-{{ $act->actividad_id }}" style="font-size: 0.85rem; font-weight: 700; color: #334155; display: block; margin-bottom: 6px;">
                                Adjuntar archivos respaldatorios adicionales administrativamente
                            </label>
                            <input type="file" 
                                    wire:model="nuevosVerificadores" 
                                    id="nuevosVerificadores-{{ $act->actividad_id }}" 
                                    multiple 
                                    accept=".pdf,.doc,.docx,.png,.jpg,.jpeg"
                                    style="font-size: 0.85rem; color: #475569;">
                            @error('nuevosVerificadores') <span style="color: #ef3340; font-size: 0.8rem; display: block; margin-top: 5px;">⚠️ {{ $message }}</span> @enderror
                            @error('nuevosVerificadores.*') <span style="color: #ef3340; font-size: 0.8rem; display: block; margin-top: 5px;">⚠️ {{ $message }}</span> @enderror
                        </div>
                        <button type="button" 
                                wire:click="adjuntarVerificadorAdministrativo({{ $act->actividad_id }})" 
                                class="btn-primary-caj" 
                                style="padding: 10px 20px; font-size: 0.85rem;"
                                wire:loading.attr="disabled"
                                wire:target="nuevosVerificadores">
                            Adjuntar Respaldos
                        </button>
                    </div>
                    <div wire:loading wire:target="nuevosVerificadores" style="color: #0F69C4; font-size: 0.8rem; font-weight: 600; margin-top: 8px;">
                        ⏳ Cargando archivos temporales al servidor, por favor espere...
                    </div>

                    <!-- Botón Administrativo para Desactivar Actividad -->
                    <div style="margin-top: 20px; border-top: 1px dashed #cbd5e1; padding-top: 15px; display: flex; justify-content: flex-end;">
                        <button type="button" 
                                wire:click="desactivarActividad({{ $act->actividad_id }})" 
                                wire:confirm="¿Está seguro de que desea desactivar de forma permanente esta actividad del sistema?"
                                class="btn-acc" 
                                style="border-color: #ef3340; color: #ef3340 !important; background-color: rgba(239, 51, 64, 0.02); font-weight: 700; padding: 8px 16px; font-size: 0.8rem; border-radius: 4px; cursor: pointer;">
                            Desactivar Actividad 🛑
                        </button>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>