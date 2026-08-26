<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';

$hoy = new DateTime('today');

$mes = filter_input(
INPUT_GET,
'mes',
FILTER_VALIDATE_INT,
['options' => ['min_range' => 1, 'max_range' => 12]]
);
$anio = filter_input(
INPUT_GET,
'anio',
FILTER_VALIDATE_INT,
['options' => ['min_range' => 2020, 'max_range' => 2100]]
);
if (!$mes || !$anio) {
$mes = (int) $hoy->format('n');
$anio = (int) $hoy->format('Y');
}
$primer_dia_mes_visible = new DateTime(
sprintf('%04d-%02d-01', $anio, $mes)
);
$primer_dia_mes_actual = new DateTime($hoy->format('Y-m-01'));
if ($primer_dia_mes_visible < $primer_dia_mes_actual) {
$mes = (int) $hoy->format('n');
$anio = (int) $hoy->format('Y');
$primer_dia_mes_visible = clone $primer_dia_mes_actual;
}

$mes_anterior = $mes === 1 ? 12 : $mes - 1;
$anio_mes_anterior = $mes === 1 ? $anio - 1 : $anio;
$mes_siguiente = $mes === 12 ? 1 : $mes + 1;
$anio_mes_siguiente = $mes === 12 ? $anio + 1 : $anio;
$primer_dia_mes_anterior = new DateTime(
sprintf('%04d-%02d-01', $anio_mes_anterior, $mes_anterior)
);
$mostrar_mes_anterior = $primer_dia_mes_anterior >= $primer_dia_mes_actual;

$dias_mes = [];
$sesiones_por_dia = [];
$dias_en_mes = (int) $primer_dia_mes_visible->format('t');
for ($d = 1; $d <= $dias_en_mes; $d++) {
$dia = new DateTime(sprintf('%04d-%02d-%02d', $anio, $mes, $d));
$dias_mes[] = $dia;
$sesiones_por_dia[$dia->format('Y-m-d')] = [];
}
$fecha_inicio_rango = $dias_mes[0]->format('Y-m-d');
$fecha_fin_rango = $dias_mes[count($dias_mes) - 1]->format('Y-m-d');

$sql = "
SELECT
s.id_sesion,
s.fecha,
s.hora_inicio,
s.hora_fin,
a.id_actividad,
a.nombre AS actividad,
a.nivel,
COALESCE(
NULLIF(p.username, ''),
CONCAT(p.nombre, ' ', p.apellidos)
) AS profesor
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
$stmt = $conexion->prepare($sql);
$stmt->bind_param('ss', $fecha_inicio_rango, $fecha_fin_rango);
$stmt->execute();
$resultado = $stmt->get_result();
while ($sesion = $resultado->fetch_assoc()) {
$sesiones_por_dia[$sesion['fecha']][] = $sesion;
}

$fecha_activa = $primer_dia_mes_visible == $primer_dia_mes_actual
? clone $hoy
: clone $primer_dia_mes_visible;
$clave_fecha_activa = $fecha_activa->format('Y-m-d');

function etiqueta_semana_de(DateTime $fecha): string
{
$dia_semana_iso = (int) $fecha->format('N');
$lunes = (clone $fecha)->modify('-' . ($dia_semana_iso - 1) . ' day');
$domingo = (clone $lunes)->modify('+6 day');
if ($lunes->format('n') === $domingo->format('n')) {
return $lunes->format('j') . ' - ' . $domingo->format('j') .
' ' . t('de') . ' ' . escapar(texto_mes((int) $lunes->format('n')));
}
return $lunes->format('j') . ' ' . t('de') . ' ' .
escapar(texto_mes((int) $lunes->format('n'))) . ' - ' .
$domingo->format('j') . ' ' . t('de') . ' ' .
escapar(texto_mes((int) $domingo->format('n')));
}

$sql_profesores = "
SELECT
id_profesor,
nombre,
apellidos,
username,
imagen,
resena
FROM profesores
WHERE activo = 1
ORDER BY apellidos, nombre
";
$resultado_profesores = $conexion->query($sql_profesores);
$profesores = $resultado_profesores->fetch_all(MYSQLI_ASSOC);
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
    <?= t('Próximas sesiones | Sama Shala') ?>
</title>
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
<p class="etiqueta">
<?= t('Calendario') ?>
</p>
<h1><?= t('Próximas sesiones') ?></h1>
<p>
<?= t('Consulta las actividades que se celebrarán próximamente.') ?>
</p>
</div>
</div>
<div class="navegacion-mes">
<?php if ($mostrar_mes_anterior): ?>
<a
class="boton-mes"
href="sesiones.php?mes=<?= $mes_anterior ?>&anio=<?= $anio_mes_anterior ?>"
aria-label="<?= t('Mes anterior') ?>"
>
←
</a>
<?php else: ?>
<span class="boton-mes boton-mes-deshabilitado" aria-hidden="true">
←
</span>
<?php endif; ?>
<span class="navegacion-mes-titulo">
<?= escapar(texto_mes($mes)) ?> <?= $anio ?>
</span>
<a
class="boton-mes"
href="sesiones.php?mes=<?= $mes_siguiente ?>&anio=<?= $anio_mes_siguiente ?>"
aria-label="<?= t('Mes siguiente') ?>"
>
→
</a>
</div>
<p class="navegacion-semana-titulo" id="etiqueta-semana-activa">
<?= etiqueta_semana_de($fecha_activa) ?>
</p>
<div class="calendario-semana">
<?php foreach ($dias_mes as $dia): ?>
<?php $clave_dia = $dia->format('Y-m-d'); ?>
<button
type="button"
class="dia-semana-boton<?= $clave_dia === $clave_fecha_activa ? ' activo' : '' ?><?= $dia < $hoy ? ' dia-semana-boton-pasado' : '' ?>"
data-fecha="<?= $clave_dia ?>"
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
<?php foreach ($dias_mes as $dia): ?>
<?php $clave_dia = $dia->format('Y-m-d'); ?>
<div
class="dia-actividades<?= $clave_dia === $clave_fecha_activa ? ' activo' : '' ?>"
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
<?= escapar(formatear_hora($sesion_dia['hora_inicio'])) ?> – <?= escapar(formatear_hora($sesion_dia['hora_fin'])) ?>
</span>
<span class="item-actividad-nombre">
<?= escapar($sesion_dia['actividad']) ?>
</span>
<span class="item-actividad-detalle">
<?= escapar($sesion_dia['profesor']) ?> · <?= escapar(texto_nivel($sesion_dia['nivel'])) ?>
</span>
</a>
<?php endforeach; ?>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>
<script>
(function () {
const idioma = "<?= idiomaActual() === 'en' ? 'en' : 'es' ?>";
const conectorDe = idioma === "en" ? "" : " " + <?= json_encode(t('de')) ?>;
const botonesDias = document.querySelectorAll(".dia-semana-boton");
const panelesDias = document.querySelectorAll(".dia-actividades");
const etiquetaSemana = document.querySelector("#etiqueta-semana-activa");
const boton = document.querySelector(".dia-semana-boton.activo");
if (boton) {
boton.scrollIntoView({ inline: "center", block: "nearest" });
}

function calcularEtiquetaSemana(fechaStr) {
const fecha = new Date(fechaStr + "T00:00:00");
const diaIso = (fecha.getDay() + 6) % 7;
const lunes = new Date(fecha);
lunes.setDate(fecha.getDate() - diaIso);
const domingo = new Date(lunes);
domingo.setDate(lunes.getDate() + 6);
const formatoMes = new Intl.DateTimeFormat(idioma, { month: "long" });
if (lunes.getMonth() === domingo.getMonth()) {
return lunes.getDate() + " - " + domingo.getDate() +
conectorDe + " " + formatoMes.format(lunes);
}
return lunes.getDate() + conectorDe + " " + formatoMes.format(lunes) +
" - " + domingo.getDate() + conectorDe + " " + formatoMes.format(domingo);
}

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
if (etiquetaSemana) {
etiquetaSemana.textContent = calcularEtiquetaSemana(fecha);
}
});
});
})();
</script>
<?php if (!empty($profesores)): ?>
<h2 class="titulo-todas-actividades">
<?= t('Conoce a nuestros profesores') ?>
</h2>
<div class="rejilla-profesores">
<?php foreach ($profesores as $profesor): ?>
<article class="tarjeta-profesor">
<?php if (!empty($profesor['imagen'])): ?>
<img
class="imagen-profesor"
src="imagenes/profesores/<?= escapar($profesor['imagen']) ?>"
alt="<?= escapar($profesor['nombre']) ?>"
>
<?php else: ?>
<div class="imagen-sin-contenido">
<?= t('Sin imagen') ?>
</div>
<?php endif; ?>
<div class="contenido-tarjeta-profesor">
<h3>
<?= escapar(
!empty($profesor['username'])
? $profesor['username']
: $profesor['nombre'] . ' ' . $profesor['apellidos']
) ?>
</h3>
<?php if (!empty($profesor['resena'])): ?>
<p><?= escapar($profesor['resena']) ?></p>
<?php endif; ?>
</div>
</article>
<?php endforeach; ?>
</div>
<?php endif; ?>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
