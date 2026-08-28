<?php
require_once __DIR__ . '/seguridad.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
$id_usuario = idUsuarioActual();
$sql = "
SELECT
cp.id_compra,
cp.fecha_compra,
cp.talla_elegida,
cp.precio_pagado,
cp.referencia_pago,
cp.estado,
p.nombre AS nombre_producto,
p.categoria
FROM compras_productos cp
INNER JOIN productos p
ON cp.id_producto = p.id_producto
WHERE cp.id_usuario = ?
ORDER BY cp.fecha_compra DESC
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$compras = $stmt->get_result();
$mensaje = $_GET['mensaje'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title><?= t('Mis compras | Sama Shala') ?></title>
<link rel="stylesheet" href="<?= urlEstilos() ?>">
<link rel="icon" type="image/png" sizes="32x32" href="imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu.php'; ?>
<main class="contenedor">
<h1><?= t('Mis compras') ?></h1>
<?php if ($mensaje === 'comprado'): ?>
<div class="mensaje mensaje-exito">
<?= t('Producto comprado correctamente. Nos pondremos en contacto para coordinar la entrega.') ?>
</div>
<?php endif; ?>
<p>
<a class="boton boton-secundario" href="tienda.php">
<?= t('Ir a la tienda') ?>
</a>
</p>
<?php if ($compras->num_rows === 0): ?>
<p><?= t('Todavía no tienes ninguna compra.') ?></p>
<?php else: ?>
<div class="rejilla-reservas">
<?php while ($compra = $compras->fetch_assoc()): ?>
<article class="tarjeta-reserva">
<h3>
<?= escapar($compra['nombre_producto']) ?>
</h3>
<p>
<strong><?= t('Categoría:') ?></strong>
<?= escapar(texto_categoria_producto($compra['categoria'])) ?>
</p>
<?php if (!empty($compra['talla_elegida'])): ?>
<p>
<strong><?= t('Talla:') ?></strong>
<?= escapar($compra['talla_elegida']) ?>
</p>
<?php endif; ?>
<p>
<strong><?= t('Comprado:') ?></strong>
<?= date(
'd/m/Y',
strtotime($compra['fecha_compra'])
) ?>
</p>
<p>
<strong><?= t('Estado:') ?></strong>
<?= escapar(ucfirst($compra['estado'])) ?>
</p>
<p class="codigo-reserva">
<?= t('Ref. pago:') ?> <?= escapar($compra['referencia_pago']) ?> ·
<?= formatear_precio(
(float) $compra['precio_pagado']
) ?>
</p>
</article>
<?php endwhile; ?>
</div>
<?php endif; ?>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
