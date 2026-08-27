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
precio,
nivel,
duracion_minutos,
imagen,
activa,
es_top,
posicion_top,
imagen_banner_top
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
'Yoga',
'Meditación',
'Sound Healing',
'Ayurveda y terapias corporales',
'Arte y bienestar',
'Comunidad',
'Retiros y experiencias',
'Formación'
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
<link rel="stylesheet" href="<?= urlEstilos('../') ?>">
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
<div class="campo bloque-precio-actividad" id="bloque-precio-actividad">
<label for="precio">
Precio
</label>
<input
type="number"
id="precio"
name="precio"
min="0"
step="0.01"
value="<?= $actividad['precio'] !== null ? escapar((string) $actividad['precio']) : '' ?>"
>
<small>
Precio fijo de este evento o terapia. Se paga
directamente al reservar, no admite paquetes ni
clase suelta.
</small>
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
<div class="campo-checkbox campo-completo">
<label>
<input
type="checkbox"
id="es_top"
name="es_top"
value="1"
<?= (int) $actividad['es_top'] === 1 ? 'checked' : '' ?>
>
Esta es una actividad top (top 3 del mes)
</label>
</div>
<fieldset
class="campo-completo bloque-top"
id="bloque-top"
>
<legend>
Posición en el top 3
</legend>
<div class="campo">
<label for="posicion_top">
Posición
</label>
<select id="posicion_top" name="posicion_top">
<?php
$posicion_actual = $actividad['posicion_top'] !== null
? (int) $actividad['posicion_top']
: 1;
?>
<option
value="1"
<?= $posicion_actual === 1 ? 'selected' : '' ?>
>
1 · Número uno (la más importante)
</option>
<option
value="2"
<?= $posicion_actual === 2 ? 'selected' : '' ?>
>
2
</option>
<option
value="3"
<?= $posicion_actual === 3 ? 'selected' : '' ?>
>
3
</option>
</select>
</div>
<div
class="campo campo-completo bloque-banner-top"
id="bloque-banner-top"
>
<label for="imagen_banner_top">
Banner destacado (solo posición 1)
</label>
<?php if (!empty($actividad['imagen_banner_top'])): ?>
<img
class="miniatura-imagen-actual"
src="../imagenes/actividades/<?= escapar($actividad['imagen_banner_top']) ?>"
alt=""
>
<?php endif; ?>
<input
type="file"
id="imagen_banner_top"
name="imagen_banner_top"
accept="image/jpeg,image/png,image/webp"
>
<small>
Imagen ancha que se muestra debajo del botón
"Filtrar actividades". Solo se usa cuando esta
actividad es la posición 1. Deja vacío para
mantener el banner actual. JPG, PNG o WEBP,
máximo 5 MB.
</small>
</div>
</fieldset>
<div class="campo-completo">
<button class="boton" type="submit">
    Guardar cambios
</button>
</div>
</form>
</main>
<script>
(function () {
const selectTipo = document.querySelector("#tipo");
const bloquePrecio = document.querySelector("#bloque-precio-actividad");
const campoPrecio = document.querySelector("#precio");
if (selectTipo && bloquePrecio) {
function actualizarBloquePrecio() {
const visible = selectTipo.value !== "clase";
bloquePrecio.classList.toggle("visible", visible);
if (campoPrecio) {
campoPrecio.required = visible;
}
}
selectTipo.addEventListener("change", actualizarBloquePrecio);
actualizarBloquePrecio();
}
const casillaTop = document.querySelector("#es_top");
const bloqueTop = document.querySelector("#bloque-top");
const selectPosicionTop = document.querySelector("#posicion_top");
const bloqueBannerTop = document.querySelector("#bloque-banner-top");
if (casillaTop && bloqueTop) {
function actualizarBloqueTop() {
bloqueTop.classList.toggle("visible", casillaTop.checked);
}
casillaTop.addEventListener("change", actualizarBloqueTop);
actualizarBloqueTop();
}
if (selectPosicionTop && bloqueBannerTop) {
function actualizarBloqueBannerTop() {
bloqueBannerTop.classList.toggle(
"visible",
selectPosicionTop.value === "1"
);
}
selectPosicionTop.addEventListener(
"change",
actualizarBloqueBannerTop
);
actualizarBloqueBannerTop();
}
})();
</script>
</body>
</html>
