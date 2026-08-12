<?php
require_once __DIR__ . '/seguridad.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
$id_usuario = idUsuarioActual();
$sql = "
SELECT
bc.id_bono_cliente,
bc.fecha_compra,
bc.fecha_caducidad,
bc.usos_iniciales,
bc.usos_disponibles,
bc.precio_pagado,
bc.referencia_pago,
bc.estado,
tb.nombre AS nombre_bono
FROM bonos_clientes bc
INNER JOIN tipos_bono tb
ON bc.id_tipo_bono = tb.id_tipo_bono
WHERE bc.id_usuario = ?
ORDER BY bc.fecha_compra DESC
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$bonos = $stmt->get_result();
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
<title>Mis bonos | Sama Shala</title>
<link rel="stylesheet" href="estilos.css">
</head>
<body>
<?php require_once __DIR__ . '/menu.php'; ?>
<main class="contenedor">
<h1>Mis bonos</h1>
<?php if ($mensaje === 'comprado'): ?>
<div class="mensaje mensaje-exito">
Bono comprado correctamente. Ya puedes
usarlo al reservar una sesión.
</div>
<?php endif; ?>
<p>
<a class="boton boton-secundario" href="bonos.php">
Comprar un nuevo bono
</a>
</p>
<?php if ($bonos->num_rows === 0): ?>
<p>Todavía no tienes ningún bono.</p>
<?php else: ?>
<div class="rejilla-reservas">
<?php while ($bono = $bonos->fetch_assoc()): ?>
<?php
$caducado =
$bono['fecha_caducidad'] !== null &&
strtotime($bono['fecha_caducidad']) < strtotime('today');
$agotado = (int) $bono['usos_disponibles'] <= 0;
?>
<article class="tarjeta-reserva">
<h3>
<?= escapar($bono['nombre_bono']) ?>
</h3>
<p>
<strong>Usos disponibles:</strong>
<?= (int) $bono['usos_disponibles'] ?> de <?= (int) $bono['usos_iniciales'] ?>
</p>
<p>
<strong>Comprado:</strong>
<?= date(
'd/m/Y',
strtotime($bono['fecha_compra'])
) ?>
</p>
<p>
<strong>Caduca:</strong>
<?= $bono['fecha_caducidad'] !== null
? date(
'd/m/Y',
strtotime($bono['fecha_caducidad'])
)
: 'Sin caducidad' ?>
</p>
<p>
<strong>Estado:</strong>
<?php if ($bono['estado'] === 'cancelado'): ?>
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
Ref. pago: <?= escapar($bono['referencia_pago']) ?> ·
<?= number_format(
(float) $bono['precio_pagado'],
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
