<?php
require_once "conexion.php";
require_once "funciones.php";
require_once "funciones_reservas.php";
require_once "funciones_payphone.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
header("Location: sesiones.php");
exit;
}
validarCsrf();
$id_sesion = filter_var(
$_POST["id_sesion"] ?? null,
FILTER_VALIDATE_INT
);
if (!$id_sesion) {
header("Location: sesiones.php?error=datos");
exit;
}
$es_invitado = !usuarioAutenticado();
if ($es_invitado) {
if (trim($_POST["sitio_web"] ?? "") !== "") {
header("Location: sesiones.php");
exit;
}
$resultado_invitado = obtenerOCrearUsuarioInvitado(
$conexion,
trim($_POST["nombre"] ?? ""),
trim($_POST["apellidos"] ?? ""),
trim($_POST["email"] ?? ""),
trim($_POST["telefono"] ?? "")
);
if (!$resultado_invitado["ok"]) {
header(
"Location: detalle_sesion.php?id=" .
$id_sesion .
"&error=" .
urlencode($resultado_invitado["error"])
);
exit;
}
$id_usuario = $resultado_invitado["id_usuario"];
} else {
$id_usuario = idUsuarioActual();
}
if (!$id_usuario) {
header("Location: sesiones.php?error=datos");
exit;
}
$metodo_pago_enviado = $es_invitado ? "evento" : trim($_POST["metodo_pago"] ?? "suelta");
$codigo_regalo = trim($_POST["codigo_regalo"] ?? "");
$tipo_pago = "suelta";
$id_paquete_cliente = null;
$precio_pagado = null;
$metodo_pago_simulado = null;
$referencia_pago = null;
$estado_pago = "pagado";
$comprobante_pago = null;
$monto_tarjeta_regalo = null;
$id_tarjeta_regalo_usada = null;
$titular_pago = trim($_POST["titular"] ?? "");
$tarjeta_pago = trim($_POST["tarjeta"] ?? "");
$repetir_semanal = isset($_POST["repetir_semanal"]);
$cantidad = 1;
if (str_starts_with($metodo_pago_enviado, "paquete:")) {
$tipo_pago = "paquete";
$id_paquete_cliente = filter_var(
substr($metodo_pago_enviado, 8),
FILTER_VALIDATE_INT
);
if (!$id_paquete_cliente) {
header(
"Location: detalle_sesion.php?id=" .
$id_sesion .
"&error=" .
urlencode("El paquete seleccionado no es válido.")
);
exit;
}
} elseif ($metodo_pago_enviado === "evento") {
$tipo_pago = "evento";
$cantidad_enviada = filter_var(
$_POST["cantidad"] ?? null,
FILTER_VALIDATE_INT
);
if ($cantidad_enviada !== false && $cantidad_enviada !== null) {
$cantidad = $cantidad_enviada;
}
$cantidad = max(
1,
min($cantidad, LIMITE_PLAZAS_POR_RESERVA)
);
$metodo_pago_compra = ($es_invitado || $codigo_regalo !== "") ? "payphone" : trim($_POST["metodo_pago_compra"] ?? "simulado");
if ($metodo_pago_compra === "transferencia") {
$resultado_comprobante = procesar_imagen_subida(
"comprobante_pago",
__DIR__ . "/imagenes/comprobantes",
"comprobante"
);
if (!$resultado_comprobante["ok"] || $resultado_comprobante["archivo"] === null) {
header(
"Location: detalle_sesion.php?id=" .
$id_sesion .
"&error=" .
urlencode("Adjunta la foto del comprobante de la transferencia.")
);
exit;
}
$comprobante_pago = $resultado_comprobante["archivo"];
$estado_pago = "pendiente";
} elseif ($metodo_pago_compra === "payphone") {
$estado_pago = "pendiente";
}
}
try {
$conexion->begin_transaction();
/*
|--------------------------------------------------------------------------
| 1. Bloquear y consultar la sesión
|--------------------------------------------------------------------------
*/

$sql_sesion = "
SELECT
id_sesion,
id_actividad,
fecha,
hora_inicio,
aforo,
estado
FROM sesiones
WHERE id_sesion = ?
FOR UPDATE
";
$stmt_sesion = $conexion->prepare($sql_sesion);
$stmt_sesion->bind_param("i", $id_sesion);
$stmt_sesion->execute();
$resultado_sesion = $stmt_sesion->get_result();
$sesion = $resultado_sesion->fetch_assoc();
$stmt_sesion->close();
if (!$sesion) {
throw new Exception(t("La sesión no existe."));
}
$sql_actividad = "
SELECT tipo, precio
FROM actividades
WHERE id_actividad = ?
";
$stmt_actividad = $conexion->prepare($sql_actividad);
$stmt_actividad->bind_param("i", $sesion["id_actividad"]);
$stmt_actividad->execute();
$actividad = $stmt_actividad->get_result()->fetch_assoc();
$stmt_actividad->close();
if (
$es_invitado &&
$actividad &&
$actividad["tipo"] === "clase" &&
$actividad["precio"] === null
) {
$paquete_invitado_clase_suelta = obtenerPaqueteInvitadoClaseSuelta($conexion);
if ($paquete_invitado_clase_suelta !== null) {
$actividad["precio"] = (float) $paquete_invitado_clase_suelta["precio"];
}
}
if ($tipo_pago === "evento") {
if (
!$actividad ||
$actividad["precio"] === null
) {
throw new Exception(
t("Esta sesión no admite el pago con precio fijo.")
);
}
if (
$estado_pago !== "pendiente" &&
($titular_pago === "" || $tarjeta_pago === "")
) {
throw new Exception(
t("Introduce los datos de pago.")
);
}
$descuento_usuario = mejorDescuentoEventoTerapia(
$conexion,
$id_usuario
);
$precio_pagado = precio_total_evento_terapia(
(float) $actividad["precio"],
$cantidad,
$descuento_usuario !== null ? $descuento_usuario["descuento"] : 0
);
$precio_a_cobrar = $precio_pagado;
if ($codigo_regalo !== "") {
$resultado_regalo = aplicarTarjetaRegalo($conexion, $codigo_regalo, $precio_pagado);
if (!$resultado_regalo["ok"]) {
throw new Exception($resultado_regalo["error"]);
}
$monto_tarjeta_regalo = $resultado_regalo["monto_cubierto"];
$id_tarjeta_regalo_usada = $resultado_regalo["id_tarjeta_regalo"];
$precio_a_cobrar = $resultado_regalo["monto_pendiente"];
}
if ($precio_a_cobrar <= 0) {
$metodo_pago_simulado = "tarjeta_regalo";
$referencia_pago = "REGALOPAGO-" . strtoupper(bin2hex(random_bytes(6)));
$estado_pago = "pagado";
} elseif ($metodo_pago_compra === "transferencia") {
$metodo_pago_simulado = "transferencia";
$referencia_pago = "TRANSF-" . strtoupper(bin2hex(random_bytes(8)));
} elseif ($metodo_pago_compra === "payphone") {
$metodo_pago_simulado = "payphone";
$referencia_pago = generarReferenciaPayphone();
} else {
$metodo_pago_simulado = "simulado";
$referencia_pago = "SIM-" . strtoupper(bin2hex(random_bytes(8)));
}
}
if (
!in_array(
$sesion["estado"],
["programada", "completa"],
true
)
) {
throw new Exception(
t("La sesión no admite reservas.")
);
}
$inicio = new DateTime(
$sesion["fecha"] .
" " .
$sesion["hora_inicio"]
);
if ($inicio <= new DateTime()) {
throw new Exception(
t("La sesión ya ha comenzado.")
);
}
/*
|--------------------------------------------------------------------------
| 2. Comprobar si ya tiene una reserva
|--------------------------------------------------------------------------
*/

$sql_reserva = "
SELECT id_reserva, estado
FROM reservas
WHERE id_sesion = ?
AND id_usuario = ?
FOR UPDATE
";
$stmt_reserva = $conexion->prepare($sql_reserva);
$stmt_reserva->bind_param(
"ii",
$id_sesion,
$id_usuario
);
$stmt_reserva->execute();
$reserva_anterior =
$stmt_reserva->get_result()->fetch_assoc();
$stmt_reserva->close();
if (
$reserva_anterior &&
$reserva_anterior["estado"] === "confirmada"
) {
throw new Exception(
t("Ya tienes una reserva confirmada.")
);
}
/*
|--------------------------------------------------------------------------
| 3. Comprobar si ya está esperando
|--------------------------------------------------------------------------
*/

$sql_lista = "
SELECT id_espera, estado
FROM lista_espera
WHERE id_sesion = ?
AND id_usuario = ?
FOR UPDATE
";
$stmt_lista = $conexion->prepare($sql_lista);
$stmt_lista->bind_param(
"ii",
$id_sesion,
$id_usuario
);
$stmt_lista->execute();
$lista_anterior =
$stmt_lista->get_result()->fetch_assoc();
$stmt_lista->close();
if (
$lista_anterior &&
$lista_anterior["estado"] === "esperando"
) {
throw new Exception(
t("Ya estás en la lista de espera.")
);
}
/*
|--------------------------------------------------------------------------
| 4. Contar las plazas ocupadas
|--------------------------------------------------------------------------
*/

$sql_contar = "
SELECT COALESCE(SUM(cantidad), 0) AS total
FROM reservas
WHERE id_sesion = ?
AND estado = 'confirmada'
";
$stmt_contar = $conexion->prepare($sql_contar);
$stmt_contar->bind_param("i", $id_sesion);
$stmt_contar->execute();
$plazas_ocupadas = (int) $stmt_contar
->get_result()
->fetch_assoc()["total"];
$stmt_contar->close();
/*
|--------------------------------------------------------------------------
| 5. Crear una reserva si queda aforo
|--------------------------------------------------------------------------
*/

if ($plazas_ocupadas + $cantidad <= (int) $sesion["aforo"]) {
/*
|--------------------------------------------------------------------------
| 5b. Si se paga con paquete, bloquear y consumir un uso
|--------------------------------------------------------------------------
*/

if ($tipo_pago === "paquete") {
$sql_paquete = "
SELECT
id_paquete_cliente,
usos_disponibles,
estado,
fecha_caducidad
FROM paquetes_clientes
WHERE id_paquete_cliente = ?
AND id_usuario = ?
FOR UPDATE
";
$stmt_paquete = $conexion->prepare($sql_paquete);
$stmt_paquete->bind_param(
"ii",
$id_paquete_cliente,
$id_usuario
);
$stmt_paquete->execute();
$paquete_cliente =
$stmt_paquete->get_result()->fetch_assoc();
$stmt_paquete->close();
if (!$paquete_cliente) {
throw new Exception(
t("El paquete seleccionado no existe.")
);
}
if ($paquete_cliente["estado"] !== "activo") {
throw new Exception(
t("El paquete seleccionado no está activo.")
);
}
if ((int) $paquete_cliente["usos_disponibles"] <= 0) {
throw new Exception(
t("El paquete seleccionado no tiene usos disponibles.")
);
}
if (
$paquete_cliente["fecha_caducidad"] !== null &&
strtotime($paquete_cliente["fecha_caducidad"]) <
strtotime("today")
) {
throw new Exception(
t("El paquete seleccionado ha caducado.")
);
}
$usos_restantes =
(int) $paquete_cliente["usos_disponibles"] - 1;
$estado_paquete =
$usos_restantes <= 0 ? "agotado" : "activo";
$sql_consumir = "
UPDATE paquetes_clientes
SET
usos_disponibles = ?,
estado = ?
WHERE id_paquete_cliente = ?
";
$stmt_consumir =
$conexion->prepare($sql_consumir);
$stmt_consumir->bind_param(
"isi",
$usos_restantes,
$estado_paquete,
$id_paquete_cliente
);
$stmt_consumir->execute();
$stmt_consumir->close();
}
$codigo_reserva = strtoupper(
bin2hex(random_bytes(8))
);
if ($reserva_anterior) {
$sql_guardar = "
UPDATE reservas
SET
estado = 'confirmada',
asistencia = 'pendiente',
fecha_reserva = NOW(),
codigo_reserva = ?,
id_paquete_cliente = ?,
tipo_pago = ?,
cantidad = ?,
precio_pagado = ?,
metodo_pago = ?,
referencia_pago = ?,
estado_pago = ?,
comprobante_pago = ?,
monto_tarjeta_regalo = ?,
id_tarjeta_regalo_usada = ?
WHERE id_reserva = ?
";
$stmt_guardar =
$conexion->prepare($sql_guardar);
$stmt_guardar->bind_param(
"sisidssssdii",
$codigo_reserva,
$id_paquete_cliente,
$tipo_pago,
$cantidad,
$precio_pagado,
$metodo_pago_simulado,
$referencia_pago,
$estado_pago,
$comprobante_pago,
$monto_tarjeta_regalo,
$id_tarjeta_regalo_usada,
$reserva_anterior["id_reserva"]
);
} else {
$sql_guardar = "
INSERT INTO reservas (
id_sesion,
id_usuario,
id_paquete_cliente,
tipo_pago,
cantidad,
precio_pagado,
metodo_pago,
referencia_pago,
estado_pago,
comprobante_pago,
monto_tarjeta_regalo,
id_tarjeta_regalo_usada,
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
?,
?,
?,
?,
?,
?,
?,
'confirmada',
'pendiente',
?
)
";
$stmt_guardar =
$conexion->prepare($sql_guardar);
$stmt_guardar->bind_param(
"iiisidssssdis",
$id_sesion,
$id_usuario,
$id_paquete_cliente,
$tipo_pago,
$cantidad,
$precio_pagado,
$metodo_pago_simulado,
$referencia_pago,
$estado_pago,
$comprobante_pago,
$monto_tarjeta_regalo,
$id_tarjeta_regalo_usada,
$codigo_reserva
);
}
$stmt_guardar->execute();
$id_reserva_actual = $reserva_anterior
? (int) $reserva_anterior["id_reserva"]
: (int) $conexion->insert_id;
$stmt_guardar->close();
/*
| Si existía una antigua solicitud de espera,
| la marcamos como promocionada.
*/

if ($lista_anterior) {
    $sql_actualizar_lista = "
UPDATE lista_espera
SET
estado = 'promocionada'
WHERE id_espera = ?
";
$stmt_actualizar_lista =
$conexion->prepare(
$sql_actualizar_lista
);
$stmt_actualizar_lista->bind_param(
"i",
$lista_anterior["id_espera"]
);
$stmt_actualizar_lista->execute();
$stmt_actualizar_lista->close();
}
$nuevo_total = $plazas_ocupadas + $cantidad;
$nuevo_estado =
$nuevo_total >= (int) $sesion["aforo"]
? "completa"
: "programada";
$sql_estado = "
UPDATE sesiones
SET estado = ?
WHERE id_sesion = ?
";
$stmt_estado =
$conexion->prepare($sql_estado);
$stmt_estado->bind_param(
"si",
$nuevo_estado,
$id_sesion
);
$stmt_estado->execute();
$stmt_estado->close();
$conexion->commit();
$sql_nombre_actividad = "SELECT nombre FROM actividades WHERE id_actividad = ?";
$stmt_nombre_actividad = $conexion->prepare($sql_nombre_actividad);
$stmt_nombre_actividad->bind_param("i", $sesion["id_actividad"]);
$stmt_nombre_actividad->execute();
$nombre_actividad = $stmt_nombre_actividad->get_result()->fetch_assoc()["nombre"] ?? "Reserva";
$stmt_nombre_actividad->close();
$detalle_actividad = $nombre_actividad . ($cantidad > 1 ? " x{$cantidad}" : "");
if ($metodo_pago_simulado === "tarjeta_regalo") {
notificarTelegram(
"<b>Reserva pagada con tarjeta de regalo</b>\n" .
"Cliente: " . htmlspecialchars(nombreClienteActual($conexion, $id_usuario), ENT_QUOTES, 'UTF-8') . "\n" .
"Actividad: " . htmlspecialchars($detalle_actividad, ENT_QUOTES, 'UTF-8') . "\n" .
"Monto: $" . number_format((float) $precio_pagado, 2)
);
header("Location: mis_reservas.php?mensaje=confirmada");
exit;
}
if ($metodo_pago_simulado === "payphone") {
$resultado_pago = payphonePrepararPago(
$precio_a_cobrar,
$referencia_pago,
$detalle_actividad
);
if (!$resultado_pago["ok"]) {
cancelarReservaYPromocionar($conexion, $id_reserva_actual);
header(
"Location: detalle_sesion.php?id=" .
$id_sesion .
"&error=" .
urlencode("No se pudo iniciar el pago con tarjeta. Intenta de nuevo.")
);
exit;
}
header("Location: " . $resultado_pago["url_pago"]);
exit;
}
if ($estado_pago === "pendiente") {
notificarPagoAdmin(
"Nuevo comprobante de transferencia (Reserva) | Sama Shala",
"Un cliente subió un comprobante de transferencia bancaria, pendiente de revisión.",
"Reserva",
$referencia_pago,
(float) $precio_pagado,
nombreClienteActual($conexion, $id_usuario),
$detalle_actividad
);
} else {
notificarTelegram(
"<b>Nueva reserva confirmada</b>\n" .
"Cliente: " . htmlspecialchars(nombreClienteActual($conexion, $id_usuario), ENT_QUOTES, 'UTF-8') . "\n" .
"Actividad: " . htmlspecialchars($detalle_actividad, ENT_QUOTES, 'UTF-8') . "\n" .
"Tipo de pago: " . htmlspecialchars($tipo_pago, ENT_QUOTES, 'UTF-8')
);
}
$resumen_recurrente = "";
if ($tipo_pago === "paquete" && $repetir_semanal) {
$dia_semana_sesion = (int) (new DateTime($sesion["fecha"]))->format("N");
$resultado_recurrente = crearReservaRecurrente(
$conexion,
$id_usuario,
(int) $sesion["id_actividad"],
$dia_semana_sesion,
$sesion["hora_inicio"],
$id_paquete_cliente
);
$resumen_recurrente = "&recurrente=" .
$resultado_recurrente["confirmadas"] .
"-" . $resultado_recurrente["pre_reservas"];
}
header(
"Location: mis_reservas.php" .
"?mensaje=confirmada" .
($estado_pago === "pendiente" ? "&pago=pendiente" : "") .
$resumen_recurrente
);
exit;
}
/*
|--------------------------------------------------------------------------
| 6. Incorporar a la lista de espera
|--------------------------------------------------------------------------
*/

if ($lista_anterior) {
$sql_espera = "
UPDATE lista_espera
SET
estado = 'esperando',
fecha_solicitud = NOW()
WHERE id_espera = ?
";
$stmt_espera =
$conexion->prepare($sql_espera);
$stmt_espera->bind_param(
"i",
$lista_anterior["id_espera"]
);
} else {
$sql_espera = "
INSERT INTO lista_espera (
id_sesion,
id_usuario,
estado
)
VALUES (?, ?, 'esperando')
";
$stmt_espera =
$conexion->prepare($sql_espera);
$stmt_espera->bind_param(
"ii",
$id_sesion,
$id_usuario
);
}
$stmt_espera->execute();
$stmt_espera->close();
$sql_completa = "
UPDATE sesiones
SET estado = 'completa'
WHERE id_sesion = ?
";
$stmt_completa =
$conexion->prepare($sql_completa);
$stmt_completa->bind_param(
"i",
$id_sesion
);
$stmt_completa->execute();
$stmt_completa->close();
$conexion->commit();
header(
"Location: mis_reservas.php" .
"?mensaje=espera"
);
exit;
} catch (Throwable $error) {
$conexion->rollback();
header(
"Location: detalle_sesion.php?id=" .
$id_sesion .
"&error=" .
urlencode($error->getMessage())
);
exit;
}