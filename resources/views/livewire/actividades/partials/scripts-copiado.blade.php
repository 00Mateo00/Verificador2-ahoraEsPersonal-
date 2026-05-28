<script>
    function copiarFilaExcelUnica(button) {
        const id = button.getAttribute('data-id');
        const headers = ["ID", "Fecha Realización", "Región", "Tipo Unidad", "Unidad Operativa", "Tipo Actividad", "Nombre Actividad", "Objetivo", "N° Participantes", "Ubicación", "Observaciones"];
        const data = [
            id,
            button.getAttribute('data-fecha'),
            button.getAttribute('data-region'),
            button.getAttribute('data-tipounidad'),
            button.getAttribute('data-unidadop'),
            button.getAttribute('data-tipoact'),
            button.getAttribute('data-nombre'),
            button.getAttribute('data-objetivo'),
            button.getAttribute('data-participantes'),
            button.getAttribute('data-ubicacion'),
            button.getAttribute('data-observacion')
        ];
        
        const tsvContent = headers.join("\t") + "\n" + data.join("\t");
        navigator.clipboard.writeText(tsvContent).then(() => {
            const originalText = button.innerHTML;
            button.innerHTML = '✓ ¡Copiado!';
            button.style.backgroundColor = '#2b8a3e';
            button.style.color = '#ffffff';
            setTimeout(() => {
                button.innerHTML = originalText;
                button.style.backgroundColor = '#ffffff';
                button.style.color = 'inherit';
            }, 2000);
        });
    }

    function copiarTsvSeleccionados() {
        const buttons = document.querySelectorAll('.btn-copiar-actividad');
        const checkedBoxes = document.querySelectorAll('input[wire\\:model\\.live="selectedIds"]:checked, input[wire\\:model="selectedIds"]:checked');
        
        if (checkedBoxes.length === 0) {
            alert('Debe seleccionar al menos una actividad.');
            return;
        }

        const headers = ["ID", "Fecha Realización", "Región", "Tipo Unidad", "Unidad Operativa", "Tipo Actividad", "Nombre Actividad", "Objetivo", "N° Participantes", "Ubicación", "Observaciones"];
        let rows = [headers.join("\t")];

        checkedBoxes.forEach(box => {
            const id = box.value;
            const matchButton = Array.from(buttons).find(b => b.getAttribute('data-id') === id);
            if (matchButton) {
                const rowData = [
                    id,
                    matchButton.getAttribute('data-fecha'),
                    matchButton.getAttribute('data-region'),
                    matchButton.getAttribute('data-tipounidad'),
                    matchButton.getAttribute('data-unidadop'),
                    matchButton.getAttribute('data-tipoact'),
                    matchButton.getAttribute('data-nombre'),
                    matchButton.getAttribute('data-objetivo'),
                    matchButton.getAttribute('data-participantes'),
                    matchButton.getAttribute('data-ubicacion'),
                    matchButton.getAttribute('data-observacion')
                ];
                rows.push(rowData.join("\t"));
            }
        });

        navigator.clipboard.writeText(rows.join("\n")).then(() => {
            const copyBtn = document.getElementById('btnCopiarMasivo');
            const originalText = copyBtn.innerHTML;
            copyBtn.innerHTML = '✓ ¡Copiado al Portapapeles!';
            copyBtn.style.backgroundColor = '#2b8a3e';
            copyBtn.style.color = '#ffffff';
            setTimeout(() => {
                copyBtn.innerHTML = originalText;
                copyBtn.style.backgroundColor = 'transparent';
                copyBtn.style.color = 'inherit';
            }, 2000);
        });
    }
</script>