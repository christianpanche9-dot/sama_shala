<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
$categoria = trim($_GET['categoria'] ?? '');
$tipo_actividad = trim($_GET['tipo'] ?? '');
$nivel = trim($_GET['nivel'] ?? '');
$niveles_permitidos = [
'todos',
'inicial',
'intermedio',
'avanzado'
];
$tipos_permitidos = [
'clase',
'evento',
'terapia'
];
if (
$nivel !== '' &&
!in_array($nivel, $niveles_permitidos, true)
) {
$nivel = '';
}
if (
$tipo_actividad !== '' &&
!in_array($tipo_actividad, $tipos_permitidos, true)
) {
$tipo_actividad = '';
}
$sql_categorias = "
SELECT DISTINCT categoria
FROM actividades
WHERE activa = 1
ORDER BY categoria
";
$resultado_categorias =
$conexion->query($sql_categorias);
$condiciones = [
'a.activa = 1'
];
$tipos = '';
$valores = [];
if ($categoria !== '') {
$condiciones[] = 'a.categoria = ?';
$tipos .= 's';
$valores[] = $categoria;
}
if ($tipo_actividad !== '') {
$condiciones[] = 'a.tipo = ?';
$tipos .= 's';
$valores[] = $tipo_actividad;
}
if ($nivel !== '') {
$condiciones[] = 'a.nivel = ?';
$tipos .= 's';
$valores[] = $nivel;
}
$where = implode(' AND ', $condiciones);
$sql = "
SELECT
a.id_actividad,
a.nombre,
a.descripcion,
a.categoria,
a.tipo,
a.nivel,
a.duracion_minutos,
a.imagen,
COUNT(s.id_sesion) AS proximas_sesiones,
MIN(
TIMESTAMP(
s.fecha,
s.hora_inicio
)
) AS proxima_fecha
FROM actividades AS a
LEFT JOIN sesiones AS s
ON s.id_actividad = a.id_actividad
AND s.estado IN (
'programada',
'completa'
)
AND TIMESTAMP(
s.fecha,
s.hora_fin
) >= NOW()
WHERE $where
GROUP BY
a.id_actividad,
a.nombre,
a.descripcion,
a.categoria,
a.tipo,
a.nivel,
a.duracion_minutos,
a.imagen
ORDER BY a.nombre
";
$stmt = $conexion->prepare($sql);
if (!$stmt) {
die('No se ha podido preparar la consulta.');
}
if ($tipos !== '') {
$stmt->bind_param(
$tipos,
...$valores
);
}
$stmt->execute();
$resultado = $stmt->get_result();

$dias_semana = [];
$sesiones_por_dia = [];
$hoy = new DateTime('today');
for ($i = 0; $i < 7; $i++) {
$dia = (clone $hoy)->modify("+$i day");
$dias_semana[] = $dia;
$sesiones_por_dia[$dia->format('Y-m-d')] = [];
}
$fecha_inicio_semana = $dias_semana[0]->format('Y-m-d');
$fecha_fin_semana = $dias_semana[6]->format('Y-m-d');
$sql_semana = "
SELECT
s.id_sesion,
s.fecha,
s.hora_inicio,
s.hora_fin,
a.id_actividad,
a.nombre AS actividad,
a.nivel,
CONCAT(p.nombre, ' ', p.apellidos) AS profesor
FROM sesiones AS s
INNER JOIN actividades AS a
ON s.id_actividad = a.id_actividad
INNER JOIN profesores AS p
ON s.id_profesor = p.id_profesor
WHERE a.activa = 1
AND s.estado IN ('programada', 'completa')
AND s.fecha BETWEEN ? AND ?
ORDER BY s.fecha, s.hora_inicio
";
$stmt_semana = $conexion->prepare($sql_semana);
$stmt_semana->bind_param(
'ss',
$fecha_inicio_semana,
$fecha_fin_semana
);
$stmt_semana->execute();
$resultado_semana = $stmt_semana->get_result();
while ($fila_semana = $resultado_semana->fetch_assoc()) {
$sesiones_por_dia[$fila_semana['fecha']][] = $fila_semana;
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
<title><?= t('Actividades | Sama Shala') ?></title>
<link rel="stylesheet" href="estilos.css">
<link rel="icon" type="image/png" sizes="32x32" href="imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu.php'; ?>
<main class="contenedor seccion">
<div class="encabezado-pagina">
<div>
<h1><?= t('Actividades') ?></h1>
<p>
<?= t('Consulta las actividades de nuestro centro y descubre increíbles clases, eventos y terapias pensadas en tu bienestar.') ?>
</p>
</div>
</div>
<button
type="button"
class="boton boton-secundario boton-alternar-filtros"
id="boton-alternar-filtros"
aria-expanded="false"
aria-controls="panel-filtros"
>
<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8">
<path d="M4 6h16M7 12h10M10 18h4"/>
</svg>
<?= t('Filtrar actividades') ?>
</button>
<div class="panel-filtros" id="panel-filtros">
<form
class="formulario-filtros"
action="actividades.php"
method="get"
>
<div class="campo">
<label for="categoria">
<?= t('Categoría') ?>
</label>
<select
id="categoria"
name="categoria"
>
<option value="">
<?= t('Todas las categorías') ?>
</option>
<?php while (
$fila_categoria =
$resultado_categorias->fetch_assoc()
): ?>
<option
value="<?= escapar(
$fila_categoria['categoria']
) ?>"
<?= $categoria ===
$fila_categoria['categoria']
? 'selected'
: '' ?>
>
<?= escapar(
$fila_categoria['categoria']
) ?>
</option>
<?php endwhile; ?>
</select>
</div>
<div class="campo">
<label for="tipo">
<?= t('Tipo') ?>
</label>
<select id="tipo" name="tipo">
<option value="">
<?= t('Cualquier tipo') ?>
</option>
<?php foreach (
$tipos_permitidos as $valor_tipo
): ?>
<option
value="<?= escapar($valor_tipo) ?>"
<?= $tipo_actividad === $valor_tipo
? 'selected'
: '' ?>
>
<?= escapar(
texto_tipo_actividad($valor_tipo)
) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="campo">
<label for="nivel">
<?= t('Nivel') ?>
</label>
<select id="nivel" name="nivel">
<option value="">
<?= t('Cualquier nivel') ?>
</option>
<?php foreach (
$niveles_permitidos as $valor_nivel
): ?>
<option
value="<?= escapar($valor_nivel) ?>"
<?= $nivel === $valor_nivel
? 'selected'
: '' ?>
>
<?= escapar(
texto_nivel($valor_nivel)
) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="acciones-filtro">
<button class="boton" type="submit">
<?= t('Filtrar') ?>
</button>
<a
class="boton boton-secundario"
href="actividades.php"
>
<?= t('Limpiar') ?>
</a>
</div>
</form>
</div>
<script>
(function () {
const boton = document.querySelector("#boton-alternar-filtros");
const panel = document.querySelector("#panel-filtros");
if (!boton || !panel) {
return;
}
boton.addEventListener("click", function () {
const abierto = panel.classList.toggle("abierto");
boton.setAttribute("aria-expanded", abierto ? "true" : "false");
});
})();
</script>
<div class="calendario-semana">
<?php foreach ($dias_semana as $indice => $dia): ?>
<button
type="button"
class="dia-semana-boton<?= $indice === 0 ? ' activo' : '' ?>"
data-fecha="<?= $dia->format('Y-m-d') ?>"
>
<span class="dia-semana-abrev">
<?= escapar(
texto_dia_semana_abreviado(
(int) $dia->format('N')
)
) ?>
</span>
<span class="dia-semana-numero">
<?= $dia->format('j') ?>
</span>
</button>
<?php endforeach; ?>
</div>
<div class="dias-actividades">
<?php foreach ($dias_semana as $indice => $dia): ?>
<?php $clave_dia = $dia->format('Y-m-d'); ?>
<div
class="dia-actividades<?= $indice === 0 ? ' activo' : '' ?>"
data-fecha="<?= $clave_dia ?>"
>
<?php if (empty($sesiones_por_dia[$clave_dia])): ?>
<p class="sin-sesiones">
<?= t('No hay actividades programadas ese día.') ?>
</p>
<?php else: ?>
<?php foreach (
$sesiones_por_dia[$clave_dia] as $sesion_dia
): ?>
<a
class="item-actividad-dia"
href="detalle_actividad.php?id=<?= (int) $sesion_dia['id_actividad'] ?>"
>
<span class="item-actividad-hora">
<?= escapar(
formatear_hora($sesion_dia['hora_inicio'])
) ?>
–
<?= escapar(
formatear_hora($sesion_dia['hora_fin'])
) ?>
</span>
<span class="item-actividad-nombre">
<?= escapar($sesion_dia['actividad']) ?>
</span>
<span class="item-actividad-detalle">
<?= escapar($sesion_dia['profesor']) ?>
·
<?= escapar(texto_nivel($sesion_dia['nivel'])) ?>
</span>
</a>
<?php endforeach; ?>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>
<script>
(function () {
const botonesDias = document.querySelectorAll(".dia-semana-boton");
const panelesDias = document.querySelectorAll(".dia-actividades");
botonesDias.forEach(function (boton) {
boton.addEventListener("click", function () {
const fecha = boton.getAttribute("data-fecha");
botonesDias.forEach(function (b) {
b.classList.toggle("activo", b === boton);
});
panelesDias.forEach(function (panel) {
panel.classList.toggle(
"activo",
panel.getAttribute("data-fecha") === fecha
);
});
});
});
})();
</script>
<?php if ($resultado->num_rows === 0): ?>
<div class="mensaje mensaje-aviso">
<?= t('No se han encontrado actividades con los filtros seleccionados.') ?>
</div>
<?php else: ?>
<h2 class="titulo-todas-actividades">
<?= t('Conoce todas nuestras actividades') ?>
</h2>
<?php
$actividades_por_tipo = [
'clase' => [],
'evento' => [],
'terapia' => []
];
while ($actividad = $resultado->fetch_assoc()) {
$actividades_por_tipo[$actividad['tipo']][] = $actividad;
}
$titulos_tipo = [
'clase' => t('Clases'),
'evento' => t('Eventos'),
'terapia' => t('Terapias')
];
?>
<?php foreach ($actividades_por_tipo as $tipo_actual => $actividades_tipo): ?>
<?php if (empty($actividades_tipo)): ?>
<?php continue; ?>
<?php endif; ?>
<section class="seccion-tipo-actividad">
<h3><?= escapar($titulos_tipo[$tipo_actual]) ?></h3>
<div class="rejilla-actividades">
<?php foreach ($actividades_tipo as $actividad): ?>
<article class="tarjeta-actividad">
<?php if (!empty($actividad['imagen'])): ?>
<img
class="imagen-actividad"
src="imagenes/actividades/<?= escapar(
$actividad['imagen']
) ?>"
alt="<?= escapar(
$actividad['nombre']
) ?>"
>
<?php else: ?>
<div class="imagen-sin-contenido">
<?= t('Sin imagen') ?>
</div>
<?php endif; ?>
<div class="contenido-tarjeta">
<div class="metadatos">
<span class="insignia">
<?= escapar(
$actividad['categoria']
) ?>
</span>
<span class="insignia insignia-clara">
<?= escapar(
texto_tipo_actividad(
$actividad['tipo']
)
) ?>
</span>
<span class="insignia insignia-clara">
<?= escapar(
texto_nivel(
$actividad['nivel']
)
) ?>
</span>
</div>
<h2>
<?= escapar(
$actividad['nombre']
) ?>
</h2>
<p>
<?= escapar(
$actividad['descripcion']
) ?>
</p>
<p class="dato-destacado">
<?= t('Duración habitual:') ?>
<?= (int)
$actividad['duracion_minutos'] ?>
<?= t('minutos') ?>
</p>
<?php if (
    (int) $actividad['proximas_sesiones'] > 0
): ?>
<p class="proxima-sesion">
<?= (int)
$actividad['proximas_sesiones'] ?>
<?= t('próximas sesiones') ?>
</p>
<p>
<?= t('Próxima:') ?>
<?= escapar(
    date(
'd/m/Y H:i',
strtotime(
$actividad['proxima_fecha']
)
)
) ?>
</p>
<?php else: ?>
<p class="sin-sesiones">
<?= t('Próximamente anunciaremos nuevas fechas.') ?>
</p>
<?php endif; ?>
<a
class="boton"
href="detalle_actividad.php?id=<?= (int)
$actividad['id_actividad'] ?>"
>
<?= t('Ver actividad') ?>
</a>
</div>
</article>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
<?php endif; ?>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>