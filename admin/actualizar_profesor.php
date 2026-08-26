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

$sql_actual = "SELECT imagen FROM profesores WHERE id_profesor = ?";
$stmt_actual = $conexion->prepare($sql_actual);
$stmt_actual->bind_param('i', $id_profesor);
$stmt_actual->execute();
$profesor_actual = $stmt_actual->get_result()->fetch_assoc();
$stmt_actual->close();
if (!$profesor_actual) {
header('Location: profesores.php?error=no_encontrado');
exit;
}
$imagen_actual = $profesor_actual['imagen'];

$nombre = trim($_POST['nombre'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$especialidad = trim($_POST['especialidad'] ?? '');
$resena = trim($_POST['resena'] ?? '');
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
if (mb_strlen($resena) > 2000) {
$errores[] = 'La reseña es demasiado larga.';
}
if (mb_strlen($username) > 60) {
$errores[] = 'El username es demasiado largo.';
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
$resultado_imagen = procesar_imagen_subida(
'imagen',
__DIR__ . '/../imagenes/profesores',
'profesor'
);
if (!$resultado_imagen['ok']) {
header("Location: editar_profesor.php?id_profesor=$id_profesor&error=datos");
exit;
}
if ($resultado_imagen['archivo'] !== null) {
if (
!empty($imagen_actual) &&
file_exists(__DIR__ . '/../imagenes/profesores/' . $imagen_actual)
) {
unlink(__DIR__ . '/../imagenes/profesores/' . $imagen_actual);
}
$imagen = $resultado_imagen['archivo'];
} else {
$imagen = $imagen_actual;
}
$username = $username === '' ? null : $username;
$sql = "
UPDATE profesores SET
nombre = ?,
apellidos = ?,
username = ?,
imagen = ?,
email = ?,
telefono = ?,
especialidad = ?,
resena = ?,
activo = ?
WHERE id_profesor = ?
";
$stmt =
$conexion->prepare($sql);
$stmt->bind_param(
'ssssssssii',
$nombre,
$apellidos,
$username,
$imagen,
$email,
$telefono,
$especialidad,
$resena,
$activo,
$id_profesor
);
$stmt->execute();
header('Location: profesores.php?mensaje=actualizado');
exit;
