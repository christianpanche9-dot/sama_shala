<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../funciones.php';

$id_entrada = filter_input(
INPUT_GET,
'id_entrada',
FILTER_VALIDATE_INT
);
if (!$id_entrada) {
header('Location: blog.php');
exit;
}

$sql = "
SELECT id_entrada, titulo, portada, contenido, video_url, activo
FROM blog_entradas
WHERE id_entrada = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_entrada);
$stmt->execute();
$entrada = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$entrada) {
header('Location: blog.php?error=no_encontrado');
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
Editar entrada | Sama Shala
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
<h1>Editar entrada</h1>
</div>
<?php if (isset($_GET['error'])): ?>
<div class="mensaje mensaje-error">
No se ha podido actualizar la entrada.
Revisa los datos del formulario.
</div>
<?php endif; ?>
<form
class="formulario-admin"
action="actualizar_blog.php"
method="post"
enctype="multipart/form-data"
id="formulario-blog"
>
<input
type="hidden"
name="id_entrada"
value="<?= (int) $entrada['id_entrada'] ?>"
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
value="<?= escapar($entrada['titulo']) ?>"
required
>
</div>
<div class="campo campo-completo">
<label for="portada">
Imagen de portada
</label>
<?php if (!empty($entrada['portada'])): ?>
<img
class="miniatura-imagen-actual"
src="../imagenes/blog/<?= escapar($entrada['portada']) ?>"
alt=""
>
<?php endif; ?>
<input
type="file"
id="portada"
name="portada"
accept="image/jpeg,image/png,image/webp"
>
<small>
Deja este campo vacío para mantener la portada actual.
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
value="<?= escapar($entrada['video_url'] ?? '') ?>"
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
<textarea id="contenido" name="contenido" hidden><?= $entrada['contenido'] ?></textarea>
</div>
<div class="campo-checkbox campo-completo">
<label>
<input
type="checkbox"
name="activo"
value="1"
<?= (int) $entrada['activo'] === 1 ? 'checked' : '' ?>
>
Publicar la entrada
</label>
</div>
<div class="campo-completo">
<button class="boton" type="submit">
Guardar cambios
</button>
</div>
</form>
</main>
<script src="https://cdn.quilljs.com/1.3.7/quill.js"></script>
<script src="editor_blog.js"></script>
</body>
</html>
