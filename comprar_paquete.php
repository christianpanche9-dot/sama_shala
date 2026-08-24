<?php
require_once __DIR__ . '/seguridad.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
$id_tipo_paquete = filter_input(
INPUT_GET,
'id',
FILTER_VALIDATE_INT
);
if (!$id_tipo_paquete) {
header('Location: paquetes.php');
exit;
}
$sql = "
SELECT
id_tipo_paquete,
nombre,
numero_usos,
precio
FROM tipos_paquete
WHERE id_tipo_paquete = ?
AND activo = 1
AND id_tenant = ?
";
$stmt = $conexion->prepare($sql);
$id_tenant = idTenantActual();
$stmt->bind_param('ii', $id_tipo_paquete, $id_tenant);
$stmt->execute();
$paquete = $stmt->get_result()->fetch_assoc();
if (!$paquete) {
http_response_code(404);
die(t('El paquete solicitado no existe.'));
}
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title><?= t('Comprar paquete | Sama Shala') ?></title>
<link rel="stylesheet" href="estilos.css">
</head>
<body>
<?php require_once __DIR__ . '/menu.php'; ?>
<main class="contenedor seccion">
<a class="enlace-volver" href="paquetes.php">
← <?= t('Volver a paquetes') ?>
</a>
<div class="ficha-sesion">
<section class="informacion-sesion">
<h1>
<?= escapar($paquete['nombre']) ?>
</h1>
<div class="rejilla-datos">
<div class="dato">
<span><?= t('Clases') ?></span>
<strong>
<?= (int) $paquete['numero_usos'] ?>
</strong>
</div>
<div class="dato">
<span><?= t('Validez') ?></span>
<strong><?= t('1 mes') ?></strong>
</div>
<div class="dato">
<span><?= t('Precio') ?></span>
<strong>
<?= formatear_precio(
(float) $paquete['precio']
) ?>
</strong>
</div>
</div>
<?php if ($error === 'pago'): ?>
<div class="mensaje mensaje-error">
<?= t('No se ha podido procesar el pago simulado. Inténtalo de nuevo.') ?>
</div>
<?php endif; ?>
</section>
<aside class="panel-reserva">
<h2><?= t('Pago simulado') ?></h2>
<p>
<?= t('Este proyecto no cobra dinero real: al confirmar se registra la compra directamente como pagada.') ?>
</p>
<form
action="procesar_compra_paquete.php"
method="post"
class="formulario"
>
<input
type="hidden"
name="id_tipo_paquete"
value="<?= (int) $paquete['id_tipo_paquete'] ?>"
>
<div class="campo">
<label for="titular">
<?= t('Nombre del titular') ?>
</label>
<input
type="text"
id="titular"
name="titular"
maxlength="150"
required
>
</div>
<div class="campo">
<label for="tarjeta">
<?= t('Número de tarjeta (simulado)') ?>
</label>
<input
type="text"
id="tarjeta"
name="tarjeta"
maxlength="19"
placeholder="4111 1111 1111 1111"
required
>
</div>
<button type="submit" class="boton boton-bloque">
<?= t('Confirmar compra de') ?>
<?= formatear_precio(
(float) $paquete['precio']
) ?>
</button>
</form>
</aside>
</div>
</main>
</body>
</html>
