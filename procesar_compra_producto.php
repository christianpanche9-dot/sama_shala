<?php
require_once __DIR__ . '/seguridad.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: tienda.php');
exit;
}
$titular = trim($_POST['titular'] ?? '');
$tarjeta = trim($_POST['tarjeta'] ?? '');
$id_usuario = idUsuarioActual();
$carrito = $_SESSION['carrito'] ?? [];
$detalle_carrito = obtenerCarritoDetallado($conexion, $carrito);
if (empty($detalle_carrito['items']) || $titular === '' || $tarjeta === '') {
header('Location: comprar_producto.php?error=pago');
exit;
}
try {
$conexion->begin_transaction();
$referencia_pago = 'SIM-' . strtoupper(
bin2hex(random_bytes(8))
);
$sql_compra = "
INSERT INTO compras_productos (
id_usuario,
id_producto,
talla_elegida,
cantidad,
precio_pagado,
metodo_pago,
referencia_pago,
estado
)
VALUES (?, ?, ?, ?, ?, 'simulado', ?, 'pagado')
";
$stmt_compra = $conexion->prepare($sql_compra);
foreach ($detalle_carrito['items'] as $item) {
$stmt_compra->bind_param(
'iisids',
$id_usuario,
$item['id_producto'],
$item['talla'],
$item['cantidad'],
$item['subtotal'],
$referencia_pago
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
