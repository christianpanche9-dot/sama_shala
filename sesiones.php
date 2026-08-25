<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';

$hoy = new DateTime('today');
$primer_dia_mes_actual = new DateTime($hoy->format('Y-m-01'));

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

$fecha_inicio_mes = $primer_dia_mes_visible->format('Y-m-01');
$fecha_fin_mes = $primer_dia_mes_visible->format('Y-m-t');

$sql = "
SELECT
s.fecha,
s.hora_inicio,
s.hora_fin,
a.id_actividad,
a.nombre AS actividad
FROM sesiones AS s
INNER JOIN actividades AS a
ON s.id_actividad = a.id_actividad
WHERE a.activa = 1
AND s.estado IN ('programada', 'completa')
AND s.fecha BETWEEN ? AND ?
ORDER BY s.fecha, s.hora_inicio
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('ss', $fecha_inicio_mes, $fecha_fin_mes);
$stmt->execute();
$resultado = $stmt->get_result();
$sesiones_por_dia = [];
while ($sesion = $resultado->fetch_assoc()) {
$sesiones_por_dia[$sesion['fecha']][] = $sesion;
}
$semanas_mes = generar_calendario_mes($anio, $mes);
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
<?php if (empty($sesiones_por_dia)): ?>
<div class="mensaje mensaje-aviso">
<?= t('No hay sesiones programadas este mes.') ?>
</div>
<?php endif; ?>
<div class="calendario-mes-publico">
<div class="calendario-publico-cabecera">
<?php for ($d = 1; $d <= 7; $d++): ?>
<span><?= escapar(texto_dia_semana_abreviado($d)) ?></span>
<?php endfor; ?>
</div>
<div class="calendario-publico-grilla">
<?php foreach ($semanas_mes as $semana): ?>
<?php foreach ($semana as $dia): ?>
<?php if ($dia === null): ?>
<div class="dia-calendario-publico dia-calendario-publico-vacio">
</div>
<?php else: ?>
<?php
$clave_dia = $dia->format('Y-m-d');
$es_hoy = $clave_dia === $hoy->format('Y-m-d');
$es_pasado = $dia < $hoy;
?>
<div class="dia-calendario-publico<?= $es_hoy ? ' dia-calendario-publico-hoy' : '' ?><?= $es_pasado ? ' dia-calendario-publico-pasado' : '' ?>">
<span class="dia-calendario-publico-numero">
<?= (int) $dia->format('j') ?>
</span>
<?php if (!empty($sesiones_por_dia[$clave_dia])): ?>
<div class="sesiones-dia-calendario">
<?php foreach ($sesiones_por_dia[$clave_dia] as $sesion_dia): ?>
<a
class="sesion-calendario-chip"
href="detalle_actividad.php?id=<?= (int) $sesion_dia['id_actividad'] ?>"
>
<span class="sesion-calendario-hora">
<?= escapar(formatear_hora($sesion_dia['hora_inicio'])) ?>
</span>
<span class="sesion-calendario-nombre">
<?= escapar($sesion_dia['actividad']) ?>
</span>
</a>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
<?php endif; ?>
<?php endforeach; ?>
<?php endforeach; ?>
</div>
</div>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
