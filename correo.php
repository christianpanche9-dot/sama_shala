<?php
function smtp_leer_respuesta($conexion_smtp): string
{
    $respuesta = '';
    while (($linea = fgets($conexion_smtp, 515)) !== false) {
        $respuesta .= $linea;
        if (isset($linea[3]) && $linea[3] === ' ') {
            break;
        }
    }
    return $respuesta;
}

function smtp_codigo(string $respuesta): int
{
    return (int) substr($respuesta, 0, 3);
}

function enviar_correo(string $destinatario, string $asunto, string $cuerpo_html): array
{
    $config_disponible = file_exists(__DIR__ . '/correo_config.php');
    if (!$config_disponible) {
        error_log("[correo] correo_config.php no existe, no se puede enviar a $destinatario");
        return ['ok' => false, 'error' => 'No hay configuración de correo.'];
    }
    require __DIR__ . '/correo_config.php';

    $conexion_smtp = @stream_socket_client(
        "ssl://{$correo_smtp_host}:{$correo_smtp_puerto}",
        $codigo_error,
        $mensaje_error,
        15
    );
    if (!$conexion_smtp) {
        error_log("[correo] No se pudo conectar a {$correo_smtp_host}:{$correo_smtp_puerto} - $mensaje_error");
        return ['ok' => false, 'error' => 'No se pudo conectar al servidor de correo.'];
    }

    stream_set_timeout($conexion_smtp, 15);

    $pasos_fallidos = false;
    $error_detalle = '';

    $respuesta = smtp_leer_respuesta($conexion_smtp);
    if (smtp_codigo($respuesta) !== 220) {
        $pasos_fallidos = true;
        $error_detalle = "Saludo inicial inválido: $respuesta";
    }

    if (!$pasos_fallidos) {
        fwrite($conexion_smtp, "EHLO " . parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? 'samashala.com'), PHP_URL_HOST) . "\r\n");
        $respuesta = smtp_leer_respuesta($conexion_smtp);
        if (smtp_codigo($respuesta) !== 250) {
            $pasos_fallidos = true;
            $error_detalle = "EHLO falló: $respuesta";
        }
    }

    if (!$pasos_fallidos) {
        fwrite($conexion_smtp, "AUTH LOGIN\r\n");
        $respuesta = smtp_leer_respuesta($conexion_smtp);
        if (smtp_codigo($respuesta) !== 334) {
            $pasos_fallidos = true;
            $error_detalle = "AUTH LOGIN falló: $respuesta";
        }
    }

    if (!$pasos_fallidos) {
        fwrite($conexion_smtp, base64_encode($correo_smtp_usuario) . "\r\n");
        $respuesta = smtp_leer_respuesta($conexion_smtp);
        if (smtp_codigo($respuesta) !== 334) {
            $pasos_fallidos = true;
            $error_detalle = "Usuario SMTP rechazado: $respuesta";
        }
    }

    if (!$pasos_fallidos) {
        fwrite($conexion_smtp, base64_encode($correo_smtp_password) . "\r\n");
        $respuesta = smtp_leer_respuesta($conexion_smtp);
        if (smtp_codigo($respuesta) !== 235) {
            $pasos_fallidos = true;
            $error_detalle = "Autenticación SMTP falló: $respuesta";
        }
    }

    if (!$pasos_fallidos) {
        fwrite($conexion_smtp, "MAIL FROM:<{$correo_smtp_usuario}>\r\n");
        $respuesta = smtp_leer_respuesta($conexion_smtp);
        if (smtp_codigo($respuesta) !== 250) {
            $pasos_fallidos = true;
            $error_detalle = "MAIL FROM falló: $respuesta";
        }
    }

    if (!$pasos_fallidos) {
        fwrite($conexion_smtp, "RCPT TO:<{$destinatario}>\r\n");
        $respuesta = smtp_leer_respuesta($conexion_smtp);
        if (smtp_codigo($respuesta) !== 250 && smtp_codigo($respuesta) !== 251) {
            $pasos_fallidos = true;
            $error_detalle = "RCPT TO falló: $respuesta";
        }
    }

    if (!$pasos_fallidos) {
        fwrite($conexion_smtp, "DATA\r\n");
        $respuesta = smtp_leer_respuesta($conexion_smtp);
        if (smtp_codigo($respuesta) !== 354) {
            $pasos_fallidos = true;
            $error_detalle = "DATA falló: $respuesta";
        }
    }

    if (!$pasos_fallidos) {
        $remitente_nombre = mb_encode_mimeheader($correo_smtp_nombre_remitente, 'UTF-8', 'B', "\r\n");
        $asunto_codificado = mb_encode_mimeheader($asunto, 'UTF-8', 'B', "\r\n");
        $cuerpo_normalizado = str_replace(["\r\n", "\r", "\n"], "\r\n", $cuerpo_html);
        $cuerpo_escapado = preg_replace('/\r\n\./', "\r\n..", $cuerpo_normalizado);

        $mensaje = "From: {$remitente_nombre} <{$correo_smtp_usuario}>\r\n";
        $mensaje .= "To: <{$destinatario}>\r\n";
        $mensaje .= "Subject: {$asunto_codificado}\r\n";
        $mensaje .= "Date: " . date('r') . "\r\n";
        $mensaje .= "Message-ID: <" . bin2hex(random_bytes(16)) . "@samashala.com>\r\n";
        $mensaje .= "MIME-Version: 1.0\r\n";
        $mensaje .= "Content-Type: text/html; charset=UTF-8\r\n";
        $mensaje .= "Content-Transfer-Encoding: 8bit\r\n";
        $mensaje .= "\r\n";
        $mensaje .= $cuerpo_escapado;
        $mensaje .= "\r\n.\r\n";

        fwrite($conexion_smtp, $mensaje);
        $respuesta = smtp_leer_respuesta($conexion_smtp);
        if (smtp_codigo($respuesta) !== 250) {
            $pasos_fallidos = true;
            $error_detalle = "Envío falló: $respuesta";
        }
    }

    fwrite($conexion_smtp, "QUIT\r\n");
    fclose($conexion_smtp);

    if ($pasos_fallidos) {
        error_log("[correo] Error enviando a $destinatario: $error_detalle");
        return ['ok' => false, 'error' => $error_detalle];
    }

    return ['ok' => true, 'error' => null];
}
