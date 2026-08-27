<?php
require_once "seguridad_admin.php";
require_once "../conexion.php";
$sql = "
SELECT
s.id_sesion,
s.fecha,
s.hora_inicio,
s.hora_fin,
s.aforo,
s.estado,
a.nombre AS actividad,
e.nombre AS espacio,
GROUP_CONCAT(
CONCAT(m.nombre, ' ', m.apellidos)
ORDER BY m.apellidos, m.nombre
SEPARATOR ', '
) AS profesores
FROM sesiones s
INNER JOIN actividades a
ON s.id_actividad = a.id_actividad
INNER JOIN espacios e
ON s.id_espacio = e.id_espacio
INNER JOIN sesiones_profesores sp
ON sp.id_sesion = s.id_sesion
INNER JOIN profesores m
ON sp.id_profesor = m.id_profesor
GROUP BY
s.id_sesion,
s.fecha,
s.hora_inicio,
s.hora_fin,
s.aforo,
s.estado,
a.nombre,
e.nombre
ORDER BY
s.fecha ASC,
s.hora_inicio ASC
";
$resultado = $conexion->query($sql);
$sesiones = $resultado->fetch_all(MYSQLI_ASSOC);
$sesiones_por_mes = [];
foreach ($sesiones as $sesion) {
$clave_mes = substr($sesion['fecha'], 0, 7);
if (!isset($sesiones_por_mes[$clave_mes])) {
$sesiones_por_mes[$clave_mes] = [];
}
$sesiones_por_mes[$clave_mes][] = $sesion;
}
$mes_actual = date('Y-m');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title>Calendario</title>
<link rel="stylesheet" href="<?= urlEstilos('../') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="../imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="../imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="../imagenes/favicon.ico">
</head>

<body>
<?php require_once "menu_admin.php"; ?>
<main class="contenedor">
<h1>Calendario</h1>
<?php if (

($_GET["mensaje"] ?? "") === "sesion_creada"
): ?>
<div class="mensaje mensaje-exito">
La sesión se ha creado correctamente.
</div>
<?php endif; ?>
<?php if (
($_GET["mensaje"] ?? "") === "sesion_actualizada"
): ?>
<div class="mensaje mensaje-exito">
La sesión se ha actualizado correctamente.
</div>
<?php endif; ?>
<?php if (
($_GET["mensaje"] ?? "") === "sesion_eliminada"
): ?>
<div class="mensaje mensaje-exito">
La sesión se ha eliminado correctamente.
</div>
<?php endif; ?>
<?php if (
($_GET["error"] ?? "") === "en_uso"
): ?>
<div class="mensaje mensaje-error">
No se puede eliminar la sesión porque tiene reservas o
apuntes en lista de espera asociados.
</div>
<?php endif; ?>
<a class="boton" href="nueva_sesion.php">
Programar una sesión
</a>
<?php if (count($sesiones) === 0): ?>
<p>No existen sesiones programadas.</p>
<?php else: ?>
<?php foreach ($sesiones_por_mes as $clave_mes => $sesiones_del_mes): ?>
<?php $fecha_mes = DateTime::createFromFormat('Y-m-d', $clave_mes . '-01'); ?>
<details class="grupo-mes-admin" <?= $clave_mes === $mes_actual ? 'open' : '' ?>>
<summary class="resumen-mes-admin">
<span>
<?= escapar(texto_mes((int) $fecha_mes->format('n'))) ?> <?= $fecha_mes->format('Y') ?>
</span>
<span class="contador-mes-admin">
<?= count($sesiones_del_mes) ?> <?= count($sesiones_del_mes) === 1 ? 'sesión' : 'sesiones' ?>
</span>
</summary>
<div class="tabla-responsive">
<table class="tabla-admin">
    <thead>
<tr>
<th>Fecha</th>
<th>Horario</th>
<th>Actividad</th>
<th>Espacio</th>
<th>Profesores</th>
<th>Aforo</th>
<th>Estado</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php foreach ($sesiones_del_mes as $sesion): ?>
<tr>
<td>
<?= date(
"d/m/Y",
strtotime($sesion["fecha"])
) ?>
</td>
<td>
<?= substr(
$sesion["hora_inicio"],
0,
5
) ?>
–
<?= substr(
$sesion["hora_fin"],
0,
5
) ?>
</td>
<td>
    <?= htmlspecialchars(
$sesion["actividad"]
) ?>
</td>

<td>
    <?= htmlspecialchars(
$sesion["espacio"]
) ?>
</td>

<td>
<?= htmlspecialchars(
$sesion["profesores"]
) ?>
</td>
<td>
    <?= $sesion["aforo"] ?>
</td>

<td>
    <?= htmlspecialchars(
ucfirst($sesion["estado"])
) ?>
</td>
<td class="acciones-tabla">
<a
class="boton boton-secundario boton-pequeno"
href="editar_sesion.php?id_sesion=<?= (int) $sesion['id_sesion'] ?>"
>
Editar
</a>
<form
action="eliminar_sesion.php"
method="post"
onsubmit="return confirm('¿Seguro que quieres eliminar esta sesión?');"
>
<input
type="hidden"
name="id_sesion"
value="<?= (int) $sesion['id_sesion'] ?>"
>
<button class="boton peligro boton-pequeno" type="submit">
Eliminar
</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</details>
<?php endforeach; ?>
<?php endif; ?>
</main>
</body>
</html>
