<?php
require_once "funciones.php";
if (usuarioAutenticado()) {
header("Location: mi_cuenta.php");
exit;
}
$error = $_GET["error"] ?? "";
$datos = $_SESSION["datos_registro"] ?? [];
$nombre = $datos["nombre"] ?? "";
$apellidos = $datos["apellidos"] ?? "";
$email = $datos["email"] ?? "";
$telefono = $datos["telefono"] ?? "";
unset($_SESSION["datos_registro"]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title><?= t("Crear una cuenta") ?></title>
<link rel="stylesheet" href="estilos.css">
<link rel="icon" type="image/png" sizes="32x32" href="imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="imagenes/favicon.ico">
</head>
<body>
<?php require "menu.php"; ?>
<main class="contenedor">
<h1><?= t("Crear una cuenta") ?></h1>
<p>
<?= t("Regístrate para reservar plazas en las actividades.") ?>
</p>
<?php if ($error === "datos"): ?>
<div class="mensaje error">
<?= t("Revisa los datos introducidos.") ?>
</div>
<?php elseif ($error === "email"): ?>
<div class="mensaje error">
<?= t("Ya existe una cuenta con ese correo electrónico.") ?>
</div>
<?php elseif ($error === "password"): ?>
<div class="mensaje error">
<?= t("Las contraseñas no coinciden.") ?>
</div>
<?php elseif ($error === "guardar"): ?>
<div class="mensaje error">
<?= t("No se ha podido crear la cuenta.") ?>
</div>
<?php endif; ?>
<form
action="guardar_registro.php"
method="post"
class="formulario"
>
<div class="campo">
    <label for="nombre"><?= t("Nombre") ?></label>
<input
type="text"
id="nombre"
name="nombre"
maxlength="100"
value="<?= escapar($nombre) ?>"
required
>
</div>
<div class="campo">
<label for="apellidos"><?= t("Apellidos") ?></label>
<input
type="text"
id="apellidos"
name="apellidos"
maxlength="150"
value="<?= escapar($apellidos) ?>"
required
>
</div>
<div class="campo">
<label for="email"><?= t("Correo electrónico") ?></label>
<input
type="email"
id="email"
name="email"
maxlength="150"
value="<?= escapar($email) ?>"
required
>
</div>
<div class="campo">
<label for="telefono"><?= t("Teléfono") ?></label>
<input
type="tel"
id="telefono"
name="telefono"
maxlength="20"
value="<?= escapar($telefono) ?>"
>
</div>
<div class="campo">
<label for="password"><?= t("Contraseña") ?></label>
<input
type="password"
id="password"
name="password"
minlength="8"
required
>
<p class="ayuda">
<?= t("Debe contener al menos ocho caracteres.") ?>
</p>
</div>
<div class="campo">
<label for="repetir_password">
<?= t("Repetir contraseña") ?>
</label>
<input
type="password"
id="repetir_password"
name="repetir_password"
minlength="8"
required
>
</div>
<button type="submit" class="boton">
<?= t("Crear mi cuenta") ?>
</button>
</form>
<p>
<?= t("¿Ya tienes una cuenta?") ?>
<a href="login.php"><?= t("Inicia sesión") ?></a>.
</p>
</main>
</body>
</html>