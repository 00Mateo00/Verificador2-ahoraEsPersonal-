<div x-data="{ open: @json($act->actividad_id == $actividad_id) }" 
     class="accordion-activity-panel" 
     style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; margin-bottom: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02); transition: all 0.2s ease;" 
     :style="open ? 'border-color: #0F69C4; box-shadow: 0 4px 12px rgba(15, 105, 196, 0.05);' : ''">
    
    <div class="accordion-activity-header" style="padding: 18px 20px; display: flex; align-items: center; justify-content: space-between; gap: 15px;">
        
        <!-- Checkbox de Selección -->
        <div style="display: flex; align-items: center; gap: 15px; flex: 1;">
            <input type="checkbox" value="{{ $act->actividad_id }}" wire:model.live="selectedIds" style="width: 16px; height: 16px; cursor: pointer;">
            
            <div @click="open = !open" style="cursor: pointer; flex: 1; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <span style="background-color: rgba(15, 105, 196, 0.08); color: #0F69C4; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">
                    {{ $act->region }}
                </span>
                <strong style="color: #0d1b2a; font-size: 0.95rem;">{{ $act->nombre_actividad }}</strong>
                <span style="color: #64748b; font-size: 0.8rem; font-weight: 500;">
                    Realizado el {{ $actDate->format('d-m-Y') }}
                </span>
            </div>
        </div>

        <button type="button" @click="open = !open" style="background: none; border: none; cursor: pointer; color: #64748b; font-size: 0.9rem; padding: 5px;">
            <span x-text="open ? '▲' : '▼'"></span>
        </button>
    </div>

    <!-- Contenido Desplegable (Cuerpo de la Tarjeta) -->
    <div x-show="open" x-transition style="border-top: 1px solid #f1f5f9; padding: 20px 24px; background-color: #fbfcfd;">
        
        <!-- Acciones de Ficha Individual -->
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
            <a href="{{ route('actividades.index', ['export' => 'single', 'id' => $act->actividad_id]) }}" class="btn-acc" style="font-size: 0.8rem; padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px; border: 1px solid #cbd5e1; border-radius: 4px; background-color: #ffffff;" target="_blank">
                📥 Descargar Ficha (Excel)
            </a>
            <button type="button" class="btn-acc btn-copiar-actividad" style="font-size: 0.8rem; padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px; border: 1px solid #cbd5e1; border-radius: 4px; background-color: #ffffff; cursor: pointer;"
                    data-id="{{ $act->actividad_id }}"
                    data-fecha="{{ $actDate->format('d-m-Y') }}"
                    data-region="{{ $act->region }}"
                    data-tipounidad="{{ $act->tipo_unidad }}"
                    data-unidadop="{{ $act->unidad_operativa }}"
                    data-tipoact="{{ $act->tipo }}"
                    data-nombre="{{ $act->nombre_actividad }}"
                    data-objetivo="{{ $act->objetivo }}"
                    data-participantes="{{ $act->n_participantes }}"
                    data-ubicacion="{{ $act->ubicacion }}"
                    data-observacion="{{ $act->observacion }}"
                    onclick="copiarFilaExcelUnica(this)">
                📋 Copiar para Excel
            </button>
        </div>

        <!-- Ficha de Datos Técnicos -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; background-color: #ffffff; border: 1px solid #e2e8f0; padding: 15px; border-radius: 6px;">
            <div>
                <strong style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; display: block; margin-bottom: 4px;">Unidad Operativa</strong>
                <span style="font-size: 0.9rem; color: #0d1b2a; font-weight: 600;">{{ $act->unidad_operativa }}</span>
            </div>
            <div>
                <strong style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; display: block; margin-bottom: 4px;">Tipo de Unidad</strong>
                <span style="font-size: 0.9rem; color: #0d1b2a; font-weight: 600;">{{ $act->tipo_unidad }}</span>
            </div>
            <div>
                <strong style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; display: block; margin-bottom: 4px;">Participantes</strong>
                <span style="font-size: 0.9rem; color: #0d1b2a; font-weight: 600;">{{ $act->n_participantes }} personas</span>
            </div>
            <div>
                <strong style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; display: block; margin-bottom: 4px;">Ubicación</strong>
                <span style="font-size: 0.9rem; color: #0d1b2a; font-weight: 600;">{{ $act->ubicacion }}</span>
            </div>
        </div>

        <!-- Objetivo -->
        <div style="margin-bottom: 15px;">
            <h4 style="margin: 0 0 6px 0; font-size: 0.85rem; color: #475569; text-transform: uppercase; font-weight: 700;">Objetivo / Descripción</h4>
            <p style="margin: 0; font-size: 0.95rem; color: #0d1b2a; line-height: 1.5;">{{ $act->objetivo }}</p>
        </div>

        <!-- Observaciones -->
        @if($act->observacion)
            <div style="margin-bottom: 20px; border-top: 1px dashed #e2e8f0; padding-top: 15px;">
                <h4 style="margin: 0 0 6px 0; font-size: 0.85rem; color: #475569; text-transform: uppercase; font-weight: 700;">Observaciones</h4>
                <p style="margin: 0; font-size: 0.95rem; color: #475569; line-height: 1.5;">{{ $act->observacion }}</p>
            </div>
        @endif

        <!-- Documentos de Respaldo Firmados -->
        @if($act->archivos->isNotEmpty())
            <div style="border-top: 1px dashed #e2e8f0; padding-top: 15px;">
                <h4 style="margin: 0 0 10px 0; font-size: 0.85rem; color: #0F69C4; text-transform: uppercase; font-weight: 700;">Documentos de Respaldo</h4>
                <div style="overflow-x: auto;">
                    <table class="table-custom-data" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Nombre del Documento</th>
                                <th>Tipo</th>
                                <th>Tamaño</th>
                                <th style="text-align: right;">Descarga</th>
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
                                        <a href="{{ asset('storage/' . $archivo->archivo_ruta) }}" download style="font-weight: 700; color: #0F69C4;">Descargar</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
</div>