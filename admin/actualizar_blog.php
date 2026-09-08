<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../funciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: blog.php');
exit;
}

$id_entrada = filter_var(
$_POST['id_entrada'] ?? '',
FILTER_VALIDATE_INT
);
if (!$id_entrada) {
header('Location: blog.php?error=no_encontrado');
exit;
}

$sql_actual = "SELECT portada FROM blog_entradas WHERE id_entrada = ?";
$stmt_actual = $conexion->prepare($sql_actual);
$stmt_actual->bind_param('i', $id_entrada);
$stmt_actual->execute();
$entrada_actual = $stmt_actual->get_result()->fetch_assoc();
$stmt_actual->close();
if (!$entrada_actual) {
header('Location: blog.php?error=no_encontrado');
exit;
}
$portada_actual = $entrada_actual['portada'];

$titulo = trim($_POST['titulo'] ?? '');
$contenido = trim($_POST['contenido'] ?? '');
$video_url = trim($_POST['video_url'] ?? '');
$activo = isset($_POST['activo']) ? 1 : 0;
$errores = [];
if ($titulo === '') {
$errores[] = 'El título es obligatorio.';
}
if (mb_strlen($titulo) > 200) {
$errores[] = 'El título es demasiado largo.';
}
if ($contenido === '') {
$errores[] = 'El contenido es obligatorio.';
}
if ($video_url !== '' && extraer_id_youtube($video_url) === null) {
$errores[] = 'La URL de YouTube no es válida.';
}
if ($errores) {
header("Location: editar_blog.php?id_entrada=$id_entrada&error=1");
exit;
}

$resultado_portada = procesar_imagen_subida(
'portada',
__DIR__ . '/../imagenes/blog',
'blog'
);
if (!$resultado_portada['ok']) {
header("Location: editar_blog.php?id_entrada=$id_entrada&error=1");
exit;
}
if ($resultado_portada['archivo'] !== null) {
if (
!empty($portada_actual) &&
file_exists(__DIR__ . '/../imagenes/blog/' . $portada_actual)
) {
unlink(__DIR__ . '/../imagenes/blog/' . $portada_actual);
}
$portada = $resultado_portada['archivo'];
} else {
$portada = $portada_actual;
}

$contenido_sanitizado = sanitizar_html_blog($contenido);
$video_url_guardada = $video_url !== '' ? $video_url : null;
$sql = "
UPDATE blog_entradas SET
titulo = ?,
portada = ?,
contenido = ?,
video_url = ?,
activo = ?
WHERE id_entrada = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param(
'ssssii',
$titulo,
$portada,
$contenido_sanitizado,
$video_url_guardada,
$activo,
$id_entrada
);
$stmt->execute();
header('Location: blog.php?mensaje=actualizado');
exit;
