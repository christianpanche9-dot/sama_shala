<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: bonos.php');
exit;
}

$id_tipo_bono = filter_input(
INPUT_POST,
'id_tipo_bono',
FILTER_VALIDATE_INT
);
if (!$id_tipo_bono) {
header('Location: bonos.php');
exit;
}

$sql_uso = "
SELECT COUNT(*) AS total
FROM bonos_clientes
WHERE id_tipo_bono = ?
";
$stmt_uso = $conexion->prepare($sql_uso);
$stmt_uso->bind_param('i', $id_tipo_bono);
$stmt_uso->execute();
$fila_uso = $stmt_uso->get_result()->fetch_assoc();
if ((int) $fila_uso['total'] > 0) {
header('Location: bonos.php?error=en_uso');
exit;
}

$sql = "DELETE FROM tipos_bono WHERE id_tipo_bono = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_tipo_bono);
$stmt->execute();
header('Location: bonos.php?mensaje=eliminado');
exit;
