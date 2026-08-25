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
<?= t('Sama Shala') ?>
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
<div id="video-fondo-hero"></div>
</div>
<div class="hero-video-cargando" id="hero-video-cargando"></div>
<div class="hero-video-superposicion"></div>
<div class="contenedor hero-interior">
<div>
<p class="etiqueta">
<?= t('Clases, Terapias y Eventos') ?>
</p>
<h1>
<?= t('Un espacio para tu Bienestar') ?>
</h1>
<p class="hero-texto">
<?= t('Consulta nuestras próximas actividades y encuentra tu equilibrio interior.') ?>
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
<?= t('Calendario') ?>
</a>
</div>
</div>
</div>
</section>
<script src="https://www.youtube.com/iframe_api"></script>
<script>
(function () {
const contenedor = document.querySelector(".hero-video-fondo");
const cargando = document.querySelector("#hero-video-cargando");
let reproductor = null;

function ajustarTamano() {
if (!reproductor || typeof reproductor.getIframe !== "function") {
return;
}
const iframe = reproductor.getIframe();
if (!contenedor || !iframe) {
return;
}
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

window.onYouTubeIframeAPIReady = function () {
reproductor = new YT.Player("video-fondo-hero", {
videoId: "OuNYTfJohwY",
playerVars: {
autoplay: 1,
mute: 1,
controls: 0,
disablekb: 1,
fs: 0,
iv_load_policy: 3,
modestbranding: 1,
playsinline: 1,
rel: 0,
cc_load_policy: 0
},
events: {
onReady: function (evento) {
evento.target.mute();
evento.target.playVideo();
ajustarTamano();
},
onStateChange: function (evento) {
if (evento.data === YT.PlayerState.PLAYING && cargando) {
cargando.classList.add("oculto");
}
if (evento.data === YT.PlayerState.ENDED) {
evento.target.seekTo(0);
evento.target.playVideo();
}
}
}
});
};

window.addEventListener("resize", ajustarTamano);
})();
</script>
<section class="contenedor seccion seccion-nosotros">
<h2><?= t('Sama Shala') ?></h2>
<p>
<?= t('Sama Shala es un espacio dedicado a promover el equilibrio, la paz y el bienestar integral. Creemos en el poder de las prácticas ancestrales para guiar a cada persona hacia una vida más saludable, tanto física como espiritualmente. Ofrecemos una combinación única de Ayurveda, yoga, sonoterapia y musicoterapia, con el objetivo de apoyarte en tu proceso de transformación personal y bienestar en un entorno de tranquilidad y amor.') ?>
</p>
<p>
<?= t('Sama Shala nació del profundo camino de bienestar holístico que William y Ángeles emprendieron juntos. Descubrieron el poder transformador del yoga, la Ayurveda y la sonoterapia, prácticas que les brindaron serenidad y equilibrio, y observaron sus efectos en las personas cercanas. Inspirados por estos descubrimientos y el deseo de ofrecer un espacio para el equilibrio, paz y bienestar, crearon Sama Shala como un lugar donde las personas pueden encontrar armonía y renovación.') ?>
</p>
</section>
<section class="franja-horarios-paquetes">
<div class="panel-horario-paquete">
<img
class="panel-imagen"
src="imagenes/actividades/horarios.png"
alt=""
>
<div class="panel-texto">
<h3><?= t('Horarios') ?></h3>
<p>
<?= t('Consulta el horario de nuestras próximas actividades.') ?>
</p>
<a class="boton" href="sesiones.php">
<?= t('Ver horario') ?>
</a>
</div>
</div>
<div class="panel-horario-paquete">
<img
class="panel-imagen"
src="imagenes/actividades/paquetes.png"
alt=""
>
<div class="panel-texto">
<h3><?= t('Paquetes') ?></h3>
<p>
<?= t('Compra tu paquete y empieza a practicar con nosotros.') ?>
</p>
<a class="boton" href="paquetes.php">
<?= t('Ver paquetes') ?>
</a>
</div>
</div>
</section>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
