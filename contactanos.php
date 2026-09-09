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
<title><?= t('Contáctanos | Sama Shala') ?></title>
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
<p class="etiqueta"><?= t('Contáctanos') ?></p>
<h1><?= t('Hablemos') ?></h1>
</div>
<p class="contacto-intro">
<?= t('¿Tienes alguna pregunta o quieres visitarnos?') ?>
</p>
<div class="contacto-grid">
<section class="contacto-info">
<div class="contacto-dato">
<span class="contacto-icono">
<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8">
<path d="M6.6 10.8c1.4 2.8 3.8 5.2 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.9 21 3 13.1 3 3.5c0-.6.4-1 1-1h3.4c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1L6.6 10.8Z"/>
</svg>
</span>
<div>
<strong><?= t('Teléfono') ?></strong>
<ul class="lista-telefonos-contacto">
<li>
<a
class="boton-whatsapp-contacto"
href="https://wa.me/593999806435"
target="_blank"
rel="noopener"
aria-label="WhatsApp"
>
<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
<path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5.06-1.33A10 10 0 1 0 12 2Zm5.68 14.24c-.24.68-1.4 1.3-1.93 1.38-.5.08-1.12.11-1.8-.11a15.6 15.6 0 0 1-3.9-2.1 12.7 12.7 0 0 1-2.6-3.3c-.34-.55-.6-1.13-.6-1.8 0-.66.35-1.06.63-1.34.24-.24.5-.28.68-.28h.5c.16 0 .38-.02.58.4.24.5.78 1.9.85 2.05.07.14.12.32.02.5-.1.19-.16.3-.3.46-.16.17-.32.38-.46.5-.15.15-.3.3-.13.6.2.36.75 1.24 1.62 2 1.13 1 2.06 1.32 2.4 1.47.26.11.42.09.58-.06.19-.2.63-.73.8-.98.17-.25.34-.2.56-.12.24.08 1.5.7 1.76.84.26.13.43.2.5.32.06.13.06.7-.18 1.38Z"/>
</svg>
</a>
+593 99 980 6435
</li>
<li>
<a
class="boton-whatsapp-contacto"
href="https://wa.me/593987606615"
target="_blank"
rel="noopener"
aria-label="WhatsApp"
>
<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
<path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5.06-1.33A10 10 0 1 0 12 2Zm5.68 14.24c-.24.68-1.4 1.3-1.93 1.38-.5.08-1.12.11-1.8-.11a15.6 15.6 0 0 1-3.9-2.1 12.7 12.7 0 0 1-2.6-3.3c-.34-.55-.6-1.13-.6-1.8 0-.66.35-1.06.63-1.34.24-.24.5-.28.68-.28h.5c.16 0 .38-.02.58.4.24.5.78 1.9.85 2.05.07.14.12.32.02.5-.1.19-.16.3-.3.46-.16.17-.32.38-.46.5-.15.15-.3.3-.13.6.2.36.75 1.24 1.62 2 1.13 1 2.06 1.32 2.4 1.47.26.11.42.09.58-.06.19-.2.63-.73.8-.98.17-.25.34-.2.56-.12.24.08 1.5.7 1.76.84.26.13.43.2.5.32.06.13.06.7-.18 1.38Z"/>
</svg>
</a>
+593 98 760 6615
</li>
</ul>
</div>
</div>
<div class="contacto-dato">
<span class="contacto-icono">
<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8">
<path d="M12 22s7-7.4 7-12.6A7 7 0 0 0 5 9.4C5 14.6 12 22 12 22Z"/>
<circle cx="12" cy="9.4" r="2.6"/>
</svg>
</span>
<div>
<strong><?= t('Dirección') ?></strong>
<p>Los Álamos y Del Arrayán, Cuenca – Ecuador</p>
</div>
</div>
<div class="contacto-dato">
<span class="contacto-icono">
<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8">
<rect x="3" y="5" width="18" height="14" rx="2"/>
<path d="m4 6.5 8 6 8-6"/>
</svg>
</span>
<div>
<strong><?= t('Correo') ?></strong>
<p><a href="mailto:samashalaec@gmail.com">samashalaec@gmail.com</a></p>
</div>
</div>
</section>
<div class="contacto-mapa">
<iframe
src="https://www.google.com/maps?q=Los+%C3%81lamos+y+Del+Arrayan%2C+Cuenca%2C+Ecuador&output=embed"
title="<?= t('Ubicación de Sama Shala') ?>"
loading="lazy"
allowfullscreen
></iframe>
</div>
</div>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
