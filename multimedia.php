<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
$sql = "
SELECT id_multimedia, titulo, descripcion, url_youtube, url_spotify
FROM multimedia
WHERE activo = 1
ORDER BY fecha_creacion DESC
";
$resultado = $conexion->query($sql);
$fichas = $resultado->fetch_all(MYSQLI_ASSOC);
$hay_youtube = false;
foreach ($fichas as &$ficha) {
$ficha['id_youtube'] = extraer_id_youtube($ficha['url_youtube'] ?? null);
$ficha['embed_spotify'] = datos_embed_spotify($ficha['url_spotify'] ?? null);
if ($ficha['id_youtube']) {
$hay_youtube = true;
}
}
unset($ficha);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title><?= t('Multimedia | Sama Shala') ?></title>
<link rel="stylesheet" href="<?= urlEstilos() ?>">
<?php if ($hay_youtube): ?>
<link rel="preconnect" href="https://www.youtube.com">
<link rel="preconnect" href="https://i.ytimg.com">
<?php endif; ?>
<link rel="icon" type="image/png" sizes="32x32" href="imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu.php'; ?>
<main class="contenedor seccion">
<div class="encabezado-pagina">
<p class="etiqueta"><?= t('Multimedia') ?></p>
<h1><?= t('Conecta, escucha y medita') ?></h1>
</div>
<?php if (empty($fichas)): ?>
<p><?= t('Todavía no hay contenido multimedia publicado.') ?></p>
<?php else: ?>
<div class="rejilla-multimedia">
<?php foreach ($fichas as $ficha): ?>
<article class="tarjeta-multimedia">
<div class="contenido-tarjeta">
<h2><?= escapar($ficha['titulo']) ?></h2>
<p><?= escapar($ficha['descripcion']) ?></p>
<?php if ($ficha['id_youtube']): ?>
<button
type="button"
class="miniatura-video"
data-id-youtube="<?= escapar($ficha['id_youtube']) ?>"
data-titulo="<?= escapar($ficha['titulo']) ?>"
aria-label="<?= escapar(sprintf(t('Ver el video de %s'), $ficha['titulo'])) ?>"
>
<img
src="https://i.ytimg.com/vi/<?= escapar($ficha['id_youtube']) ?>/hqdefault.jpg"
alt=""
loading="lazy"
>
<span class="miniatura-video-boton">▶</span>
</button>
<?php endif; ?>
<?php if ($ficha['embed_spotify']): ?>
<div
class="spotify-embed"
style="height: <?= (int) $ficha['embed_spotify']['alto'] ?>px"
>
<iframe
src="<?= escapar($ficha['embed_spotify']['url']) ?>"
title="<?= escapar($ficha['titulo']) ?>"
allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
loading="lazy"
></iframe>
</div>
<?php endif; ?>
</div>
</article>
<?php endforeach; ?>
</div>
<div class="modal-video" id="modal-video" hidden>
<div class="modal-video-fondo" data-cerrar-modal-video></div>
<div class="modal-video-contenido" role="dialog" aria-modal="true" aria-label="<?= t('Video') ?>">
<button
type="button"
class="modal-video-cerrar"
data-cerrar-modal-video
aria-label="<?= t('Cerrar') ?>"
>
&times;
</button>
<div class="modal-video-marco">
<iframe
id="modal-video-iframe"
src=""
title=""
allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
allowfullscreen
></iframe>
</div>
</div>
</div>
<script>
(function () {
var modal = document.getElementById('modal-video');
var iframe = document.getElementById('modal-video-iframe');
if (!modal || !iframe) {
return;
}
function abrirModal(idYoutube, titulo) {
iframe.src = 'https://www.youtube.com/embed/' + idYoutube + '?autoplay=1';
iframe.title = titulo;
modal.hidden = false;
document.body.classList.add('bloquear-scroll');
}
function cerrarModal() {
modal.hidden = true;
iframe.src = '';
document.body.classList.remove('bloquear-scroll');
}
document.querySelectorAll('.miniatura-video').forEach(function (boton) {
boton.addEventListener('click', function () {
abrirModal(
boton.getAttribute('data-id-youtube'),
boton.getAttribute('data-titulo')
);
});
});
modal.querySelectorAll('[data-cerrar-modal-video]').forEach(function (elemento) {
elemento.addEventListener('click', cerrarModal);
});
document.addEventListener('keydown', function (evento) {
if (evento.key === 'Escape' && !modal.hidden) {
cerrarModal();
}
});
})();
</script>
<?php endif; ?>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
