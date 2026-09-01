<?php
require_once __DIR__ . '/seguridad.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: paquetes.php');
exit;
}
$id_tipo_paquete = filter_var(
$_POST['id_tipo_paquete'] ?? null,
FILTER_VALIDATE_INT
);
$metodo_pago_compra = trim($_POST['metodo_pago_compra'] ?? 'simulado');
$es_transferencia = $metodo_pago_compra === 'transferencia';
$titular = trim($_POST['titular'] ?? '');
$tarjeta = trim($_POST['tarjeta'] ?? '');
$id_usuario = idUsuarioActual();
$id_tenant = idTenantActual();
if (!$id_tipo_paquete) {
header(
'Location: comprar_paquete.php?id=' .
$id_tipo_paquete .
'&error=pago'
);
exit;
}
if (!$es_transferencia && ($titular === '' || $tarjeta === '')) {
header(
'Location: comprar_paquete.php?id=' .
$id_tipo_paquete .
'&error=pago'
);
exit;
}
$comprobante_pago = null;
if ($es_transferencia) {
$resultado_comprobante = procesar_imagen_subida(
'comprobante_pago',
__DIR__ . '/imagenes/comprobantes',
'comprobante'
);
if (!$resultado_comprobante['ok'] || $resultado_comprobante['archivo'] === null) {
header(
'Location: comprar_paquete.php?id=' .
$id_tipo_paquete .
'&error=pago'
);
exit;
}
$comprobante_pago = $resultado_comprobante['archivo'];
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
$referencia_pago = ($es_transferencia ? 'TRANSF-' : 'SIM-') . strtoupper(
bin2hex(random_bytes(8))
);
$metodo_pago = $es_transferencia ? 'transferencia' : 'simulado';
$estado = $es_transferencia ? 'pendiente' : 'activo';
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
comprobante_pago,
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
?,
?,
?,
?
)
";
$stmt_compra = $conexion->prepare($sql_compra);
$stmt_compra->bind_param(
'iisiidssss',
$id_usuario,
$id_tipo_paquete,
$fecha_caducidad,
$numero_usos,
$numero_usos,
$tipo_paquete['precio'],
$metodo_pago,
$referencia_pago,
$comprobante_pago,
$estado
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
