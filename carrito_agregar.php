<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: tienda.php');
exit;
}
$id_producto = filter_var(
$_POST['id_producto'] ?? '',
FILTER_VALIDATE_INT
);
$cantidad = filter_var(
$_POST['cantidad'] ?? '',
FILTER_VALIDATE_INT,
[
'options' => [
'min_range' => 1,
'max_range' => 99
]
]
);
$talla = trim($_POST['talla'] ?? '');
$volver = trim($_POST['volver'] ?? 'tienda.php');
$destinos_permitidos = ['tienda.php', 'comprar_producto.php'];
if (!in_array($volver, $destinos_permitidos, true)) {
$volver = 'tienda.php';
}
if (!$id_producto || !$cantidad) {
header("Location: $volver");
exit;
}
$sql = "
SELECT id_producto, tallas
FROM productos
WHERE id_producto = ?
AND activo = 1
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_producto);
$stmt->execute();
$producto = $stmt->get_result()->fetch_assoc();
if (!$producto) {
header('Location: tienda.php');
exit;
}
$tallas_producto = [];
if (!empty($producto['tallas'])) {
$tallas_producto = array_filter(array_map('trim', explode(',', $producto['tallas'])));
}
if (!empty($tallas_producto) && !in_array($talla, $tallas_producto, true)) {
header("Location: detalle_producto.php?id=$id_producto&error=talla");
exit;
}
$talla_guardada = !empty($tallas_producto) ? $talla : null;
if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
$_SESSION['carrito'] = [];
}
$clave = $id_producto . '|' . ($talla_guardada ?? '');
if (!isset($_SESSION['carrito'][$clave])) {
$_SESSION['carrito'][$clave] = [
'id_producto' => $id_producto,
'talla' => $talla_guardada,
'cantidad' => 0
];
}
$_SESSION['carrito'][$clave]['cantidad'] += $cantidad;
header("Location: $volver#carrito");
exit;
