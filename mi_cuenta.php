<?php
require_once "seguridad.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title><?= t("Mi cuenta") ?></title>
<link rel="stylesheet" href="<?= urlEstilos() ?>">
<link rel="icon" type="image/png" sizes="32x32" href="imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="imagenes/favicon.ico">
</head>
<body>
<?php require "menu.php"; ?>
<main class="contenedor seccion">
<div class="encabezado-pagina">
<div>
<p class="etiqueta"><?= t("Mi cuenta") ?></p>
<h1>
<?= t("Hola,") ?> <?= escapar($_SESSION["usuario"]["nombre"]) ?>
</h1>
<p>
<?= t("Gestiona tus datos, reservas y paquetes desde aquí.") ?>
</p>
</div>
</div>
<section class="panel-cuenta">
<div class="panel-cuenta-cabecera">
<span class="avatar-cuenta">
<?= escapar(mb_strtoupper(mb_substr($_SESSION["usuario"]["nombre"], 0, 1))) ?>
</span>
<div>
<h2><?= t("Mis datos") ?></h2>
<p class="panel-cuenta-subtitulo"><?= t("Información de tu perfil") ?></p>
</div>
</div>
<dl>
<dt><?= t("Nombre") ?></dt>
<dd>
<?= escapar(
$_SESSION["usuario"]["nombre"] .
" " .
$_SESSION["usuario"]["apellidos"]
) ?>
</dd>
<dt><?= t("Correo electrónico") ?></dt>
<dd>
    <?= escapar(
$_SESSION["usuario"]["email"]
) ?>
</dd>
</dl>
</section>
<h2 class="titulo-opciones-cuenta"><?= t("Accesos rápidos") ?></h2>
<section class="opciones-cuenta">
<article class="tarjeta">
<span class="icono-tarjeta-cuenta">
<svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8">
<rect x="3" y="5" width="18" height="16" rx="2"/>
<path d="M3 10h18M8 3v4M16 3v4"/>
</svg>
</span>
<h2><?= t("Mis reservas") ?></h2>
<p>
    <?= t("Consulta tus próximas actividades y tu historial.") ?>
</p>
<a class="boton boton-bloque" href="mis_reservas.php">
<?= t("Ver mis reservas") ?>
</a>
</article>
<article class="tarjeta">
<span class="icono-tarjeta-cuenta">
<svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8">
<path d="M4 8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-3a2 2 0 0 0 0-4V8Z"/>
<path d="M10 6v12" stroke-dasharray="3 3"/>
</svg>
</span>
<h2><?= t("Mis paquetes") ?></h2>
<p>
    <?= t("Consulta tus paquetes y los usos disponibles.") ?>
</p>
<a class="boton boton-bloque" href="mis_paquetes.php">
<?= t("Ver mis paquetes") ?>
</a>
</article>
</section>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>