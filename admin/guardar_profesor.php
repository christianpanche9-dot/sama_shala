<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: nuevo_profesor.php');
exit;
}

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
header('Location: nuevo_profesor.php?error=datos');
exit;
}
$sql_comprobar = "
SELECT id_profesor
FROM profesores
WHERE email = ?
";
$stmt_comprobar =
$conexion->prepare($sql_comprobar);
$stmt_comprobar->bind_param(
's',
$email
);
$stmt_comprobar->execute();
$resultado_comprobar =
$stmt_comprobar->get_result();
if ($resultado_comprobar->num_rows > 0) {
header('Location: nuevo_profesor.php?error=email');
exit;
}
$username = $username === '' ? null : $username;
$sql_insertar = "
INSERT INTO profesores (
nombre,
apellidos,
username,
email,
telefono,
especialidad,
resena,
activo
)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)
";
$stmt_insertar =
$conexion->prepare($sql_insertar);
$stmt_insertar->bind_param(
'sssssssi',
$nombre,
$apellidos,
$username,
$email,
$telefono,
$especialidad,
$resena,
$activo
);
$stmt_insertar->execute();
header('Location: profesores.php?mensaje=creado');
exit;