<?php
require_once __DIR__ . '/seguridad.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: tienda.php');
exit;
}
$metodo_pago_compra = trim($_POST['metodo_pago_compra'] ?? 'simulado');
$es_transferencia = $metodo_pago_compra === 'transferencia';
$titular = trim($_POST['titular'] ?? '');
$tarjeta = trim($_POST['tarjeta'] ?? '');
$id_usuario = idUsuarioActual();
$carrito = $_SESSION['carrito'] ?? [];
$detalle_carrito = obtenerCarritoDetallado($conexion, $carrito);
if (empty($detalle_carrito['items'])) {
header('Location: comprar_producto.php?error=pago');
exit;
}
if (!$es_transferencia && ($titular === '' || $tarjeta === '')) {
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
$referencia_pago = ($es_transferencia ? 'TRANSF-' : 'SIM-') . strtoupper(
bin2hex(random_bytes(8))
);
$metodo_pago = $es_transferencia ? 'transferencia' : 'simulado';
$estado = $es_transferencia ? 'pendiente' : 'pagado';
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
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
";
$stmt_compra = $conexion->prepare($sql_compra);
foreach ($detalle_carrito['items'] as $item) {
$stmt_compra->bind_param(
'iisidssss',
$id_usuario,
$item['id_producto'],
$item['talla'],
$item['cantidad'],
$item['subtotal'],
$metodo_pago,
$referencia_pago,
$comprobante_pago,
$estado
);
$stmt_compra->execute();
}
$stmt_compra->close();
$conexion->commit();
$_SESSION['carrito'] = [];
header('Location: mis_compras.php?mensaje=comprado');
exit;
} catch (Throwable $error) {
$conexion->rollback();
header('Location: comprar_producto.php?error=pago');
exit;
}
