<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
$sql = "
SELECT
id_tipo_paquete,
nombre,
numero_usos,
precio,
dias_validez
FROM tipos_paquete
WHERE activo = 1
AND id_tenant = ?
ORDER BY precio
";
$stmt = $conexion->prepare($sql);
$id_tenant = idTenantActual();
$stmt->bind_param('i', $id_tenant);
$stmt->execute();
$resultado = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title>Paquetes | Sama Shala</title>
<link rel="stylesheet" href="estilos.css">
</head>
<body>
<?php require_once __DIR__ . '/menu.php'; ?>
<main class="contenedor seccion">
<div class="encabezado-pagina">
<div>
<p class="etiqueta">
Precios
</p>
<h1>Paquetes de clases</h1>
<p>
Compra un paquete y utilízalo para reservar
las sesiones que quieras mientras tenga
usos disponibles.
</p>
</div>
</div>
<?php if ($resultado->num_rows === 0): ?>
<div class="mensaje mensaje-aviso">
No hay paquetes disponibles en este momento.
</div>
<?php else: ?>
<div class="rejilla-actividades">
<?php while (
$paquete = $resultado->fetch_assoc()
): ?>
<article class="tarjeta-actividad">
<div class="contenido-tarjeta">
<h2>
<?= escapar($paquete['nombre']) ?>
</h2>
<p class="dato-destacado">
<?= (int) $paquete['numero_usos'] ?> clases
</p>
<p>
<?php if ($paquete['dias_validez'] !== null): ?>
Válido durante <?= (int) $paquete['dias_validez'] ?> días desde la compra.
<?php else: ?>
Sin caducidad.
<?php endif; ?>
</p>
<p class="numero-plazas">
<?= number_format(
(float) $paquete['precio'],
2,
',',
'.'
) ?> €
</p>
<a
class="boton"
href="comprar_paquete.php?id=<?= (int)
$paquete['id_tipo_paquete'] ?>"
>
Comprar este paquete
</a>
</div>
</article>
<?php endwhile; ?>
</div>
<?php endif; ?>
</main>
</body>
</html>
