<?php
require_once "seguridad_admin.php";
require_once "../conexion.php";
validarCsrf();
$errores = [];
$id_actividad = filter_input(
INPUT_POST,
"id_actividad",
FILTER_VALIDATE_INT
);
$id_espacio = filter_input(
INPUT_POST,
"id_espacio",
FILTER_VALIDATE_INT
);
$profesores_seleccionados = $_POST["profesores"] ?? [];
if (!is_array($profesores_seleccionados)) {
$profesores_seleccionados = [];
}
$profesores_seleccionados = array_values(array_unique(array_filter(
array_map("intval", $profesores_seleccionados)
)));
$fecha = trim($_POST["fecha"] ?? "");
$hora_inicio = trim($_POST["hora_inicio"] ?? "");
$duracion = filter_input(
INPUT_POST,
"duracion",
FILTER_VALIDATE_INT
);
$aforo = filter_input(
INPUT_POST,
"aforo",
FILTER_VALIDATE_INT
);
$observaciones = trim($_POST["observaciones"] ?? "");
$es_recurrente = isset($_POST["es_recurrente"]);
/*
|--------------------------------------------------------------------------
| 1. Validaciones básicas
|--------------------------------------------------------------------------
*/


if (!$id_actividad) {
$errores[] = "Debes seleccionar una actividad válida.";
}
if (!$id_espacio) {
$errores[] = "Debes seleccionar un espacio válido.";
}
if (empty($profesores_seleccionados)) {
$errores[] = "Debes seleccionar al menos un profesor.";
}
if (!$es_recurrente && $fecha === "") {
    $errores[] = "Debes indicar una fecha.";
}
if (!$es_recurrente && $hora_inicio === "") {
$errores[] = "Debes indicar una hora de inicio.";
}
if ($duracion === false || $duracion === null) {
$errores[] = "La duración no es válida.";
} elseif ($duracion < 15 || $duracion > 480) {
$errores[] =
"La duración debe estar entre 15 y 480 minutos.";
}
if ($aforo === false || $aforo === null || $aforo <= 0) {
$errores[] = "El aforo debe ser superior a cero.";
}
/*
|--------------------------------------------------------------------------
| 2. Validar y calcular el horario
|--------------------------------------------------------------------------
*/

$inicio = false;
$fin = false;
$hora_fin = null;
if (
!$es_recurrente &&
$fecha !== "" &&
$hora_inicio !== "" &&
$duracion !== false &&
$duracion !== null &&
$duracion >= 15 &&
$duracion <= 480
) {
$inicio = DateTime::createFromFormat(
"Y-m-d H:i",
$fecha . " " . $hora_inicio
);
$errores_fecha = DateTime::getLastErrors();
if (
!$inicio ||
(
$errores_fecha !== false &&
(
$errores_fecha["warning_count"] > 0 ||
$errores_fecha["error_count"] > 0
)
)
) {
$errores[] = "La fecha o la hora no son válidas.";
} else {
$fin = clone $inicio;
$fin->modify("+{$duracion} minutes");

// Margen de 15 minutos para comprobaciones
$inicio_comprobacion = clone $inicio;
$inicio_comprobacion->modify("-15 minutes");

$fin_comprobacion = clone $fin;
$fin_comprobacion->modify("+15 minutes");
if (
$inicio->format("Y-m-d") !==
$fin->format("Y-m-d")
) {
$errores[] =
"La sesión debe comenzar y terminar el mismo día.";
}
$ahora = new DateTime();
if ($inicio <= $ahora) {
$errores[] =
"La fecha y la hora de inicio deben estar en el futuro.";
}
$hora_inicio = $inicio->format("H:i:s");
$hora_fin = $fin->format("H:i:s");
$fecha = $inicio->format("Y-m-d");
}
}
/*
|--------------------------------------------------------------------------
| 2b. Validar y generar las ocurrencias de una sesión recurrente
|--------------------------------------------------------------------------
*/
$ocurrencias_recurrentes = [];
$sesiones_recurrentes_omitidas = 0;
if ($es_recurrente) {
$dias_recurrentes_activos = $_POST["dias_recurrentes"] ?? [];
if (!is_array($dias_recurrentes_activos)) {
$dias_recurrentes_activos = [];
}
$horarios_por_dia_recurrente = [];
foreach ($dias_recurrentes_activos as $dia_recurrente_valor) {
$dia_recurrente_numero = filter_var(
$dia_recurrente_valor,
FILTER_VALIDATE_INT,
[
"options" => [
"min_range" => 1,
"max_range" => 7
]
]
);
if ($dia_recurrente_numero === false) {
continue;
}
$horas_dia_recurrente =
$_POST["horas_recurrente_" . $dia_recurrente_numero] ?? [];
if (!is_array($horas_dia_recurrente)) {
$horas_dia_recurrente = [$horas_dia_recurrente];
}
$horas_validas_dia_recurrente = [];
foreach ($horas_dia_recurrente as $hora_dia_recurrente) {
$hora_dia_recurrente = trim($hora_dia_recurrente);
if (
$hora_dia_recurrente !== "" &&
hora_valida($hora_dia_recurrente)
) {
$horas_validas_dia_recurrente[$hora_dia_recurrente] =
$hora_dia_recurrente;
}
}
if (!empty($horas_validas_dia_recurrente)) {
$horarios_por_dia_recurrente[$dia_recurrente_numero] =
array_values($horas_validas_dia_recurrente);
}
}
$fechas_recurrentes = $_POST["fechas_recurrentes"] ?? [];
if (!is_array($fechas_recurrentes)) {
$fechas_recurrentes = [];
}
if (empty($horarios_por_dia_recurrente) || empty($fechas_recurrentes)) {
$errores[] =
"Debes marcar al menos un día con un horario y al menos una fecha en el calendario.";
}
if (
$duracion !== false &&
$duracion !== null &&
$duracion >= 15 &&
$duracion <= 480
) {
$ahora_recurrente = new DateTime();
foreach ($fechas_recurrentes as $fecha_recurrente) {
$fecha_recurrente = trim($fecha_recurrente);
if (!fecha_valida($fecha_recurrente)) {
$sesiones_recurrentes_omitidas++;
continue;
}
$dia_semana_fecha_recurrente = (int) DateTime::createFromFormat(
"Y-m-d",
$fecha_recurrente
)->format("N");
$horas_del_dia_recurrente =
$horarios_por_dia_recurrente[$dia_semana_fecha_recurrente] ?? [];
if (empty($horas_del_dia_recurrente)) {
$sesiones_recurrentes_omitidas++;
continue;
}
foreach ($horas_del_dia_recurrente as $hora_inicio_recurrente) {
$inicio_recurrente = DateTime::createFromFormat(
"Y-m-d H:i",
$fecha_recurrente . " " . $hora_inicio_recurrente
);
if (!$inicio_recurrente || $inicio_recurrente <= $ahora_recurrente) {
$sesiones_recurrentes_omitidas++;
continue;
}
$fin_recurrente = clone $inicio_recurrente;
$fin_recurrente->modify("+{$duracion} minutes");
$ocurrencias_recurrentes[] = [
"fecha" => $fecha_recurrente,
"hora_inicio" => $inicio_recurrente->format("H:i:s"),
"hora_fin" => $fin_recurrente->format("H:i:s")
];
}
}
if (
empty($errores) &&
empty($ocurrencias_recurrentes)
) {
$errores[] =
"No se ha podido generar ninguna sesión válida con los días, horarios y fechas indicados.";
}
}
}
/*
|--------------------------------------------------------------------------
| 3. Comprobar la actividad
|--------------------------------------------------------------------------

*/

if ($id_actividad) {
$sql_actividad = "
SELECT id_actividad, nombre
FROM actividades
WHERE id_actividad = ?
AND activa = 1
";
$stmt_actividad =
$conexion->prepare($sql_actividad);
$stmt_actividad->bind_param(
"i",
$id_actividad
);
$stmt_actividad->execute();
$resultado_actividad =
$stmt_actividad->get_result();
$actividad =
$resultado_actividad->fetch_assoc();
if (!$actividad) {
$errores[] =
"La actividad seleccionada no existe o está inactiva.";
}
$stmt_actividad->close();
}
/*
|--------------------------------------------------------------------------
| 4. Comprobar el espacio y su capacidad
|--------------------------------------------------------------------------

*/

$espacio = null;
if ($id_espacio) {
$sql_espacio = "
SELECT id_espacio, nombre, aforo_maximo
FROM espacios
WHERE id_espacio = ?
AND activo = 1
";
$stmt_espacio =
$conexion->prepare($sql_espacio);
$stmt_espacio->bind_param(
"i",
$id_espacio
);
$stmt_espacio->execute();
$resultado_espacio =
$stmt_espacio->get_result();
$espacio =
$resultado_espacio->fetch_assoc();
if (!$espacio) {
$errores[] =
"El espacio seleccionado no existe o está inactivo.";
} elseif (
$aforo !== false &&
$aforo !== null &&
$aforo > $espacio["aforo_maximo"]
) {
$errores[] =
"El aforo solicitado supera la capacidad de " .
$espacio["nombre"] .
", que admite un máximo de " .
$espacio["capacidad"] .
" personas.";
}
$stmt_espacio->close();
}
/*
|--------------------------------------------------------------------------
| 5. Comprobar el profesor
|--------------------------------------------------------------------------
*/

$profesores_datos = [];
foreach ($profesores_seleccionados as $id_profesor_valido) {
$sql_profesor = "
SELECT id_profesor, nombre, apellidos
FROM profesores
WHERE id_profesor = ?
AND activo = 1
";
$stmt_profesor =
$conexion->prepare($sql_profesor);
$stmt_profesor->bind_param(
"i",
$id_profesor_valido
);
$stmt_profesor->execute();
$resultado_profesor =
$stmt_profesor->get_result();
$profesor =
$resultado_profesor->fetch_assoc();
if (!$profesor) {
$errores[] =
"Uno de los profesores seleccionados no existe o está inactivo.";
} else {
$profesores_datos[] = $profesor;
}
$stmt_profesor->close();
}
/*
|--------------------------------------------------------------------------
| 6. Comprobar solapamientos
|--------------------------------------------------------------------------
*/
if (!$es_recurrente && empty($errores)) {
/*
|--------------------------------------------------------------------------
| 6.1 Conflicto del espacio
|--------------------------------------------------------------------------
*/

$sql_conflicto_espacio = "
SELECT
s.id_sesion,
s.hora_inicio,
s.hora_fin,
a.nombre AS actividad
FROM sesiones s
INNER JOIN actividades a
ON s.id_actividad = a.id_actividad
WHERE s.fecha = ?
AND s.id_espacio = ?
AND s.estado <> 'cancelada'
AND s.hora_inicio < ?
AND s.hora_fin > ?
LIMIT 1
";
$stmt_conflicto_espacio =
$conexion->prepare($sql_conflicto_espacio);
$stmt_conflicto_espacio->bind_param(
"siss",
$fecha,
$id_espacio,
$fin_comprobacion->format("H:i:s"),
$inicio_comprobacion->format("H:i:s")
);
$stmt_conflicto_espacio->execute();
$resultado_conflicto_espacio =
$stmt_conflicto_espacio->get_result();
if ($resultado_conflicto_espacio->num_rows > 0) {
$conflicto =
$resultado_conflicto_espacio->fetch_assoc();
$errores[] =
"El espacio ya está ocupado por la actividad \"" .
htmlspecialchars($conflicto["actividad"]) .
"\", de " .
substr($conflicto["hora_inicio"], 0, 5) .
" a " .
substr($conflicto["hora_fin"], 0, 5) .
".";
}
$stmt_conflicto_espacio->close();
/*
|--------------------------------------------------------------------------
| 6.2 Conflicto del profesor
|--------------------------------------------------------------------------
*/

$sql_conflicto_profesor = "
SELECT
s.id_sesion,
s.hora_inicio,
s.hora_fin,
a.nombre AS actividad,
e.nombre AS espacio
FROM sesiones s
INNER JOIN sesiones_profesores sp
ON sp.id_sesion = s.id_sesion
INNER JOIN actividades a
ON s.id_actividad = a.id_actividad
INNER JOIN espacios e
ON s.id_espacio = e.id_espacio
WHERE s.fecha = ?
AND sp.id_profesor = ?
AND s.estado <> 'cancelada'
AND s.hora_inicio < ?
AND s.hora_fin > ?
LIMIT 1
";
$stmt_conflicto_profesor =
$conexion->prepare($sql_conflicto_profesor);
foreach ($profesores_datos as $profesor_conflicto) {
$id_profesor_conflicto = (int) $profesor_conflicto["id_profesor"];
$stmt_conflicto_profesor->bind_param(
"siss",
$fecha,
$id_profesor_conflicto,
$fin_comprobacion->format("H:i:s"),
$inicio_comprobacion->format("H:i:s")
);
$stmt_conflicto_profesor->execute();
$resultado_conflicto_profesor =
$stmt_conflicto_profesor->get_result();
if ($resultado_conflicto_profesor->num_rows > 0) {
$conflicto =
$resultado_conflicto_profesor->fetch_assoc();
$errores[] =
htmlspecialchars($profesor_conflicto["nombre"] . " " . $profesor_conflicto["apellidos"]) .
" ya tiene asignada la actividad \"" .
htmlspecialchars($conflicto["actividad"]) .
"\" en " .
htmlspecialchars($conflicto["espacio"]) .
", de " .
substr($conflicto["hora_inicio"], 0, 5) .
" a " .
substr($conflicto["hora_fin"], 0, 5) .
".";
}
}
$stmt_conflicto_profesor->close();
}
/*
|--------------------------------------------------------------------------
| 7. Mostrar los errores
|--------------------------------------------------------------------------
*/

if (!empty($errores)) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title>No se ha podido crear la sesión</title>
<link rel="stylesheet" href="<?= urlEstilos('../') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="../imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="../imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="../imagenes/favicon.ico">
</head>
<body>
    <main class="contenedor">
<h1>No se ha podido crear la sesión</h1>
<div class="mensaje error">
<ul>
<?php foreach ($errores as $error): ?>
<li><?= $error ?></li>
<?php endforeach; ?>
</ul>
</div>
<a
class="boton"
href="nueva_sesion.php"
>
Volver al formulario
</a>
</main>
</body>
</html>
<?php
exit;
}
/*
|--------------------------------------------------------------------------
| 8. Insertar la sesión
|--------------------------------------------------------------------------
*/
$id_profesor_principal = $profesores_seleccionados[0];
if ($es_recurrente) {
$conexion->begin_transaction();
try {
$sql_conflicto_espacio_recurrente = "
SELECT id_sesion
FROM sesiones
WHERE fecha = ?
AND id_espacio = ?
AND estado <> 'cancelada'
AND hora_inicio < ?
AND hora_fin > ?
LIMIT 1
";
$stmt_conflicto_espacio_recurrente =
$conexion->prepare($sql_conflicto_espacio_recurrente);
$sql_conflicto_profesor_recurrente = "
SELECT s.id_sesion
FROM sesiones s
INNER JOIN sesiones_profesores sp
ON sp.id_sesion = s.id_sesion
WHERE s.fecha = ?
AND sp.id_profesor = ?
AND s.estado <> 'cancelada'
AND s.hora_inicio < ?
AND s.hora_fin > ?
LIMIT 1
";
$stmt_conflicto_profesor_recurrente =
$conexion->prepare($sql_conflicto_profesor_recurrente);
$sql_insertar_recurrente = "
INSERT INTO sesiones (
id_actividad,
id_espacio,
id_profesor,
fecha,
hora_inicio,
hora_fin,
aforo,
estado,
observaciones
)
VALUES (?, ?, ?, ?, ?, ?, ?, 'programada', ?)
";
$stmt_insertar_recurrente =
$conexion->prepare($sql_insertar_recurrente);
$sql_insertar_profesores_recurrente = "
INSERT INTO sesiones_profesores (id_sesion, id_profesor)
VALUES (?, ?)
";
$stmt_insertar_profesores_recurrente =
$conexion->prepare($sql_insertar_profesores_recurrente);
$sesiones_recurrentes_creadas = 0;
foreach ($ocurrencias_recurrentes as $ocurrencia) {
$fecha_ocurrencia = $ocurrencia["fecha"];
$hora_inicio_ocurrencia = $ocurrencia["hora_inicio"];
$hora_fin_ocurrencia = $ocurrencia["hora_fin"];
$inicio_comprobacion_ocurrencia = DateTime::createFromFormat(
"Y-m-d H:i:s",
$fecha_ocurrencia . " " . $hora_inicio_ocurrencia
)->modify("-15 minutes")->format("H:i:s");
$fin_comprobacion_ocurrencia = DateTime::createFromFormat(
"Y-m-d H:i:s",
$fecha_ocurrencia . " " . $hora_fin_ocurrencia
)->modify("+15 minutes")->format("H:i:s");
$stmt_conflicto_espacio_recurrente->bind_param(
"siss",
$fecha_ocurrencia,
$id_espacio,
$fin_comprobacion_ocurrencia,
$inicio_comprobacion_ocurrencia
);
$stmt_conflicto_espacio_recurrente->execute();
$hay_conflicto_ocurrencia =
$stmt_conflicto_espacio_recurrente->get_result()->num_rows > 0;
if (!$hay_conflicto_ocurrencia) {
foreach ($profesores_seleccionados as $id_profesor_conflicto_recurrente) {
$stmt_conflicto_profesor_recurrente->bind_param(
"siss",
$fecha_ocurrencia,
$id_profesor_conflicto_recurrente,
$fin_comprobacion_ocurrencia,
$inicio_comprobacion_ocurrencia
);
$stmt_conflicto_profesor_recurrente->execute();
if ($stmt_conflicto_profesor_recurrente->get_result()->num_rows > 0) {
$hay_conflicto_ocurrencia = true;
break;
}
}
}
if ($hay_conflicto_ocurrencia) {
$sesiones_recurrentes_omitidas++;
continue;
}
$stmt_insertar_recurrente->bind_param(
"iiisssis",
$id_actividad,
$id_espacio,
$id_profesor_principal,
$fecha_ocurrencia,
$hora_inicio_ocurrencia,
$hora_fin_ocurrencia,
$aforo,
$observaciones
);
if (!$stmt_insertar_recurrente->execute()) {
$sesiones_recurrentes_omitidas++;
continue;
}
$id_sesion_recurrente_creada = $conexion->insert_id;
foreach ($profesores_seleccionados as $id_profesor_asignado_recurrente) {
$stmt_insertar_profesores_recurrente->bind_param(
"ii",
$id_sesion_recurrente_creada,
$id_profesor_asignado_recurrente
);
$stmt_insertar_profesores_recurrente->execute();
}
$sesiones_recurrentes_creadas++;
}
$stmt_conflicto_espacio_recurrente->close();
$stmt_conflicto_profesor_recurrente->close();
$stmt_insertar_recurrente->close();
$stmt_insertar_profesores_recurrente->close();
$conexion->commit();
$conexion->close();
header(
"Location: sesiones.php?mensaje=sesiones_recurrentes_creadas" .
"&creadas=" . $sesiones_recurrentes_creadas .
"&omitidas=" . $sesiones_recurrentes_omitidas
);
exit;
} catch (Throwable $error) {
$conexion->rollback();
$conexion->close();
echo "No se han podido guardar las sesiones.";
exit;
}
}
$conexion->begin_transaction();
try {
$sql_insertar = "
INSERT INTO sesiones (
id_actividad,
id_espacio,
id_profesor,
fecha,
hora_inicio,
hora_fin,
aforo,
estado,
observaciones
)
VALUES (?, ?, ?, ?, ?, ?, ?, 'programada', ?)
";
$stmt_insertar =
$conexion->prepare($sql_insertar);
$stmt_insertar->bind_param(
"iiisssis",
$id_actividad,
$id_espacio,
$id_profesor_principal,
$fecha,
$hora_inicio,
$hora_fin,
$aforo,
$observaciones
);
$stmt_insertar->execute();
$id_sesion_creada = $conexion->insert_id;
$stmt_insertar->close();

$sql_insertar_profesores = "
INSERT INTO sesiones_profesores (id_sesion, id_profesor)
VALUES (?, ?)
";
$stmt_insertar_profesores =
$conexion->prepare($sql_insertar_profesores);
foreach ($profesores_seleccionados as $id_profesor_asignado) {
$stmt_insertar_profesores->bind_param(
"ii",
$id_sesion_creada,
$id_profesor_asignado
);
$stmt_insertar_profesores->execute();
}
$stmt_insertar_profesores->close();

$conexion->commit();
$conexion->close();
header(
    "Location: sesiones.php?mensaje=sesion_creada"
);
exit;
} catch (Throwable $error) {
$conexion->rollback();
$conexion->close();
echo "No se ha podido guardar la sesión.";
exit;
}