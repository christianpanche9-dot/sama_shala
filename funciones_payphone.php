<?php
require_once __DIR__ . '/payphone_config.php';

function generarReferenciaPayphone(): string
{
    return 'PP' . strtoupper(bin2hex(random_bytes(6)));
}

function notificarPagoPayphone(string $tipo, string $referencia, float $monto, string $cliente, string $descripcion): void
{
    require_once __DIR__ . '/correo.php';
    $asunto = 'Nuevo pago con PayPhone (' . $tipo . ') | Sama Shala';
    $cuerpo = '
        <p>Se ha confirmado un nuevo pago con PayPhone.</p>
        <ul>
            <li><strong>Tipo:</strong> ' . htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') . '</li>
            <li><strong>Cliente:</strong> ' . htmlspecialchars($cliente, ENT_QUOTES, 'UTF-8') . '</li>
            <li><strong>Detalle:</strong> ' . htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') . '</li>
            <li><strong>Monto:</strong> $' . number_format($monto, 2) . '</li>
            <li><strong>Referencia:</strong> ' . htmlspecialchars($referencia, ENT_QUOTES, 'UTF-8') . '</li>
        </ul>
    ';
    enviar_correo('reservas@samashala.com', $asunto, $cuerpo);
}

function payphonePrepararPago(float $monto, string $referencia, string $descripcion): array
{
    global $payphone_token, $payphone_store_id, $payphone_response_url;

    $centavos = (int) round($monto * 100);

    $cuerpo = [
        'amount' => $centavos,
        'amountWithoutTax' => $centavos,
        'amountWithTax' => 0,
        'tax' => 0,
        'service' => 0,
        'tip' => 0,
        'currency' => 'USD',
        'clientTransactionId' => $referencia,
        'reference' => $descripcion,
        'responseUrl' => $payphone_response_url,
        'storeId' => $payphone_store_id,
    ];

    $peticion = curl_init('https://pay.payphonetodoesposible.com/api/button/Prepare');
    curl_setopt_array($peticion, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $payphone_token,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($cuerpo),
        CURLOPT_TIMEOUT => 15,
    ]);
    $respuesta = curl_exec($peticion);
    $codigo_http = curl_getinfo($peticion, CURLINFO_HTTP_CODE);
    $error_curl = curl_error($peticion);
    curl_close($peticion);

    if ($respuesta === false || $codigo_http !== 200) {
        return [
            'ok' => false,
            'error' => $error_curl !== '' ? $error_curl : ('PayPhone respondió ' . $codigo_http . ': ' . $respuesta),
        ];
    }

    $datos = json_decode($respuesta, true);
    $url_pago = $datos['payWithCard'] ?? $datos['PayWithCard']
        ?? $datos['payWithPayPhone'] ?? $datos['PayWithPayPhone'] ?? null;
    if (!$url_pago) {
        return [
            'ok' => false,
            'error' => 'Respuesta de PayPhone sin URL de pago: ' . $respuesta,
        ];
    }

    return [
        'ok' => true,
        'url_pago' => $url_pago,
        'id_payphone' => $datos['paymentId'] ?? $datos['PaymentId'] ?? null,
    ];
}

function payphoneConfirmarPago(string $id_payphone, string $referencia): array
{
    global $payphone_token;

    $cuerpo = [
        'id' => (int) $id_payphone,
        'clientTxId' => $referencia,
    ];

    $peticion = curl_init('https://pay.payphonetodoesposible.com/api/button/V2/Confirm');
    curl_setopt_array($peticion, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $payphone_token,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($cuerpo),
        CURLOPT_TIMEOUT => 15,
    ]);
    $respuesta = curl_exec($peticion);
    $codigo_http = curl_getinfo($peticion, CURLINFO_HTTP_CODE);
    curl_close($peticion);

    if ($respuesta === false || $codigo_http !== 200) {
        return [
            'ok' => false,
            'aprobado' => false,
            'error' => 'No se ha podido confirmar el pago con PayPhone.',
        ];
    }

    $datos = json_decode($respuesta, true);
    $estado = $datos['transactionStatus'] ?? $datos['TransactionStatus'] ?? '';
    $monto_centavos = $datos['amount'] ?? $datos['Amount'] ?? null;

    return [
        'ok' => true,
        'aprobado' => strtolower((string) $estado) === 'approved',
        'estado' => $estado,
        'monto' => $monto_centavos !== null ? ((int) $monto_centavos) / 100 : null,
        'datos' => $datos,
    ];
}

function buscarPagoPendientePayphone(mysqli $conexion, string $referencia): ?array
{
    $sql_paquete = "
        SELECT id_paquete_cliente AS id, id_usuario, precio_pagado AS monto
        FROM paquetes_clientes
        WHERE referencia_pago = ?
        AND metodo_pago = 'payphone'
        AND estado = 'pendiente'
        LIMIT 1
    ";
    $stmt = $conexion->prepare($sql_paquete);
    $stmt->bind_param('s', $referencia);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($fila) {
        return [
            'tipo' => 'paquete',
            'id' => (int) $fila['id'],
            'id_usuario' => (int) $fila['id_usuario'],
            'monto' => (float) $fila['monto'],
        ];
    }

    $sql_reserva = "
        SELECT id_reserva AS id, id_usuario, precio_pagado AS monto
        FROM reservas
        WHERE referencia_pago = ?
        AND metodo_pago = 'payphone'
        AND estado_pago = 'pendiente'
        AND estado = 'confirmada'
        LIMIT 1
    ";
    $stmt = $conexion->prepare($sql_reserva);
    $stmt->bind_param('s', $referencia);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($fila) {
        return [
            'tipo' => 'reserva',
            'id' => (int) $fila['id'],
            'id_usuario' => (int) $fila['id_usuario'],
            'monto' => (float) $fila['monto'],
        ];
    }

    $sql_producto = "
        SELECT id_usuario, SUM(precio_pagado) AS monto
        FROM compras_productos
        WHERE referencia_pago = ?
        AND metodo_pago = 'payphone'
        AND estado = 'pendiente'
        GROUP BY id_usuario
        LIMIT 1
    ";
    $stmt = $conexion->prepare($sql_producto);
    $stmt->bind_param('s', $referencia);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($fila) {
        return [
            'tipo' => 'producto',
            'id' => null,
            'id_usuario' => (int) $fila['id_usuario'],
            'monto' => (float) $fila['monto'],
        ];
    }

    return null;
}
