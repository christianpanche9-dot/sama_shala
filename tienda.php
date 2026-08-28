<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
$sql = "
SELECT id_producto, nombre, categoria, precio, imagen
FROM productos
WHERE activo = 1
ORDER BY nombre
";
$resultado = $conexion->query($sql);
$productos_por_categoria = [
'bija' => [],
'ayurveda' => [],
'fotografia' => [],
'angyoga' => []
];
while ($producto = $resultado->fetch_assoc()) {
$productos_por_categoria[$producto['categoria']][] = $producto;
}
$subtitulos_categoria = [
'bija' => t('Panadería artesanal'),
'ayurveda' => t('Medicina ayurvédica'),
'fotografia' => t('Sesiones fotográficas y fotografías de la naturaleza ecuatoriana'),
'angyoga' => t('Ropa de yoga estilizada')
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title><?= t('Tienda | Sama Shala') ?></title>
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
<div>
<p class="etiqueta"><?= t('Tienda') ?></p>
<h1><?= t('Tienda') ?></h1>
<p>
<?= t('Descubre Bija, Ayurveda, Fotografía y Angyoga: productos y servicios pensados en tu bienestar.') ?>
</p>
</div>
</div>
<?php foreach ($productos_por_categoria as $categoria_actual => $productos_categoria): ?>
<?php if (empty($productos_categoria)): ?>
<?php continue; ?>
<?php endif; ?>
<section class="seccion-tipo-actividad seccion-categoria-<?= escapar($categoria_actual) ?>">
<h3><?= escapar(texto_categoria_producto($categoria_actual)) ?></h3>
<p class="subtitulo-categoria-tienda">
<?= escapar($subtitulos_categoria[$categoria_actual]) ?>
</p>
<div class="rejilla-actividades">
<?php foreach ($productos_categoria as $producto): ?>
<article class="tarjeta-actividad">
<?php if (!empty($producto['imagen'])): ?>
<img
class="imagen-actividad"
src="imagenes/productos/<?= escapar($producto['imagen']) ?>"
alt="<?= escapar($producto['nombre']) ?>"
>
<?php else: ?>
<div class="imagen-sin-contenido">
<?= t('Sin imagen') ?>
</div>
<?php endif; ?>
<div class="contenido-tarjeta">
<h2><?= escapar($producto['nombre']) ?></h2>
<p class="precio-sesion">
<strong><?= formatear_precio((float) $producto['precio']) ?></strong>
</p>
<a
class="boton boton-bloque"
href="detalle_producto.php?id=<?= (int) $producto['id_producto'] ?>"
>
<?= t('Comprar') ?>
</a>
</div>
</article>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
<?php if (array_sum(array_map('count', $productos_por_categoria)) === 0): ?>
<div class="mensaje mensaje-aviso">
<?= t('Todavía no hay productos disponibles en la tienda.') ?>
</div>
<?php endif; ?>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
