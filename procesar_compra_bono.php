<?php
require_once __DIR__ . '/seguridad.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: bonos.php');
exit;
}
$id_tipo_bono = filter_input(
INPUT_POST,
'id_tipo_bono',
FILTER_VALIDATE_INT
);
$titular = trim($_POST['titular'] ?? '');
$tarjeta = trim($_POST['tarjeta'] ?? '');
$id_usuario = idUsuarioActual();
$id_tenant = idTenantActual();
if (!$id_tipo_bono || $titular === '' || $tarjeta === '') {
header(
'Location: comprar_bono.php?id=' .
$id_tipo_bono .
'&error=pago'
);
exit;
}
try {
$conexion->begin_transaction();
$sql_bono = "
SELECT
id_tipo_bono,
numero_usos,
precio,
dias_validez
FROM tipos_bono
WHERE id_tipo_bono = ?
AND activo = 1
AND id_tenant = ?
FOR UPDATE
";
$stmt_bono = $conexion->prepare($sql_bono);
$stmt_bono->bind_param(
'ii',
$id_tipo_bono,
$id_tenant
);
$stmt_bono->execute();
$tipo_bono = $stmt_bono->get_result()->fetch_assoc();
$stmt_bono->close();
if (!$tipo_bono) {
throw new Exception('El bono seleccionado no existe.');
}
$referencia_pago = 'SIM-' . strtoupper(
bin2hex(random_bytes(8))
);
$numero_usos = (int) $tipo_bono['numero_usos'];
$fecha_caducidad = $tipo_bono['dias_validez'] !== null
? date(
'Y-m-d',
strtotime(
'+' . (int) $tipo_bono['dias_validez'] . ' days'
)
)
: null;
$sql_compra = "
INSERT INTO bonos_clientes (
id_usuario,
id_tipo_bono,
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
$id_tipo_bono,
$fecha_caducidad,
$numero_usos,
$numero_usos,
$tipo_bono['precio'],
$referencia_pago
);
$stmt_compra->execute();
$stmt_compra->close();
$conexion->commit();
header(
'Location: mis_bonos.php?mensaje=comprado'
);
exit;
} catch (Throwable $error) {
$conexion->rollback();
header(
'Location: comprar_bono.php?id=' .
$id_tipo_bono .
'&error=pago'
);
exit;
}
