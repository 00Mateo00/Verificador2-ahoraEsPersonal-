@php
    $hora = now()->hour;
    $saludo = ($hora < 12) ? 'Buenos días' : 'Buenas tardes';
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        #email-body,
        #email-body * {
            box-sizing: border-box;
        }

        #email-body {
            margin: 0;
            padding: 40px 20px;
            background-color: #f4f6f8 !important;
            font-family: Arial, Helvetica, sans-serif !important;
            color: #333333 !important;
            line-height: 1.6;
        }

        #email-body .email-container {
            max-width: 700px;
            margin: 0 auto;
            background-color: #ffffff !important;
            border-radius: 8px;
            padding: 40px;
            border: 1px solid #e5e7eb;
        }

        #email-body p {
            margin: 0 0 16px 0;
            color: #333333 !important;
            font-size: 15px;
        }

        #email-body strong {
            color: #111827 !important;
        }

        #email-body .button-wrapper {
            margin-top: 30px;
            margin-bottom: 30px;
        }

        #email-body .button {
            display: inline-block;
            background-color: #0059c2 !important;
            color: #ffffff !important;
            text-decoration: none !important;
            padding: 14px 24px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
        }

        #email-body .footer {
            margin-top: 30px;
            font-size: 14px;
            border-top: 1px solid #eeeeee;
            padding-top: 15px;
        }
    </style>
</head>

<body id="email-body">
    <div class="email-container">

        <p>{{ $saludo }}, equipo de <strong>{{ $unidad->unidad_nombre }}</strong>.</p>

        <p>
            Junto con saludar, se les informa que se ha procesado una nueva carga masiva de actividades en la Intranet institucional y se han detectado **nuevos registros asignados a su unidad operativa**.
        </p>

        <p>
            Es necesario que ingresen a la plataforma para revisar estas actividades pendientes, adjuntar los documentos de respaldo (verificadores) correspondientes y proceder con su firma o validación.
        </p>

        <div class="button-wrapper">
            <a href="{{ route('actividades.index') }}" class="button">
                IR A MIS ACTIVIDADES PENDIENTES
            </a>
        </div>

        <p>Por favor, recuerden que los respaldos deben subirse en formato digital (PDF, Word, Excel o imágenes) con un límite máximo de 5MB por archivo.</p>

        <p class="footer">
            Atentamente,<br>
            <strong>Intranet de Verificación de Actividades</strong><br>
            Corporación de Asistencia Judicial de la Región del Biobío
        </p>

    </div>
</body>
</html>