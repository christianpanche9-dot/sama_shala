<?php
require_once "conexion.php";
require_once "funciones.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
header("Location: recuperar_contrasena.php");
exit;
}

$email = strtolower(trim($_POST["email"] ?? ""));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
header("Location: recuperar_contrasena.php?error=datos");
exit;
}

$sql = "
SELECT id_usuario
FROM usuarios
WHERE email = ? AND activo = 1
LIMIT 1
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($usuario) {
$token = bin2hex(random_bytes(32));
$expira = (new DateTime("+1 hour"))->format("Y-m-d H:i:s");
$sql_token = "
UPDATE usuarios SET
token_recuperacion = ?,
token_recuperacion_expira = ?
WHERE id_usuario = ?
";
$stmt_token = $conexion->prepare($sql_token);
$stmt_token->bind_param(
"ssi",
$token,
$expira,
$usuario["id_usuario"]
);
$stmt_token->execute();
$stmt_token->close();

$protocolo = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off")
? "https"
: "http";
$enlace = $protocolo . "://" . $_SERVER["HTTP_HOST"] .
"/restablecer_contrasena.php?token=" . $token;
enviar_correo_recuperacion($email, $enlace);
}

$conexion->close();
header("Location: recuperar_contrasena.php?mensaje=enviado");
exit;
