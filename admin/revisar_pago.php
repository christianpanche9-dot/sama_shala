<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../funciones.php';
require_once __DIR__ . '/../funciones_reservas.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: pagos.php');
exit;
}

$tipo_origen = trim($_POST['tipo_origen'] ?? '');
$id_registro = filter_var($_POST['id_registro'] ?? null, FILTER_VALIDATE_INT);
$accion = trim($_POST['accion'] ?? '');

$tipos_permitidos = ['paquete', 'evento', 'producto'];
$acciones_permitidas = ['aprobar', 'rechazar'];

if (
!$id_registro ||
!in_array($tipo_origen, $tipos_permitidos, true) ||
!in_array($accion, $acciones_permitidas, true)
) {
header('Location: pagos.php?mensaje=error');
exit;
}

try {
if ($tipo_origen === 'paquete') {
$nuevo_estado = $accion === 'aprobar' ? 'activo' : 'cancelado';
$sql = "
UPDATE paquetes_clientes
SET estado = ?
WHERE id_paquete_cliente = ?
AND estado = 'pendiente'
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('si', $nuevo_estado, $id_registro);
$stmt->execute();
if ($stmt->affected_rows === 0) {
throw new Exception('No se ha podido actualizar el paquete.');
}
$stmt->close();
} elseif ($tipo_origen === 'producto') {
$nuevo_estado = $accion === 'aprobar' ? 'pagado' : 'rechazado';
$sql = "
UPDATE compras_productos
SET estado = ?
WHERE id_compra = ?
AND estado = 'pendiente'
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('si', $nuevo_estado, $id_registro);
$stmt->execute();
if ($stmt->affected_rows === 0) {
throw new Exception('No se ha podido actualizar la compra.');
}
$stmt->close();
} else {
$sql_estado_pago = "
SELECT estado, estado_pago
FROM reservas
WHERE id_reserva = ?
";
$stmt_estado_pago = $conexion->prepare($sql_estado_pago);
$stmt_estado_pago->bind_param('i', $id_registro);
$stmt_estado_pago->execute();
$reserva = $stmt_estado_pago->get_result()->fetch_assoc();
$stmt_estado_pago->close();
if (
!$reserva ||
$reserva['estado_pago'] !== 'pendiente' ||
$reserva['estado'] !== 'confirmada'
) {
throw new Exception('La reserva no está pendiente de revisión.');
}
if ($accion === 'aprobar') {
$sql = "
UPDATE reservas
SET estado_pago = 'pagado'
WHERE id_reserva = ?
AND estado_pago = 'pendiente'
AND estado = 'confirmada'
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_registro);
$stmt->execute();
if ($stmt->affected_rows === 0) {
throw new Exception('No se ha podido actualizar la reserva.');
}
$stmt->close();
} else {
cancelarReservaYPromocionar($conexion, $id_registro);
}
}
header(
'Location: pagos.php?mensaje=' .
($accion === 'aprobar' ? 'aprobado' : 'rechazado')
);
exit;
} catch (Throwable $error) {
header('Location: pagos.php?mensaje=error');
exit;
}
