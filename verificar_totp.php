<?php
require_once "conexion.php";
require_once "funciones.php";
require_once "totp.php";

if (!isset($_SESSION["totp_pendiente"])) {
header("Location: login.php");
exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
validarCsrf();
$intentos = (int) ($_SESSION["totp_intentos"] ?? 0);
if ($intentos >= 5) {
unset($_SESSION["totp_pendiente"], $_SESSION["totp_intentos"]);
header("Location: login.php?error=intentos");
exit;
}
$codigo = trim($_POST["codigo"] ?? "");
$id_usuario = $_SESSION["totp_pendiente"]["id_usuario"];
$stmt = $conexion->prepare(
"SELECT totp_secret, totp_habilitado FROM usuarios WHERE id_usuario = ? LIMIT 1"
);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$fila = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conexion->close();

if (
$fila &&
(int) $fila["totp_habilitado"] === 1 &&
!empty($fila["totp_secret"]) &&
totpVerificarCodigo($fila["totp_secret"], $codigo)
) {
$usuario = $_SESSION["totp_pendiente"];
unset($_SESSION["totp_pendiente"], $_SESSION["totp_intentos"]);
session_regenerate_id(true);
$_SESSION["usuario"] = $usuario;
header("Location: admin/index.php");
exit;
}

$_SESSION["totp_intentos"] = $intentos + 1;
$error = "codigo";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title><?= t("Verificación en dos pasos") ?></title>
<link rel="stylesheet" href="<?= urlEstilos() ?>">
<link rel="icon" type="image/png" sizes="32x32" href="imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="imagenes/favicon.ico">
</head>
<body>
<?php require "menu.php"; ?>
<main class="contenedor">
<h1><?= t("Verificación en dos pasos") ?></h1>
<p>
<?= t("Ingresa el código de 6 dígitos de tu aplicación de autenticación.") ?>
</p>
<?php if ($error === "codigo"): ?>
<div class="mensaje error">
<?= t("El código no es válido o ha caducado.") ?>
</div>
<?php endif; ?>
<form
method="post"
class="formulario"
>
<input
type="hidden"
name="csrf_token"
value="<?= escapar(tokenCsrf()) ?>"
>
<div class="campo">
<label for="codigo"><?= t("Código de verificación") ?></label>
<input
type="text"
id="codigo"
name="codigo"
inputmode="numeric"
pattern="[0-9]{6}"
maxlength="6"
autocomplete="one-time-code"
required
autofocus
>
</div>
<div class="grupo-botones">
<button type="submit" class="boton">
<?= t("Verificar") ?>
</button>
<a class="boton boton-secundario" href="logout.php">
<?= t("Cancelar") ?>
</a>
</div>
</form>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
