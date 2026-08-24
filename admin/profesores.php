<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../funciones.php';
$sql = "
SELECT
id_profesor,
nombre,
apellidos,
email,
especialidad,
activo
FROM profesores
ORDER BY apellidos, nombre
";
$resultado = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title>Profesores | Administración</title>
<link rel="stylesheet" href="../estilos.css">
<link rel="icon" type="image/png" sizes="32x32" href="../imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="../imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="../imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu_admin.php'; ?>

<main class="contenedor seccion">
<div class="encabezado-con-accion">
<h1>Profesores</h1>
<a class="boton" href="nuevo_profesor.php">
Nuevo profesor
</a>
</div>

<?php if (
    ($_GET['mensaje'] ?? '') === 'creado'
): ?>

<div class="mensaje mensaje-exito">
El profesor se ha creado correctamente.
</div>
<?php endif; ?>
<?php if (
    ($_GET['mensaje'] ?? '') === 'actualizado'
): ?>

<div class="mensaje mensaje-exito">
El profesor se ha actualizado correctamente.
</div>
<?php endif; ?>
<?php if (
    ($_GET['mensaje'] ?? '') === 'eliminado'
): ?>

<div class="mensaje mensaje-exito">
El profesor se ha eliminado correctamente.
</div>
<?php endif; ?>
<?php if (
    ($_GET['error'] ?? '') === 'en_uso'
): ?>

<div class="mensaje mensaje-error">
No se puede eliminar el profesor porque tiene sesiones asociadas.
Desactívalo si no quieres que se le asignen nuevas sesiones.
</div>
<?php endif; ?>
<div class="tabla-responsive">
<table class="tabla-admin">
<thead>
<tr>
<th>Profesor</th>
<th>Correo</th>
<th>Especialidad</th>
<th>Estado</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php while (
$profesor = $resultado->fetch_assoc()
): ?>
<tr>
<td>
<?= escapar(
$profesor['nombre'] . ' ' .
$profesor['apellidos']
) ?>
</td>

<td>
    <?= escapar($profesor['email']) ?>
</td>

<td>
    <?= escapar(
$profesor['especialidad']
) ?>
</td>

<td>
<?= (int) $profesor['activo'] === 1
? 'Activo'
: 'Inactivo' ?>
</td>
<td class="acciones-tabla">
<a
class="boton boton-secundario boton-pequeno"
href="editar_profesor.php?id_profesor=<?= (int) $profesor['id_profesor'] ?>"
>
Editar
</a>
<form
action="eliminar_profesor.php"
method="post"
onsubmit="return confirm('¿Seguro que quieres eliminar este profesor?');"
>
<input
type="hidden"
name="id_profesor"
value="<?= (int) $profesor['id_profesor'] ?>"
>
<button class="boton peligro boton-pequeno" type="submit">
Eliminar
</button>
</form>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</main>
</body>
</html>