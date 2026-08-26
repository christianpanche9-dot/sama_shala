<?php
require_once "conexion.php";
require_once "funciones.php";
if (usuarioAutenticado()) {
header("Location: mi_cuenta.php");
exit;
}

$token = trim($_GET["token"] ?? "");
$error = $_GET["error"] ?? "";
$token_valido = false;

if ($token !== "") {
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
$token_valido = $stmt->get_result()->fetch_assoc() !== null;
$stmt->close();
}
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title><?= t("Restablecer contraseña") ?></title>
<link rel="stylesheet" href="<?= urlEstilos() ?>">
<link rel="icon" type="image/png" sizes="32x32" href="imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="imagenes/favicon.ico">
</head>
<body>
<?php require "menu.php"; ?>
<main class="contenedor">
<h1><?= t("Restablecer contraseña") ?></h1>
<?php if (!$token_valido): ?>
<div class="mensaje error">
<?= t("El enlace no es válido o ha caducado. Solicita uno nuevo.") ?>
</div>
<p>
<a href="recuperar_contrasena.php">
<?= t("Solicitar un nuevo enlace") ?>
</a>
</p>
<?php else: ?>
<?php if ($error === "password"): ?>
<div class="mensaje error">
<?= t("Las contraseñas no coinciden.") ?>
</div>
<?php elseif ($error === "datos"): ?>
<div class="mensaje error">
<?= t("La contraseña debe tener al menos ocho caracteres.") ?>
</div>
<?php endif; ?>
<form
action="guardar_nueva_contrasena.php"
method="post"
class="formulario"
>
<input
type="hidden"
name="token"
value="<?= escapar($token) ?>"
>
<div class="campo">
<label for="password"><?= t("Nueva contraseña") ?></label>
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
<?= t("Guardar contraseña") ?>
</button>
</form>
<?php endif; ?>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
