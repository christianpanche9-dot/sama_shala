<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
$carrito = $_SESSION['carrito'] ?? [];
$detalle_carrito = obtenerCarritoDetallado($conexion, $carrito);
$error = $_GET['error'] ?? '';
$es_invitado = !usuarioAutenticado();
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
<div class="ficha-sesion ficha-pago">
<section class="informacion-sesion">
<h2><?= t('Resumen del pedido') ?></h2>
<div class="lista-carrito">
<?php foreach ($detalle_carrito['items'] as $item): ?>
<div class="fila-carrito">
<?php if (!empty($item['imagen'])): ?>
<img
class="miniatura-fila-carrito"
loading="lazy"
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
<?= t('No se ha podido iniciar el pago. Inténtalo de nuevo.') ?>
</div>
<?php elseif ($error !== ''): ?>
<div class="mensaje mensaje-error">
<?= escapar($error) ?>
</div>
<?php endif; ?>
</section>
<aside class="panel-reserva">
<h2><?= t('¿Cómo quieres pagar?') ?></h2>
<form
action="procesar_compra_producto.php"
method="post"
enctype="multipart/form-data"
class="formulario"
id="formulario-pago-producto"
>
<input
type="hidden"
name="csrf_token"
value="<?= escapar(tokenCsrf()) ?>"
>
<?php if ($es_invitado): ?>
<fieldset class="campo-completo">
<legend><?= t('Tus datos') ?></legend>
<div class="campo">
<label for="nombre_invitado"><?= t('Nombre') ?></label>
<input type="text" id="nombre_invitado" name="nombre" maxlength="80" required>
</div>
<div class="campo">
<label for="apellidos_invitado"><?= t('Apellidos') ?></label>
<input type="text" id="apellidos_invitado" name="apellidos" maxlength="120" required>
</div>
<div class="campo">
<label for="email_invitado"><?= t('Correo electrónico') ?></label>
<input type="email" id="email_invitado" name="email" maxlength="150" required>
<small><?= t('Te enviaremos aquí la confirmación de tu compra.') ?></small>
</div>
<div class="campo">
<label for="telefono_invitado"><?= t('Teléfono') ?></label>
<input type="tel" id="telefono_invitado" name="telefono" maxlength="30">
</div>
</fieldset>
<p class="campo-trampa-bot" aria-hidden="true">
<label for="sitio_web">Sitio web</label>
<input type="text" id="sitio_web" name="sitio_web" tabindex="-1" autocomplete="off">
</p>
<p>
<?= t('Serás redirigido a PayPhone para completar el pago de forma segura con tu tarjeta.') ?>
</p>
<?php else: ?>
<fieldset class="campo-completo">
<label class="opcion-pago">
<input
type="radio"
name="metodo_pago_compra"
value="payphone"
checked
data-metodo-pago
>
<?= t('Tarjeta (PayPhone)') ?>
</label>
<label class="opcion-pago">
<input
type="radio"
name="metodo_pago_compra"
value="transferencia"
data-metodo-pago
>
<?= t('Transferencia bancaria') ?>
</label>
</fieldset>
<div id="bloque-pago-payphone">
<p>
<?= t('Serás redirigido a PayPhone para completar el pago de forma segura con tu tarjeta.') ?>
</p>
</div>
<div id="bloque-pago-transferencia" hidden>
<?php require __DIR__ . '/datos_transferencia.php'; ?>
<div class="campo">
<label for="comprobante_pago">
<?= t('Foto del comprobante de la transferencia') ?>
</label>
<input
type="file"
id="comprobante_pago"
name="comprobante_pago"
accept="image/*"
data-requerido-transferencia
>
</div>
<p class="ayuda">
<?= t('Tu compra quedará pendiente de revisión hasta que confirmemos el pago.') ?>
</p>
</div>
<?php endif; ?>
<button type="submit" class="boton boton-bloque">
<?= t('Confirmar compra de') ?> <?= formatear_precio($detalle_carrito['total']) ?>
</button>
</form>
</aside>
</div>
<script>
(function () {
var formulario = document.getElementById('formulario-pago-producto');
var bloquePayphone = document.getElementById('bloque-pago-payphone');
var bloqueTransferencia = document.getElementById('bloque-pago-transferencia');
var radios = formulario.querySelectorAll('[data-metodo-pago]');
function actualizarModoPago() {
var seleccionado = formulario.querySelector('[data-metodo-pago]:checked');
if (!seleccionado) {
return;
}
var esTransferencia = seleccionado.value === 'transferencia';
bloquePayphone.hidden = esTransferencia;
bloqueTransferencia.hidden = !esTransferencia;
formulario.querySelectorAll('[data-requerido-transferencia]').forEach(function (campo) {
campo.required = esTransferencia;
});
}
radios.forEach(function (radio) {
radio.addEventListener('change', actualizarModoPago);
});
actualizarModoPago();
})();
</script>
<?php endif; ?>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
