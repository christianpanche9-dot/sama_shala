<?php
require_once __DIR__ . '/seguridad.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
require_once __DIR__ . '/funciones_payphone.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: tienda.php');
exit;
}
validarCsrf();
$metodo_pago_compra = trim($_POST['metodo_pago_compra'] ?? 'payphone');
$es_transferencia = $metodo_pago_compra === 'transferencia';
$id_usuario = idUsuarioActual();
$carrito = $_SESSION['carrito'] ?? [];
$detalle_carrito = obtenerCarritoDetallado($conexion, $carrito);
if (empty($detalle_carrito['items'])) {
header('Location: comprar_producto.php?error=pago');
exit;
}
$comprobante_pago = null;
if ($es_transferencia) {
$resultado_comprobante = procesar_imagen_subida(
'comprobante_pago',
__DIR__ . '/imagenes/comprobantes',
'comprobante'
);
if (!$resultado_comprobante['ok'] || $resultado_comprobante['archivo'] === null) {
header('Location: comprar_producto.php?error=pago');
exit;
}
$comprobante_pago = $resultado_comprobante['archivo'];
}
try {
$conexion->begin_transaction();
$referencia_pago = $es_transferencia
? ('TRANSF-' . strtoupper(bin2hex(random_bytes(8))))
: generarReferenciaPayphone();
$metodo_pago = $es_transferencia ? 'transferencia' : 'payphone';
$sql_compra = "
INSERT INTO compras_productos (
id_usuario,
id_producto,
talla_elegida,
cantidad,
precio_pagado,
metodo_pago,
referencia_pago,
comprobante_pago,
estado
)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')
";
$stmt_compra = $conexion->prepare($sql_compra);
foreach ($detalle_carrito['items'] as $item) {
$stmt_compra->bind_param(
'iisidsss',
$id_usuario,
$item['id_producto'],
$item['talla'],
$item['cantidad'],
$item['subtotal'],
$metodo_pago,
$referencia_pago,
$comprobante_pago
);
$stmt_compra->execute();
}
$stmt_compra->close();
$conexion->commit();
$_SESSION['carrito'] = [];

if (!$es_transferencia) {
$resultado_pago = payphonePrepararPago(
(float) $detalle_carrito['total'],
$referencia_pago,
'Compra en tienda Sama Shala'
);
if (!$resultado_pago['ok']) {
$sql_cancelar = "
UPDATE compras_productos
SET estado = 'rechazado'
WHERE referencia_pago = ?
AND estado = 'pendiente'
";
$stmt_cancelar = $conexion->prepare($sql_cancelar);
$stmt_cancelar->bind_param('s', $referencia_pago);
$stmt_cancelar->execute();
$stmt_cancelar->close();
header('Location: comprar_producto.php?error=pago');
exit;
}
header('Location: ' . $resultado_pago['url_pago']);
exit;
}

notificarPagoAdmin(
'Nuevo comprobante de transferencia (Producto) | Sama Shala',
'Un cliente subió un comprobante de transferencia bancaria, pendiente de revisión.',
'Producto',
$referencia_pago,
(float) $detalle_carrito['total'],
nombreClienteActual($conexion, $id_usuario),
implode(', ', array_map(
fn ($item) => $item['nombre'] . ' x' . $item['cantidad'],
$detalle_carrito['items']
))
);

header('Location: mis_compras.php?mensaje=comprado');
exit;
} catch (Throwable $error) {
$conexion->rollback();
header('Location: comprar_producto.php?error=pago');
exit;
}
