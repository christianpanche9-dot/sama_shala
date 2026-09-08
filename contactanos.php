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
<p>+593 99 980 6435 · +593 98 760 6615</p>
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
<p>Los Álamos y Ordoñez Lasso, Cuenca – Ecuador</p>
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
src="https://www.google.com/maps?q=Los+%C3%81lamos+y+Ordo%C3%B1ez+Lasso%2C+Cuenca%2C+Ecuador&output=embed"
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
