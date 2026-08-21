<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../funciones.php';
$sql = "
SELECT
bc.id_paquete_cliente,
bc.fecha_compra,
bc.precio_pagado,
bc.metodo_pago,
bc.referencia_pago,
bc.estado,
tb.nombre AS nombre_paquete,
CONCAT(u.nombre, ' ', u.apellidos) AS cliente
FROM paquetes_clientes bc
INNER JOIN tipos_paquete tb
ON bc.id_tipo_paquete = tb.id_tipo_paquete
INNER JOIN usuarios u
ON bc.id_usuario = u.id_usuario
ORDER BY bc.fecha_compra DESC
";
$resultado = $conexion->query($sql);
$sql_total = "
SELECT COALESCE(SUM(precio_pagado), 0) AS total
FROM paquetes_clientes
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
<?= formatear_precio($total_ventas) ?>
</strong>
</p>
</div>
</div>
<?php if ($resultado->num_rows === 0): ?>
<div class="mensaje mensaje-aviso">
Todavía no se ha comprado ningún paquete.
</div>
<?php else: ?>
<div class="tabla-responsive">
<table class="tabla-admin">
<thead>
<tr>
<th>Fecha</th>
<th>Cliente</th>
<th>Paquete</th>
<th>Importe</th>
<th>Método</th>
<th>Referencia</th>
<th>Estado del paquete</th>
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
<?= escapar($pago['nombre_paquete']) ?>
</td>
<td>
<?= formatear_precio(
(float) $pago['precio_pagado']
) ?>
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
