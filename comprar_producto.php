<?php
require_once __DIR__ . '/seguridad.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
$id_producto = filter_input(
INPUT_GET,
'id',
FILTER_VALIDATE_INT
);
if (!$id_producto) {
header('Location: tienda.php');
exit;
}
$sql = "
SELECT id_producto, nombre, precio, tallas, imagen
FROM productos
WHERE id_producto = ?
AND activo = 1
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_producto);
$stmt->execute();
$producto = $stmt->get_result()->fetch_assoc();
if (!$producto) {
http_response_code(404);
die(t('El producto solicitado no existe.'));
}
$tallas_producto = [];
if (!empty($producto['tallas'])) {
$tallas_producto = array_filter(array_map('trim', explode(',', $producto['tallas'])));
}
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
<a class="enlace-volver" href="detalle_producto.php?id=<?= (int) $producto['id_producto'] ?>">
← <?= t('Volver al producto') ?>
</a>
<div class="ficha-sesion">
<section class="informacion-sesion">
<h1>
<?= escapar($producto['nombre']) ?>
</h1>
<div class="rejilla-datos">
<div class="dato">
<span><?= t('Precio') ?></span>
<strong>
<?= formatear_precio((float) $producto['precio']) ?>
</strong>
</div>
</div>
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
<input
type="hidden"
name="id_producto"
value="<?= (int) $producto['id_producto'] ?>"
>
<?php if (!empty($tallas_producto)): ?>
<div class="campo">
<label for="talla">
<?= t('Talla') ?>
</label>
<select id="talla" name="talla" required>
<option value="">
<?= t('Selecciona una talla') ?>
</option>
<?php foreach ($tallas_producto as $talla): ?>
<option value="<?= escapar($talla) ?>">
<?= escapar($talla) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<?php endif; ?>
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
<?= t('Confirmar compra de') ?> <?= formatear_precio(
(float) $producto['precio']
) ?>
</button>
</form>
</aside>
</div>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
