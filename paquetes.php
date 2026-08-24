<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
$sql = "
SELECT
id_tipo_paquete,
nombre,
numero_usos,
precio
FROM tipos_paquete
WHERE activo = 1
AND id_tenant = ?
ORDER BY precio
";
$stmt = $conexion->prepare($sql);
$id_tenant = idTenantActual();
$stmt->bind_param('i', $id_tenant);
$stmt->execute();
$resultado = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title><?= t('Paquetes | Sama Shala') ?></title>
<link rel="stylesheet" href="estilos.css">
</head>
<body>
<?php require_once __DIR__ . '/menu.php'; ?>
<main class="contenedor seccion">
<div class="encabezado-pagina">
<div>
<p class="etiqueta">
<?= t('Precios') ?>
</p>
<h1><?= t('Paquetes de clases') ?></h1>
<p>
<?= t('Compra un paquete y utilízalo para reservar las sesiones que quieras mientras tenga usos disponibles.') ?>
</p>
</div>
</div>
<?php if ($resultado->num_rows === 0): ?>
<div class="mensaje mensaje-aviso">
<?= t('No hay paquetes disponibles en este momento.') ?>
</div>
<?php else: ?>
<div class="rejilla-actividades">
<?php while (
$paquete = $resultado->fetch_assoc()
): ?>
<article class="tarjeta-actividad">
<div class="contenido-tarjeta">
<h2>
<?= escapar($paquete['nombre']) ?>
</h2>
<p class="dato-destacado">
<?= (int) $paquete['numero_usos'] ?> <?= t('clases') ?>
</p>
<p>
<?= t('Válido durante 1 mes desde la compra.') ?>
</p>
<p class="numero-plazas">
<?= formatear_precio(
(float) $paquete['precio']
) ?>
</p>
<a
class="boton"
href="comprar_paquete.php?id=<?= (int)
$paquete['id_tipo_paquete'] ?>"
>
<?= t('Comprar este paquete') ?>
</a>
</div>
</article>
<?php endwhile; ?>
</div>
<?php endif; ?>
</main>
</body>
</html>
