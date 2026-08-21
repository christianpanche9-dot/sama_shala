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
header('Location: profesores.php?error=no_encontrado');
exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$especialidad = trim($_POST['especialidad'] ?? '');
$activo = isset($_POST['activo']) ? 1 : 0;
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
if ($especialidad === '') {
$errores[] = 'La especialidad es obligatoria.';
}
if ($errores) {
header("Location: editar_profesor.php?id_profesor=$id_profesor&error=datos");
exit;
}
$sql_comprobar = "
SELECT id_profesor
FROM profesores
WHERE email = ?
AND id_profesor <> ?
";
$stmt_comprobar =
$conexion->prepare($sql_comprobar);
$stmt_comprobar->bind_param(
'si',
$email,
$id_profesor
);
$stmt_comprobar->execute();
$resultado_comprobar =
$stmt_comprobar->get_result();
if ($resultado_comprobar->num_rows > 0) {
header("Location: editar_profesor.php?id_profesor=$id_profesor&error=email");
exit;
}
$sql = "
UPDATE profesores SET
nombre = ?,
apellidos = ?,
email = ?,
telefono = ?,
especialidad = ?,
activo = ?
WHERE id_profesor = ?
";
$stmt =
$conexion->prepare($sql);
$stmt->bind_param(
'sssssii',
$nombre,
$apellidos,
$email,
$telefono,
$especialidad,
$activo,
$id_profesor
);
$stmt->execute();
header('Location: profesores.php?mensaje=actualizado');
exit;
