<?php
// Esta página NO exige sesión iniciada a propósito: PayPhone redirige aquí
// mediante una navegación normal (GET) que en teoría conserva la cookie de
// sesión, pero no podemos depender de ello. La seguridad real está en que
// la referencia es un token aleatorio imposible de adivinar y en que el
// estado del pago se verifica siempre contra la API de PayPhone (nunca se
// confía en los parámetros de la URL por sí solos).
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
require_once __DIR__ . '/funciones_reservas.php';
require_once __DIR__ . '/funciones_payphone.php';

$id_payphone = trim($_GET['id'] ?? '');
$referencia = trim($_GET['clientTransactionId'] ?? '');

$vista = [
    'aprobado' => false,
    'titulo' => t('No se ha podido verificar el pago'),
    'mensaje' => t('No hemos recibido los datos esperados de PayPhone. Si realizaste el pago, contáctanos indicando la referencia.'),
    'enlace_texto' => t('Volver al inicio'),
    'enlace_url' => 'index.php',
];

if ($id_payphone !== '' && $referencia !== '') {
    $pendiente = buscarPagoPendientePayphone($conexion, $referencia);

    if ($pendiente === null) {
        $vista['titulo'] = t('Esta transacción ya fue procesada');
        $vista['mensaje'] = t('No encontramos ninguna compra pendiente con esta referencia. Es posible que ya se haya confirmado antes.');
    } else {
        $confirmacion = payphoneConfirmarPago($id_payphone, $referencia);

        $monto_coincide =
            $confirmacion['ok'] &&
            $confirmacion['monto'] !== null &&
            abs($confirmacion['monto'] - $pendiente['monto']) < 0.01;

        $aprobado = $confirmacion['ok'] && $confirmacion['aprobado'] && $monto_coincide;

        if ($aprobado) {
            if ($pendiente['tipo'] === 'paquete') {
                $sql = "
                    UPDATE paquetes_clientes
                    SET estado = 'activo', id_transaccion_payphone = ?
                    WHERE id_paquete_cliente = ?
                    AND estado = 'pendiente'
                ";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param('si', $id_payphone, $pendiente['id']);
                $stmt->execute();
                $actualizado = $stmt->affected_rows > 0;
                $stmt->close();
                if ($actualizado) {
                    activarPreReservasPendientes($conexion, $pendiente['id_usuario'], $pendiente['id']);
                }
                $vista['enlace_texto'] = t('Ver mis paquetes');
                $vista['enlace_url'] = 'mis_paquetes.php';
            } elseif ($pendiente['tipo'] === 'reserva') {
                $sql = "
                    UPDATE reservas
                    SET estado_pago = 'pagado', id_transaccion_payphone = ?
                    WHERE id_reserva = ?
                    AND estado_pago = 'pendiente'
                    AND estado = 'confirmada'
                ";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param('si', $id_payphone, $pendiente['id']);
                $stmt->execute();
                $stmt->close();
                $vista['enlace_texto'] = t('Ver mis reservas');
                $vista['enlace_url'] = 'mis_reservas.php';
            } else {
                $sql = "
                    UPDATE compras_productos
                    SET estado = 'pagado', id_transaccion_payphone = ?
                    WHERE referencia_pago = ?
                    AND estado = 'pendiente'
                ";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param('ss', $id_payphone, $referencia);
                $stmt->execute();
                $stmt->close();
                $vista['enlace_texto'] = t('Ver mis compras');
                $vista['enlace_url'] = 'mis_compras.php';
            }

            $vista['aprobado'] = true;
            $vista['titulo'] = t('¡Pago aprobado!');
            $vista['mensaje'] = t('Tu pago con PayPhone se ha confirmado correctamente.');
        } else {
            if ($pendiente['tipo'] === 'paquete') {
                $sql = "
                    UPDATE paquetes_clientes
                    SET estado = 'cancelado'
                    WHERE id_paquete_cliente = ?
                    AND estado = 'pendiente'
                ";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param('i', $pendiente['id']);
                $stmt->execute();
                $stmt->close();
                $vista['enlace_texto'] = t('Volver a paquetes');
                $vista['enlace_url'] = 'paquetes.php';
            } elseif ($pendiente['tipo'] === 'reserva') {
                cancelarReservaYPromocionar($conexion, $pendiente['id'], $pendiente['id_usuario']);
                $vista['enlace_texto'] = t('Ver sesiones');
                $vista['enlace_url'] = 'sesiones.php';
            } else {
                $sql = "
                    UPDATE compras_productos
                    SET estado = 'rechazado'
                    WHERE referencia_pago = ?
                    AND estado = 'pendiente'
                ";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param('s', $referencia);
                $stmt->execute();
                $stmt->close();
                $vista['enlace_texto'] = t('Volver a la tienda');
                $vista['enlace_url'] = 'tienda.php';
            }

            $vista['titulo'] = t('El pago no se completó');
            $vista['mensaje'] = t('PayPhone no confirmó el pago (fue cancelado, rechazado, o no coincide el monto). No se ha aplicado ningún cargo desde este sitio.');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title><?= t('Confirmación de pago | Sama Shala') ?></title>
<link rel="stylesheet" href="<?= urlEstilos() ?>">
<link rel="icon" type="image/png" sizes="32x32" href="imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu.php'; ?>
<main class="contenedor seccion">
<div class="ficha-sesion ficha-pago">
<section class="informacion-sesion">
<h1><?= escapar($vista['titulo']) ?></h1>
<div class="mensaje <?= $vista['aprobado'] ? 'mensaje-exito' : 'mensaje-error' ?>">
<?= escapar($vista['mensaje']) ?>
</div>
<?php if ($referencia !== ''): ?>
<p class="ayuda">
<?= t('Referencia') ?>: <?= escapar($referencia) ?>
</p>
<?php endif; ?>
<p>
<a class="boton boton-bloque" href="<?= escapar($vista['enlace_url']) ?>">
<?= escapar($vista['enlace_texto']) ?>
</a>
</p>
</section>
</div>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
