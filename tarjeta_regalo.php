<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
$codigo = strtoupper(trim($_GET['codigo'] ?? ''));
$tarjeta = null;
if ($codigo !== '') {
$sql = "
SELECT codigo, saldo_inicial, saldo_disponible, estado, fecha_compra, fecha_caducidad
FROM tarjetas_regalo
WHERE codigo = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('s', $codigo);
$stmt->execute();
$tarjeta = $stmt->get_result()->fetch_assoc();
$stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= t('Tu tarjeta de regalo | Sama Shala') ?></title>
<link rel="stylesheet" href="<?= urlEstilos() ?>">
<link rel="icon" type="image/png" sizes="32x32" href="imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu.php'; ?>
<main class="contenedor seccion">
<?php if (!$tarjeta): ?>
<h1><?= t('Tarjeta de regalo no encontrada') ?></h1>
<div class="mensaje mensaje-error">
<?= t('No encontramos ninguna tarjeta de regalo con ese código.') ?>
</div>
<?php else: ?>
<h1><?= t('¡Tu tarjeta de regalo está lista!') ?></h1>
<?php if ($tarjeta['estado'] === 'pendiente'): ?>
<div class="mensaje mensaje-aviso">
<?= t('Esta tarjeta todavía no se ha activado. Si acabas de pagar, espera unos segundos y recarga esta página.') ?>
</div>
<?php else: ?>
<div class="tarjeta-regalo-vista">
<img
src="generar_imagen_regalo.php?codigo=<?= urlencode($tarjeta['codigo']) ?>"
alt="<?= t('Tarjeta de regalo Sama Shala') ?>"
class="tarjeta-regalo-imagen"
>
</div>
<p class="precio-sesion">
<?= t('Saldo disponible:') ?>
<strong><?= formatear_precio((float) $tarjeta['saldo_disponible']) ?></strong>
<?= t('de') ?>
<?= formatear_precio((float) $tarjeta['saldo_inicial']) ?>
</p>
<p>
<?= t('Estado:') ?>
<?= escapar(ucfirst($tarjeta['estado'])) ?>
<?php if ($tarjeta['fecha_caducidad']): ?>
— <?= t('válida hasta') ?> <?= date('d/m/Y', strtotime($tarjeta['fecha_caducidad'])) ?>
<?php endif; ?>
</p>
<p>
<a
class="boton"
href="generar_imagen_regalo.php?codigo=<?= urlencode($tarjeta['codigo']) ?>&descargar=1"
>
<?= t('Descargar tarjeta') ?>
</a>
</p>
<p class="ayuda">
<?= t('Comparte esta imagen con quien quieras regalar: puede usar el código al pagar cualquier compra, reserva o paquete del sitio.') ?>
</p>
<?php endif; ?>
<?php endif; ?>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
