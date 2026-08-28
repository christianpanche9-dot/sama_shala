<?php
require_once __DIR__ . '/seguridad.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: tienda.php');
exit;
}
$id_producto = filter_var(
$_POST['id_producto'] ?? '',
FILTER_VALIDATE_INT
);
$talla = trim($_POST['talla'] ?? '');
$titular = trim($_POST['titular'] ?? '');
$tarjeta = trim($_POST['tarjeta'] ?? '');
$id_usuario = idUsuarioActual();
if (!$id_producto || $titular === '' || $tarjeta === '') {
header(
'Location: comprar_producto.php?id=' .
$id_producto .
'&error=pago'
);
exit;
}
try {
$conexion->begin_transaction();
$sql_producto = "
SELECT id_producto, precio, tallas
FROM productos
WHERE id_producto = ?
AND activo = 1
FOR UPDATE
";
$stmt_producto = $conexion->prepare($sql_producto);
$stmt_producto->bind_param('i', $id_producto);
$stmt_producto->execute();
$producto = $stmt_producto->get_result()->fetch_assoc();
$stmt_producto->close();
if (!$producto) {
throw new Exception('El producto seleccionado no existe.');
}
$tallas_producto = [];
if (!empty($producto['tallas'])) {
$tallas_producto = array_filter(array_map('trim', explode(',', $producto['tallas'])));
}
if (!empty($tallas_producto) && !in_array($talla, $tallas_producto, true)) {
throw new Exception('La talla seleccionada no es válida.');
}
$talla_guardada = !empty($tallas_producto) ? $talla : null;
$referencia_pago = 'SIM-' . strtoupper(
bin2hex(random_bytes(8))
);
$sql_compra = "
INSERT INTO compras_productos (
id_usuario,
id_producto,
talla_elegida,
precio_pagado,
metodo_pago,
referencia_pago,
estado
)
VALUES (?, ?, ?, ?, 'simulado', ?, 'pagado')
";
$stmt_compra = $conexion->prepare($sql_compra);
$stmt_compra->bind_param(
'iisds',
$id_usuario,
$id_producto,
$talla_guardada,
$producto['precio'],
$referencia_pago
);
$stmt_compra->execute();
$stmt_compra->close();
$conexion->commit();
header('Location: mis_compras.php?mensaje=comprado');
exit;
} catch (Throwable $error) {
$conexion->rollback();
header(
'Location: comprar_producto.php?id=' .
$id_producto .
'&error=pago'
);
exit;
}
