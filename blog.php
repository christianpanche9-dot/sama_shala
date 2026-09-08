<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
$sql = "
SELECT id_entrada, titulo, portada, contenido, fecha_creacion
FROM blog_entradas
WHERE activo = 1
ORDER BY fecha_creacion DESC
";
$resultado = $conexion->query($sql);
$entradas = $resultado->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title><?= t('Blog | Sama Shala') ?></title>
<link rel="stylesheet" href="<?= urlEstilos() ?>">
<link rel="icon" type="image/png" sizes="32x32" href="imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu.php'; ?>
<main class="contenedor seccion">
<div class="encabezado-pagina">
<p class="etiqueta"><?= t('Blog') ?></p>
<h1><?= t('Nuestro blog') ?></h1>
</div>
<?php if (empty($entradas)): ?>
<p><?= t('Todavía no hay entradas publicadas.') ?></p>
<?php else: ?>
<div class="rejilla-actividades">
<?php foreach ($entradas as $entrada): ?>
<article class="tarjeta-actividad">
<?php if (!empty($entrada['portada'])): ?>
<img
class="imagen-actividad"
src="imagenes/blog/<?= escapar($entrada['portada']) ?>"
alt="<?= escapar($entrada['titulo']) ?>"
>
<?php else: ?>
<div class="imagen-sin-contenido">
<?= t('Sin imagen') ?>
</div>
<?php endif; ?>
<div class="contenido-tarjeta">
<h2><?= escapar($entrada['titulo']) ?></h2>
<p class="dato-destacado">
<?= escapar(formatear_fecha(substr($entrada['fecha_creacion'], 0, 10))) ?>
</p>
<p>
<?= escapar(mb_substr(trim(strip_tags($entrada['contenido'])), 0, 150)) ?>…
</p>
<a
class="boton boton-bloque"
href="blog_detalle.php?id=<?= (int) $entrada['id_entrada'] ?>"
>
<?= t('Leer entrada') ?>
</a>
</div>
</article>
<?php endforeach; ?>
</div>
<?php endif; ?>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
