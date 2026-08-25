<?php
require_once "seguridad_admin.php";
require_once "../conexion.php";
require_once __DIR__ . '/../funciones.php';
$sql_espacios = "
SELECT id_espacio, nombre, aforo_maximo
FROM espacios
WHERE activo = 1
ORDER BY nombre
";
$sql_profesores = "
SELECT id_profesor, nombre, apellidos
FROM profesores
WHERE activo = 1
ORDER BY apellidos, nombre
";
$espacios = $conexion->query($sql_espacios);
$profesores = $conexion->query($sql_profesores);
$hoy = new DateTime('today');
$mes_actual = (int) $hoy->format('n');
$anio_actual = (int) $hoy->format('Y');
$mes_siguiente = $mes_actual === 12 ? 1 : $mes_actual + 1;
$anio_siguiente = $mes_actual === 12 ? $anio_actual + 1 : $anio_actual;
$meses_calendario = [
['anio' => $anio_actual, 'mes' => $mes_actual],
['anio' => $anio_siguiente, 'mes' => $mes_siguiente]
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
    Nueva actividad | Sama Shala
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
<h1>Nueva actividad</h1>
</div>
<?php if (isset($_GET['error'])): ?>
<div class="mensaje mensaje-error">
No se ha podido guardar la actividad.
Revisa los datos del formulario.
</div>
<?php endif; ?>
<form
class="formulario-admin"
action="guardar_actividad.php"
method="post"
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
></textarea>
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
<option value="Deporte">
Deporte
</option>
<option value="Bienestar">
Bienestar
</option>
<option value="Cultura">
Cultura
</option>
<option value="Formación">
Formación
</option>
<option value="Ocio">
Ocio
</option>
</select>
</div>
<div class="campo">
<label for="tipo">
Tipo
</label>
<select id="tipo" name="tipo" required>
<option value="clase">
Clase
</option>
<option value="evento">
Evento
</option>
<option value="terapia">
Terapia
</option>
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
<option value="inicial">
Inicial
</option>
<option value="intermedio">
Intermedio
</option>
<option value="avanzado">
Avanzado
</option>
<option value="todos">
Todos los niveles
</option>
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
value="60"
required
>
</div>
<div class="campo">
<label for="imagen">
Nombre de la imagen
</label>
<input
type="text"
id="imagen"
name="imagen"
maxlength="255"
placeholder="yoga.jpg"
>
</div>
<div class="campo-checkbox campo-completo">
<label>
<input
type="checkbox"
name="activa"
value="1"
checked
>
Mostrar la actividad en el catálogo
</label>
</div>
<div class="campo-checkbox campo-completo">
<label>
<input
type="checkbox"
id="es_regular"
name="es_regular"
value="1"
>
¿Es una actividad regular?
</label>
</div>
<fieldset
class="campo-completo bloque-regular"
id="bloque-regular"
>
<legend>
Programar sus sesiones regulares
</legend>
<p class="ayuda">
Marca en el calendario las fechas en las que se repite
esta actividad. Se creará una sesión para cada fecha
marcada, con el mismo profesor, espacio, hora y aforo.
</p>
<div class="formulario-admin">
<div class="campo">
<label for="id_profesor_regular">
Profesor
</label>
<select
id="id_profesor_regular"
name="id_profesor_regular"
>
<option value="">
Selecciona un profesor
</option>
<?php while (
$profesor_regular = $profesores->fetch_assoc()
): ?>
<option
value="<?= (int) $profesor_regular['id_profesor'] ?>"
>
<?= escapar(
$profesor_regular['nombre'] . ' ' .
$profesor_regular['apellidos']
) ?>
</option>
<?php endwhile; ?>
</select>
</div>
<div class="campo">
<label for="id_espacio_regular">
Espacio
</label>
<select
id="id_espacio_regular"
name="id_espacio_regular"
>
<option value="">
Selecciona un espacio
</option>
<?php while (
$espacio_regular = $espacios->fetch_assoc()
): ?>
<option
value="<?= (int) $espacio_regular['id_espacio'] ?>"
data-aforo="<?= (int) $espacio_regular['aforo_maximo'] ?>"
>
<?= escapar($espacio_regular['nombre']) ?>
—
máximo
<?= (int) $espacio_regular['aforo_maximo'] ?>
</option>
<?php endwhile; ?>
</select>
</div>
<div class="campo">
<label for="hora_inicio_regular">
Hora de inicio
</label>
<input
type="time"
id="hora_inicio_regular"
name="hora_inicio_regular"
>
</div>
<div class="campo">
<label for="aforo_regular">
Aforo de cada sesión
</label>
<input
type="number"
id="aforo_regular"
name="aforo_regular"
min="1"
>
<small id="ayuda-aforo-regular">
Selecciona un espacio para conocer
su aforo máximo.
</small>
</div>
</div>
<div class="calendarios-regular">
<?php foreach ($meses_calendario as $mes_info): ?>
<div class="calendario-mes">
<p class="calendario-mes-titulo">
<?= escapar(texto_mes($mes_info['mes'])) ?>
<?= $mes_info['anio'] ?>
</p>
<div class="calendario-mes-cabecera">
<?php for ($d = 1; $d <= 7; $d++): ?>
<span>
<?= escapar(texto_dia_semana_abreviado($d)) ?>
</span>
<?php endfor; ?>
</div>
<div class="calendario-mes-grilla">
<?php
$semanas = generar_calendario_mes(
$mes_info['anio'],
$mes_info['mes']
);
?>
<?php foreach ($semanas as $semana): ?>
<?php foreach ($semana as $dia): ?>
<?php if ($dia === null): ?>
<span class="dia-calendario dia-calendario-vacio"></span>
<?php elseif ($dia < $hoy): ?>
<span class="dia-calendario dia-calendario-pasado">
<?= (int) $dia->format('j') ?>
</span>
<?php else: ?>
<label class="dia-calendario">
<input
type="checkbox"
name="fechas_regulares[]"
value="<?= $dia->format('Y-m-d') ?>"
class="entrada-dia-calendario"
>
<span><?= (int) $dia->format('j') ?></span>
</label>
<?php endif; ?>
<?php endforeach; ?>
<?php endforeach; ?>
</div>
</div>
<?php endforeach; ?>
</div>
</fieldset>
<div class="campo-completo">
<button class="boton" type="submit">
    Guardar actividad
</button>
</div>
</form>
</main>
<script>
(function () {
const casillaRegular = document.querySelector("#es_regular");
const bloqueRegular = document.querySelector("#bloque-regular");
const selectEspacio = document.querySelector("#id_espacio_regular");
const campoAforo = document.querySelector("#aforo_regular");
const ayudaAforo = document.querySelector("#ayuda-aforo-regular");
if (casillaRegular && bloqueRegular) {
function actualizarBloqueRegular() {
bloqueRegular.classList.toggle(
"visible",
casillaRegular.checked
);
}
casillaRegular.addEventListener("change", actualizarBloqueRegular);
actualizarBloqueRegular();
}
if (selectEspacio && campoAforo) {
selectEspacio.addEventListener("change", function () {
const opcion = selectEspacio.selectedOptions[0];
const aforoMaximo = opcion
? opcion.getAttribute("data-aforo")
: null;
if (aforoMaximo) {
campoAforo.max = aforoMaximo;
campoAforo.value = aforoMaximo;
if (ayudaAforo) {
ayudaAforo.textContent =
"Aforo máximo del espacio: " + aforoMaximo + ".";
}
}
});
}
})();
</script>
</body>
</html>
