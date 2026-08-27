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
$precio = trim($_POST['precio'] ?? '');
$nivel = trim($_POST['nivel'] ?? '');
$duracion = $_POST['duracion_minutos'] ?? '';
$activa = isset($_POST['activa']) ? 1 : 0;
$es_top = isset($_POST['es_top']) ? 1 : 0;
$posicion_top = null;
$categorias_validas = [
'Yoga',
'Meditación',
'Sound Healing',
'Ayurveda y terapias corporales',
'Arte y bienestar',
'Comunidad',
'Retiros y experiencias',
'Formación'
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
'terapia',
'taller'
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
if ($tipo === 'clase') {
$precio = null;
} else {
$precio = filter_var(
$precio,
FILTER_VALIDATE_FLOAT,
[
'options' => [
'min_range' => 0.01
]
]
);
if ($precio === false) {
$errores[] =
'El precio es obligatorio para eventos y terapias, y debe ser mayor que 0.';
}
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
if ($es_top) {
$posicion_top = filter_var(
$_POST['posicion_top'] ?? '',
FILTER_VALIDATE_INT,
[
'options' => [
'min_range' => 1,
'max_range' => 3
]
]
);
if ($posicion_top === false) {
$errores[] =
'La posición en el top 3 debe ser 1, 2 o 3.';
$posicion_top = null;
}
}
if ($errores) {
header('Location: nueva_actividad.php?error=1');
exit;
}
$resultado_imagen = procesar_imagen_subida(
'imagen',
__DIR__ . '/../imagenes/actividades',
'actividad'
);
if (!$resultado_imagen['ok']) {
header('Location: nueva_actividad.php?error=1');
exit;
}
$imagen = $resultado_imagen['archivo'] ?? '';
$imagen_banner_top = null;
if ($posicion_top === 1) {
$resultado_banner = procesar_imagen_subida(
'imagen_banner_top',
__DIR__ . '/../imagenes/actividades',
'banner',
1920
);
if (!$resultado_banner['ok']) {
header('Location: nueva_actividad.php?error=1');
exit;
}
$imagen_banner_top = $resultado_banner['archivo'];
}
if ($posicion_top !== null) {
$sql_anterior = "
SELECT id_actividad, imagen_banner_top
FROM actividades
WHERE posicion_top = ?
";
$stmt_anterior = $conexion->prepare($sql_anterior);
$stmt_anterior->bind_param('i', $posicion_top);
$stmt_anterior->execute();
$anterior = $stmt_anterior->get_result()->fetch_assoc();
$stmt_anterior->close();
if ($anterior) {
if (
!empty($anterior['imagen_banner_top']) &&
file_exists(
__DIR__ . '/../imagenes/actividades/' .
$anterior['imagen_banner_top']
)
) {
unlink(
__DIR__ . '/../imagenes/actividades/' .
$anterior['imagen_banner_top']
);
}
$sql_liberar = "
UPDATE actividades SET
es_top = 0,
posicion_top = NULL,
imagen_banner_top = NULL
WHERE id_actividad = ?
";
$stmt_liberar = $conexion->prepare($sql_liberar);
$stmt_liberar->bind_param('i', $anterior['id_actividad']);
$stmt_liberar->execute();
$stmt_liberar->close();
}
}
$sql = "
INSERT INTO actividades (
nombre,
descripcion,
categoria,
tipo,
precio,
nivel,
duracion_minutos,
imagen,
activa,
es_top,
posicion_top,
imagen_banner_top
)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param(
'ssssdsisiiis',
$nombre,
$descripcion,
$categoria,
$tipo,
$precio,
$nivel,
$duracion,
$imagen,
$activa,
$es_top,
$posicion_top,
$imagen_banner_top
);
$stmt->execute();
$id_actividad = $conexion->insert_id;
$sesiones_creadas = 0;
$sesiones_omitidas = 0;
$es_regular = isset($_POST['es_regular']);
if ($es_regular) {
$id_profesor_regular = filter_var(
$_POST['id_profesor_regular'] ?? '',
FILTER_VALIDATE_INT
);
$id_espacio_regular = filter_var(
$_POST['id_espacio_regular'] ?? '',
FILTER_VALIDATE_INT
);
$hora_inicio_regular = trim(
$_POST['hora_inicio_regular'] ?? ''
);
$aforo_regular = filter_var(
$_POST['aforo_regular'] ?? '',
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
$sql_insertar_sesion_profesor = "
INSERT INTO sesiones_profesores (id_sesion, id_profesor)
VALUES (?, ?)
";
$stmt_insertar_sesion_profesor =
$conexion->prepare($sql_insertar_sesion_profesor);
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
$id_sesion_regular = $conexion->insert_id;
$stmt_insertar_sesion_profesor->bind_param(
'ii',
$id_sesion_regular,
$id_profesor_regular
);
$stmt_insertar_sesion_profesor->execute();
$sesiones_creadas++;
} else {
$sesiones_omitidas++;
}
}
$stmt_conflicto->close();
$stmt_insertar_sesion->close();
$stmt_insertar_sesion_profesor->close();
}
}
header(
'Location: actividades.php?mensaje=creada' .
'&sesiones_creadas=' . $sesiones_creadas .
'&sesiones_omitidas=' . $sesiones_omitidas
);
exit;