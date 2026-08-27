<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';

$id_usuario = filter_input(
INPUT_GET,
'id_usuario',
FILTER_VALIDATE_INT
);
if (!$id_usuario) {
header('Location: usuarios.php');
exit;
}

$sql = "
SELECT
id_usuario,
nombre,
apellidos,
email,
telefono,
rol,
activo
FROM usuarios
WHERE id_usuario = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) {
header('Location: usuarios.php?error=no_encontrado');
exit;
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
<title>Editar usuario | Sama Shala</title>
<link rel="stylesheet" href="<?= urlEstilos('../') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="../imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="../imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="../imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu_admin.php'; ?>
<main class="contenedor seccion">
<a class="enlace-volver" href="usuarios.php">
← Volver a usuarios
</a>
<h1>Editar usuario</h1>
<?php if (($_GET['error'] ?? '') === 'datos'): ?>
<div class="mensaje mensaje-error">
Revisa los datos del formulario.
</div>
<?php endif; ?>
<?php if (($_GET['error'] ?? '') === 'email'): ?>
<div class="mensaje mensaje-error">
Ya existe otro usuario con ese correo.
</div>
<?php endif; ?>
<form
class="formulario-admin"
action="actualizar_usuario.php"
method="post"
>
<input
type="hidden"
name="id_usuario"
value="<?= (int) $usuario['id_usuario'] ?>"
>
<div class="campo">
<label for="nombre">Nombre</label>
<input
type="text"
id="nombre"
name="nombre"
maxlength="80"
value="<?= escapar($usuario['nombre']) ?>"
required
>
</div>
<div class="campo">
<label for="apellidos">Apellidos</label>
<input
type="text"
id="apellidos"
name="apellidos"
maxlength="120"
value="<?= escapar($usuario['apellidos']) ?>"
required
>
</div>
<div class="campo">
<label for="email">Correo electrónico</label>
<input
type="email"
id="email"
name="email"
maxlength="150"
value="<?= escapar($usuario['email']) ?>"
required
>
</div>
<div class="campo">
<label for="telefono">Teléfono</label>
<input
type="tel"
id="telefono"
name="telefono"
maxlength="30"
value="<?= escapar($usuario['telefono']) ?>"
>
</div>
<div class="campo">
<label for="rol">Rol</label>
<select id="rol" name="rol" <?= (int) $usuario['id_usuario'] === idUsuarioActual() ? 'disabled' : '' ?>>
<option
value="cliente"
<?= $usuario['rol'] === 'cliente' ? 'selected' : '' ?>
>
Cliente
</option>
<option
value="admin"
<?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>
>
Administrador
</option>
</select>
<?php if ((int) $usuario['id_usuario'] === idUsuarioActual()): ?>
<input type="hidden" name="rol" value="<?= escapar($usuario['rol']) ?>">
<small>
No puedes cambiar el rol de tu propio usuario.
</small>
<?php endif; ?>
</div>
<div class="campo-completo">
<button class="boton" type="submit">
Guardar cambios
</button>
</div>
</form>
</main>
</body>
</html>
