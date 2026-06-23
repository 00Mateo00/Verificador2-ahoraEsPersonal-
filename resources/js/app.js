document.addEventListener('alpine:init', () => {
    // Caso 1: Componente de Alpine para el temporizador de la importación
    Alpine.data('timerComponent', () => ({
        timeLeft: 10,
        timerInterval: null,
        init() {
            this.timeLeft = 10;
            this.timerInterval = setInterval(() => {
                if (this.timeLeft > 1) {
                    this.timeLeft--;
                } else {
                    clearInterval(this.timerInterval);
                    this.$wire.set('step', 4); // Transición síncrona al Paso 4
                }
            }, 1000);
        },
        cancel() {
            clearInterval(this.timerInterval);
            this.$wire.cancelSend();
        }
    }));
});