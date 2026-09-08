<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../funciones.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: nuevo_blog.php');
exit;
}
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
header('Location: nuevo_blog.php?error=1');
exit;
}
$resultado_portada = procesar_imagen_subida(
'portada',
__DIR__ . '/../imagenes/blog',
'blog'
);
if (!$resultado_portada['ok'] || $resultado_portada['archivo'] === null) {
header('Location: nuevo_blog.php?error=1');
exit;
}
$portada = $resultado_portada['archivo'];
$contenido_sanitizado = sanitizar_html_blog($contenido);
$video_url_guardada = $video_url !== '' ? $video_url : null;
$sql = "
INSERT INTO blog_entradas (
titulo,
portada,
contenido,
video_url,
activo
)
VALUES (?, ?, ?, ?, ?)
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param(
'ssssi',
$titulo,
$portada,
$contenido_sanitizado,
$video_url_guardada,
$activo
);
$stmt->execute();
header('Location: blog.php?mensaje=creado');
exit;
