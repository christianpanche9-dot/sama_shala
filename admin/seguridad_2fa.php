<?php
require_once "seguridad_admin.php";
require_once __DIR__ . "/../conexion.php";
require_once __DIR__ . "/../totp.php";

$id_usuario = idUsuarioActual();
$stmt = $conexion->prepare(
"SELECT totp_habilitado FROM usuarios WHERE id_usuario = ? LIMIT 1"
);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$fila = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conexion->close();

$habilitado = $fila && (int) $fila["totp_habilitado"] === 1;
$error = $_GET["error"] ?? "";
$mensaje = $_GET["mensaje"] ?? "";

if (!$habilitado) {
if (empty($_SESSION["totp_secreto_pendiente"])) {
$_SESSION["totp_secreto_pendiente"] = totpGenerarSecreto();
}
$secreto = $_SESSION["totp_secreto_pendiente"];
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
<link rel="stylesheet" href="<?= urlEstilos('../') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="../imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="../imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="../imagenes/favicon.ico">
</head>
<body>
<?php require "menu_admin.php"; ?>
<main class="contenedor seccion">
<div class="encabezado-pagina">
<div>
<p class="etiqueta"><?= t("Administración") ?></p>
<h1><?= t("Verificación en dos pasos") ?></h1>
</div>
</div>
<?php if ($mensaje === "activado"): ?>
<div class="mensaje exito">
<?= t("La verificación en dos pasos se activó correctamente.") ?>
</div>
<?php elseif ($mensaje === "desactivado"): ?>
<div class="mensaje exito">
<?= t("La verificación en dos pasos se desactivó.") ?>
</div>
<?php endif; ?>
<?php if ($error === "codigo"): ?>
<div class="mensaje error">
<?= t("El código no es válido. Inténtalo de nuevo.") ?>
</div>
<?php elseif ($error === "password"): ?>
<div class="mensaje error">
<?= t("La contraseña no es correcta.") ?>
</div>
<?php endif; ?>

<?php if ($habilitado): ?>
<p>
<?= t("La verificación en dos pasos está activa en tu cuenta. Cada vez que inicies sesión, se te pedirá un código de tu aplicación de autenticación.") ?>
</p>
<form
action="desactivar_totp.php"
method="post"
class="formulario"
>
<input
type="hidden"
name="csrf_token"
value="<?= escapar(tokenCsrf()) ?>"
>
<div class="campo">
<label for="password">
<?= t("Confirma tu contraseña para desactivarla") ?>
</label>
<input
type="password"
id="password"
name="password"
required
>
</div>
<button type="submit" class="boton peligro">
<?= t("Desactivar verificación en dos pasos") ?>
</button>
</form>
<?php else: ?>
<p>
<?= t("Escanea o copia esta clave en una aplicación de autenticación (Google Authenticator, Authy, etc.) y luego ingresa el código de 6 dígitos para confirmar la activación.") ?>
</p>
<p class="codigo-secreto-totp">
<?= escapar(totpSecretoLegible($secreto)) ?>
</p>
<form
action="activar_totp.php"
method="post"
class="formulario"
>
<input
type="hidden"
name="csrf_token"
value="<?= escapar(tokenCsrf()) ?>"
>
<div class="campo">
<label for="codigo">
<?= t("Código de verificación") ?>
</label>
<input
type="text"
id="codigo"
name="codigo"
inputmode="numeric"
pattern="[0-9]{6}"
maxlength="6"
required
>
</div>
<button type="submit" class="boton">
<?= t("Activar verificación en dos pasos") ?>
</button>
</form>
<?php endif; ?>
</main>
<?php require_once __DIR__ . '/../pie.php'; ?>
</body>
</html>
