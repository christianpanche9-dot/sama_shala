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

$sql_actual = "SELECT imagen FROM actividades WHERE id_actividad = ?";
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

$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');
$tipo = trim($_POST['tipo'] ?? '');
$nivel = trim($_POST['nivel'] ?? '');
$duracion = $_POST['duracion_minutos'] ?? '';
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
$sql = "
UPDATE actividades SET
nombre = ?,
descripcion = ?,
categoria = ?,
tipo = ?,
nivel = ?,
duracion_minutos = ?,
imagen = ?,
activa = ?
WHERE id_actividad = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param(
'sssssisii',
$nombre,
$descripcion,
$categoria,
$tipo,
$nivel,
$duracion,
$imagen,
$activa,
$id_actividad
);
$stmt->execute();
header('Location: actividades.php?mensaje=actualizada');
exit;
