<?php
require_once __DIR__ . '/funciones.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title>
<?= t('Sama Shala | Reserva actividades y espacios') ?>
</title>
<link rel="stylesheet" href="estilos.css">
<link rel="icon" type="image/png" sizes="32x32" href="imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu.php'; ?>
<main>
<section class="hero">
<div class="contenedor hero-interior">
<div>
<p class="etiqueta">
<?= t('Actividades, talleres y espacios') ?>
</p>
<h1>
<?= t('Cada reserva tiene su lugar') ?>
</h1>
<p class="hero-texto">
<?= t('Consulta las próximas actividades del centro, comprueba sus horarios y encuentra una plaza.') ?>
</p>
<div class="grupo-botones">
<a
class="boton"
href="actividades.php"
>
<?= t('Ver actividades') ?>
</a>
<a
class="boton boton-secundario"
href="sesiones.php"
>
<?= t('Próximas sesiones') ?>
</a>
</div>
</div>
<div class="hero-resumen">
<h2><?= t('¿Cómo funciona?') ?></h2>
<ol>
<li><?= t('Elige una actividad.') ?></li>
<li><?= t('Consulta sus próximas sesiones.') ?></li>
<li><?= t('Comprueba fecha, horario y plazas.') ?></li>
<li><?= t('Reserva la sesión que te interese.') ?></li>
</ol>
</div>
</div>
</section>
<section class="contenedor seccion">
<h2><?= t('Un proyecto basado en la disponibilidad') ?></h2>
<div class="rejilla-ventajas">
<article class="tarjeta-informativa">
<h3><?= t('Actividades') ?></h3>
<p>
<?= t('Descubre qué puedes hacer, su categoría, nivel y duración habitual.') ?>
</p>
</article>
<article class="tarjeta-informativa">
<h3><?= t('Calendario') ?></h3>
<p>
<?= t('Consulta cuándo se celebra cada actividad, en qué espacio y con qué profesor.') ?>
</p>
</article>
<article class="tarjeta-informativa">
<h3><?= t('Plazas') ?></h3>
<p>
<?= t('Comprueba el aforo y las plazas disponibles antes de realizar la reserva.') ?>
</p>
</article>
</div>
</section>
</main>
</body>
</html>
