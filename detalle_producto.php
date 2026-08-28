<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
$id_producto = filter_input(
INPUT_GET,
'id',
FILTER_VALIDATE_INT
);
if (!$id_producto || $id_producto < 1) {
http_response_code(400);
die(t('El identificador no es válido.'));
}
$sql_producto = "
SELECT id_producto, nombre, descripcion, categoria, precio, tallas, imagen
FROM productos
WHERE id_producto = ?
AND activo = 1
";
$stmt_producto = $conexion->prepare($sql_producto);
$stmt_producto->bind_param('i', $id_producto);
$stmt_producto->execute();
$producto = $stmt_producto->get_result()->fetch_assoc();
if (!$producto) {
http_response_code(404);
die(t('El producto no existe o no está disponible.'));
}
$sql_imagenes = "
SELECT imagen
FROM producto_imagenes
WHERE id_producto = ?
ORDER BY orden, id_imagen
";
$stmt_imagenes = $conexion->prepare($sql_imagenes);
$stmt_imagenes->bind_param('i', $id_producto);
$stmt_imagenes->execute();
$imagenes_producto = array_column(
$stmt_imagenes->get_result()->fetch_all(MYSQLI_ASSOC),
'imagen'
);
$galeria = [];
if (!empty($producto['imagen'])) {
$galeria[] = $producto['imagen'];
}
foreach ($imagenes_producto as $imagen_detalle) {
$galeria[] = $imagen_detalle;
}
$tallas_producto = [];
if (!empty($producto['tallas'])) {
$tallas_producto = array_filter(array_map('trim', explode(',', $producto['tallas'])));
}
$sql_sugerencias = "
SELECT id_producto, nombre, precio, imagen
FROM productos
WHERE categoria = ?
AND activo = 1
AND id_producto != ?
ORDER BY nombre
LIMIT 4
";
$stmt_sugerencias = $conexion->prepare($sql_sugerencias);
$stmt_sugerencias->bind_param('si', $producto['categoria'], $id_producto);
$stmt_sugerencias->execute();
$sugerencias = $stmt_sugerencias->get_result()->fetch_all(MYSQLI_ASSOC);
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
<?= escapar($producto['nombre']) ?> <?= t('| Sama Shala') ?>
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
<a class="enlace-volver" href="tienda.php">
← <?= t('Volver a la tienda') ?>
</a>
<div class="ficha-sesion">
<section class="informacion-sesion">
<?php if (!empty($galeria)): ?>
<img
class="imagen-detalle"
id="imagen-principal-producto"
src="imagenes/productos/<?= escapar($galeria[0]) ?>"
alt="<?= escapar($producto['nombre']) ?>"
>
<?php if (count($galeria) > 1): ?>
<div class="galeria-miniaturas">
<?php foreach ($galeria as $indice_imagen => $imagen_galeria): ?>
<img
class="miniatura-galeria <?= $indice_imagen === 0 ? 'activa' : '' ?>"
src="imagenes/productos/<?= escapar($imagen_galeria) ?>"
alt="<?= escapar($producto['nombre']) ?>"
>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php endif; ?>
<h1><?= escapar($producto['nombre']) ?></h1>
<p class="descripcion-destacada">
<?= nl2br(escapar($producto['descripcion'])) ?>
</p>
<p class="dato-destacado">
<?= t('Categoría:') ?> <?= escapar(texto_categoria_producto($producto['categoria'])) ?>
</p>
<?php if (!empty($tallas_producto)): ?>
<p>
<strong><?= t('Tallas disponibles:') ?></strong>
<?= escapar(implode(', ', $tallas_producto)) ?>
</p>
<?php endif; ?>
</section>
<aside class="panel-reserva">
<p class="precio-sesion">
<?= t('Precio') ?>
<strong><?= formatear_precio((float) $producto['precio']) ?></strong>
</p>
<a
class="boton boton-bloque"
href="comprar_producto.php?id=<?= (int) $producto['id_producto'] ?>"
>
<?= t('Comprar') ?>
</a>
</aside>
</div>
<?php if (!empty($sugerencias)): ?>
<section class="seccion">
<h2><?= t('También te puede interesar') ?></h2>
<div class="rejilla-actividades">
<?php foreach ($sugerencias as $sugerencia): ?>
<article class="tarjeta-actividad">
<?php if (!empty($sugerencia['imagen'])): ?>
<img
class="imagen-actividad"
src="imagenes/productos/<?= escapar($sugerencia['imagen']) ?>"
alt="<?= escapar($sugerencia['nombre']) ?>"
>
<?php else: ?>
<div class="imagen-sin-contenido">
<?= t('Sin imagen') ?>
</div>
<?php endif; ?>
<div class="contenido-tarjeta">
<h2><?= escapar($sugerencia['nombre']) ?></h2>
<p class="precio-sesion">
<strong><?= formatear_precio((float) $sugerencia['precio']) ?></strong>
</p>
<a
class="boton boton-bloque"
href="detalle_producto.php?id=<?= (int) $sugerencia['id_producto'] ?>"
>
<?= t('Comprar') ?>
</a>
</div>
</article>
<?php endforeach; ?>
</div>
</section>
<?php endif; ?>
</main>
<?php if (count($galeria) > 1): ?>
<script>
(function () {
var principal = document.getElementById('imagen-principal-producto');
var miniaturas = document.querySelectorAll('.miniatura-galeria');
if (!principal || !miniaturas.length) {
return;
}
miniaturas.forEach(function (miniatura) {
miniatura.addEventListener('click', function () {
principal.src = miniatura.src;
miniaturas.forEach(function (otra) {
otra.classList.remove('activa');
});
miniatura.classList.add('activa');
});
});
})();
</script>
<?php endif; ?>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
