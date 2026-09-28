<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../funciones.php';
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
Nueva ficha | Sama Shala
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
<h1>Nueva ficha</h1>
</div>
<?php if (isset($_GET['error'])): ?>
<div class="mensaje mensaje-error">
No se ha podido guardar la ficha.
Revisa los datos del formulario.
</div>
<?php endif; ?>
<form
class="formulario-admin"
action="guardar_multimedia.php"
method="post"
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
></textarea>
</div>
<div class="campo campo-completo">
<label for="url_youtube">
Enlace de YouTube (opcional)
</label>
<input
type="url"
id="url_youtube"
name="url_youtube"
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
checked
>
Publicar la ficha
</label>
</div>
<div class="campo-completo">
<button class="boton" type="submit">
Guardar ficha
</button>
</div>
</form>
</main>
</body>
</html>
