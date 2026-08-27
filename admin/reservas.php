<?php
require_once "seguridad_admin.php";
require_once "../conexion.php";
require_once "../funciones.php";
$fecha = trim($_GET["fecha"] ?? "");
$id_actividad = filter_input(
INPUT_GET,
"id_actividad",
FILTER_VALIDATE_INT
);
$id_actividad = $id_actividad ?: 0;
$estado = trim($_GET["estado"] ?? "");
$asistencia = trim($_GET["asistencia"] ?? "");
$buscar = trim($_GET["buscar"] ?? "");
$estados_permitidos = [
"",
"confirmada",
"cancelada"
];
$asistencias_permitidas = [
    "",
"pendiente",
"asistio",
"no_asistio"
];
if (!in_array($estado, $estados_permitidos, true)) {
$estado = "";
}
if (
!in_array(
$asistencia,
$asistencias_permitidas,
true
)
) {
$asistencia = "";
}
$sql_actividades = "
SELECT id_actividad, nombre
FROM actividades
ORDER BY nombre ASC
";
$actividades =
$conexion->query($sql_actividades);
$patron = "%" . $buscar . "%";
$sql = "
SELECT
r.id_reserva,
r.codigo_reserva,
r.fecha_reserva,
r.estado,
r.asistencia,
u.nombre,
u.apellidos,
u.email,
s.id_sesion,
s.fecha,
s.hora_inicio,
s.hora_fin,
a.nombre AS actividad,
e.nombre AS espacio
FROM reservas r
INNER JOIN usuarios u
ON r.id_usuario = u.id_usuario
INNER JOIN sesiones s
ON r.id_sesion = s.id_sesion
INNER JOIN actividades a
ON s.id_actividad = a.id_actividad
INNER JOIN espacios e
ON s.id_espacio = e.id_espacio
WHERE (? = '' OR s.fecha = ?)
AND (? = 0 OR a.id_actividad = ?)
AND (? = '' OR r.estado = ?)
AND (? = '' OR r.asistencia = ?)
AND (
? = ''
OR u.nombre LIKE ?
OR u.apellidos LIKE ?
OR u.email LIKE ?
)
ORDER BY
s.fecha DESC,
s.hora_inicio DESC,
u.apellidos ASC,
u.nombre ASC
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param(
"ssiissssssss",
$fecha,
$fecha,
$id_actividad,
$id_actividad,
$estado,
$estado,
$asistencia,
$asistencia,
$buscar,
$patron,
$patron,
$patron
);
$stmt->execute();
$reservas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$reservas_por_mes = [];
foreach ($reservas as $reserva) {
$clave_mes = substr($reserva['fecha'], 0, 7);
if (!isset($reservas_por_mes[$clave_mes])) {
$reservas_por_mes[$clave_mes] = [];
}
$reservas_por_mes[$clave_mes][] = $reserva;
}
$mes_actual = date('Y-m');
$primer_mes = array_key_first($reservas_por_mes);
$mensaje = $_GET["mensaje"] ?? "";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title>Gestión de reservas</title>
<link rel="stylesheet" href="<?= urlEstilos('../') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="../imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="../imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="../imagenes/favicon.ico">
</head>
<body>
<?php require "menu_admin.php"; ?>
<main class="contenedor">
<h1>Gestión de reservas</h1>
<?php if ($mensaje === "asistencia"): ?>
<div class="mensaje mensaje-exito">
La asistencia se ha actualizado.
</div>
<?php elseif ($mensaje === "cancelada"): ?>
<div class="mensaje mensaje-exito">
La reserva se ha cancelado.
</div>
<?php endif; ?>
<form method="get" class="filtros">
<div class="campo">
<label for="fecha">Fecha</label>
<input
type="date"
id="fecha"
name="fecha"
value="<?= escapar($fecha) ?>"
>
</div>
<div class="campo">
<label for="id_actividad">
Actividad
</label>
<select
id="id_actividad"
name="id_actividad"
>
<option value="0">
Todas
</option>
<?php while (
$actividad =
$actividades->fetch_assoc()
): ?>
<option
value="<?=
$actividad["id_actividad"]
?>"
<?=
$id_actividad ==
$actividad["id_actividad"]
? "selected"
: ""
?>
>
<?= escapar(
$actividad["nombre"]
) ?>
</option>
<?php endwhile; ?>
</select>
</div>
<div class="campo">
<label for="estado">
Estado
</label>
<select id="estado" name="estado">
<option value="">
Todos
</option>
<option
value="confirmada"
<?= $estado === "confirmada"
? "selected"
: "" ?>
>
Confirmada
</option>
<option
value="cancelada"
<?= $estado === "cancelada"
? "selected"
: "" ?>
>
Cancelada
</option>
</select>
</div>
<div class="campo">
<label for="asistencia">
Asistencia
</label>
<select
id="asistencia"
name="asistencia"
>
<option value="">
Todas
</option>
<option
value="pendiente"
<?= $asistencia === "pendiente"
? "selected"
: "" ?>
>
Pendiente
</option>
<option
value="asistio"
<?= $asistencia === "asistio"
? "selected"
: "" ?>
>
Asistió
</option>
<option
value="no_asistio"
<?= $asistencia === "no_asistio"
? "selected"
: "" ?>
>
No asistió
</option>
</select>
</div>
<div class="campo">
<label for="buscar">
Buscar cliente
</label>
<input
type="search"
id="buscar"
name="buscar"
value="<?= escapar($buscar) ?>"
placeholder="Nombre, apellidos o correo"
>
</div>
<div class="campo campo-acciones-filtro">
<div class="acciones-filtro">
<button type="submit" class="boton">
Aplicar filtros
</button>
<a
href="reservas.php"
class="boton boton-secundario"
>
Limpiar
</a>
</div>
</div>
</form>
<?php if (count($reservas) === 0): ?>
<p>No se han encontrado reservas.</p>
<?php else: ?>
<?php foreach ($reservas_por_mes as $clave_mes => $reservas_del_mes): ?>
<?php $fecha_mes = DateTime::createFromFormat('Y-m-d', $clave_mes . '-01'); ?>
<details class="grupo-mes-admin" <?= ($clave_mes === $mes_actual || $clave_mes === $primer_mes) ? 'open' : '' ?>>
<summary class="resumen-mes-admin">
<span>
<?= escapar(texto_mes((int) $fecha_mes->format('n'))) ?> <?= $fecha_mes->format('Y') ?>
</span>
<span class="contador-mes-admin">
<?= count($reservas_del_mes) ?> <?= count($reservas_del_mes) === 1 ? 'reserva' : 'reservas' ?>
</span>
</summary>
<div class="tabla-responsive">
<table class="tabla-admin">
<thead>
<tr>
<th>Fecha</th>
<th>Actividad</th>
<th>Cliente</th>
<th>Estado</th>
<th>Asistencia</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php foreach ($reservas_del_mes as $reserva): ?>
<tr>
<td>
<?= date(
"d/m/Y",
strtotime(
$reserva["fecha"]
)
) ?>
<br>
<?= substr(
$reserva["hora_inicio"],
0,
5
) ?>
</td>
<td>
    <?= escapar(
$reserva["actividad"]
) ?>

<br>
<small>
<?= escapar(
$reserva["espacio"]
) ?>
</small>
</td>
<td>
<?= escapar(
$reserva["apellidos"] .
", " .
$reserva["nombre"]
) ?>
<br>
<small>
<?= escapar(
$reserva["email"]
) ?>
</small>
</td>
<td>
<span class="estado">
    <?= escapar(
ucfirst(
$reserva["estado"]
)
) ?>
</span>
</td>
<td>
<?= escapar(
str_replace(
"_",
" ",
ucfirst(
$reserva[
"asistencia"
]
)
)
) ?>
</td>
<td>
<a
class="boton boton-secundario boton-pequeno"
href="detalles_sesion.php?id=<?=
(int) $reserva["id_sesion"]
?>"
>
Ver sesión
</a>
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
<?php
$stmt->close();
$conexion->close();