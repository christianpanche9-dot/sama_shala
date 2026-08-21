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
precio,
dias_validez
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
die('El paquete solicitado no existe.');
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
<title>Comprar paquete | Sama Shala</title>
<link rel="stylesheet" href="estilos.css">
</head>
<body>
<?php require_once __DIR__ . '/menu.php'; ?>
<main class="contenedor seccion">
<a class="enlace-volver" href="paquetes.php">
← Volver a paquetes
</a>
<div class="ficha-sesion">
<section class="informacion-sesion">
<h1>
<?= escapar($paquete['nombre']) ?>
</h1>
<div class="rejilla-datos">
<div class="dato">
<span>Clases</span>
<strong>
<?= (int) $paquete['numero_usos'] ?>
</strong>
</div>
<div class="dato">
<span>Validez</span>
<strong>
<?= $paquete['dias_validez'] !== null
? (int) $paquete['dias_validez'] . ' días'
: 'Sin caducidad' ?>
</strong>
</div>
<div class="dato">
<span>Precio</span>
<strong>
<?= number_format(
(float) $paquete['precio'],
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
(float) $paquete['precio'],
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
