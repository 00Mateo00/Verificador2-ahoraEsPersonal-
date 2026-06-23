@php
$isAdmin = $isAdmin ?? false;
@endphp

<div class="dashboard-card dashboard-filter-card">
    <div class="filter-panel-header">
        <div>
            <h3>
                Filtros de Búsqueda {{ $isAdmin ? 'Administrador' : '' }}
            </h3>
        </div>
        <button 
            type="button" 
            @click="advancedOpen = !advancedOpen" 
            class="filter-toggle-btn">
            <span x-text="advancedOpen ? 'Ocultar Filtros Avanzados' : 'Mostrar Filtros Avanzados'"></span>
            <span x-text="advancedOpen ? '▲' : '▼'"></span>
        </button>
    </div>

    <!-- Filtros Básicos (Siempre visibles) -->
    <div class="dashboard-filter-grid">
        <div class="form-group-item" style="margin: 0;">
            <label for="buscar" style="font-size: 0.8rem; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Texto Libre</label>
            <input type="text" wire:model.live.debounce.350ms="buscar" id="buscar" class="form-input-control" placeholder="Ej: Ciberseguridad, Reunión, etc." style="width: 100%; box-sizing: border-box;">
        </div>

        @if($isAdmin)
        <div class="form-group-item" style="margin: 0;">
            <label for="actividad_id" style="font-size: 0.8rem; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Filtrar por ID (Exacto)</label>
            <input type="text" wire:model.live="actividad_id" id="actividad_id" class="form-input-control" placeholder="Ej: 142" style="width: 100%; box-sizing: border-box;">
        </div>
        @endif

        <div class="form-group-item" style="margin: 0;">
            <label for="ano" style="font-size: 0.8rem; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Año de Actividad</label>
            <select wire:model.live="ano" id="ano" class="form-select-control" style="width: 100%; box-sizing: border-box; padding: 12px 14px; border: 1px solid #dee2e6; border-radius: 4px;">
                <option value="">Todos los años</option>
                @for($y = date('Y'); $y >= 2020; $y--)
                <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>

    <!-- Filtros Avanzados (Desplegable) -->
    <div x-show="advancedOpen" x-transition class="dashboard-filter-advanced" x-bind:style:"advancedOpen ? 'display:grid' : 'display:none'">

        @if($isAdmin && isset($funcionarios))
        <div class="form-group-item" style="margin: 0;">
            <label for="funcionario_id" style="font-size: 0.8rem; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Filtrar por Funcionario</label>
            <select wire:model.live="funcionario_id" id="funcionario_id" class="form-select-control" style="width: 100%; box-sizing: border-box; padding: 12px 14px; border: 1px solid #dee2e6; border-radius: 4px;">
                <option value="">Todos los funcionarios</option>
                @foreach($funcionarios as $f)
                <option value="{{ $f->usuario_id }}">
                    {{ $f->persona ? $f->persona->persona_nombre . ' ' . $f->persona->persona_apellido : $f->usuario_nombre }}
                </option>
                @endforeach
            </select>

        </div>

        <div class="form-group-item" style="margin: 0;">
            <label for="estado" style="font-size: 0.8rem; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Estado del Reporte</label>
            <select wire:model.live="estado" id="estado" class="form-select-control" style="width: 100%; box-sizing: border-box; padding: 12px 14px; border: 1px solid #dee2e6; border-radius: 4px;">
                <option value="1">Activos</option>
                <option value="0">Ocultos / Inactivos</option>
                <option value="">Todos los registros</option>
            </select>
        </div>
        <div class="form-group-item" style="margin: 0;">
            <label for="region" style="font-size: 0.8rem; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Región</label>
            <select wire:model.live="region" id="region" class="form-select-control" style="width: 100%; box-sizing: border-box; padding: 12px 14px; border: 1px solid #dee2e6; border-radius: 4px;">
                <option value="">Todas las regiones</option>
                <option value="Región Metropolitana">Región Metropolitana</option>
                <option value="Región de Valparaíso">Región de Valparaíso</option>
                <option value="Región del Biobío">Región del Biobío</option>
                <option value="Región de Antofagasta">Región de Antofagasta</option>
                <option value="Región de la Araucanía">Región de la Araucanía</option>
            </select>
        </div>
        <div class="form-group-item" style="margin: 0;">
            <label for="tipo_unidad" style="font-size: 0.8rem; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Tipo Unidad</label>
            <select wire:model.live="tipo_unidad" id="tipo_unidad" class="form-select-control" style="width: 100%; box-sizing: border-box; padding: 12px 14px; border: 1px solid #dee2e6; border-radius: 4px;">
                <option value="">Todos los tipos...</option>
                <option value="División Tecnológica">División Tecnológica</option>
                <option value="Departamento de Operaciones">Departamento de Operaciones</option>
                <option value="Oficina de Atención Ciudadana">Oficina de Atención Ciudadana</option>
                <option value="Dirección Nacional">Dirección Nacional</option>
            </select>
        </div>
        @endif

        <div class="form-group-item" style="margin: 0;">
            <label for="fecha_inicio" style="font-size: 0.8rem; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Fecha Desde</label>
            <input type="date" wire:model.live="fecha_inicio" id="fecha_inicio" class="form-input-control" style="width: 100%; box-sizing: border-box;">
        </div>

        <div class="form-group-item" style="margin: 0;">
            <label for="fecha_fin" style="font-size: 0.8rem; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Fecha Hasta</label>
            <input type="date" wire:model.live="fecha_fin" id="fecha_fin" class="form-input-control" style="width: 100%; box-sizing: border-box;">
        </div>

        <div class="form-group-item" style="margin: 0;">
            <label for="tipo" style="font-size: 0.8rem; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Tipo Actividad</label>
            <select wire:model.live="tipo" id="tipo" class="form-select-control" style="width: 100%; box-sizing: border-box; padding: 12px 14px; border: 1px solid #dee2e6; border-radius: 4px;">
                <option value="">Todos los tipos...</option>
                <option value="Capacitación Interna">Capacitación Interna</option>
                <option value="Reunión Bilateral">Reunión Bilateral</option>
                <option value="Auditoría de Control">Auditoría de Control</option>
                <option value="Despliegue en Terreno">Despliegue en Terreno</option>
            </select>
        </div>
    </div>

    <div class="filter-actions">
        <!-- Botón de actualización manual inmediata (Item 4.5) -->
        <button type="button" 
                wire:click="$refresh" 
                class="btn-secondary" 
                style="padding: 8px 16px; font-size: 0.85rem; border: 1px solid #0F69C4; color: #0F69C4; border-radius: 4px; cursor: pointer; background: rgba(15, 105, 196, 0.05); font-weight: 600;"
                wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="$refresh">🔄 Actualizar Datos</span>
            <span wire:loading wire:target="$refresh">⏳ Actualizando...</span>
        </button>
        <button type="button" wire:click="$set('buscar', ''); $set('ano', ''); $set('fecha_inicio', ''); $set('fecha_fin', ''); $set('region', ''); $set('tipo_unidad', ''); $set('tipo', ''); $set('actividad_id', ''); {{ $isAdmin ? '$set(\'funcionario_id\', \'\'); $set(\'estado\', \'1\');' : '' }}" class="btn-secondary" style="padding: 8px 16px; font-size: 0.85rem; border: 1px solid #cbd5e1; border-radius: 4px; cursor: pointer; background: transparent;">
            Limpiar Filtros
        </button>
    </div>
</div>