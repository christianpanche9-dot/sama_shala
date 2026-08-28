<?php
require_once __DIR__ . '/funciones.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: tienda.php');
exit;
}
$clave = trim($_POST['clave'] ?? '');
if (isset($_SESSION['carrito'][$clave])) {
unset($_SESSION['carrito'][$clave]);
}
$volver = trim($_POST['volver'] ?? 'tienda.php');
$destinos_permitidos = ['tienda.php', 'comprar_producto.php'];
if (!in_array($volver, $destinos_permitidos, true)) {
$volver = 'tienda.php';
}
header("Location: $volver#carrito");
exit;
