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

$sql_actual = "SELECT activo FROM productos WHERE id_producto = ?";
$stmt_actual = $conexion->prepare($sql_actual);
$stmt_actual->bind_param('i', $id_producto);
$stmt_actual->execute();
$fila = $stmt_actual->get_result()->fetch_assoc();
if (!$fila) {
header('Location: productos.php?error=no_encontrado');
exit;
}

$nuevo_estado = (int) $fila['activo'] === 1 ? 0 : 1;
$sql = "UPDATE productos SET activo = ? WHERE id_producto = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('ii', $nuevo_estado, $id_producto);
$stmt->execute();

header(
'Location: productos.php?mensaje=' .
($nuevo_estado === 1 ? 'activado' : 'desactivado')
);
exit;
