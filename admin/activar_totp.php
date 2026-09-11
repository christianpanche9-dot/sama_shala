<?php
require_once "seguridad_admin.php";
require_once __DIR__ . "/../conexion.php";
require_once __DIR__ . "/../totp.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
header("Location: seguridad_2fa.php");
exit;
}
validarCsrf();

$secreto = $_SESSION["totp_secreto_pendiente"] ?? "";
$codigo = trim($_POST["codigo"] ?? "");

if ($secreto === "" || !totpVerificarCodigo($secreto, $codigo)) {
header("Location: seguridad_2fa.php?error=codigo");
exit;
}

$id_usuario = idUsuarioActual();
$stmt = $conexion->prepare(
"UPDATE usuarios SET totp_secret = ?, totp_habilitado = 1 WHERE id_usuario = ?"
);
$stmt->bind_param("si", $secreto, $id_usuario);
$stmt->execute();

unset($_SESSION["totp_secreto_pendiente"]);
header("Location: seguridad_2fa.php?mensaje=activado");
exit;
