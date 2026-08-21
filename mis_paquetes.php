<?php
require_once __DIR__ . '/seguridad.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
$id_usuario = idUsuarioActual();
$sql = "
SELECT
bc.id_paquete_cliente,
bc.fecha_compra,
bc.fecha_caducidad,
bc.usos_iniciales,
bc.usos_disponibles,
bc.precio_pagado,
bc.referencia_pago,
bc.estado,
tb.nombre AS nombre_paquete
FROM paquetes_clientes bc
INNER JOIN tipos_paquete tb
ON bc.id_tipo_paquete = tb.id_tipo_paquete
WHERE bc.id_usuario = ?
ORDER BY bc.fecha_compra DESC
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$paquetes = $stmt->get_result();
$mensaje = $_GET['mensaje'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title>Mis paquetes | Sama Shala</title>
<link rel="stylesheet" href="estilos.css">
</head>
<body>
<?php require_once __DIR__ . '/menu.php'; ?>
<main class="contenedor">
<h1>Mis paquetes</h1>
<?php if ($mensaje === 'comprado'): ?>
<div class="mensaje mensaje-exito">
Paquete comprado correctamente. Ya puedes
usarlo al reservar una sesión.
</div>
<?php endif; ?>
<p>
<a class="boton boton-secundario" href="paquetes.php">
Comprar un nuevo paquete
</a>
</p>
<?php if ($paquetes->num_rows === 0): ?>
<p>Todavía no tienes ningún paquete.</p>
<?php else: ?>
<div class="rejilla-reservas">
<?php while ($paquete = $paquetes->fetch_assoc()): ?>
<?php
$caducado =
$paquete['fecha_caducidad'] !== null &&
strtotime($paquete['fecha_caducidad']) < strtotime('today');
$agotado = (int) $paquete['usos_disponibles'] <= 0;
?>
<article class="tarjeta-reserva">
<h3>
<?= escapar($paquete['nombre_paquete']) ?>
</h3>
<p>
<strong>Usos disponibles:</strong>
<?= (int) $paquete['usos_disponibles'] ?> de <?= (int) $paquete['usos_iniciales'] ?>
</p>
<p>
<strong>Comprado:</strong>
<?= date(
'd/m/Y',
strtotime($paquete['fecha_compra'])
) ?>
</p>
<p>
<strong>Caduca:</strong>
<?= $paquete['fecha_caducidad'] !== null
? date(
'd/m/Y',
strtotime($paquete['fecha_caducidad'])
)
: 'Sin caducidad' ?>
</p>
<p>
<strong>Estado:</strong>
<?php if ($paquete['estado'] === 'cancelado'): ?>
Cancelado
<?php elseif ($caducado): ?>
Caducado
<?php elseif ($agotado): ?>
Agotado
<?php else: ?>
Activo
<?php endif; ?>
</p>
<p class="codigo-reserva">
Ref. pago: <?= escapar($paquete['referencia_pago']) ?> ·
<?= number_format(
(float) $paquete['precio_pagado'],
2,
',',
'.'
) ?> €
</p>
</article>
<?php endwhile; ?>
</div>
<?php endif; ?>
</main>
</body>
</html>
