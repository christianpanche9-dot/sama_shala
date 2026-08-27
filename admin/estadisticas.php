<?php
require_once "seguridad_admin.php";
require_once "../conexion.php";
require_once "../funciones.php";
$sql = "
SELECT
s.id_sesion,
s.fecha,
s.hora_inicio,
s.aforo,
s.estado,
a.nombre AS actividad,
(
SELECT COUNT(*)
FROM reservas r
WHERE r.id_sesion = s.id_sesion
AND r.estado = 'confirmada'
) AS confirmadas,
(
SELECT COUNT(*)
FROM reservas r
WHERE r.id_sesion = s.id_sesion
AND r.estado = 'cancelada'
) AS canceladas,
(
SELECT COUNT(*)
FROM reservas r
WHERE r.id_sesion = s.id_sesion
AND r.estado = 'confirmada'
AND r.asistencia = 'asistio'
) AS asistieron,
(
SELECT COUNT(*)
FROM reservas r
WHERE r.id_sesion = s.id_sesion
AND r.estado = 'confirmada'
AND r.asistencia = 'no_asistio'
) AS ausentes,
(
SELECT COUNT(*)
FROM lista_espera le
WHERE le.id_sesion = s.id_sesion
AND le.estado = 'esperando'
) AS esperando
FROM sesiones s
INNER JOIN actividades a
ON s.id_actividad = a.id_actividad
ORDER BY
s.fecha DESC,
s.hora_inicio DESC
";
$resultado = $conexion->query($sql);
$sesiones = $resultado->fetch_all(MYSQLI_ASSOC);
$total_sesiones = count($sesiones);
$total_confirmadas = 0;
$total_canceladas = 0;
$total_esperando = 0;
$suma_ocupacion = 0;
foreach ($sesiones as $sesion) {
$total_confirmadas += (int) $sesion['confirmadas'];
$total_canceladas += (int) $sesion['canceladas'];
$total_esperando += (int) $sesion['esperando'];
$suma_ocupacion += $sesion['aforo'] > 0
? min(100, $sesion['confirmadas'] / $sesion['aforo'] * 100)
: 0;
}
$ocupacion_media = $total_sesiones > 0
? round($suma_ocupacion / $total_sesiones, 1)
: 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title>Estadísticas de ocupación</title>
<link rel="stylesheet" href="<?= urlEstilos('../') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="../imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="../imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="../imagenes/favicon.ico">
</head>
<body>
<?php require "menu_admin.php"; ?>
<main class="contenedor seccion">
<div class="encabezado-pagina">
<p class="etiqueta">
Administración
</p>
<h1>Estadísticas de ocupación</h1>
<p>
Resumen de aforo, confirmaciones y asistencia de todas las sesiones.
</p>
</div>
<?php if ($total_sesiones === 0): ?>
<div class="mensaje mensaje-aviso">
Todavía no hay sesiones para mostrar estadísticas.
</div>
<?php else: ?>
<div class="rejilla-resumen-admin">
<div class="tarjeta-resumen">
<span>Sesiones</span>
<strong><?= $total_sesiones ?></strong>
</div>
<div class="tarjeta-resumen">
<span>Ocupación media</span>
<strong><?= $ocupacion_media ?>%</strong>
</div>
<div class="tarjeta-resumen">
<span>Confirmadas</span>
<strong><?= $total_confirmadas ?></strong>
</div>
<div class="tarjeta-resumen">
<span>En espera</span>
<strong><?= $total_esperando ?></strong>
</div>
</div>
<div class="tabla-responsive">
<table class="tabla-admin">
<thead>
<tr>
<th>Sesión</th>
<th>Aforo</th>
<th>Confirmadas</th>
<th>Ocupación</th>
<th>Canceladas</th>
<th>En espera</th>
<th>Asistieron</th>
<th>Ausentes</th>
</tr>
</thead>
<tbody>
<?php foreach ($sesiones as $sesion): ?>
<?php
$ocupacion =
$sesion["aforo"] > 0
? round(
$sesion["confirmadas"] /
$sesion["aforo"] *
100,
1
)
: 0;
?>
<tr>
<td>
<a
href="detalles_sesion.php?id=<?=
(int) $sesion["id_sesion"]
?>"
>
<?= escapar(
$sesion["actividad"]
) ?>
</a>
<br>
<small>
<?= date(
"d/m/Y",
strtotime(
$sesion["fecha"]
)
) ?>
·
<?= substr(
$sesion["hora_inicio"],
0,
5
) ?>
</small>
</td>
<td>
    <?= (int) $sesion["aforo"] ?>
</td>

<td>
<?= (int)
$sesion["confirmadas"] ?>
</td>
<td>
<?= $ocupacion ?> %
<div class="barra-mini">
<span
style="width: <?=
min(
100,
$ocupacion
)
?>%"
></span>
</div>
</td>
<td>
<?= (int)
$sesion["canceladas"] ?>
</td>
<td>
<?= (int)
$sesion["esperando"] ?>
</td>
<td>
<?= (int)
$sesion["asistieron"] ?>
</td>
<td>
<?= (int)
$sesion["ausentes"] ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</main>
</body>
</html>
<?php
$conexion->close();