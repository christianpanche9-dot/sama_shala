<?php
require_once "funciones.php";
if (usuarioAutenticado()) {
header("Location: mi_cuenta.php");
exit;
}
$error = $_GET["error"] ?? "";
$mensaje = $_GET["mensaje"] ?? "";
$volver = $_GET["volver"] ?? "";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title><?= t("Iniciar sesión") ?></title>
<link rel="stylesheet" href="<?= urlEstilos() ?>">
<link rel="icon" type="image/png" sizes="32x32" href="imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="imagenes/favicon.ico">
</head>
<body>
<?php require "menu.php"; ?>
<main class="contenedor">
<h1><?= t("Iniciar sesión") ?></h1>
<?php if ($mensaje === "registro"): ?>
<div class="mensaje exito">
<?= t("La cuenta se ha creado correctamente. Ya puedes iniciar sesión.") ?>
</div>
<?php elseif ($mensaje === "password_actualizada"): ?>
<div class="mensaje exito">
<?= t("Tu contraseña se ha actualizado. Ya puedes iniciar sesión.") ?>
</div>
<?php endif; ?>
<?php if ($error === "credenciales"): ?>
<div class="mensaje error">
<?= t("El correo o la contraseña no son correctos.") ?>
</div>
<?php elseif ($error === "inactivo"): ?>
    <div class="mensaje error">
<?= t("Esta cuenta está desactivada.") ?>
</div>
<?php elseif ($error === "acceso"): ?>
<div class="mensaje aviso">
<?= t("Debes iniciar sesión para acceder a esa página.") ?>
</div>
<?php elseif ($error === "google"): ?>
<div class="mensaje error">
<?= t("No se pudo iniciar sesión con Google. Inténtalo de nuevo.") ?>
</div>
<?php elseif ($error === "intentos"): ?>
<div class="mensaje error">
<?= t("Demasiados intentos. Espera unos minutos e inténtalo de nuevo.") ?>
</div>
<?php endif; ?>
<form
action="validar_login.php"
method="post"
class="formulario"
>
<input
type="hidden"
name="volver"
value="<?= escapar($volver) ?>"
>
<input
type="hidden"
name="csrf_token"
value="<?= escapar(tokenCsrf()) ?>"
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
<div class="campo">
<label for="password"><?= t("Contraseña") ?></label>
<input
type="password"
id="password"
name="password"
required
>
<p class="ayuda">
<a href="recuperar_contrasena.php">
<?= t("¿Olvidaste tu contraseña?") ?>
</a>
</p>
</div>
<div class="grupo-botones">
<button type="submit" class="boton">
<?= t("Entrar") ?>
</button>
<a class="boton boton-secundario" href="registro.php">
<?= t("Registrarse") ?>
</a>
</div>
<p class="separador-o"><?= t("o") ?></p>
<a class="boton-google" href="iniciar_google.php">
<svg viewBox="0 0 48 48" width="18" height="18" aria-hidden="true">
<path fill="#FFC107" d="M43.6 20.5h-1.9V20H24v8h11.3c-1.6 4.7-6.1 8-11.3 8-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34.6 6.1 29.6 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.7-.4-3.5Z"/>
<path fill="#FF3D00" d="m6.3 14.7 6.6 4.8C14.6 15.9 18.9 13 24 13c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34.6 6.1 29.6 4 24 4c-7.5 0-14 4.2-17.7 10.7Z"/>
<path fill="#4CAF50" d="M24 44c5.5 0 10.4-1.9 14.3-5.1l-6.6-5.6C29.6 34.9 26.9 36 24 36c-5.2 0-9.6-3.3-11.3-7.9l-6.6 5.1C9.9 39.8 16.4 44 24 44Z"/>
<path fill="#1976D2" d="M43.6 20.5h-1.9V20H24v8h11.3c-.8 2.2-2.2 4.1-4 5.4l6.6 5.6C41.9 35.9 44 30.5 44 24c0-1.3-.1-2.7-.4-3.5Z"/>
</svg>
<?= t("Continuar con Google") ?>
</a>
</form>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>