<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';

$hoy = new DateTime('today');

$fecha_solicitada = trim($_GET['fecha'] ?? '');
if ($fecha_solicitada !== '' && fecha_valida($fecha_solicitada)) {
$inicio_semana = new DateTime($fecha_solicitada);
} else {
$inicio_semana = clone $hoy;
}
if ($inicio_semana < $hoy) {
$inicio_semana = clone $hoy;
}

$dias_semana = [];
$sesiones_por_dia = [];
for ($i = 0; $i < 7; $i++) {
$dia = (clone $inicio_semana)->modify("+$i day");
$dias_semana[] = $dia;
$sesiones_por_dia[$dia->format('Y-m-d')] = [];
}
$fecha_inicio_rango = $dias_semana[0]->format('Y-m-d');
$fecha_fin_rango = $dias_semana[6]->format('Y-m-d');

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

$mes_semana = (int) $inicio_semana->format('n');
$anio_semana = (int) $inicio_semana->format('Y');
$mes_anterior = $mes_semana === 1 ? 12 : $mes_semana - 1;
$anio_mes_anterior = $mes_semana === 1 ? $anio_semana - 1 : $anio_semana;
$mes_siguiente = $mes_semana === 12 ? 1 : $mes_semana + 1;
$anio_mes_siguiente = $mes_semana === 12 ? $anio_semana + 1 : $anio_semana;
$primer_dia_mes_anterior = new DateTime(
sprintf('%04d-%02d-01', $anio_mes_anterior, $mes_anterior)
);
$ultimo_dia_mes_anterior = (clone $primer_dia_mes_anterior)
->modify('last day of this month');
$mostrar_mes_anterior = $ultimo_dia_mes_anterior >= $hoy;
$destino_mes_anterior = $primer_dia_mes_anterior < $hoy
? clone $hoy
: $primer_dia_mes_anterior;
$destino_mes_siguiente = new DateTime(
sprintf('%04d-%02d-01', $anio_mes_siguiente, $mes_siguiente)
);

$semana_anterior = (clone $inicio_semana)->modify('-7 day');
$semana_siguiente = (clone $inicio_semana)->modify('+7 day');
$mostrar_semana_anterior = $semana_anterior >= $hoy;

if ($dias_semana[0]->format('n') === $dias_semana[6]->format('n')) {
$etiqueta_semana =
$dias_semana[0]->format('j') . ' - ' .
$dias_semana[6]->format('j') . ' ' .
t('de') . ' ' . escapar(texto_mes((int) $dias_semana[0]->format('n')));
} else {
$etiqueta_semana =
$dias_semana[0]->format('j') . ' ' . t('de') . ' ' .
escapar(texto_mes((int) $dias_semana[0]->format('n'))) . ' - ' .
$dias_semana[6]->format('j') . ' ' . t('de') . ' ' .
escapar(texto_mes((int) $dias_semana[6]->format('n')));
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
href="sesiones.php?fecha=<?= $destino_mes_anterior->format('Y-m-d') ?>"
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
<?= escapar(texto_mes($mes_semana)) ?> <?= $anio_semana ?>
</span>
<a
class="boton-mes"
href="sesiones.php?fecha=<?= $destino_mes_siguiente->format('Y-m-d') ?>"
aria-label="<?= t('Mes siguiente') ?>"
>
→
</a>
</div>
<div class="navegacion-semana">
<?php if ($mostrar_semana_anterior): ?>
<a
class="boton-mes"
href="sesiones.php?fecha=<?= $semana_anterior->format('Y-m-d') ?>"
aria-label="<?= t('Semana anterior') ?>"
>
←
</a>
<?php else: ?>
<span class="boton-mes boton-mes-deshabilitado" aria-hidden="true">
←
</span>
<?php endif; ?>
<span class="navegacion-semana-titulo">
<?= $etiqueta_semana ?>
</span>
<a
class="boton-mes"
href="sesiones.php?fecha=<?= $semana_siguiente->format('Y-m-d') ?>"
aria-label="<?= t('Semana siguiente') ?>"
>
→
</a>
</div>
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
