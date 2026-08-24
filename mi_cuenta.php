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
<link rel="stylesheet" href="estilos.css">
<link rel="icon" type="image/png" sizes="32x32" href="imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="imagenes/favicon.ico">
</head>
<body>
<?php require "menu.php"; ?>
<main class="contenedor">
<h1>
<?= t("Hola,") ?>
<?= escapar($_SESSION["usuario"]["nombre"]) ?>
</h1>
<section class="panel-cuenta">
<h2><?= t("Mis datos") ?></h2>
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
<section class="opciones-cuenta">
<article class="tarjeta">
<h2><?= t("Mis reservas") ?></h2>
<p>
    <?= t("Consulta tus próximas actividades y tu historial.") ?>
</p>
<a class="boton" href="mis_reservas.php">
<?= t("Ver mis reservas") ?>
</a>
</article>
<article class="tarjeta">
<h2><?= t("Mis paquetes") ?></h2>
<p>
    <?= t("Consulta tus paquetes y los usos disponibles.") ?>
</p>
<a class="boton" href="mis_paquetes.php">
<?= t("Ver mis paquetes") ?>
</a>
</article>
</section>
</main>
</body>
</html>