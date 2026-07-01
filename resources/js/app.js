/**
 * GESTOR DE DASHBOARD - CAJBIOBIO (Vanilla JS)
 */
const DashboardManager = {
    init() {
        this.initFilters();
        this.initAccordions();
        document.addEventListener("livewire:navigated", () => this.init());
    },

    normalizeText(text) {
        return text
            .toString()
            .toLowerCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "");
    },

    initFilters() {
        document
            .querySelectorAll("[data-unit-container]")
            .forEach((c) => this.applyUnitFilters(c));
        document.addEventListener("input", (e) => {
            if (e.target.classList.contains("js-unit-search")) {
                this.applyUnitFilters(
                    e.target.closest("[data-unit-container]"),
                );
            }
        });
        document.addEventListener("click", (e) => {
            const btn = e.target.closest(".js-unit-filter-btn");
            if (btn) {
                const container = btn.closest("[data-unit-container]");
                container
                    .querySelectorAll(".js-unit-filter-btn")
                    .forEach((b) => {
                        b.classList.remove("active-filter");
                        b.style.backgroundColor = "#f8fafc";
                        b.style.color = "#475569";
                    });
                btn.classList.add("active-filter");
                btn.style.backgroundColor =
                    btn.dataset.activeColor || "#0F69C4";
                btn.style.color = "#ffffff";
                container.dataset.currentFilter = btn.dataset.filter;
                this.applyUnitFilters(container);
            }
        });
    },

    applyUnitFilters(container) {
        if (!container) return;
        const searchTerm = this.normalizeText(
            container.querySelector(".js-unit-search").value,
        );
        const currentStatus = container.dataset.currentFilter || "all";
        container.querySelectorAll("[data-unit-row]").forEach((row) => {
            const matchesSearch = this.normalizeText(
                row.dataset.unitName || "",
            ).includes(searchTerm);
            const matchesStatus =
                currentStatus === "all" ||
                row.dataset.unitStatus === currentStatus;
            row.style.display = matchesSearch && matchesStatus ? "" : "none";
        });
    },

    initAccordions() {
        document.addEventListener("click", (e) => {
            const toggle = e.target.closest(".js-region-toggle");
            if (toggle) {
                const targetRow = document.getElementById(
                    toggle.dataset.target,
                );
                if (targetRow) {
                    const isHidden = targetRow.style.display === "none";
                    targetRow.style.display = isHidden ? "" : "none";
                    toggle.style.backgroundColor = isHidden
                        ? "rgba(15, 105, 196, 0.04)"
                        : "";
                    const icon = toggle.querySelector(".js-accordion-icon");
                    if (icon) icon.textContent = isHidden ? "▲" : "▼";
                }
            }
        });
    },
};

/**
 * REGISTRO DE COMPONENTES ALPINE.JS
 */
const AlpineRegistry = {
    init() {
        document.addEventListener("alpine:init", () => {
            Alpine.data("timerComponent", this.timerComponent);
            Alpine.data("importSummary", this.importSummary);
        });
    },

    timerComponent() {
        return {
            timeLeft: 10,
            timerInterval: null,
            init() {
                this.timerInterval = setInterval(() => {
                    if (this.timeLeft > 1) this.timeLeft--;
                    else {
                        clearInterval(this.timerInterval);
                        this.$wire.set("step", 4);
                    }
                }, 1000);
            },
            cancel() {
                clearInterval(this.timerInterval);
                this.$wire.cancelSend();
            },
        };
    },

    importSummary(rows) {
        return {
            rows: rows,
            get stats() {
                const summary = { tipos: {}, subtipos: {}, unidades: {} };
                this.rows.forEach((row) => {
                    const t = row["TIPO_MODIFICADO"] || "Sin Clasificar";
                    const s = row["SUB_TIPO_MODIFICADO"] || "Sin Clasificar";
                    const u = row["TIPO_UNIDAD"] || "Otros";
                    summary.tipos[t] = (summary.tipos[t] || 0) + 1;
                    summary.subtipos[s] = (summary.subtipos[s] || 0) + 1;
                    summary.unidades[u] = (summary.unidades[u] || 0) + 1;
                });
                return summary;
            },
        };
    },
};

// Inicialización Global
document.addEventListener("DOMContentLoaded", () => {
    DashboardManager.init();
});
AlpineRegistry.init();
