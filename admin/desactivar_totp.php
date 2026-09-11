<?php
require_once "seguridad_admin.php";
require_once __DIR__ . "/../conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
header("Location: seguridad_2fa.php");
exit;
}
validarCsrf();

$password = $_POST["password"] ?? "";
$id_usuario = idUsuarioActual();

$stmt = $conexion->prepare(
"SELECT password FROM usuarios WHERE id_usuario = ? LIMIT 1"
);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$fila = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$fila || !password_verify($password, $fila["password"])) {
$conexion->close();
header("Location: seguridad_2fa.php?error=password");
exit;
}

$stmt = $conexion->prepare(
"UPDATE usuarios SET totp_secret = NULL, totp_habilitado = 0 WHERE id_usuario = ?"
);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();

header("Location: seguridad_2fa.php?mensaje=desactivado");
exit;
