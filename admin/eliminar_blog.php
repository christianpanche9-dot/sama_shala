<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: blog.php');
exit;
}

$id_entrada = filter_var(
$_POST['id_entrada'] ?? '',
FILTER_VALIDATE_INT
);
if (!$id_entrada) {
header('Location: blog.php');
exit;
}

$sql_portada = "SELECT portada FROM blog_entradas WHERE id_entrada = ?";
$stmt_portada = $conexion->prepare($sql_portada);
$stmt_portada->bind_param('i', $id_entrada);
$stmt_portada->execute();
$fila = $stmt_portada->get_result()->fetch_assoc();
$portada = $fila['portada'] ?? '';

$sql = "DELETE FROM blog_entradas WHERE id_entrada = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_entrada);
$stmt->execute();

if (
!empty($portada) &&
file_exists(__DIR__ . '/../imagenes/blog/' . $portada)
) {
unlink(__DIR__ . '/../imagenes/blog/' . $portada);
}

header('Location: blog.php?mensaje=eliminado');
exit;
