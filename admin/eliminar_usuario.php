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

$sql_uso = "
SELECT COUNT(*) AS total
FROM reservas
WHERE id_usuario = ?
AND estado = 'confirmada'
";
$stmt_uso = $conexion->prepare($sql_uso);
$stmt_uso->bind_param('i', $id_usuario);
$stmt_uso->execute();
$fila_uso = $stmt_uso->get_result()->fetch_assoc();
if ((int) $fila_uso['total'] > 0) {
header('Location: usuarios.php?error=en_uso');
exit;
}

$sql = "DELETE FROM usuarios WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_usuario);
$stmt->execute();

header('Location: usuarios.php?mensaje=eliminado');
exit;
