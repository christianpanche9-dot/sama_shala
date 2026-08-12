<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../funciones.php';
$sql = "
SELECT
bc.id_bono_cliente,
bc.fecha_compra,
bc.precio_pagado,
bc.metodo_pago,
bc.referencia_pago,
bc.estado,
tb.nombre AS nombre_bono,
CONCAT(u.nombre, ' ', u.apellidos) AS cliente
FROM bonos_clientes bc
INNER JOIN tipos_bono tb
ON bc.id_tipo_bono = tb.id_tipo_bono
INNER JOIN usuarios u
ON bc.id_usuario = u.id_usuario
ORDER BY bc.fecha_compra DESC
";
$resultado = $conexion->query($sql);
$sql_total = "
SELECT COALESCE(SUM(precio_pagado), 0) AS total
FROM bonos_clientes
";
$total_ventas = (float) $conexion
->query($sql_total)
->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title>Pagos | Administración</title>
<link rel="stylesheet" href="../estilos.css">
</head>
<body>
<?php require_once __DIR__ . '/menu_admin.php'; ?>
<main class="contenedor seccion">
<div class="encabezado-pagina">
<div>
<p class="etiqueta">
Administración
</p>
<h1>Pagos</h1>
<p>
Total ingresado (simulado):
<strong>
<?= number_format($total_ventas, 2, ',', '.') ?> €
</strong>
</p>
</div>
</div>
<?php if ($resultado->num_rows === 0): ?>
<div class="mensaje mensaje-aviso">
Todavía no se ha comprado ningún bono.
</div>
<?php else: ?>
<div class="tabla-responsive">
<table class="tabla-admin">
<thead>
<tr>
<th>Fecha</th>
<th>Cliente</th>
<th>Bono</th>
<th>Importe</th>
<th>Método</th>
<th>Referencia</th>
<th>Estado del bono</th>
</tr>
</thead>
<tbody>
<?php while ($pago = $resultado->fetch_assoc()): ?>
<tr>
<td>
<?= date(
'd/m/Y H:i',
strtotime($pago['fecha_compra'])
) ?>
</td>
<td>
<?= escapar($pago['cliente']) ?>
</td>
<td>
<?= escapar($pago['nombre_bono']) ?>
</td>
<td>
<?= number_format(
(float) $pago['precio_pagado'],
2,
',',
'.'
) ?> €
</td>
<td>
<?= escapar(ucfirst($pago['metodo_pago'])) ?>
</td>
<td>
<?= escapar($pago['referencia_pago']) ?>
</td>
<td>
<?= escapar(ucfirst($pago['estado'])) ?>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</main>
</body>
</html>
