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
header('Location: usuarios.php?error=no_encontrado');
exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$rol = trim($_POST['rol'] ?? '');

$errores = [];
if ($nombre === '') {
$errores[] = 'El nombre es obligatorio.';
}
if ($apellidos === '') {
$errores[] = 'Los apellidos son obligatorios.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
$errores[] = 'El correo no es válido.';
}
if (!in_array($rol, ['cliente', 'admin'], true)) {
$errores[] = 'El rol no es válido.';
}
if ($errores) {
header("Location: editar_usuario.php?id_usuario=$id_usuario&error=datos");
exit;
}

if ($id_usuario === idUsuarioActual()) {
$sql_rol_actual = "SELECT rol FROM usuarios WHERE id_usuario = ?";
$stmt_rol_actual = $conexion->prepare($sql_rol_actual);
$stmt_rol_actual->bind_param('i', $id_usuario);
$stmt_rol_actual->execute();
$rol = $stmt_rol_actual->get_result()->fetch_assoc()['rol'] ?? $rol;
}

$sql_comprobar = "
SELECT id_usuario
FROM usuarios
WHERE email = ?
AND id_usuario <> ?
";
$stmt_comprobar = $conexion->prepare($sql_comprobar);
$stmt_comprobar->bind_param('si', $email, $id_usuario);
$stmt_comprobar->execute();
if ($stmt_comprobar->get_result()->num_rows > 0) {
header("Location: editar_usuario.php?id_usuario=$id_usuario&error=email");
exit;
}

$sql = "
UPDATE usuarios SET
nombre = ?,
apellidos = ?,
email = ?,
telefono = ?,
rol = ?
WHERE id_usuario = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param(
'sssssi',
$nombre,
$apellidos,
$email,
$telefono,
$rol,
$id_usuario
);
$stmt->execute();
header('Location: usuarios.php?mensaje=actualizado');
exit;
