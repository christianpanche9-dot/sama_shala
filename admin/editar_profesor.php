<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';

$id_profesor = filter_input(
INPUT_GET,
'id_profesor',
FILTER_VALIDATE_INT
);
if (!$id_profesor) {
header('Location: profesores.php');
exit;
}

$sql = "
SELECT
id_profesor,
nombre,
apellidos,
email,
telefono,
especialidad,
activo
FROM profesores
WHERE id_profesor = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_profesor);
$stmt->execute();
$profesor = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$profesor) {
header('Location: profesores.php?error=no_encontrado');
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
<title>Editar profesor | Sama Shala</title>
<link rel="stylesheet" href="../estilos.css">
<link rel="icon" type="image/png" sizes="32x32" href="../imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="../imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="../imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu_admin.php'; ?>
<main class="contenedor seccion">
<a class="enlace-volver" href="profesores.php">
← Volver a profesores
</a>
<h1>Editar profesor</h1>
<?php if (
    ($_GET['error'] ?? '') === 'datos'
): ?>

<div class="mensaje mensaje-error">
Revisa los datos del formulario.
</div>
<?php endif; ?>
<?php if (
    ($_GET['error'] ?? '') === 'email'
): ?>

<div class="mensaje mensaje-error">
Ya existe otro profesor con ese correo.
</div>
<?php endif; ?>
<form
class="formulario-admin"
action="actualizar_profesor.php"
method="post"
>
<input
type="hidden"
name="id_profesor"
value="<?= (int) $profesor['id_profesor'] ?>"
>
<div class="campo">
<label for="nombre">Nombre</label>
<input
type="text"
id="nombre"
name="nombre"
maxlength="80"
value="<?= escapar($profesor['nombre']) ?>"
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
value="<?= escapar($profesor['apellidos']) ?>"
required
>
</div>
<div class="campo">
<label for="email">Correo electrónico</label>
<input
type="email"
id="email"
name="email"
maxlength="180"
value="<?= escapar($profesor['email']) ?>"
required
>
</div>
<div class="campo">
<label for="telefono">Teléfono</label>
<input
type="tel"
id="telefono"
name="telefono"
maxlength="25"
value="<?= escapar($profesor['telefono']) ?>"
>
</div>
<div class="campo campo-completo">
<label for="especialidad">
Especialidad
</label>
<input
type="text"
id="especialidad"
name="especialidad"
maxlength="180"
value="<?= escapar($profesor['especialidad']) ?>"
required
>
</div>
<div class="campo-checkbox campo-completo">
<label>
<input
type="checkbox"
name="activo"
value="1"
<?= (int) $profesor['activo'] === 1 ? 'checked' : '' ?>
>
Profesor activo
</label>
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
