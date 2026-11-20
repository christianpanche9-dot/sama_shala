<?php
require_once "seguridad_admin.php";
require_once "../conexion.php";
require_once "../funciones.php";
require_once "../funciones_reservas.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
header("Location: sesiones.php");
exit;
}
validarCsrf();
$id_sesion = filter_var(
$_POST["id_sesion"] ?? null,
FILTER_VALIDATE_INT
);
$id_usuario = filter_var(
$_POST["id_usuario"] ?? null,
FILTER_VALIDATE_INT
);
if (!$id_sesion || !$id_usuario) {
header("Location: sesiones.php?error=datos");
exit;
}
$cantidad = filter_var(
$_POST["cantidad"] ?? 1,
FILTER_VALIDATE_INT
);
$cantidad = $cantidad ? max(1, min($cantidad, LIMITE_PLAZAS_POR_RESERVA)) : 1;
$precio_pagado = filter_var(
$_POST["precio_pagado"] ?? "",
FILTER_VALIDATE_FLOAT
);
$precio_pagado = $precio_pagado !== false ? $precio_pagado : null;
try {
$conexion->begin_transaction();
$sql_sesion = "
SELECT
s.aforo,
s.estado,
a.tipo AS tipo_actividad
FROM sesiones s
INNER JOIN actividades a
ON s.id_actividad = a.id_actividad
WHERE s.id_sesion = ?
FOR UPDATE
";
$stmt_sesion = $conexion->prepare($sql_sesion);
$stmt_sesion->bind_param("i", $id_sesion);
$stmt_sesion->execute();
$sesion = $stmt_sesion->get_result()->fetch_assoc();
$stmt_sesion->close();
if (!$sesion || !in_array($sesion["estado"], ["programada", "completa"], true)) {
throw new Exception("La sesión no admite reservas.");
}
$sql_cliente = "SELECT id_usuario FROM usuarios WHERE id_usuario = ? AND rol = 'cliente'";
$stmt_cliente = $conexion->prepare($sql_cliente);
$stmt_cliente->bind_param("i", $id_usuario);
$stmt_cliente->execute();
$cliente_valido = $stmt_cliente->get_result()->fetch_assoc();
$stmt_cliente->close();
if (!$cliente_valido) {
throw new Exception("El cliente no es válido.");
}
$tipo_pago = $sesion["tipo_actividad"] === "clase" ? "suelta" : "evento";
if ($tipo_pago === "suelta") {
$cantidad = 1;
}
$sql_ocupacion = "
SELECT COALESCE(SUM(cantidad), 0) AS total
FROM reservas
WHERE id_sesion = ?
AND estado = 'confirmada'
";
$stmt_ocupacion = $conexion->prepare($sql_ocupacion);
$stmt_ocupacion->bind_param("i", $id_sesion);
$stmt_ocupacion->execute();
$plazas_ocupadas = (int) $stmt_ocupacion->get_result()->fetch_assoc()["total"];
$stmt_ocupacion->close();
if ($plazas_ocupadas + $cantidad > (int) $sesion["aforo"]) {
throw new Exception("No hay cupo suficiente.");
}
$codigo_reserva = generarCodigoReserva();
$referencia_pago = "EFECTIVO-" . strtoupper(bin2hex(random_bytes(6)));
$sql_guardar = "
INSERT INTO reservas (
id_sesion,
id_usuario,
tipo_pago,
cantidad,
precio_pagado,
metodo_pago,
referencia_pago,
estado_pago,
estado,
asistencia,
codigo_reserva
)
VALUES (
?,
?,
?,
?,
?,
'efectivo',
?,
'pagado',
'confirmada',
'pendiente',
?
)
";
$stmt_guardar = $conexion->prepare($sql_guardar);
$stmt_guardar->bind_param(
"iisidss",
$id_sesion,
$id_usuario,
$tipo_pago,
$cantidad,
$precio_pagado,
$referencia_pago,
$codigo_reserva
);
$stmt_guardar->execute();
$stmt_guardar->close();
$nuevo_total = $plazas_ocupadas + $cantidad;
$nuevo_estado = $nuevo_total >= (int) $sesion["aforo"] ? "completa" : "programada";
$sql_estado = "UPDATE sesiones SET estado = ? WHERE id_sesion = ?";
$stmt_estado = $conexion->prepare($sql_estado);
$stmt_estado->bind_param("si", $nuevo_estado, $id_sesion);
$stmt_estado->execute();
$stmt_estado->close();
$conexion->commit();
$sql_nombre_actividad = "
SELECT a.nombre
FROM sesiones s
INNER JOIN actividades a ON s.id_actividad = a.id_actividad
WHERE s.id_sesion = ?
";
$stmt_nombre_actividad = $conexion->prepare($sql_nombre_actividad);
$stmt_nombre_actividad->bind_param("i", $id_sesion);
$stmt_nombre_actividad->execute();
$nombre_actividad = $stmt_nombre_actividad->get_result()->fetch_assoc()["nombre"] ?? "Reserva";
$stmt_nombre_actividad->close();
notificarTelegram(
"<b>Reserva registrada en efectivo (admin)</b>\n" .
"Cliente: " . htmlspecialchars(nombreClienteActual($conexion, $id_usuario), ENT_QUOTES, 'UTF-8') . "\n" .
"Actividad: " . htmlspecialchars($nombre_actividad . ($cantidad > 1 ? " x{$cantidad}" : ""), ENT_QUOTES, 'UTF-8') . "\n" .
"Monto: " . ($precio_pagado !== null ? '$' . number_format($precio_pagado, 2) : "(no especificado)")
);
header("Location: detalles_sesion.php?id={$id_sesion}&mensaje=reserva_creada");
exit;
} catch (Throwable $error) {
$conexion->rollback();
header("Location: detalles_sesion.php?id={$id_sesion}&mensaje=error_reserva");
exit;
}
