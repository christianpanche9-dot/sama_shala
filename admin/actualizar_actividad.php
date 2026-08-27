<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: actividades.php');
exit;
}

$id_actividad = filter_input(
INPUT_POST,
'id_actividad',
FILTER_VALIDATE_INT
);
if (!$id_actividad) {
header('Location: actividades.php?error=no_encontrada');
exit;
}

$sql_actual = "
SELECT imagen, imagen_banner_top
FROM actividades
WHERE id_actividad = ?
";
$stmt_actual = $conexion->prepare($sql_actual);
$stmt_actual->bind_param('i', $id_actividad);
$stmt_actual->execute();
$actividad_actual = $stmt_actual->get_result()->fetch_assoc();
$stmt_actual->close();
if (!$actividad_actual) {
header('Location: actividades.php?error=no_encontrada');
exit;
}
$imagen_actual = $actividad_actual['imagen'];
$banner_actual = $actividad_actual['imagen_banner_top'];

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
header("Location: editar_actividad.php?id_actividad=$id_actividad&error=1");
exit;
}
$resultado_imagen = procesar_imagen_subida(
'imagen',
__DIR__ . '/../imagenes/actividades',
'actividad'
);
if (!$resultado_imagen['ok']) {
header("Location: editar_actividad.php?id_actividad=$id_actividad&error=1");
exit;
}
if ($resultado_imagen['archivo'] !== null) {
if (
!empty($imagen_actual) &&
file_exists(__DIR__ . '/../imagenes/actividades/' . $imagen_actual)
) {
unlink(__DIR__ . '/../imagenes/actividades/' . $imagen_actual);
}
$imagen = $resultado_imagen['archivo'];
} else {
$imagen = $imagen_actual;
}

function eliminar_banner_top(?string $archivo): void
{
if (
!empty($archivo) &&
file_exists(__DIR__ . '/../imagenes/actividades/' . $archivo)
) {
unlink(__DIR__ . '/../imagenes/actividades/' . $archivo);
}
}

if ($posicion_top === 1) {
$resultado_banner = procesar_imagen_subida(
'imagen_banner_top',
__DIR__ . '/../imagenes/actividades',
'banner',
1920
);
if (!$resultado_banner['ok']) {
header("Location: editar_actividad.php?id_actividad=$id_actividad&error=1");
exit;
}
if ($resultado_banner['archivo'] !== null) {
eliminar_banner_top($banner_actual);
$imagen_banner_top = $resultado_banner['archivo'];
} else {
$imagen_banner_top = $banner_actual;
}
} else {
eliminar_banner_top($banner_actual);
$imagen_banner_top = null;
}

if ($posicion_top !== null) {
$sql_anterior = "
SELECT id_actividad, imagen_banner_top
FROM actividades
WHERE posicion_top = ? AND id_actividad != ?
";
$stmt_anterior = $conexion->prepare($sql_anterior);
$stmt_anterior->bind_param('ii', $posicion_top, $id_actividad);
$stmt_anterior->execute();
$anterior = $stmt_anterior->get_result()->fetch_assoc();
$stmt_anterior->close();
if ($anterior) {
eliminar_banner_top($anterior['imagen_banner_top']);
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
UPDATE actividades SET
nombre = ?,
descripcion = ?,
categoria = ?,
tipo = ?,
precio = ?,
nivel = ?,
duracion_minutos = ?,
imagen = ?,
activa = ?,
es_top = ?,
posicion_top = ?,
imagen_banner_top = ?
WHERE id_actividad = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param(
'ssssdsisiiisi',
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
$imagen_banner_top,
$id_actividad
);
$stmt->execute();
header('Location: actividades.php?mensaje=actualizada');
exit;
