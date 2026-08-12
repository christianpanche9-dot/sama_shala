<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
$sql = "
SELECT
id_tipo_bono,
nombre,
numero_usos,
precio,
dias_validez
FROM tipos_bono
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
<title>Bonos | Sama Shala</title>
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
<h1>Bonos de clases</h1>
<p>
Compra un bono y utilízalo para reservar
las sesiones que quieras mientras tenga
usos disponibles.
</p>
</div>
</div>
<?php if ($resultado->num_rows === 0): ?>
<div class="mensaje mensaje-aviso">
No hay bonos disponibles en este momento.
</div>
<?php else: ?>
<div class="rejilla-actividades">
<?php while (
$bono = $resultado->fetch_assoc()
): ?>
<article class="tarjeta-actividad">
<div class="contenido-tarjeta">
<h2>
<?= escapar($bono['nombre']) ?>
</h2>
<p class="dato-destacado">
<?= (int) $bono['numero_usos'] ?> clases
</p>
<p>
<?php if ($bono['dias_validez'] !== null): ?>
Válido durante <?= (int) $bono['dias_validez'] ?> días desde la compra.
<?php else: ?>
Sin caducidad.
<?php endif; ?>
</p>
<p class="numero-plazas">
<?= number_format(
(float) $bono['precio'],
2,
',',
'.'
) ?> €
</p>
<a
class="boton"
href="comprar_bono.php?id=<?= (int)
$bono['id_tipo_bono'] ?>"
>
Comprar este bono
</a>
</div>
</article>
<?php endwhile; ?>
</div>
<?php endif; ?>
</main>
</body>
</html>
