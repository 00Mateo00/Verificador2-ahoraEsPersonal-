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

        <p>{{ $saludo }}, equipo de la <strong>Corporación de Asistencia Judicial</strong>.</p>

        <p>
            Hemos recibido una solicitud de restablecimiento de contraseña para su cuenta de usuario de la Intranet institucional.
        </p>

        <div class="button-wrapper">
            <a href="{{ $url }}" class="button">
                RESTABLECER CONTRASEÑA
            </a>
        </div>

        <p>
            Este enlace de restablecimiento de contraseña expirará en 60 minutos de forma automática por motivos de seguridad.
        </p>

        <p>
            Si usted no solicitó este cambio, no se requiere realizar ninguna acción adicional y sus credenciales actuales se mantendrán seguras.
        </p>

        <p class="footer">
            Atentamente,<br>
            <strong>Intranet de Verificación de Actividades</strong><br>
            Corporación de Asistencia Judicial de la Región del Biobío
        </p>

    </div>
</body>
</html>