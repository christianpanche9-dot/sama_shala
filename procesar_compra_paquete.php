<?php
require_once __DIR__ . '/seguridad.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: paquetes.php');
exit;
}
$id_tipo_paquete = filter_input(
INPUT_POST,
'id_tipo_paquete',
FILTER_VALIDATE_INT
);
$titular = trim($_POST['titular'] ?? '');
$tarjeta = trim($_POST['tarjeta'] ?? '');
$id_usuario = idUsuarioActual();
$id_tenant = idTenantActual();
if (!$id_tipo_paquete || $titular === '' || $tarjeta === '') {
header(
'Location: comprar_paquete.php?id=' .
$id_tipo_paquete .
'&error=pago'
);
exit;
}
try {
$conexion->begin_transaction();
$sql_paquete = "
SELECT
id_tipo_paquete,
numero_usos,
precio
FROM tipos_paquete
WHERE id_tipo_paquete = ?
AND activo = 1
AND id_tenant = ?
FOR UPDATE
";
$stmt_paquete = $conexion->prepare($sql_paquete);
$stmt_paquete->bind_param(
'ii',
$id_tipo_paquete,
$id_tenant
);
$stmt_paquete->execute();
$tipo_paquete = $stmt_paquete->get_result()->fetch_assoc();
$stmt_paquete->close();
if (!$tipo_paquete) {
throw new Exception('El paquete seleccionado no existe.');
}
$referencia_pago = 'SIM-' . strtoupper(
bin2hex(random_bytes(8))
);
$numero_usos = (int) $tipo_paquete['numero_usos'];
$fecha_caducidad = date('Y-m-d', strtotime('+1 month'));
$sql_compra = "
INSERT INTO paquetes_clientes (
id_usuario,
id_tipo_paquete,
fecha_compra,
fecha_caducidad,
usos_iniciales,
usos_disponibles,
precio_pagado,
metodo_pago,
referencia_pago,
estado
)
VALUES (
?,
?,
NOW(),
?,
?,
?,
?,
'simulado',
?,
'activo'
)
";
$stmt_compra = $conexion->prepare($sql_compra);
$stmt_compra->bind_param(
'iisiids',
$id_usuario,
$id_tipo_paquete,
$fecha_caducidad,
$numero_usos,
$numero_usos,
$tipo_paquete['precio'],
$referencia_pago
);
$stmt_compra->execute();
$stmt_compra->close();
$conexion->commit();
header(
'Location: mis_paquetes.php?mensaje=comprado'
);
exit;
} catch (Throwable $error) {
$conexion->rollback();
header(
'Location: comprar_paquete.php?id=' .
$id_tipo_paquete .
'&error=pago'
);
exit;
}
