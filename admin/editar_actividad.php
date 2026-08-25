<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';

$id_actividad = filter_input(
INPUT_GET,
'id_actividad',
FILTER_VALIDATE_INT
);
if (!$id_actividad) {
header('Location: actividades.php');
exit;
}

$sql = "
SELECT
id_actividad,
nombre,
descripcion,
categoria,
tipo,
nivel,
duracion_minutos,
imagen,
activa
FROM actividades
WHERE id_actividad = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_actividad);
$stmt->execute();
$actividad = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$actividad) {
header('Location: actividades.php?error=no_encontrada');
exit;
}

$categorias = [
'Deporte',
'Bienestar',
'Cultura',
'Formación',
'Ocio'
];
$tipos = [
'clase' => 'Clase',
'evento' => 'Evento',
'terapia' => 'Terapia'
];
$niveles = [
'inicial' => 'Inicial',
'intermedio' => 'Intermedio',
'avanzado' => 'Avanzado',
'todos' => 'Todos los niveles'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title>
    Editar actividad | Sama Shala
</title>
<link rel="stylesheet" href="../estilos.css">
<link rel="icon" type="image/png" sizes="32x32" href="../imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="../imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="../imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu_admin.php'; ?>
<main class="contenedor seccion">
<a class="enlace-volver" href="actividades.php">
← Volver a actividades
</a>
<div class="encabezado-pagina">
<p class="etiqueta">
Actividades
</p>
<h1>Editar actividad</h1>
</div>
<?php if (isset($_GET['error'])): ?>
<div class="mensaje mensaje-error">
No se ha podido guardar la actividad.
Revisa los datos del formulario.
</div>
<?php endif; ?>
<form
class="formulario-admin"
action="actualizar_actividad.php"
method="post"
enctype="multipart/form-data"
>
<input
type="hidden"
name="id_actividad"
value="<?= (int) $actividad['id_actividad'] ?>"
>
<div class="campo">
<label for="nombre">
Nombre
</label>
<input
type="text"
id="nombre"
name="nombre"
maxlength="120"
value="<?= escapar($actividad['nombre']) ?>"
required
>
</div>
<div class="campo campo-completo">
<label for="descripcion">
Descripción
</label>
<textarea
id="descripcion"
name="descripcion"
rows="6"
required
><?= escapar($actividad['descripcion']) ?></textarea>
</div>
<div class="campo">
<label for="categoria">
Categoría
</label>
<select
id="categoria"
name="categoria"
required
>
<option value="">
Selecciona una categoría
</option>
<?php foreach ($categorias as $categoria): ?>
<option
value="<?= escapar($categoria) ?>"
<?= $actividad['categoria'] === $categoria ? 'selected' : '' ?>
>
<?= escapar($categoria) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="campo">
<label for="tipo">
Tipo
</label>
<select id="tipo" name="tipo" required>
<?php foreach ($tipos as $valor => $etiqueta): ?>
<option
value="<?= escapar($valor) ?>"
<?= $actividad['tipo'] === $valor ? 'selected' : '' ?>
>
<?= escapar($etiqueta) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="campo">
<label for="nivel">
Nivel
</label>
<select id="nivel" name="nivel" required>
<option value="">
Selecciona un nivel
</option>
<?php foreach ($niveles as $valor => $etiqueta): ?>
<option
value="<?= escapar($valor) ?>"
<?= $actividad['nivel'] === $valor ? 'selected' : '' ?>
>
<?= escapar($etiqueta) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="campo">
<label for="duracion_minutos">
Duración habitual
</label>
<input
type="number"
id="duracion_minutos"
name="duracion_minutos"
min="15"
max="480"
step="5"
value="<?= (int) $actividad['duracion_minutos'] ?>"
required
>
</div>
<div class="campo campo-completo">
<label for="imagen">
Foto de la actividad
</label>
<?php if (!empty($actividad['imagen'])): ?>
<img
class="miniatura-imagen-actual"
src="../imagenes/actividades/<?= escapar($actividad['imagen']) ?>"
alt=""
>
<?php endif; ?>
<input
type="file"
id="imagen"
name="imagen"
accept="image/jpeg,image/png,image/webp"
>
<small>
Deja este campo vacío para mantener la imagen actual.
JPG, PNG o WEBP, máximo 5 MB.
</small>
</div>
<div class="campo-checkbox campo-completo">
<label>
<input
type="checkbox"
name="activa"
value="1"
<?= (int) $actividad['activa'] === 1 ? 'checked' : '' ?>
>
Mostrar la actividad en el catálogo
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
