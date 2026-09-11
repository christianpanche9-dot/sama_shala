<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: blog.php');
exit;
}
validarCsrf();

$id_entrada = filter_var(
$_POST['id_entrada'] ?? '',
FILTER_VALIDATE_INT
);
if (!$id_entrada) {
header('Location: blog.php');
exit;
}

$sql_actual = "SELECT activo FROM blog_entradas WHERE id_entrada = ?";
$stmt_actual = $conexion->prepare($sql_actual);
$stmt_actual->bind_param('i', $id_entrada);
$stmt_actual->execute();
$fila = $stmt_actual->get_result()->fetch_assoc();
if (!$fila) {
header('Location: blog.php?error=no_encontrado');
exit;
}

$nuevo_estado = (int) $fila['activo'] === 1 ? 0 : 1;
$sql = "UPDATE blog_entradas SET activo = ? WHERE id_entrada = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('ii', $nuevo_estado, $id_entrada);
$stmt->execute();

header(
'Location: blog.php?mensaje=' .
($nuevo_estado === 1 ? 'activado' : 'desactivado')
);
exit;
