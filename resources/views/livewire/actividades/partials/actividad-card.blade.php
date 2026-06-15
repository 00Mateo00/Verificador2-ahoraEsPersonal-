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
                                        <a href="{{ route('archivos.descargar', $archivo->archivo_id) }}" style="font-weight: 700; color: #0F69C4;">Descargar</a>
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