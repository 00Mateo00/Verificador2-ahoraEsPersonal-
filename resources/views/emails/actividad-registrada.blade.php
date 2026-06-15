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

        #email-body .section-title {
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 16px;
            font-weight: bold;
            color: #111827 !important;
        }

        #email-body table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        #email-body td {
            padding: 10px 0;
            vertical-align: top;
            font-size: 14px;
            border-bottom: 1px solid #eeeeee;
            color: #333333 !important;
        }

        #email-body .label {
            width: 240px;
            font-weight: bold;
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
        }

        @media only screen and (max-width: 600px) {
            #email-body .email-container {
                padding: 25px;
            }

            #email-body td {
                display: block;
                width: 100% !important;
                border-bottom: none;
                padding: 4px 0;
            }

            #email-body .label {
                margin-top: 12px;
            }

            #email-body .button {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body id="email-body">
    <div class="email-container">

        <p>{{ $saludo }}.</p>

        <p>
            Junto con saludar, por medio del presente se remite información adjunta
            correspondiente a los verificadores de la actividad de promoción realizada,
            para los fines que estime pertinentes.
        </p>

        <p class="section-title">
            Antecedentes de la actividad
        </p>

        <table>
            <tr>
                <td class="label">Fecha actividad:</td>
                <td>{{ $actividad->FECHA ? $actividad->FECHA->format('d-m-Y') : 'N/A' }}</td>
            </tr>

            <tr>
                <td class="label">Unidad responsable:</td>
                <td>{{ $actividad->UNIDAD }}</td>
            </tr>

            <tr>
                <td class="label">N° ID de ingreso en sistema:</td>
                <td>#{{ $actividad->actividad_id }}</td>
            </tr>
        </table>

        <div class="button-wrapper">
            <a
                href="{{ route('admin.actividades', ['id' => $actividad->actividad_id]) }}"
                class="button"
            >
                REVISAR ACTIVIDAD EN SISTEMA
            </a>
        </div>

        <p>Sin otro particular, saluda atentamente,</p>

        <p class="footer">
            Atte:<br>

            <strong>
                {{ $actividad->usuario->persona->nombre_completo ?? $actividad->usuario->usuario_nombre }}
            </strong>
        </p>

    </div>
</body>
</html>