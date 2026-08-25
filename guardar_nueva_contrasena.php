<?php
require_once "conexion.php";
require_once "funciones.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
header("Location: recuperar_contrasena.php");
exit;
}

$token = trim($_POST["token"] ?? "");
$password = $_POST["password"] ?? "";
$repetir_password = $_POST["repetir_password"] ?? "";

if ($token === "") {
header("Location: recuperar_contrasena.php");
exit;
}
if (strlen($password) < 8) {
header(
"Location: restablecer_contrasena.php?token=" .
urlencode($token) . "&error=datos"
);
exit;
}
if ($password !== $repetir_password) {
header(
"Location: restablecer_contrasena.php?token=" .
urlencode($token) . "&error=password"
);
exit;
}

$sql = "
SELECT id_usuario
FROM usuarios
WHERE token_recuperacion = ?
AND token_recuperacion_expira > NOW()
LIMIT 1
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) {
header("Location: recuperar_contrasena.php?error=token");
exit;
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);
$sql_actualizar = "
UPDATE usuarios SET
password = ?,
token_recuperacion = NULL,
token_recuperacion_expira = NULL
WHERE id_usuario = ?
";
$stmt_actualizar = $conexion->prepare($sql_actualizar);
$stmt_actualizar->bind_param(
"si",
$password_hash,
$usuario["id_usuario"]
);
$stmt_actualizar->execute();
$stmt_actualizar->close();
$conexion->close();

header("Location: login.php?mensaje=password_actualizada");
exit;
