/**
 * GESTOR DE DASHBOARD - CAJBIOBIO (Vanilla JS)
 * Maneja filtrado de unidades, acordeones de regiones y búsqueda fuzzy.
 */
const DashboardManager = {
    init() {
        this.initFilters();
        this.initAccordions();
        
        // Reinicializar tras ciclos de Livewire si fuera necesario
        document.addEventListener('livewire:navigated', () => this.init());
    },

    /**
     * Normaliza texto eliminando acentos y convirtiendo a minúsculas para búsqueda precisa.
     */
    normalizeText(text) {
        return text.toString().toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    },

    initFilters() {
        // Ejecutar el filtro inicial para cada contenedor en la página
        document.querySelectorAll('[data-unit-container]').forEach(container => {
            this.applyUnitFilters(container);
        });

        // Escuchar cambios en inputs de búsqueda de unidades
        document.addEventListener('input', (e) => {
            if (e.target.classList.contains('js-unit-search')) {
                this.applyUnitFilters(e.target.closest('[data-unit-container]'));
            }
        });

        // Escuchar clicks en botones de filtro de estado (Pendientes, Al día, etc)
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.js-unit-filter-btn');
            if (btn) {
                const container = btn.closest('[data-unit-container]');
                const filterValue = btn.dataset.filter;
                
                // Actualizar UI de botones
                container.querySelectorAll('.js-unit-filter-btn').forEach(b => {
                    b.classList.remove('active-filter');
                    b.style.backgroundColor = '#f8fafc';
                    b.style.color = '#475569';
                });
                
                btn.classList.add('active-filter');
                btn.style.backgroundColor = btn.dataset.activeColor || '#0F69C4';
                btn.style.color = '#ffffff';
                
                container.dataset.currentFilter = filterValue;
                this.applyUnitFilters(container);
            }
        });
    },

    applyUnitFilters(container) {
        if (!container) return;

        const searchInput = container.querySelector('.js-unit-search');
        const searchTerm = this.normalizeText(searchInput.value);
        const currentStatus = container.dataset.currentFilter || 'all';
        const rows = container.querySelectorAll('[data-unit-row]');

        rows.forEach(row => {
            const unitName = this.normalizeText(row.dataset.unitName || '');
            const unitStatus = row.dataset.unitStatus;

            const matchesSearch = unitName.includes(searchTerm);
            const matchesStatus = currentStatus === 'all' || unitStatus === currentStatus;

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    },

    initAccordions() {
        document.addEventListener('click', (e) => {
            const toggle = e.target.closest('.js-region-toggle');
            if (toggle) {
                const targetId = toggle.dataset.target;
                const targetRow = document.getElementById(targetId);
                
                if (targetRow) {
                    const isHidden = targetRow.style.display === 'none';
                    targetRow.style.display = isHidden ? '' : 'none';
                    
                    // Actualizar estilo de la fila de cabecera
                    toggle.style.backgroundColor = isHidden ? 'rgba(15, 105, 196, 0.04)' : '';
                    const icon = toggle.querySelector('.js-accordion-icon');
                    if (icon) icon.textContent = isHidden ? '▲' : '▼';
                }
            }
        });
    }
};

// Inicialización
document.addEventListener('DOMContentLoaded', () => {
    DashboardManager.init();
});

// Compatibilidad con temporizador Alpine (se mantiene el existente pero corregido)
document.addEventListener("alpine:init", () => {
    Alpine.data("timerComponent", () => ({
        timeLeft: 10,
        timerInterval: null,
        init() {
            this.timerInterval = setInterval(() => {
                if (this.timeLeft > 1) {
                    this.timeLeft--;
                } else {
                    clearInterval(this.timerInterval);
                    this.$wire.set("step", 4);
                }
            }, 1000);
        },
        cancel() {
            clearInterval(this.timerInterval);
            this.$wire.cancelSend();
        }
    }));
});
