<?php
require_once "funciones.php";
if (usuarioAutenticado()) {
header("Location: mi_cuenta.php");
exit;
}
$mensaje = $_GET["mensaje"] ?? "";
$error = $_GET["error"] ?? "";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title><?= t("Recuperar contraseña") ?></title>
<link rel="stylesheet" href="<?= urlEstilos() ?>">
<link rel="icon" type="image/png" sizes="32x32" href="imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="imagenes/favicon.ico">
</head>
<body>
<?php require "menu.php"; ?>
<main class="contenedor">
<h1><?= t("Recuperar contraseña") ?></h1>
<p>
<?= t("Escribe tu correo electrónico y te enviaremos instrucciones para restablecer tu contraseña.") ?>
</p>
<?php if ($mensaje === "enviado"): ?>
<div class="mensaje exito">
<?= t("Si existe una cuenta con ese correo, te hemos enviado instrucciones para restablecer la contraseña.") ?>
</div>
<?php elseif ($error === "datos"): ?>
<div class="mensaje error">
<?= t("Escribe un correo electrónico válido.") ?>
</div>
<?php elseif ($error === "token"): ?>
<div class="mensaje error">
<?= t("El enlace no es válido o ha caducado. Solicita uno nuevo.") ?>
</div>
<?php endif; ?>
<form
action="solicitar_recuperacion.php"
method="post"
class="formulario"
>
<div class="campo">
<label for="email"><?= t("Correo electrónico") ?></label>
<input
type="email"
id="email"
name="email"
required
>
</div>
<button type="submit" class="boton">
<?= t("Enviar instrucciones") ?>
</button>
</form>
<p>
<a href="login.php"><?= t("Volver a iniciar sesión") ?></a>
</p>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
