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

$sql = "DELETE FROM multimedia WHERE id_multimedia = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_multimedia);
$stmt->execute();

header('Location: multimedia.php?mensaje=eliminado');
exit;
