<?php
require_once __DIR__ . '/seguridad.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
$id_tipo_bono = filter_input(
INPUT_GET,
'id',
FILTER_VALIDATE_INT
);
if (!$id_tipo_bono) {
header('Location: bonos.php');
exit;
}
$sql = "
SELECT
id_tipo_bono,
nombre,
numero_usos,
precio,
dias_validez
FROM tipos_bono
WHERE id_tipo_bono = ?
AND activo = 1
AND id_tenant = ?
";
$stmt = $conexion->prepare($sql);
$id_tenant = idTenantActual();
$stmt->bind_param('ii', $id_tipo_bono, $id_tenant);
$stmt->execute();
$bono = $stmt->get_result()->fetch_assoc();
if (!$bono) {
http_response_code(404);
die('El bono solicitado no existe.');
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
<title>Comprar bono | Sama Shala</title>
<link rel="stylesheet" href="estilos.css">
</head>
<body>
<?php require_once __DIR__ . '/menu.php'; ?>
<main class="contenedor seccion">
<a class="enlace-volver" href="bonos.php">
← Volver a bonos
</a>
<div class="ficha-sesion">
<section class="informacion-sesion">
<h1>
<?= escapar($bono['nombre']) ?>
</h1>
<div class="rejilla-datos">
<div class="dato">
<span>Clases</span>
<strong>
<?= (int) $bono['numero_usos'] ?>
</strong>
</div>
<div class="dato">
<span>Validez</span>
<strong>
<?= $bono['dias_validez'] !== null
? (int) $bono['dias_validez'] . ' días'
: 'Sin caducidad' ?>
</strong>
</div>
<div class="dato">
<span>Precio</span>
<strong>
<?= number_format(
(float) $bono['precio'],
2,
',',
'.'
) ?> €
</strong>
</div>
</div>
<?php if ($error === 'pago'): ?>
<div class="mensaje mensaje-error">
No se ha podido procesar el pago simulado.
Inténtalo de nuevo.
</div>
<?php endif; ?>
</section>
<aside class="panel-reserva">
<h2>Pago simulado</h2>
<p>
Este proyecto no cobra dinero real:
al confirmar se registra la compra
directamente como pagada.
</p>
<form
action="procesar_compra_bono.php"
method="post"
class="formulario"
>
<input
type="hidden"
name="id_tipo_bono"
value="<?= (int) $bono['id_tipo_bono'] ?>"
>
<div class="campo">
<label for="titular">
Nombre del titular
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
Número de tarjeta (simulado)
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
Confirmar compra de
<?= number_format(
(float) $bono['precio'],
2,
',',
'.'
) ?> €
</button>
</form>
</aside>
</div>
</main>
</body>
</html>
