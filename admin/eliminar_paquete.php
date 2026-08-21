<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: paquetes.php');
exit;
}

$id_tipo_paquete = filter_input(
INPUT_POST,
'id_tipo_paquete',
FILTER_VALIDATE_INT
);
if (!$id_tipo_paquete) {
header('Location: paquetes.php');
exit;
}

$sql_uso = "
SELECT COUNT(*) AS total
FROM paquetes_clientes
WHERE id_tipo_paquete = ?
";
$stmt_uso = $conexion->prepare($sql_uso);
$stmt_uso->bind_param('i', $id_tipo_paquete);
$stmt_uso->execute();
$fila_uso = $stmt_uso->get_result()->fetch_assoc();
if ((int) $fila_uso['total'] > 0) {
header('Location: paquetes.php?error=en_uso');
exit;
}

$sql = "DELETE FROM tipos_paquete WHERE id_tipo_paquete = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_tipo_paquete);
$stmt->execute();
header('Location: paquetes.php?mensaje=eliminado');
exit;
