<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
$id_entrada = filter_input(
INPUT_GET,
'id',
FILTER_VALIDATE_INT
);
$entrada = null;
if ($id_entrada) {
$sql = "
SELECT id_entrada, titulo, portada, contenido, video_url
FROM blog_entradas
WHERE id_entrada = ?
AND activo = 1
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_entrada);
$stmt->execute();
$entrada = $stmt->get_result()->fetch_assoc();
$stmt->close();
}
$id_youtube = $entrada ? extraer_id_youtube($entrada['video_url'] ?? null) : null;
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
<?= $entrada ? escapar($entrada['titulo']) . ' ' . t('| Sama Shala') : t('Entrada no encontrada | Sama Shala') ?>
</title>
<link rel="stylesheet" href="<?= urlEstilos() ?>">
<link rel="icon" type="image/png" sizes="32x32" href="imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu.php'; ?>
<main class="contenedor seccion">
<a class="enlace-volver" href="blog.php">
← <?= t('Volver al blog') ?>
</a>
<?php if (!$entrada): ?>
<div class="mensaje mensaje-error">
<?= t('La entrada no existe o no está disponible.') ?>
</div>
<?php else: ?>
<article class="entrada-blog">
<?php if (!empty($entrada['portada'])): ?>
<img
class="imagen-detalle"
src="imagenes/blog/<?= escapar($entrada['portada']) ?>"
alt="<?= escapar($entrada['titulo']) ?>"
>
<?php endif; ?>
<h1><?= escapar($entrada['titulo']) ?></h1>
<?php if ($id_youtube): ?>
<div class="video-blog">
<iframe
src="https://www.youtube.com/embed/<?= escapar($id_youtube) ?>"
title="<?= escapar($entrada['titulo']) ?>"
allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
allowfullscreen
></iframe>
</div>
<?php endif; ?>
<div class="contenido-blog">
<?= $entrada['contenido'] ?>
</div>
</article>
<?php endif; ?>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
