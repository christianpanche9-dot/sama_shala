<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
$sql = "
SELECT id_producto, nombre, categoria, precio, tallas, imagen
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
$carrito = $_SESSION['carrito'] ?? [];
$detalle_carrito = obtenerCarritoDetallado($conexion, $carrito);
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
<?= t('Somos una tienda con alma, donde el bienestar se vive de cuatro formas: el aroma del pan artesanal de Bija, la sabiduría ancestral de la medicina ayurvédica, la belleza capturada de la naturaleza ecuatoriana en fotografía, y el movimiento consciente con la ropa de yoga de Angyoga.') ?>
</p>
<?php if (array_sum(array_map('count', $productos_por_categoria)) > 0): ?>
<nav class="enlaces-categorias-tienda">
<?php foreach ($productos_por_categoria as $categoria_enlace => $productos_categoria_enlace): ?>
<?php if (empty($productos_categoria_enlace)): ?>
<?php continue; ?>
<?php endif; ?>
<a
class="enlace-categoria-<?= escapar($categoria_enlace) ?>"
href="#categoria-<?= escapar($categoria_enlace) ?>"
>
<?= escapar(texto_categoria_producto($categoria_enlace)) ?>
</a>
<?php endforeach; ?>
</nav>
<?php endif; ?>
</div>
</div>
<div class="diseno-tienda">
<div class="columna-productos-tienda">
<?php foreach ($productos_por_categoria as $categoria_actual => $productos_categoria): ?>
<?php if (empty($productos_categoria)): ?>
<?php continue; ?>
<?php endif; ?>
<section
class="seccion-tipo-actividad seccion-categoria-<?= escapar($categoria_actual) ?>"
id="categoria-<?= escapar($categoria_actual) ?>"
>
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
class="boton boton-secundario boton-bloque"
href="detalle_producto.php?id=<?= (int) $producto['id_producto'] ?>"
>
<?= t('Ver detalle del producto') ?>
</a>
<?php if (empty($producto['tallas'])): ?>
<form
action="carrito_agregar.php"
method="post"
class="formulario-agregar-carrito"
>
<input type="hidden" name="id_producto" value="<?= (int) $producto['id_producto'] ?>">
<input type="hidden" name="volver" value="tienda.php">
<label>
<?= t('Cantidad') ?>
<input type="number" name="cantidad" value="1" min="1" max="99">
</label>
<button type="submit" class="boton boton-bloque">
<?= t('Agregar al carrito') ?>
</button>
</form>
<?php endif; ?>
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
</div>
<aside class="panel-reserva panel-carrito" id="carrito">
<h2><?= t('Carrito') ?></h2>
<?php if (empty($detalle_carrito['items'])): ?>
<p><?= t('Tu carrito está vacío.') ?></p>
<?php else: ?>
<div class="lista-carrito">
<?php foreach ($detalle_carrito['items'] as $item): ?>
<div class="fila-carrito">
<div class="datos-fila-carrito">
<strong><?= escapar($item['nombre']) ?></strong>
<?php if (!empty($item['talla'])): ?>
<span><?= t('Talla:') ?> <?= escapar($item['talla']) ?></span>
<?php endif; ?>
<span><?= t('Cantidad:') ?> <?= (int) $item['cantidad'] ?></span>
</div>
<div class="precio-fila-carrito">
<?= formatear_precio($item['subtotal']) ?>
</div>
<form action="carrito_quitar.php" method="post">
<input type="hidden" name="clave" value="<?= escapar($item['clave']) ?>">
<input type="hidden" name="volver" value="tienda.php">
<button type="submit" class="enlace-quitar-carrito">
<?= t('Quitar') ?>
</button>
</form>
</div>
<?php endforeach; ?>
</div>
<p class="total-carrito">
<?= t('Total') ?>
<strong><?= formatear_precio($detalle_carrito['total']) ?></strong>
</p>
<a class="boton boton-bloque" href="comprar_producto.php">
<?= t('Pagar') ?>
</a>
<?php endif; ?>
</aside>
</div>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
