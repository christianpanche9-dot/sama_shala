<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: profesores.php');
exit;
}

$id_profesor = filter_input(
INPUT_POST,
'id_profesor',
FILTER_VALIDATE_INT
);
if (!$id_profesor) {
header('Location: profesores.php');
exit;
}

$sql_uso = "
SELECT COUNT(*) AS total
FROM sesiones
WHERE id_profesor = ?
";
$stmt_uso = $conexion->prepare($sql_uso);
$stmt_uso->bind_param('i', $id_profesor);
$stmt_uso->execute();
$fila_uso = $stmt_uso->get_result()->fetch_assoc();
if ((int) $fila_uso['total'] > 0) {
header('Location: profesores.php?error=en_uso');
exit;
}

$sql = "DELETE FROM profesores WHERE id_profesor = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_profesor);
$stmt->execute();
header('Location: profesores.php?mensaje=eliminado');
exit;
