<?php
require_once __DIR__ . '/seguridad.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
$carrito = $_SESSION['carrito'] ?? [];
$detalle_carrito = obtenerCarritoDetallado($conexion, $carrito);
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title><?= t('Comprar producto | Sama Shala') ?></title>
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
<h1><?= t('Pagar') ?></h1>
<?php if (empty($detalle_carrito['items'])): ?>
<div class="mensaje mensaje-aviso">
<?= t('Tu carrito está vacío.') ?>
</div>
<p>
<a class="boton" href="tienda.php">
<?= t('Ir a la tienda') ?>
</a>
</p>
<?php else: ?>
<div class="ficha-sesion">
<section class="informacion-sesion">
<h2><?= t('Resumen del pedido') ?></h2>
<div class="lista-carrito">
<?php foreach ($detalle_carrito['items'] as $item): ?>
<div class="fila-carrito">
<?php if (!empty($item['imagen'])): ?>
<img
class="miniatura-fila-carrito"
src="imagenes/productos/<?= escapar($item['imagen']) ?>"
alt="<?= escapar($item['nombre']) ?>"
>
<?php endif; ?>
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
</div>
<?php endforeach; ?>
</div>
<p class="total-carrito">
<?= t('Total') ?>
<strong><?= formatear_precio($detalle_carrito['total']) ?></strong>
</p>
<?php if ($error === 'pago'): ?>
<div class="mensaje mensaje-error">
<?= t('No se ha podido procesar el pago simulado. Inténtalo de nuevo.') ?>
</div>
<?php endif; ?>
</section>
<aside class="panel-reserva">
<h2><?= t('Pago simulado') ?></h2>
<p>
<?= t('Este proyecto no cobra dinero real: al confirmar se registra la compra directamente como pagada.') ?>
</p>
<form
action="procesar_compra_producto.php"
method="post"
class="formulario"
>
<div class="campo">
<label for="titular">
<?= t('Nombre del titular') ?>
</label>
<input
type="text"
id="titular"
name="titular"
maxlength="150"
required
>
</div>
<div class="campo">
<label for="tarjeta">
<?= t('Número de tarjeta (simulado)') ?>
</label>
<input
type="text"
id="tarjeta"
name="tarjeta"
maxlength="19"
placeholder="4111 1111 1111 1111"
required
>
</div>
<button type="submit" class="boton boton-bloque">
<?= t('Confirmar compra de') ?> <?= formatear_precio($detalle_carrito['total']) ?>
</button>
</form>
</aside>
</div>
<?php endif; ?>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
