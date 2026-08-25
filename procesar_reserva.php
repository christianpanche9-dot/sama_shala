<?php
require_once "seguridad.php";
require_once "conexion.php";
require_once "funciones.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
header("Location: sesiones.php");
exit;
}
$id_sesion = filter_input(
INPUT_POST,
"id_sesion",
FILTER_VALIDATE_INT
);
$id_usuario = idUsuarioActual();
if (!$id_sesion || !$id_usuario) {
header("Location: sesiones.php?error=datos");
exit;
}
$metodo_pago_enviado = trim($_POST["metodo_pago"] ?? "suelta");
$tipo_pago = "suelta";
$id_paquete_cliente = null;
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
SELECT COUNT(*) AS total
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

if ($plazas_ocupadas < (int) $sesion["aforo"]) {
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
tipo_pago = ?
WHERE id_reserva = ?
";
$stmt_guardar =
$conexion->prepare($sql_guardar);
$stmt_guardar->bind_param(
"sisi",
$codigo_reserva,
$id_paquete_cliente,
$tipo_pago,
$reserva_anterior["id_reserva"]
);
} else {
$sql_guardar = "
INSERT INTO reservas (
id_sesion,
id_usuario,
id_paquete_cliente,
tipo_pago,
estado,
asistencia,
codigo_reserva
)
VALUES (
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
"iiiss",
$id_sesion,
$id_usuario,
$id_paquete_cliente,
$tipo_pago,
$codigo_reserva
);
}
$stmt_guardar->execute();
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
$nuevo_total = $plazas_ocupadas + 1;
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
header(
"Location: mis_reservas.php" .
"?mensaje=confirmada"
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