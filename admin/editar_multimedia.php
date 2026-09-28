<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../funciones.php';

$id_multimedia = filter_input(
INPUT_GET,
'id_multimedia',
FILTER_VALIDATE_INT
);
if (!$id_multimedia) {
header('Location: multimedia.php');
exit;
}

$sql = "
SELECT id_multimedia, titulo, descripcion, url_youtube, url_spotify, activo
FROM multimedia
WHERE id_multimedia = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_multimedia);
$stmt->execute();
$ficha = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$ficha) {
header('Location: multimedia.php?error=no_encontrado');
exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title>
Editar ficha | Sama Shala
</title>
<link rel="stylesheet" href="<?= urlEstilos('../') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="../imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="../imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="../imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu_admin.php'; ?>
<main class="contenedor seccion">
<a class="enlace-volver" href="multimedia.php">
← Volver a multimedia
</a>
<div class="encabezado-pagina">
<p class="etiqueta">
Multimedia
</p>
<h1>Editar ficha</h1>
</div>
<?php if (isset($_GET['error'])): ?>
<div class="mensaje mensaje-error">
No se ha podido actualizar la ficha.
Revisa los datos del formulario.
</div>
<?php endif; ?>
<form
class="formulario-admin"
action="actualizar_multimedia.php"
method="post"
>
<input
type="hidden"
name="id_multimedia"
value="<?= (int) $ficha['id_multimedia'] ?>"
>
<input
type="hidden"
name="csrf_token"
value="<?= escapar(tokenCsrf()) ?>"
>
<div class="campo campo-completo">
<label for="titulo">
Título
</label>
<input
type="text"
id="titulo"
name="titulo"
maxlength="200"
value="<?= escapar($ficha['titulo']) ?>"
required
>
</div>
<div class="campo campo-completo">
<label for="descripcion">
Descripción breve
</label>
<textarea
id="descripcion"
name="descripcion"
rows="3"
maxlength="300"
required
><?= escapar($ficha['descripcion']) ?></textarea>
</div>
<div class="campo campo-completo">
<label for="url_youtube">
Enlace de YouTube (opcional)
</label>
<input
type="url"
id="url_youtube"
name="url_youtube"
value="<?= escapar($ficha['url_youtube'] ?? '') ?>"
placeholder="https://www.youtube.com/watch?v=..."
>
</div>
<div class="campo campo-completo">
<label for="url_spotify">
Enlace de Spotify (opcional)
</label>
<input
type="url"
id="url_spotify"
name="url_spotify"
value="<?= escapar($ficha['url_spotify'] ?? '') ?>"
placeholder="https://open.spotify.com/track/..."
>
<small>
Puede ser un track, álbum, playlist, episodio o show de Spotify.
</small>
</div>
<p class="ayuda">
Debes indicar al menos un enlace, de YouTube o de Spotify.
</p>
<div class="campo-checkbox campo-completo">
<label>
<input
type="checkbox"
name="activo"
value="1"
<?= (int) $ficha['activo'] === 1 ? 'checked' : '' ?>
>
Publicar la ficha
</label>
</div>
<div class="campo-completo">
<button class="boton" type="submit">
Guardar cambios
</button>
</div>
</form>
</main>
</body>
</html>
