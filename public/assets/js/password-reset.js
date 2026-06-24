document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    const submitBtn = document.querySelector('button[type="submit"]');
    const form = document.querySelector('form');
    
    const reqList = document.getElementById('password-requirements');
    if (!reqList) return;

    // Leer configuraciones parametrizadas desde data attributes
    const minLength = parseInt(reqList.getAttribute('data-min-length') || '8', 10);
    const requireMixedCase = reqList.getAttribute('data-require-mixed-case') === 'true';
    const requireLetters = reqList.getAttribute('data-require-letters') === 'true';
    const requireNumbers = reqList.getAttribute('data-require-numbers') === 'true';
    const requireSymbols = reqList.getAttribute('data-require-symbols') === 'true';

    // Establecer el placeholder dinámico del input
    if (passwordInput) {
        passwordInput.placeholder = `Mínimo ${minLength} caracteres`;
    }

    // Actualizar el valor numérico en el texto del requisito de longitud
    const minLengthLabel = document.getElementById('min-length-val');
    if (minLengthLabel) {
        minLengthLabel.textContent = minLength;
    }

    // Configurar botones de visualización de contraseña (Mostrar/Ocultar)
    const toggleBtn = document.getElementById('toggle-password');
    const toggleConfirmBtn = document.getElementById('toggle-confirm-password');

    if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener('click', () => {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            toggleBtn.textContent = isPassword ? 'Ocultar' : 'Mostrar';
        });
    }

    if (toggleConfirmBtn && confirmInput) {
        toggleConfirmBtn.addEventListener('click', () => {
            const target = document.getElementById('password_confirmation');
            if (target) {
                const isPass = target.type === 'password';
                target.type = isPass ? 'text' : 'password';
                toggleConfirmBtn.textContent = isPass ? 'Ocultar' : 'Mostrar';
            }
        });
    }

    let submitted = false;

    function checkRules() {
        const val = passwordInput ? passwordInput.value : '';
        const confirmVal = confirmInput ? confirmInput.value : '';
        
        // Reglas de validación síncronas en tiempo real
        const rules = {
            length: val.length >= minLength,
            letters: !requireLetters || /[a-zA-Z]/.test(val),
            mixed: !requireMixedCase || (/[a-z]/.test(val) && /[A-Z]/.test(val)),
            numbers: !requireNumbers || /\d/.test(val),
            symbols: !requireSymbols || /[^a-zA-Z0-9]/.test(val),
            match: val.length > 0 && val === confirmVal
        };

        // Ocultar de forma limpia las directivas no configuradas en backend
        if (!requireLetters) hideElement('req-letters');
        if (!requireMixedCase) hideElement('req-mixed');
        if (!requireNumbers) hideElement('req-numbers');
        if (!requireSymbols) hideElement('req-symbols');

        const hasTyped = val.length > 0;
        const confirmTyped = confirmVal.length > 0;

        // Actualizar estados visuales de los items de requisitos
        updateItemStyle('req-length', rules.length, hasTyped);
        if (requireLetters) updateItemStyle('req-letters', rules.letters, hasTyped);
        if (requireMixedCase) updateItemStyle('req-mixed', rules.mixed, hasTyped);
        if (requireNumbers) updateItemStyle('req-numbers', rules.numbers, hasTyped);
        if (requireSymbols) updateItemStyle('req-symbols', rules.symbols, hasTyped);
        
        // El requisito de match solo se torna rojo si el usuario ya escribió en el de confirmación,
        // o si ha presionado el botón de submit para enviar el formulario.
        updateItemStyle('req-match', rules.match, confirmTyped);

        // Control de estado deshabilitado del botón de envío
        // "Debe mostrarse deshabilitado cuando: Ambos campos estén vacíos. Falte la contraseña. Falte la confirmación."
        const isButtonDisabled = val.trim() === '' || confirmVal.trim() === '';
        if (submitBtn) {
            submitBtn.disabled = isButtonDisabled;
        }

        // Retornar si cumple la totalidad de reglas válidas configuradas
        let valid = rules.length && rules.match;
        if (requireLetters) valid = valid && rules.letters;
        if (requireMixedCase) valid = valid && rules.mixed;
        if (requireNumbers) valid = valid && rules.numbers;
        if (requireSymbols) valid = valid && rules.symbols;

        return valid;
    }

    function hideElement(id) {
        const el = document.getElementById(id);
        if (el) el.style.setProperty('display', 'none', 'important');
    }

    function updateItemStyle(elementId, isValid, hasTyped) {
        const item = document.getElementById(elementId);
        if (!item) return;

        const icon = item.querySelector('.req-icon');
        
        let state = 'neutral'; // 'neutral', 'valid', 'failed'
        
        if (isValid) {
            state = 'valid';
        } else {
            if (submitted || hasTyped) {
                state = 'failed';
            }
        }

        // Inyectar clases y colores inline nativos para evitar dependencias
        if (state === 'valid') {
            item.style.color = '#2b8a3e';
            if (icon) {
                icon.style.backgroundColor = 'rgba(43, 138, 62, 0.1)';
                icon.style.color = '#2b8a3e';
                icon.textContent = '✓';
            }
        } else if (state === 'failed') {
            item.style.color = '#ef3340';
            if (icon) {
                icon.style.backgroundColor = 'rgba(239, 51, 64, 0.1)';
                icon.style.color = '#ef3340';
                icon.textContent = '✕';
            }
        } else {
            item.style.color = '#64748b';
            if (icon) {
                icon.style.backgroundColor = '#e2e8f0';
                icon.style.color = '#64748b';
                icon.textContent = '○';
            }
        }
    }

    // Registrar Event Listeners nativos e inmediatos
    if (passwordInput) passwordInput.addEventListener('input', checkRules);
    if (confirmInput) confirmInput.addEventListener('input', checkRules);

    // Ejecutar chequeo inicial sincrónico
    checkRules();

    // Bloquear envío del formulario si no se cumplen todos los requisitos
    if (form) {
        form.addEventListener('submit', (e) => {
            submitted = true;
            const allValid = checkRules();
            if (!allValid) {
                e.preventDefault();
            }
        });
    }
});