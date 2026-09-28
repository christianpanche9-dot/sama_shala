<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../funciones.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: nuevo_multimedia.php');
exit;
}
validarCsrf();
$titulo = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$url_youtube = trim($_POST['url_youtube'] ?? '');
$url_spotify = trim($_POST['url_spotify'] ?? '');
$activo = isset($_POST['activo']) ? 1 : 0;
$errores = [];
if ($titulo === '') {
$errores[] = 'El título es obligatorio.';
}
if (mb_strlen($titulo) > 200) {
$errores[] = 'El título es demasiado largo.';
}
if ($descripcion === '') {
$errores[] = 'La descripción es obligatoria.';
}
if (mb_strlen($descripcion) > 300) {
$errores[] = 'La descripción es demasiado larga.';
}
if ($url_youtube !== '' && extraer_id_youtube($url_youtube) === null) {
$errores[] = 'La URL de YouTube no es válida.';
}
if ($url_spotify !== '' && datos_embed_spotify($url_spotify) === null) {
$errores[] = 'La URL de Spotify no es válida.';
}
if ($url_youtube === '' && $url_spotify === '') {
$errores[] = 'Debes indicar al menos un enlace, de YouTube o de Spotify.';
}
if ($errores) {
header('Location: nuevo_multimedia.php?error=1');
exit;
}
$url_youtube_guardada = $url_youtube !== '' ? $url_youtube : null;
$url_spotify_guardada = $url_spotify !== '' ? $url_spotify : null;
$sql = "
INSERT INTO multimedia (
titulo,
descripcion,
url_youtube,
url_spotify,
activo
)
VALUES (?, ?, ?, ?, ?)
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param(
'ssssi',
$titulo,
$descripcion,
$url_youtube_guardada,
$url_spotify_guardada,
$activo
);
$stmt->execute();
header('Location: multimedia.php?mensaje=creado');
exit;
