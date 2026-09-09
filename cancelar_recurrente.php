<?php
require_once "seguridad.php";
require_once "conexion.php";
require_once "funciones.php";
require_once "funciones_reservas.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
header("Location: mis_reservas.php");
exit;
}

$id_recurrente = filter_var(
$_POST["id_recurrente"] ?? null,
FILTER_VALIDATE_INT
);
$id_usuario = idUsuarioActual();

if (!$id_recurrente || !$id_usuario) {
header("Location: mis_reservas.php");
exit;
}

$sql_verificar = "
SELECT id_recurrente
FROM reservas_recurrentes
WHERE id_recurrente = ?
AND id_usuario = ?
";
$stmt_verificar = $conexion->prepare($sql_verificar);
$stmt_verificar->bind_param("ii", $id_recurrente, $id_usuario);
$stmt_verificar->execute();
$pertenece = $stmt_verificar->get_result()->fetch_assoc();
$stmt_verificar->close();

if (!$pertenece) {
header("Location: mis_reservas.php");
exit;
}

$sql_marcar = "
UPDATE reservas_recurrentes
SET estado = 'cancelada'
WHERE id_recurrente = ?
";
$stmt_marcar = $conexion->prepare($sql_marcar);
$stmt_marcar->bind_param("i", $id_recurrente);
$stmt_marcar->execute();
$stmt_marcar->close();

$sql_futuras = "
SELECT r.id_reserva
FROM reservas r
INNER JOIN sesiones s ON r.id_sesion = s.id_sesion
WHERE r.id_recurrente = ?
AND r.estado IN ('confirmada', 'pre_reserva')
AND CONCAT(s.fecha, ' ', s.hora_inicio) > NOW()
";
$stmt_futuras = $conexion->prepare($sql_futuras);
$stmt_futuras->bind_param("i", $id_recurrente);
$stmt_futuras->execute();
$reservas_futuras = array_column(
$stmt_futuras->get_result()->fetch_all(MYSQLI_ASSOC),
"id_reserva"
);
$stmt_futuras->close();

foreach ($reservas_futuras as $id_reserva) {
try {
cancelarReservaYPromocionar($conexion, $id_reserva, $id_usuario);
} catch (Throwable $error) {
continue;
}
}

header("Location: mis_reservas.php?mensaje=recurrente_cancelada");
exit;
