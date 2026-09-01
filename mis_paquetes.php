<?php
require_once __DIR__ . '/seguridad.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
$id_usuario = idUsuarioActual();
$sql = "
SELECT
bc.id_paquete_cliente,
bc.fecha_compra,
bc.fecha_caducidad,
bc.usos_iniciales,
bc.usos_disponibles,
bc.precio_pagado,
bc.referencia_pago,
bc.estado,
tb.nombre AS nombre_paquete
FROM paquetes_clientes bc
INNER JOIN tipos_paquete tb
ON bc.id_tipo_paquete = tb.id_tipo_paquete
WHERE bc.id_usuario = ?
ORDER BY bc.fecha_compra DESC
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$paquetes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$mensaje = $_GET['mensaje'] ?? '';
$tiene_inscripcion = obtenerInscripcion($conexion, $id_usuario) !== null;
$paquete_recien_comprado = $paquetes[0] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title><?= t('Mis paquetes | Sama Shala') ?></title>
<link rel="stylesheet" href="<?= urlEstilos() ?>">
<link rel="icon" type="image/png" sizes="32x32" href="imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu.php'; ?>
<main class="contenedor">
<h1><?= t('Mis paquetes') ?></h1>
<?php if ($mensaje === 'comprado'): ?>
<?php if ($paquete_recien_comprado !== null && $paquete_recien_comprado['estado'] === 'pendiente'): ?>
<div class="mensaje mensaje-aviso">
<?= t('Hemos registrado tu compra por transferencia bancaria. Quedará pendiente de revisión hasta que confirmemos el pago.') ?>
</div>
<?php else: ?>
<div class="mensaje mensaje-exito">
<?= t('Paquete comprado correctamente. Ya puedes usarlo al reservar una sesión.') ?>
</div>
<?php endif; ?>
<?php if (!$tiene_inscripcion): ?>
<div class="mensaje mensaje-exito">
<p>
<?= t('Para completar tu inscripción, rellena este formulario (solo la primera vez).') ?>
</p>
<a class="boton" href="formulario_inscripcion.php">
<?= t('Ir al formulario de inscripción') ?>
</a>
</div>
<?php endif; ?>
<?php endif; ?>
<p>
<a class="boton boton-secundario" href="paquetes.php">
<?= t('Comprar un nuevo paquete') ?>
</a>
</p>
<?php if (count($paquetes) === 0): ?>
<p><?= t('Todavía no tienes ningún paquete.') ?></p>
<?php else: ?>
<div class="rejilla-reservas">
<?php foreach ($paquetes as $paquete): ?>
<?php
$caducado =
$paquete['fecha_caducidad'] !== null &&
strtotime($paquete['fecha_caducidad']) < strtotime('today');
$agotado = (int) $paquete['usos_disponibles'] <= 0;
?>
<article class="tarjeta-reserva">
<h3>
<?= escapar($paquete['nombre_paquete']) ?>
</h3>
<p>
<strong><?= t('Usos disponibles:') ?></strong>
<?= (int) $paquete['usos_disponibles'] ?> <?= t('de') ?> <?= (int) $paquete['usos_iniciales'] ?>
</p>
<p>
<strong><?= t('Comprado:') ?></strong>
<?= date(
'd/m/Y',
strtotime($paquete['fecha_compra'])
) ?>
</p>
<p>
<strong><?= t('Caduca:') ?></strong>
<?= $paquete['fecha_caducidad'] !== null
? date(
'd/m/Y',
strtotime($paquete['fecha_caducidad'])
)
: t('Sin caducidad') ?>
</p>
<p>
<strong><?= t('Estado:') ?></strong>
<?php if ($paquete['estado'] === 'pendiente'): ?>
<?= t('Pendiente de revisión') ?>
<?php elseif ($paquete['estado'] === 'cancelado'): ?>
<?= t('Cancelado') ?>
<?php elseif ($caducado): ?>
<?= t('Caducado') ?>
<?php elseif ($agotado): ?>
<?= t('Agotado') ?>
<?php else: ?>
<?= t('Activo') ?>
<?php endif; ?>
</p>
<p class="codigo-reserva">
<?= t('Ref. pago:') ?> <?= escapar($paquete['referencia_pago']) ?> ·
<?= formatear_precio(
(float) $paquete['precio_pagado']
) ?>
</p>
</article>
<?php endforeach; ?>
</div>
<?php endif; ?>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
