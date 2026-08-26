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
$paquetes = $resultado->fetch_all(MYSQLI_ASSOC);
$paquetes_multiclase = array_values(
array_filter($paquetes, fn ($p) => (int) $p['numero_usos'] > 1)
);
$id_paquete_destacado = count($paquetes_multiclase) >= 3
? (int) $paquetes_multiclase[intdiv(count($paquetes_multiclase), 2)]['id_tipo_paquete']
: null;
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
<p class="etiqueta">
<?= t('Precios') ?>
</p>
<h1><?= t('Paquetes de clases') ?></h1>
<p>
<?= t('Compra un paquete y utilízalo para reservar las sesiones que quieras mientras tenga usos disponibles.') ?>
</p>
</div>
</div>
<?php if (count($paquetes) === 0): ?>
<div class="mensaje mensaje-aviso">
<?= t('No hay paquetes disponibles en este momento.') ?>
</div>
<?php else: ?>
<div class="rejilla-actividades rejilla-paquetes">
<?php foreach ($paquetes as $paquete):
$es_clase_suelta = (int) $paquete['numero_usos'] === 1;
$es_destacado = $id_paquete_destacado !== null
&& (int) $paquete['id_tipo_paquete'] === $id_paquete_destacado;
?>
<article class="tarjeta-actividad tarjeta-paquete<?= $es_destacado ? ' tarjeta-paquete-destacada' : '' ?>">
<?php if ($es_destacado): ?>
<span class="etiqueta-destacada"><?= t('Más popular') ?></span>
<?php endif; ?>
<div class="contenido-tarjeta">
<h2>
<?= escapar($paquete['nombre']) ?>
</h2>
<span class="insignia insignia-paquete">
<?= (int) $paquete['numero_usos'] ?> <?= $es_clase_suelta ? t('clase') : t('clases') ?>
</span>
<p class="precio-paquete">
<?= formatear_precio(
(float) $paquete['precio']
) ?>
</p>
<p class="validez-paquete">
<?= t('Válido durante 1 mes desde la compra.') ?>
</p>
<a
class="boton boton-bloque"
href="comprar_paquete.php?id=<?= (int)
$paquete['id_tipo_paquete'] ?>"
>
<?= $es_clase_suelta ? t('Comprar clase') : t('Comprar este paquete') ?>
</a>
</div>
</article>
<?php endforeach; ?>
</div>
<?php endif; ?>
<h2 class="titulo-todas-actividades"><?= t('Condiciones de uso') ?></h2>
<div class="tarjeta-informativa">
<ol class="lista-condiciones">
<li><?= t('Los paquetes tienen una duración de 1 mes.') ?></li>
<li><?= t('Los paquetes son para el uso de las clases de yoga; otras actividades como eventos y terapias tienen un costo aparte de los paquetes de clases.') ?></li>
<li><?= t('Las clases son intransferibles a otro usuario.') ?></li>
<li><?= t('Tienes hasta 15 minutos antes de que comience la clase para cancelar tu reserva.') ?></li>
</ol>
</div>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
