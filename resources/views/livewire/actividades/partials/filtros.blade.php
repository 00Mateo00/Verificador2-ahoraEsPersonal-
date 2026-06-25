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
    <div x-show="advancedOpen" x-transition class="dashboard-filter-advanced" x-bind:style="advancedOpen ? 'display:grid' : 'display:none'">

        <div class="form-group-item" style="margin: 0;">
            <label for="mes" style="font-size: 0.8rem; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Mes de Actividad</label>
            <select wire:model.live="mes" id="mes" class="form-select-control" style="width: 100%; box-sizing: border-box; padding: 12px 14px; border: 1px solid #dee2e6; border-radius: 4px;">
                <option value="">Todos los meses</option>
                <option value="1">Enero</option>
                <option value="2">Febrero</option>
                <option value="3">Marzo</option>
                <option value="4">Abril</option>
                <option value="5">Mayo</option>
                <option value="6">Junio</option>
                <option value="7">Julio</option>
                <option value="8">Agosto</option>
                <option value="9">Septiembre</option>
                <option value="10">Octubre</option>
                <option value="11">Noviembre</option>
                <option value="12">Diciembre</option>
            </select>
        </div>

        @if(Auth::user()->rol === \App\Enums\UserRole::Admin || Auth::user()->rol === \App\Enums\UserRole::Auditor)
        <div class="form-group-item" style="margin: 0;">
            <label for="director_filtro" style="font-size: 0.8rem; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Director Regional</label>
            <select wire:model.live="director_filtro" id="director_filtro" class="form-select-control" style="width: 100%; box-sizing: border-box; padding: 12px 14px; border: 1px solid #dee2e6; border-radius: 4px;">
                <option value="">Todos los directores</option>
                @foreach($directoresRegionales as $dir)
                    <option value="{{ $dir->id }}">{{ $dir->name }}</option>
                @endforeach
            </select>
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
        @endif
    </div>

    <div class="filter-actions">
        <!-- Botón de actualización manual inmediata (Item 4.5) -->
        <button type="button" 
                wire:click="$refresh" 
                class="btn-secondary" 
                style="padding: 8px 16px; font-size: 0.85rem; border: 1px solid #0F69C4; color: #0F69C4; border-radius: 4px; cursor: pointer; background: rgba(15, 105, 196, 0.05); font-weight: 600;"
                wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="$refresh"> Actualizar Datos</span>
            <span wire:loading wire:target="$refresh">⏳ Actualizando...</span>
        </button>
        <button type="button" wire:click="$set('buscar', ''); $set('ano', ''); $set('mes', ''); $set('director_filtro', ''); $set('tipo', ''); $set('actividad_id', ''); $set('unidad_filtro', '');" class="btn-secondary" style="padding: 8px 16px; font-size: 0.85rem; border: 1px solid #cbd5e1; border-radius: 4px; cursor: pointer; background: transparent;">
            Limpiar Filtros
        </button>
    </div>