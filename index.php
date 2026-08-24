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
<div class="hero-video-fondo">
<iframe
id="video-fondo-hero"
src="https://www.youtube.com/embed/3Lh9xC0dhfs?autoplay=1&mute=1&loop=1&playlist=3Lh9xC0dhfs&controls=0&showinfo=0&rel=0&modestbranding=1&iv_load_policy=3&disablekb=1&playsinline=1"
title=""
frameborder="0"
allow="autoplay; encrypted-media"
></iframe>
</div>
<div class="hero-video-superposicion"></div>
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
</div>
</section>
<script>
(function () {
const contenedor = document.querySelector(".hero-video-fondo");
const iframe = document.querySelector("#video-fondo-hero");
if (!contenedor || !iframe) {
return;
}
function ajustarTamano() {
const ancho = contenedor.offsetWidth;
const alto = contenedor.offsetHeight;
const proporcion = 16 / 9;
if (ancho / alto > proporcion) {
iframe.style.width = "100%";
iframe.style.height = (ancho / proporcion) + "px";
} else {
iframe.style.height = "100%";
iframe.style.width = (alto * proporcion) + "px";
}
}
window.addEventListener("resize", ajustarTamano);
ajustarTamano();
})();
</script>
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
