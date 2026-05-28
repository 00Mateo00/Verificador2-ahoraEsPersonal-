<div style="background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.8); padding: 15px 20px; border-radius: 8px; margin-bottom: 15px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
    <div style="display: flex; align-items: center; gap: 12px;">
        <input type="checkbox" wire:model.live="selectAll" id="selectAllCheckbox" style="width: 16px; height: 16px; cursor: pointer;">
        <label for="selectAllCheckbox" style="font-size: 0.9rem; font-weight: 600; color: #334155; cursor: pointer;">Seleccionar Todo el Conjunto</label>
    </div>

    <div style="display: flex; gap: 10px; align-items: center;">
        @if(count($selectedIds) > 0)
            <span style="font-size: 0.85rem; font-weight: 600; color: #0F69C4; background-color: rgba(15, 105, 196, 0.08); padding: 4px 10px; border-radius: 4px;">
                {{ count($selectedIds) }} Seleccionados
            </span>
            <button type="button" wire:click="exportSelected" class="btn-primary" style="background-color: #2b8a3e; color: #ffffff; padding: 8px 16px; font-size: 0.85rem; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                📊 Descargar Selección (Excel)
            </button>
            <button type="button" id="btnCopiarMasivo" class="btn-secondary" style="padding: 8px 16px; font-size: 0.85rem; border: 1px solid #cbd5e1; border-radius: 4px; cursor: pointer; background: transparent;" onclick="copiarTsvSeleccionados()">
                📋 Copiar para Excel
            </button>
        @endif
    </div>
</div>