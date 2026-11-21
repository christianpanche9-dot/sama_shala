<?php
require_once __DIR__ . '/seguridad.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
require_once __DIR__ . '/funciones_reservas.php';
require_once __DIR__ . '/funciones_payphone.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: paquetes.php');
exit;
}
validarCsrf();
$id_tipo_paquete = filter_var(
$_POST['id_tipo_paquete'] ?? null,
FILTER_VALIDATE_INT
);
$codigo_regalo = trim($_POST['codigo_regalo'] ?? '');
$metodo_pago_compra = $codigo_regalo !== '' ? 'payphone' : trim($_POST['metodo_pago_compra'] ?? 'payphone');
$es_transferencia = $metodo_pago_compra === 'transferencia';
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
nombre,
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
$precio_paquete = (float) $tipo_paquete['precio'];
$precio_a_cobrar = $precio_paquete;
$monto_tarjeta_regalo = null;
$id_tarjeta_regalo_usada = null;
if ($codigo_regalo !== '') {
$resultado_regalo = aplicarTarjetaRegalo($conexion, $codigo_regalo, $precio_paquete);
if (!$resultado_regalo['ok']) {
throw new Exception($resultado_regalo['error']);
}
$monto_tarjeta_regalo = $resultado_regalo['monto_cubierto'];
$id_tarjeta_regalo_usada = $resultado_regalo['id_tarjeta_regalo'];
$precio_a_cobrar = $resultado_regalo['monto_pendiente'];
}
$pagado_con_regalo_completo = $precio_a_cobrar <= 0;
if ($pagado_con_regalo_completo) {
$metodo_pago = 'tarjeta_regalo';
$referencia_pago = 'REGALOPAGO-' . strtoupper(bin2hex(random_bytes(6)));
$estado_inicial = 'activo';
} else {
$metodo_pago = $es_transferencia ? 'transferencia' : 'payphone';
$referencia_pago = $es_transferencia
? ('TRANSF-' . strtoupper(bin2hex(random_bytes(8))))
: generarReferenciaPayphone();
$estado_inicial = 'pendiente';
}
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
monto_tarjeta_regalo,
id_tarjeta_regalo_usada,
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
?,
?,
?
)
";
$stmt_compra = $conexion->prepare($sql_compra);
$stmt_compra->bind_param(
'iisiidsssdis',
$id_usuario,
$id_tipo_paquete,
$fecha_caducidad,
$numero_usos,
$numero_usos,
$precio_paquete,
$metodo_pago,
$referencia_pago,
$comprobante_pago,
$monto_tarjeta_regalo,
$id_tarjeta_regalo_usada,
$estado_inicial
);
$stmt_compra->execute();
$id_paquete_cliente_nuevo = $conexion->insert_id;
$stmt_compra->close();
$conexion->commit();

if ($pagado_con_regalo_completo) {
notificarPagoAdmin(
'Paquete pagado con tarjeta de regalo | Sama Shala',
'Un cliente activó un paquete usando el saldo de una tarjeta de regalo.',
'Paquete',
$referencia_pago,
$precio_paquete,
nombreClienteActual($conexion, $id_usuario),
$tipo_paquete['nombre']
);
header('Location: mis_paquetes.php?mensaje=comprado');
exit;
}

if (!$es_transferencia) {
$resultado_pago = payphonePrepararPago(
$precio_a_cobrar,
$referencia_pago,
'Paquete: ' . $tipo_paquete['nombre']
);
if (!$resultado_pago['ok']) {
$sql_cancelar = "
UPDATE paquetes_clientes
SET estado = 'cancelado'
WHERE id_paquete_cliente = ?
AND estado = 'pendiente'
";
$stmt_cancelar = $conexion->prepare($sql_cancelar);
$stmt_cancelar->bind_param('i', $id_paquete_cliente_nuevo);
$stmt_cancelar->execute();
$stmt_cancelar->close();
header(
'Location: comprar_paquete.php?id=' .
$id_tipo_paquete .
'&error=pago'
);
exit;
}
header('Location: ' . $resultado_pago['url_pago']);
exit;
}

notificarPagoAdmin(
'Nuevo comprobante de transferencia (Paquete) | Sama Shala',
'Un cliente subió un comprobante de transferencia bancaria, pendiente de revisión.',
'Paquete',
$referencia_pago,
(float) $tipo_paquete['precio'],
nombreClienteActual($conexion, $id_usuario),
$tipo_paquete['nombre']
);

header(
'Location: mis_paquetes.php?mensaje=comprado'
);
exit;
} catch (Throwable $error) {
$conexion->rollback();
header(
'Location: comprar_paquete.php?id=' .
$id_tipo_paquete .
'&error=' . urlencode($error->getMessage())
);
exit;
}
