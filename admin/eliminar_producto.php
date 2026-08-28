<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: productos.php');
exit;
}

$id_producto = filter_var(
$_POST['id_producto'] ?? '',
FILTER_VALIDATE_INT
);
if (!$id_producto) {
header('Location: productos.php');
exit;
}

$sql_uso = "SELECT COUNT(*) AS total FROM compras_productos WHERE id_producto = ?";
$stmt_uso = $conexion->prepare($sql_uso);
$stmt_uso->bind_param('i', $id_producto);
$stmt_uso->execute();
$fila_uso = $stmt_uso->get_result()->fetch_assoc();
if ((int) $fila_uso['total'] > 0) {
header('Location: productos.php?error=en_uso');
exit;
}

$sql_imagenes = "SELECT imagen FROM producto_imagenes WHERE id_producto = ?";
$stmt_imagenes = $conexion->prepare($sql_imagenes);
$stmt_imagenes->bind_param('i', $id_producto);
$stmt_imagenes->execute();
$imagenes_detalle = array_column(
$stmt_imagenes->get_result()->fetch_all(MYSQLI_ASSOC),
'imagen'
);

$sql_imagen = "SELECT imagen FROM productos WHERE id_producto = ?";
$stmt_imagen = $conexion->prepare($sql_imagen);
$stmt_imagen->bind_param('i', $id_producto);
$stmt_imagen->execute();
$fila_imagen = $stmt_imagen->get_result()->fetch_assoc();
$imagen_producto = $fila_imagen['imagen'] ?? '';

$sql = "DELETE FROM productos WHERE id_producto = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_producto);
$stmt->execute();

foreach ([$imagen_producto, ...$imagenes_detalle] as $archivo_a_borrar) {
if (
!empty($archivo_a_borrar) &&
file_exists(__DIR__ . '/../imagenes/productos/' . $archivo_a_borrar)
) {
unlink(__DIR__ . '/../imagenes/productos/' . $archivo_a_borrar);
}
}
header('Location: productos.php?mensaje=eliminado');
exit;
