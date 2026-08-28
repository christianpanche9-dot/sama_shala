<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../funciones.php';
$sql = "
SELECT
fecha_pago,
cliente,
concepto,
tipo_registro,
precio_pagado,
metodo_pago,
referencia_pago,
estado
FROM (
SELECT
bc.fecha_compra AS fecha_pago,
CONCAT(u.nombre, ' ', u.apellidos) AS cliente,
tb.nombre AS concepto,
'Paquete' AS tipo_registro,
bc.precio_pagado,
bc.metodo_pago,
bc.referencia_pago,
bc.estado
FROM paquetes_clientes bc
INNER JOIN tipos_paquete tb
ON bc.id_tipo_paquete = tb.id_tipo_paquete
INNER JOIN usuarios u
ON bc.id_usuario = u.id_usuario

UNION ALL

SELECT
r.fecha_reserva AS fecha_pago,
CONCAT(u.nombre, ' ', u.apellidos) AS cliente,
a.nombre AS concepto,
CASE a.tipo
WHEN 'terapia' THEN 'Terapia'
WHEN 'taller' THEN 'Taller'
ELSE 'Evento'
END AS tipo_registro,
r.precio_pagado,
r.metodo_pago,
r.referencia_pago,
r.estado
FROM reservas r
INNER JOIN sesiones s ON r.id_sesion = s.id_sesion
INNER JOIN actividades a ON s.id_actividad = a.id_actividad
INNER JOIN usuarios u ON r.id_usuario = u.id_usuario
WHERE r.tipo_pago = 'evento'

UNION ALL

SELECT
cp.fecha_compra AS fecha_pago,
CONCAT(u.nombre, ' ', u.apellidos) AS cliente,
p.nombre AS concepto,
'Producto' AS tipo_registro,
cp.precio_pagado,
cp.metodo_pago,
cp.referencia_pago,
cp.estado
FROM compras_productos cp
INNER JOIN productos p ON cp.id_producto = p.id_producto
INNER JOIN usuarios u ON cp.id_usuario = u.id_usuario
) AS pagos_combinados
ORDER BY fecha_pago DESC
";
$resultado = $conexion->query($sql);
$pagos = $resultado->fetch_all(MYSQLI_ASSOC);
$pagos_por_mes = [];
foreach ($pagos as $pago) {
$clave_mes = substr($pago['fecha_pago'], 0, 7);
if (!isset($pagos_por_mes[$clave_mes])) {
$pagos_por_mes[$clave_mes] = [];
}
$pagos_por_mes[$clave_mes][] = $pago;
}
$mes_actual = date('Y-m');
$primer_mes = array_key_first($pagos_por_mes);
$sql_total = "
SELECT
(SELECT COALESCE(SUM(precio_pagado), 0) FROM paquetes_clientes) +
(SELECT COALESCE(SUM(precio_pagado), 0) FROM reservas WHERE tipo_pago = 'evento') +
(SELECT COALESCE(SUM(precio_pagado), 0) FROM compras_productos)
AS total
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
<link rel="stylesheet" href="<?= urlEstilos('../') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="../imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="../imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="../imagenes/favicon.ico">
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
<?php if (count($pagos) === 0): ?>
<div class="mensaje mensaje-aviso">
Todavía no se ha registrado ningún pago.
</div>
<?php else: ?>
<?php foreach ($pagos_por_mes as $clave_mes => $pagos_del_mes): ?>
<?php $fecha_mes = DateTime::createFromFormat('Y-m-d', $clave_mes . '-01'); ?>
<details class="grupo-mes-admin" <?= ($clave_mes === $mes_actual || $clave_mes === $primer_mes) ? 'open' : '' ?>>
<summary class="resumen-mes-admin">
<span>
<?= escapar(texto_mes((int) $fecha_mes->format('n'))) ?> <?= $fecha_mes->format('Y') ?>
</span>
<span class="contador-mes-admin">
<?= count($pagos_del_mes) ?> <?= count($pagos_del_mes) === 1 ? 'pago' : 'pagos' ?>
</span>
</summary>
<div class="tabla-responsive">
<table class="tabla-admin">
<thead>
<tr>
<th>Fecha</th>
<th>Cliente</th>
<th>Tipo</th>
<th>Concepto</th>
<th>Importe</th>
<th>Método</th>
<th>Referencia</th>
<th>Estado</th>
</tr>
</thead>
<tbody>
<?php foreach ($pagos_del_mes as $pago): ?>
<tr>
<td>
<?= date(
'd/m/Y H:i',
strtotime($pago['fecha_pago'])
) ?>
</td>
<td>
<?= escapar($pago['cliente']) ?>
</td>
<td>
<?= escapar($pago['tipo_registro']) ?>
</td>
<td>
<?= escapar($pago['concepto']) ?>
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
<?php endforeach; ?>
</tbody>
</table>
</div>
</details>
<?php endforeach; ?>
<?php endif; ?>
</main>
</body>
</html>
