<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: nueva_actividad.php');
exit;
}
$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');
$tipo = trim($_POST['tipo'] ?? '');
$nivel = trim($_POST['nivel'] ?? '');
$duracion = $_POST['duracion_minutos'] ?? '';
$imagen = trim($_POST['imagen'] ?? '');
$activa = isset($_POST['activa']) ? 1 : 0;
$categorias_validas = [
'Deporte',
'Bienestar',
'Cultura',
'Formación',
'Ocio'
];
$niveles_validos = [
'inicial',
'intermedio',
'avanzado',
'todos'
];
$tipos_validos = [
'clase',
'evento',
'terapia'
];
$errores = [];
if ($nombre === '') {
$errores[] = 'El nombre es obligatorio.';
}
if (mb_strlen($nombre) > 120) {
$errores[] = 'El nombre es demasiado largo.';
}
if ($descripcion === '') {
$errores[] = 'La descripción es obligatoria.';
}
if (!in_array(
$categoria,
$categorias_validas,
true
)) {
$errores[] = 'La categoría no es válida.';
}
if (!in_array(
$tipo,
$tipos_validos,
true
)) {
$errores[] = 'El tipo no es válido.';
}
if (!in_array(
$nivel,
$niveles_validos,
true
)) {
$errores[] = 'El nivel no es válido.';
}
$duracion = filter_var(
    $duracion,
FILTER_VALIDATE_INT,
[
'options' => [
'min_range' => 15,
'max_range' => 480
]
]
);
if ($duracion === false) {
$errores[] =
'La duración debe estar entre 15 y 480 minutos.';
}
if (mb_strlen($imagen) > 255) {
$errores[] =
'El nombre de la imagen es demasiado largo.';
}
if ($errores) {
header('Location: nueva_actividad.php?error=1');
exit;
}
$sql = "
INSERT INTO actividades (
nombre,
descripcion,
categoria,
tipo,
nivel,
duracion_minutos,
imagen,
activa
)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param(
'sssssisi',
$nombre,
$descripcion,
$categoria,
$tipo,
$nivel,
$duracion,
$imagen,
$activa
);
$stmt->execute();
$id_actividad = $conexion->insert_id;
$sesiones_creadas = 0;
$sesiones_omitidas = 0;
$es_regular = isset($_POST['es_regular']);
if ($es_regular) {
$id_profesor_regular = filter_input(
INPUT_POST,
'id_profesor_regular',
FILTER_VALIDATE_INT
);
$id_espacio_regular = filter_input(
INPUT_POST,
'id_espacio_regular',
FILTER_VALIDATE_INT
);
$hora_inicio_regular = trim(
$_POST['hora_inicio_regular'] ?? ''
);
$aforo_regular = filter_input(
INPUT_POST,
'aforo_regular',
FILTER_VALIDATE_INT
);
$fechas_regulares = $_POST['fechas_regulares'] ?? [];
if (
$id_profesor_regular &&
$id_espacio_regular &&
$hora_inicio_regular !== '' &&
hora_valida($hora_inicio_regular) &&
$aforo_regular &&
is_array($fechas_regulares)
) {
$sql_conflicto = "
SELECT id_sesion
FROM sesiones
WHERE fecha = ?
AND estado <> 'cancelada'
AND hora_inicio < ?
AND hora_fin > ?
AND (id_espacio = ? OR id_profesor = ?)
LIMIT 1
";
$stmt_conflicto = $conexion->prepare($sql_conflicto);
$sql_insertar_sesion = "
INSERT INTO sesiones (
id_actividad,
id_espacio,
id_profesor,
fecha,
hora_inicio,
hora_fin,
aforo,
estado
)
VALUES (?, ?, ?, ?, ?, ?, ?, 'programada')
";
$stmt_insertar_sesion =
$conexion->prepare($sql_insertar_sesion);
foreach ($fechas_regulares as $fecha_regular) {
$fecha_regular = trim($fecha_regular);
if (!fecha_valida($fecha_regular)) {
$sesiones_omitidas++;
continue;
}
$inicio_regular = DateTime::createFromFormat(
'Y-m-d H:i',
$fecha_regular . ' ' . $hora_inicio_regular
);
$fin_regular = clone $inicio_regular;
$fin_regular->modify("+{$duracion} minutes");
$hora_inicio_sesion = $inicio_regular->format('H:i:s');
$hora_fin_sesion = $fin_regular->format('H:i:s');
$stmt_conflicto->bind_param(
'sssii',
$fecha_regular,
$hora_fin_sesion,
$hora_inicio_sesion,
$id_espacio_regular,
$id_profesor_regular
);
$stmt_conflicto->execute();
$resultado_conflicto = $stmt_conflicto->get_result();
if ($resultado_conflicto->num_rows > 0) {
$sesiones_omitidas++;
continue;
}
$stmt_insertar_sesion->bind_param(
'iiisssi',
$id_actividad,
$id_espacio_regular,
$id_profesor_regular,
$fecha_regular,
$hora_inicio_sesion,
$hora_fin_sesion,
$aforo_regular
);
if ($stmt_insertar_sesion->execute()) {
$sesiones_creadas++;
} else {
$sesiones_omitidas++;
}
}
$stmt_conflicto->close();
$stmt_insertar_sesion->close();
}
}
header(
'Location: actividades.php?mensaje=creada' .
'&sesiones_creadas=' . $sesiones_creadas .
'&sesiones_omitidas=' . $sesiones_omitidas
);
exit;