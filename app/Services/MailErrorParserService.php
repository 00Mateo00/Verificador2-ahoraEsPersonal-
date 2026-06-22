<?php

namespace App\Services;

class MailErrorParserService
{
    /**
     * Mapea un mensaje de error técnico de correo a una estructura comprensible.
     */
    public static function parse(?string $rawError): array
    {
        if (empty($rawError)) {
            return [
                'key' => 'none',
                'title' => 'Sin Errores',
                'explanation' => 'No se registraron fallos de conexión o envío para este correo electrónico.',
                'suggestion' => 'El correo fue despachado exitosamente.',
                'technical' => ''
            ];
        }

        $rawErrorLower = strtolower($rawError);

        // 1. Credenciales SMTP incorrectas
        if (
            str_contains($rawErrorLower, 'auth') || 
            str_contains($rawErrorLower, '535') || 
            str_contains($rawErrorLower, 'credentials') || 
            str_contains($rawErrorLower, 'username and password not accepted')
        ) {
            return [
                'key' => 'auth_failure',
                'title' => 'Credenciales SMTP Incorrectas',
                'explanation' => 'El servidor de salida de correo rechazó las credenciales de acceso institucional (usuario o contraseña inválidos).',
                'suggestion' => 'Revise y actualice las credenciales del servidor SMTP en la configuración de la intranet.',
                'technical' => $rawError
            ];
        }

        // 2. Timeout de conexión SMTP
        if (
            str_contains($rawErrorLower, 'timeout') || 
            str_contains($rawErrorLower, 'timed out') || 
            str_contains($rawErrorLower, 'time out')
        ) {
            return [
                'key' => 'timeout',
                'title' => 'Tiempo de Espera Agotado (Timeout)',
                'explanation' => 'La conexión con el servidor de correo tardó demasiado tiempo en responder y el proceso fue abortado.',
                'suggestion' => 'Compruebe la latencia de la red del servidor o aumente el límite de timeout del mailer.',
                'technical' => $rawError
            ];
        }

        // 3. Servidor SMTP caído o inaccesible
        if (
            str_contains($rawErrorLower, 'refused') || 
            str_contains($rawErrorLower, 'unable to connect') || 
            str_contains($rawErrorLower, 'could not connect') || 
            str_contains($rawErrorLower, 'cannot connect') ||
            str_contains($rawErrorLower, 'connection could not be established')
        ) {
            return [
                'key' => 'server_down',
                'title' => 'Servidor de Correo Inaccesible',
                'explanation' => 'No se pudo establecer conexión con el servidor de salida. El servidor podría estar apagado, fuera de línea o el host/puerto es erróneo.',
                'suggestion' => 'Verifique que la dirección del host y puerto de correo sean válidos y que no existan bloqueos de firewall.',
                'technical' => $rawError
            ];
        }

        // 4. Errores de handshake o TLS/SSL
        if (
            str_contains($rawErrorLower, 'ssl') || 
            str_contains($rawErrorLower, 'tls') || 
            str_contains($rawErrorLower, 'handshake') || 
            str_contains($rawErrorLower, 'certificate') ||
            str_contains($rawErrorLower, 'encryption') ||
            str_contains($rawErrorLower, 'starttls')
        ) {
            return [
                'key' => 'ssl_error',
                'title' => 'Error de Conexión Segura (SSL/TLS)',
                'explanation' => 'La negociación de cifrado seguro falló. Esto ocurre por discrepancia de protocolos de cifrado o certificados expirados.',
                'suggestion' => 'Compruebe que el puerto coincida con el tipo de cifrado requerido (ej: SSL para puerto 465, TLS/STARTTLS para 587).',
                'technical' => $rawError
            ];
        }

        // 5. Correo de destino inexistente o rechazado
        if (
            str_contains($rawErrorLower, '550') || 
            str_contains($rawErrorLower, 'invalid recipient') || 
            str_contains($rawErrorLower, 'mailbox unavailable') || 
            str_contains($rawErrorLower, 'user unknown') || 
            str_contains($rawErrorLower, 'rejected')
        ) {
            return [
                'key' => 'recipient_rejected',
                'title' => 'Buzón de Destino Inválido o Rechazado',
                'explanation' => 'El servidor receptor rechazó la entrega. La dirección podría no existir, estar deshabilitada o tener el buzón de entrada lleno.',
                'suggestion' => 'Verifique que el correo del destinatario esté escrito correctamente y que su cuenta esté activa.',
                'technical' => $rawError
            ];
        }

        // 6. Errores no categorizados (Fallback seguro)
        return [
            'key' => 'unknown',
            'title' => 'Error Interno de Despacho',
            'explanation' => 'Ocurrió un error inesperado al procesar y despachar el correo electrónico institucional.',
            'suggestion' => 'Consulte al administrador de sistemas para revisar los logs de depuración del servidor.',
            'technical' => $rawError
        ];
    }
}