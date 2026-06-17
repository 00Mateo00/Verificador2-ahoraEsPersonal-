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

        <p>{{ $saludo }}, <strong>{{ $user->name }}</strong>.</p>

        <p>
            Le informamos que se ha iniciado un proceso de <strong>renovación obligatoria de contraseña</strong> para su cuenta de acceso de la Intranet institucional de la Corporación de Asistencia Judicial de la Región del Biobío.
        </p>
        <div class="button-wrapper">
            <a href="{{ $url }}" class="button">
                CREAR NUEVA CONTRASEÑA
            </a>
        </div>
        <p>
            Por políticas de seguridad interna, todas las unidades operativas y de control deben renovar sus contraseñas de acceso de forma periódica cada 90 días para garantizar la confidencialidad de la información gestionada.
        </p>

        <p>
            Su contraseña actual expirará (o ha expirado ya) el día <strong>{{ $expirationDateString }}</strong>. Posterior a esta fecha, no podrá acceder al sistema de verificación de actividades sin realizar previamente el cambio correspondiente.
        </p>



        <p>
            <em>Nota importante:</em> Este enlace es seguro, de un solo uso y expirará automáticamente en un lapso de 60 minutos de haber sido solicitado. Este correo forma parte de una política de actualización periódica obligatoria y es distinto a una solicitud de recuperación de credenciales olvidadas.
        </p>

        <p class="footer">
            Atentamente,<br>
            <strong>Intranet de Verificación de Actividades</strong><br>
            Corporación de Asistencia Judicial de la Región del Biobío
        </p>

    </div>
</body>
</html>