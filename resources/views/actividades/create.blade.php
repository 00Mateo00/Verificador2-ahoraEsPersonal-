@extends('layouts.app')

@section('title', 'Registrar Actividad - FirmaGob')

@section('breadcrumbs')
<a href="#">Actividades</a>
<span class="separator">‣</span>
<span>Registrar Actividades</span>
@endsection



@section('content')
<div class="panel-header-section">
    <h2>Registrar Nueva Actividad Institucional</h2>
    <p style="margin: 5px 0 0; color: var(--color-text-light); font-size: 0.95rem;">
        Complete el siguiente formulario para dejar registro de la actividad realizada. Todos los campos de
        texto tienen límites de caracteres para asegurar el orden público del reporte.
    </p>
</div>

<livewire:actividades.create-form />
@endsection

@stack('styles')

<style>
    .form-grid-columns-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .file-selected-badge {
        background-color: #f8fafc;
        border: 1px solid var(--color-border);
        padding: 10px 15px;
        border-radius: 4px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .file-selected-badge button {
        background: none;
        border: none;
        color: #ef3340;
        font-size: 1.2rem;
        cursor: pointer;
        line-height: 1;
    }
</style>

@push('scripts')
<script>
    function confirmarVolver() {
        if (confirm('¿Está seguro de que desea volver? Se perderán los datos no guardados.')) {
            window.location.href = '{{ route("home") }}';
        }
    }

    function confirmarGuardar(e) {
        console.log(e)
        const form = document.querySelector('#actividadForm');
        if (form.checkValidity()) {

            if (confirm('¿Está seguro de que desea registrar y guardar esta actividad?')) {
                console.log(form);
                form.submit();
            }
        } else {
            form.reportValidity();
        }
    }

    function clearFile() {
        selectedFiles = [];
        updateFileInput();
        renderFileList();
    }

    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('archivo');
    const fileList = document.getElementById('file-list');
    let selectedFiles = [];

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropzone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
        }, false);
    });

    dropzone.addEventListener('drop', (e) => {
        const files = Array.from(e.dataTransfer.files);
        addFiles(files);
    });

    fileInput.addEventListener('change', (e) => {
        const files = Array.from(e.target.files);
        addFiles(files);
    });

    function addFiles(files) {
        if ((selectedFiles.length + files.length) > 10) {
            alert('Máximo 10 archivos permitidos');
            return;
        }

        const allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
        const maxSize = 5 * 1024 * 1024;

        files.forEach(file => {
            const extension = file.name.split('.').pop().toLowerCase();
            if (!allowedExtensions.includes(extension)) {
                alert(`Archivo no permitido: ${file.name}`);
                return;
            }
            if (file.size > maxSize) {
                alert(`Archivo excede 5MB: ${file.name}`);
                return;
            }
            selectedFiles.push(file);
        });

        updateFileInput();
        renderFileList();
    }

    function updateFileInput() {
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;
    }

    function renderFileList() {
        fileList.innerHTML = '';
        if (selectedFiles.length === 0) return;

        selectedFiles.forEach((file, index) => {
            const item = document.createElement('div');
            item.className = 'file-selected-badge';
            item.style.marginBottom = '10px';
            item.innerHTML = `
                <span>Archivo: <strong>${file.name}</strong></span>
                <button type="button" onclick="removeFile(${index})">×</button>
            `;
            fileList.appendChild(item);
        });
    }

    function removeFile(index) {
        selectedFiles.splice(index, 1);
        updateFileInput();
        renderFileList();
    }
</script>
@endpush