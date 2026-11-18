<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: usuarios.php');
exit;
}
validarCsrf();

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

try {
$sql = "DELETE FROM usuarios WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
} catch (Throwable $error) {
header('Location: usuarios.php?error=en_uso');
exit;
}

header('Location: usuarios.php?mensaje=eliminado');
exit;
