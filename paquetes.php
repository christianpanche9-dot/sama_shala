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
<div class="diseno-paquetes">
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
<div class="envoltorio-boton-paquete">
<a
class="boton boton-bloque enlace-comprar-paquete desactivado"
aria-disabled="true"
href="comprar_paquete.php?id=<?= (int)
$paquete['id_tipo_paquete'] ?>"
>
<?= $es_clase_suelta ? t('Comprar clase') : t('Comprar este paquete') ?>
</a>
<span class="aviso-boton-desactivado">
<?= t('Marca la casilla de condiciones para continuar.') ?>
</span>
</div>
</div>
</article>
<?php endforeach; ?>
</div>
<aside class="panel-reserva panel-condiciones">
<h2><?= t('Condiciones de uso') ?></h2>
<ol class="lista-condiciones">
<li><?= t('Los paquetes tienen una duración de 1 mes.') ?></li>
<li><?= t('Los paquetes son para el uso de las clases de yoga; otras actividades como eventos y terapias tienen un costo aparte de los paquetes de clases.') ?></li>
<li><?= t('Las clases son intransferibles a otro usuario.') ?></li>
<li><?= t('Tienes hasta 15 minutos antes de que comience la clase para cancelar tu reserva.') ?></li>
</ol>
<label class="casilla-condiciones">
<input type="checkbox" id="check-condiciones">
<span><?= t('He leído y acepto las condiciones de uso.') ?></span>
</label>
<p class="mensaje mensaje-error aviso-condiciones" id="aviso-condiciones" hidden>
<?= t('Debes aceptar las condiciones de uso para continuar.') ?>
</p>
</aside>
</div>
<?php endif; ?>
</main>
<script>
(function () {
var casilla = document.getElementById('check-condiciones');
var aviso = document.getElementById('aviso-condiciones');
var enlaces = document.querySelectorAll('.enlace-comprar-paquete');
if (!casilla) { return; }
function actualizarEstado() {
var aceptado = casilla.checked;
enlaces.forEach(function (enlace) {
enlace.classList.toggle('desactivado', !aceptado);
enlace.setAttribute('aria-disabled', String(!aceptado));
});
if (aceptado) { aviso.hidden = true; }
}
casilla.addEventListener('change', actualizarEstado);
enlaces.forEach(function (enlace) {
enlace.addEventListener('click', function (evento) {
if (!casilla.checked) {
evento.preventDefault();
aviso.hidden = false;
aviso.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
});
});
actualizarEstado();
})();
</script>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
