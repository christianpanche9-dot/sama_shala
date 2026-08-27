<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: usuarios.php');
exit;
}

$id_usuario = filter_input(
INPUT_POST,
'id_usuario',
FILTER_VALIDATE_INT
);
if (!$id_usuario) {
header('Location: usuarios.php');
exit;
}

if ($id_usuario === idUsuarioActual()) {
header('Location: usuarios.php?error=propio_usuario');
exit;
}

$sql_actual = "SELECT activo FROM usuarios WHERE id_usuario = ?";
$stmt_actual = $conexion->prepare($sql_actual);
$stmt_actual->bind_param('i', $id_usuario);
$stmt_actual->execute();
$fila = $stmt_actual->get_result()->fetch_assoc();
if (!$fila) {
header('Location: usuarios.php?error=no_encontrado');
exit;
}

$nuevo_estado = (int) $fila['activo'] === 1 ? 0 : 1;
$sql = "UPDATE usuarios SET activo = ? WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('ii', $nuevo_estado, $id_usuario);
$stmt->execute();

header(
'Location: usuarios.php?mensaje=' .
($nuevo_estado === 1 ? 'activado' : 'desactivado')
);
exit;
