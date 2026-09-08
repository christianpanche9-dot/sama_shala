<?php
require_once "seguridad_admin.php";
require_once "../conexion.php";
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
Nueva entrada | Sama Shala
</title>
<link rel="stylesheet" href="<?= urlEstilos('../') ?>">
<link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
<link rel="icon" type="image/png" sizes="32x32" href="../imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="../imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="../imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu_admin.php'; ?>
<main class="contenedor seccion">
<a class="enlace-volver" href="blog.php">
← Volver al blog
</a>
<div class="encabezado-pagina">
<p class="etiqueta">
Blog
</p>
<h1>Nueva entrada</h1>
</div>
<?php if (isset($_GET['error'])): ?>
<div class="mensaje mensaje-error">
No se ha podido guardar la entrada.
Revisa los datos del formulario.
</div>
<?php endif; ?>
<form
class="formulario-admin"
action="guardar_blog.php"
method="post"
enctype="multipart/form-data"
id="formulario-blog"
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
<label for="portada">
Imagen de portada
</label>
<input
type="file"
id="portada"
name="portada"
accept="image/jpeg,image/png,image/webp"
required
>
<small>
Es la imagen que se muestra en la tarjeta del listado y arriba de la entrada.
JPG, PNG o WEBP, máximo 5 MB.
</small>
</div>
<div class="campo campo-completo">
<label for="video_url">
Video de YouTube (opcional)
</label>
<input
type="url"
id="video_url"
name="video_url"
placeholder="https://www.youtube.com/watch?v=..."
>
<small>
Pega el link del video de YouTube que quieras mostrar en la entrada.
</small>
</div>
<div class="campo campo-completo">
<label for="editor-contenido">
Contenido
</label>
<div id="editor-contenido"></div>
<textarea id="contenido" name="contenido" hidden required></textarea>
</div>
<div class="campo-checkbox campo-completo">
<label>
<input
type="checkbox"
name="activo"
value="1"
checked
>
Publicar la entrada
</label>
</div>
<div class="campo-completo">
<button class="boton" type="submit">
Guardar entrada
</button>
</div>
</form>
</main>
<script src="https://cdn.quilljs.com/1.3.7/quill.js"></script>
<script src="editor_blog.js"></script>
</body>
</html>
