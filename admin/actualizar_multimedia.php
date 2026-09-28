<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../funciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: multimedia.php');
exit;
}
validarCsrf();

$id_multimedia = filter_var(
$_POST['id_multimedia'] ?? '',
FILTER_VALIDATE_INT
);
if (!$id_multimedia) {
header('Location: multimedia.php?error=no_encontrado');
exit;
}

$sql_actual = "SELECT id_multimedia FROM multimedia WHERE id_multimedia = ?";
$stmt_actual = $conexion->prepare($sql_actual);
$stmt_actual->bind_param('i', $id_multimedia);
$stmt_actual->execute();
$ficha_actual = $stmt_actual->get_result()->fetch_assoc();
$stmt_actual->close();
if (!$ficha_actual) {
header('Location: multimedia.php?error=no_encontrado');
exit;
}

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
header("Location: editar_multimedia.php?id_multimedia=$id_multimedia&error=1");
exit;
}

$url_youtube_guardada = $url_youtube !== '' ? $url_youtube : null;
$url_spotify_guardada = $url_spotify !== '' ? $url_spotify : null;
$sql = "
UPDATE multimedia SET
titulo = ?,
descripcion = ?,
url_youtube = ?,
url_spotify = ?,
activo = ?
WHERE id_multimedia = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param(
'ssssii',
$titulo,
$descripcion,
$url_youtube_guardada,
$url_spotify_guardada,
$activo,
$id_multimedia
);
$stmt->execute();
header('Location: multimedia.php?mensaje=actualizado');
exit;
