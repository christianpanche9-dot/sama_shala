<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: multimedia.php');
exit;
}
validarCsrf();

$id_multimedia = filter_var(
$_POST['id_multimedia'] ?? '',
FILTER_VALIDATE_INT
);
if (!$id_multimedia) {
header('Location: multimedia.php');
exit;
}

$sql_actual = "SELECT activo FROM multimedia WHERE id_multimedia = ?";
$stmt_actual = $conexion->prepare($sql_actual);
$stmt_actual->bind_param('i', $id_multimedia);
$stmt_actual->execute();
$fila = $stmt_actual->get_result()->fetch_assoc();
if (!$fila) {
header('Location: multimedia.php?error=no_encontrado');
exit;
}

$nuevo_estado = (int) $fila['activo'] === 1 ? 0 : 1;
$sql = "UPDATE multimedia SET activo = ? WHERE id_multimedia = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('ii', $nuevo_estado, $id_multimedia);
$stmt->execute();

header(
'Location: multimedia.php?mensaje=' .
($nuevo_estado === 1 ? 'activado' : 'desactivado')
);
exit;
