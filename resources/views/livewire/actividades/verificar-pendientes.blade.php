<div>
    @if($actividades->isEmpty())
        <div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 8px; padding: 40px; text-align: center; color: #64748b;">
            <span style="font-size: 2rem;">🎉</span>
            <p style="margin: 10px 0 0; font-weight: 600; font-size: 1.1rem; color: #2b8a3e;">¡Excelente! No tienes actividades pendientes de verificación.</p>
            <p style="margin: 5px 0 0; font-size: 0.9rem;">Todas las actividades asignadas a tu unidad ya cuentan con su respectivo respaldo.</p>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 20px;">
            @foreach($actividades as $act)
                <div style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                    <!-- Cabecera de la Actividad -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <span style="background-color: rgba(239, 51, 64, 0.08); color: #ef3340; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; margin-right: 8px;">
                                Pendiente
                            </span>
                            <strong style="font-size: 1rem; color: #0d1b2a;">{{ $act->TIPO_ACTIVIDAD }}</strong>
                            <p style="margin: 4px 0 0; font-size: 0.85rem; color: #64748b;">
                                Código original: <strong>{{ $act->COD ?: 'N/A' }}</strong> | Realización: <strong>{{ $act->FECHA }}</strong>
                            </p>
                        </div>
                        <div style="font-size: 0.85rem; color: #475569; background-color: #f8fafc; padding: 4px 10px; border-radius: 4px; border: 1px solid #e2e8f0;">
                            👥 <strong>{{ $act->PARTICIPANTES }}</strong> participantes
                        </div>
                    </div>

                    <!-- Detalle / Descripción -->
                    <div style="margin-bottom: 20px;">
                        <span style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 700; display: block; margin-bottom: 4px;">Detalle de Actividad</span>
                        <p style="margin: 0; font-size: 0.9rem; color: #334155; line-height: 1.5;">{{ $act->DET_ACTIVIDAD }}</p>
                    </div>

                    <!-- Formulario de Subida del Verificador -->
                    <div style="background-color: #fbfcfd; border: 1px dashed #cbd5e1; padding: 15px; border-radius: 6px; display: grid; grid-template-columns: 1fr auto; gap: 15px; align-items: center; flex-wrap: wrap;">
                        <div>
                            <label for="verificador-{{ $act->actividad_id }}" style="font-size: 0.8rem; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">
                                Adjuntar archivos respaldatorios (PDF, Word, Excel, Imagen - Máx 5MB)
                            </label>
                            
                            <!-- Input de Archivo -->
                            <input type="file" wire:model="verificadores.{{ $act->actividad_id }}" id="verificador-{{ $act->actividad_id }}" multiple style="font-size: 0.85rem; color: #475569;">
                            
                            @error('verificadores.' . $act->actividad_id)
                                <span style="color: #ef3340; font-size: 0.8rem; display: block; margin-top: 6px; font-weight: 600;">⚠️ {{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <!-- Botón de Envío -->
                            <button type="button" wire:click="verificarActividad({{ $act->actividad_id }})" class="btn-primary-caj" style="padding: 10px 20px; font-size: 0.9rem; background-color: #2b8a3e;" wire:loading.attr="disabled" wire:target="verificadores.{{ $act->actividad_id }}">
                                <span wire:loading.remove wire:target="verificarActividad({{ $act->actividad_id }})">✓ Guardar Verificador</span>
                                <span wire:loading wire:target="verificarActividad({{ $act->actividad_id }})">Guardando...</span>
                            </button>
                        </div>
                    </div>

                    <!-- Estado de Carga -->
                    <div wire:loading wire:target="verificadores.{{ $act->actividad_id }}" style="color: #0F69C4; font-size: 0.8rem; font-weight: 600; margin-top: 8px;">
                        ⏳ Subiendo archivos al servidor, por favor espere...
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top: 20px;">
            {{ $actividades->links() }}
        </div>
    @endif
</div>