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
<link rel="stylesheet" href="estilos.css">
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
</div>
<div class="grupo-botones">
<button type="submit" class="boton">
<?= t("Entrar") ?>
</button>
<a class="boton boton-secundario" href="registro.php">
<?= t("Registrarse") ?>
</a>
</div>
</form>
</main>
</body>
</html>