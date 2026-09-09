<?php
function generarCodigoReserva(): string
{
return strtoupper(
    bin2hex(random_bytes(8))
);
}

function crearReservaRecurrente(
    mysqli $conexion,
    int $id_usuario,
    int $id_actividad,
    int $dia_semana,
    string $hora_inicio,
    int $id_paquete_cliente
): array {
    $sql_patron = "
        INSERT INTO reservas_recurrentes (id_usuario, id_actividad, dia_semana, hora_inicio, estado)
        VALUES (?, ?, ?, ?, 'activa')
        ON DUPLICATE KEY UPDATE estado = 'activa'
    ";
    $stmt_patron = $conexion->prepare($sql_patron);
    $stmt_patron->bind_param('iiis', $id_usuario, $id_actividad, $dia_semana, $hora_inicio);
    $stmt_patron->execute();
    $stmt_patron->close();

    $sql_id = "
        SELECT id_recurrente
        FROM reservas_recurrentes
        WHERE id_usuario = ?
        AND id_actividad = ?
        AND dia_semana = ?
        AND hora_inicio = ?
    ";
    $stmt_id = $conexion->prepare($sql_id);
    $stmt_id->bind_param('iiis', $id_usuario, $id_actividad, $dia_semana, $hora_inicio);
    $stmt_id->execute();
    $id_recurrente = (int) $stmt_id->get_result()->fetch_assoc()['id_recurrente'];
    $stmt_id->close();

    $dia_semana_mysql = $dia_semana - 1;
    $sql_sesiones = "
        SELECT s.id_sesion
        FROM sesiones s
        WHERE s.id_actividad = ?
        AND WEEKDAY(s.fecha) = ?
        AND s.hora_inicio = ?
        AND CONCAT(s.fecha, ' ', s.hora_inicio) > NOW()
        AND s.estado IN ('programada', 'completa')
        AND NOT EXISTS (
            SELECT 1 FROM reservas r
            WHERE r.id_sesion = s.id_sesion
            AND r.id_usuario = ?
            AND r.estado IN ('confirmada', 'pre_reserva')
        )
        ORDER BY s.fecha ASC
    ";
    $stmt_sesiones = $conexion->prepare($sql_sesiones);
    $stmt_sesiones->bind_param(
        'iisi',
        $id_actividad,
        $dia_semana_mysql,
        $hora_inicio,
        $id_usuario
    );
    $stmt_sesiones->execute();
    $sesiones_pendientes = array_column(
        $stmt_sesiones->get_result()->fetch_all(MYSQLI_ASSOC),
        'id_sesion'
    );
    $stmt_sesiones->close();

    $confirmadas = 0;
    $pre_reservas = 0;
    $omitidas_por_aforo = 0;

    foreach ($sesiones_pendientes as $id_sesion) {
        $conexion->begin_transaction();
        try {
            $sql_sesion = "
                SELECT id_sesion, aforo
                FROM sesiones
                WHERE id_sesion = ?
                FOR UPDATE
            ";
            $stmt_sesion = $conexion->prepare($sql_sesion);
            $stmt_sesion->bind_param('i', $id_sesion);
            $stmt_sesion->execute();
            $sesion = $stmt_sesion->get_result()->fetch_assoc();
            $stmt_sesion->close();

            $sql_ocupadas = "
                SELECT COALESCE(SUM(cantidad), 0) AS total
                FROM reservas
                WHERE id_sesion = ?
                AND estado = 'confirmada'
            ";
            $stmt_ocupadas = $conexion->prepare($sql_ocupadas);
            $stmt_ocupadas->bind_param('i', $id_sesion);
            $stmt_ocupadas->execute();
            $ocupadas = (int) $stmt_ocupadas->get_result()->fetch_assoc()['total'];
            $stmt_ocupadas->close();

            if ($ocupadas >= (int) $sesion['aforo']) {
                $conexion->commit();
                $omitidas_por_aforo++;
                continue;
            }

            $sql_paquete = "
                SELECT usos_disponibles, estado, fecha_caducidad
                FROM paquetes_clientes
                WHERE id_paquete_cliente = ?
                AND id_usuario = ?
                FOR UPDATE
            ";
            $stmt_paquete = $conexion->prepare($sql_paquete);
            $stmt_paquete->bind_param('ii', $id_paquete_cliente, $id_usuario);
            $stmt_paquete->execute();
            $paquete = $stmt_paquete->get_result()->fetch_assoc();
            $stmt_paquete->close();

            $paquete_vigente = $paquete
                && $paquete['estado'] === 'activo'
                && (int) $paquete['usos_disponibles'] > 0
                && (
                    $paquete['fecha_caducidad'] === null
                    || strtotime($paquete['fecha_caducidad']) >= strtotime('today')
                );

            $codigo = generarCodigoReserva();

            if ($paquete_vigente) {
                $usos_restantes = (int) $paquete['usos_disponibles'] - 1;
                $estado_paquete = $usos_restantes <= 0 ? 'agotado' : 'activo';
                $sql_consumir = "
                    UPDATE paquetes_clientes
                    SET usos_disponibles = ?, estado = ?
                    WHERE id_paquete_cliente = ?
                ";
                $stmt_consumir = $conexion->prepare($sql_consumir);
                $stmt_consumir->bind_param('isi', $usos_restantes, $estado_paquete, $id_paquete_cliente);
                $stmt_consumir->execute();
                $stmt_consumir->close();

                $sql_reserva = "
                    INSERT INTO reservas (
                        id_sesion, id_usuario, id_paquete_cliente, id_recurrente,
                        tipo_pago, estado, asistencia, codigo_reserva
                    )
                    VALUES (?, ?, ?, ?, 'paquete', 'confirmada', 'pendiente', ?)
                ";
                $stmt_reserva = $conexion->prepare($sql_reserva);
                $stmt_reserva->bind_param(
                    'iiiis',
                    $id_sesion,
                    $id_usuario,
                    $id_paquete_cliente,
                    $id_recurrente,
                    $codigo
                );
                $stmt_reserva->execute();
                $stmt_reserva->close();

                $nuevo_total = $ocupadas + 1;
                $nuevo_estado = $nuevo_total >= (int) $sesion['aforo'] ? 'completa' : 'programada';
                $sql_estado = "UPDATE sesiones SET estado = ? WHERE id_sesion = ?";
                $stmt_estado = $conexion->prepare($sql_estado);
                $stmt_estado->bind_param('si', $nuevo_estado, $id_sesion);
                $stmt_estado->execute();
                $stmt_estado->close();

                $confirmadas++;
            } else {
                $sql_reserva = "
                    INSERT INTO reservas (
                        id_sesion, id_usuario, id_recurrente,
                        tipo_pago, estado, asistencia, codigo_reserva
                    )
                    VALUES (?, ?, ?, 'paquete', 'pre_reserva', 'pendiente', ?)
                ";
                $stmt_reserva = $conexion->prepare($sql_reserva);
                $stmt_reserva->bind_param(
                    'iiis',
                    $id_sesion,
                    $id_usuario,
                    $id_recurrente,
                    $codigo
                );
                $stmt_reserva->execute();
                $stmt_reserva->close();

                $pre_reservas++;
            }
            $conexion->commit();
        } catch (Throwable $error) {
            $conexion->rollback();
        }
    }

    return [
        'id_recurrente' => $id_recurrente,
        'confirmadas' => $confirmadas,
        'pre_reservas' => $pre_reservas,
        'omitidas_por_aforo' => $omitidas_por_aforo
    ];
}

function activarPreReservasPendientes(mysqli $conexion, int $id_usuario, int $id_paquete_cliente): void
{
    $sql_paquete = "
        SELECT usos_disponibles, estado, fecha_caducidad
        FROM paquetes_clientes
        WHERE id_paquete_cliente = ?
        AND id_usuario = ?
        FOR UPDATE
    ";
    $stmt_paquete = $conexion->prepare($sql_paquete);
    $stmt_paquete->bind_param('ii', $id_paquete_cliente, $id_usuario);
    $stmt_paquete->execute();
    $paquete = $stmt_paquete->get_result()->fetch_assoc();
    $stmt_paquete->close();

    if (
        !$paquete
        || $paquete['estado'] !== 'activo'
        || (int) $paquete['usos_disponibles'] <= 0
        || (
            $paquete['fecha_caducidad'] !== null
            && strtotime($paquete['fecha_caducidad']) < strtotime('today')
        )
    ) {
        return;
    }

    $sql_pendientes = "
        SELECT r.id_reserva, r.id_sesion, s.aforo
        FROM reservas r
        INNER JOIN sesiones s ON r.id_sesion = s.id_sesion
        WHERE r.id_usuario = ?
        AND r.estado = 'pre_reserva'
        AND CONCAT(s.fecha, ' ', s.hora_inicio) > NOW()
        ORDER BY s.fecha ASC, s.hora_inicio ASC
    ";
    $stmt_pendientes = $conexion->prepare($sql_pendientes);
    $stmt_pendientes->bind_param('i', $id_usuario);
    $stmt_pendientes->execute();
    $pendientes = $stmt_pendientes->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_pendientes->close();

    $usos_disponibles = (int) $paquete['usos_disponibles'];

    foreach ($pendientes as $pendiente) {
        if ($usos_disponibles <= 0) {
            break;
        }
        $conexion->begin_transaction();
        try {
            $sql_sesion = "SELECT aforo FROM sesiones WHERE id_sesion = ? FOR UPDATE";
            $stmt_sesion = $conexion->prepare($sql_sesion);
            $stmt_sesion->bind_param('i', $pendiente['id_sesion']);
            $stmt_sesion->execute();
            $sesion = $stmt_sesion->get_result()->fetch_assoc();
            $stmt_sesion->close();

            $sql_ocupadas = "
                SELECT COALESCE(SUM(cantidad), 0) AS total
                FROM reservas
                WHERE id_sesion = ?
                AND estado = 'confirmada'
            ";
            $stmt_ocupadas = $conexion->prepare($sql_ocupadas);
            $stmt_ocupadas->bind_param('i', $pendiente['id_sesion']);
            $stmt_ocupadas->execute();
            $ocupadas = (int) $stmt_ocupadas->get_result()->fetch_assoc()['total'];
            $stmt_ocupadas->close();

            if ($ocupadas >= (int) $sesion['aforo']) {
                $conexion->commit();
                continue;
            }

            $usos_disponibles--;
            $estado_paquete = $usos_disponibles <= 0 ? 'agotado' : 'activo';
            $sql_consumir = "
                UPDATE paquetes_clientes
                SET usos_disponibles = ?, estado = ?
                WHERE id_paquete_cliente = ?
            ";
            $stmt_consumir = $conexion->prepare($sql_consumir);
            $stmt_consumir->bind_param('isi', $usos_disponibles, $estado_paquete, $id_paquete_cliente);
            $stmt_consumir->execute();
            $stmt_consumir->close();

            $codigo = generarCodigoReserva();
            $sql_activar = "
                UPDATE reservas
                SET estado = 'confirmada',
                    id_paquete_cliente = ?,
                    codigo_reserva = ?,
                    fecha_reserva = NOW()
                WHERE id_reserva = ?
            ";
            $stmt_activar = $conexion->prepare($sql_activar);
            $stmt_activar->bind_param('isi', $id_paquete_cliente, $codigo, $pendiente['id_reserva']);
            $stmt_activar->execute();
            $stmt_activar->close();

            $nuevo_total = $ocupadas + 1;
            $nuevo_estado = $nuevo_total >= (int) $sesion['aforo'] ? 'completa' : 'programada';
            $sql_estado = "UPDATE sesiones SET estado = ? WHERE id_sesion = ?";
            $stmt_estado = $conexion->prepare($sql_estado);
            $stmt_estado->bind_param('si', $nuevo_estado, $pendiente['id_sesion']);
            $stmt_estado->execute();
            $stmt_estado->close();

            $conexion->commit();
        } catch (Throwable $error) {
            $conexion->rollback();
        }
    }
}
function cancelarReservaYPromocionar(
mysqli $conexion,
int $id_reserva,
?int $id_usuario = null
): int {
$conexion->begin_transaction();
try {
/*
|--------------------------------------------------------------------------
| 1. Consultar la reserva
|--------------------------------------------------------------------------
*/

$sql_reserva = "
SELECT
r.id_reserva,
r.id_sesion,
r.id_usuario,
r.id_paquete_cliente,
r.estado,
s.fecha,
s.hora_inicio
FROM reservas r
INNER JOIN sesiones s
ON r.id_sesion = s.id_sesion
WHERE r.id_reserva = ?
";
if ($id_usuario !== null) {
$sql_reserva .= "
AND r.id_usuario = ?
";
}
$sql_reserva .= "
FOR UPDATE
";
$stmt_reserva =
$conexion->prepare($sql_reserva);
if ($id_usuario !== null) {
$stmt_reserva->bind_param(
"ii",
$id_reserva,
$id_usuario
);
} else {
$stmt_reserva->bind_param(
"i",
$id_reserva
);
}
$stmt_reserva->execute();
$reserva = $stmt_reserva
->get_result()
->fetch_assoc();
$stmt_reserva->close();
if (!$reserva) {
throw new Exception(
"La reserva no existe."
);
}
if (!in_array($reserva["estado"], ["confirmada", "pre_reserva"], true)) {
throw new Exception(
"La reserva no está confirmada."
);
}
$era_pre_reserva = $reserva["estado"] === "pre_reserva";
if ($id_usuario !== null && !$era_pre_reserva) {
$inicio_sesion = strtotime(
$reserva["fecha"] . " " . $reserva["hora_inicio"]
);
if ($inicio_sesion - time() < 15 * 60) {
throw new Exception(
"Ya no se puede cancelar: faltan menos de 15 minutos para el inicio de la sesión."
);
}
}
$id_sesion =
(int) $reserva["id_sesion"];
/*
|--------------------------------------------------------------------------
| 2. Bloquear la sesión
|--------------------------------------------------------------------------
*/
$sql_sesion = "
SELECT id_sesion, aforo, estado
FROM sesiones
WHERE id_sesion = ?
FOR UPDATE
";
$stmt_sesion =
$conexion->prepare($sql_sesion);
$stmt_sesion->bind_param(
"i",
$id_sesion
);
$stmt_sesion->execute();
$sesion = $stmt_sesion
->get_result()
->fetch_assoc();
$stmt_sesion->close();
if (!$sesion) {
throw new Exception(
"La sesión no existe."
);
}
/*
|--------------------------------------------------------------------------
| 3. Cancelar la reserva
|--------------------------------------------------------------------------
*/

$sql_cancelar = "
UPDATE reservas
SET
estado = 'cancelada',
asistencia = 'pendiente'
WHERE id_reserva = ?
";
$stmt_cancelar =
$conexion->prepare($sql_cancelar);
$stmt_cancelar->bind_param(
"i",
$id_reserva
);
$stmt_cancelar->execute();
$stmt_cancelar->close();
/*
|--------------------------------------------------------------------------
| 3b. Restaurar el uso del paquete, si la reserva se pagó con uno
|--------------------------------------------------------------------------
*/

if ($reserva["id_paquete_cliente"] !== null) {
$sql_bloquear_paquete = "
SELECT
id_paquete_cliente,
usos_disponibles,
estado,
fecha_caducidad
FROM paquetes_clientes
WHERE id_paquete_cliente = ?
FOR UPDATE
";
$stmt_bloquear_paquete =
$conexion->prepare($sql_bloquear_paquete);
$stmt_bloquear_paquete->bind_param(
"i",
$reserva["id_paquete_cliente"]
);
$stmt_bloquear_paquete->execute();
$paquete_cliente = $stmt_bloquear_paquete
->get_result()
->fetch_assoc();
$stmt_bloquear_paquete->close();
if ($paquete_cliente && $paquete_cliente["estado"] !== "cancelado") {
$sigue_vigente =
$paquete_cliente["fecha_caducidad"] === null ||
strtotime($paquete_cliente["fecha_caducidad"]) >=
strtotime("today");
$usos_restaurados =
(int) $paquete_cliente["usos_disponibles"] + 1;
$estado_restaurado =
$sigue_vigente ? "activo" : "caducado";
$sql_restaurar_paquete = "
UPDATE paquetes_clientes
SET
usos_disponibles = ?,
estado = ?
WHERE id_paquete_cliente = ?
";
$stmt_restaurar_paquete =
$conexion->prepare($sql_restaurar_paquete);
$stmt_restaurar_paquete->bind_param(
"isi",
$usos_restaurados,
$estado_restaurado,
$reserva["id_paquete_cliente"]
);
$stmt_restaurar_paquete->execute();
$stmt_restaurar_paquete->close();
}
}
/*
|--------------------------------------------------------------------------
| 4-5. Promover en cadena mientras queden plazas libres y gente esperando
|--------------------------------------------------------------------------
| Una reserva puede cubrir varias plazas (columna cantidad), así que
| cancelarla puede liberar más de un hueco: se promociona a tantas
| personas de la lista de espera como plazas libres queden.
*/

while (true) {
$sql_ocupadas = "
SELECT COALESCE(SUM(cantidad), 0) AS total
FROM reservas
WHERE id_sesion = ?
AND estado = 'confirmada'
";
$stmt_ocupadas =
$conexion->prepare($sql_ocupadas);
$stmt_ocupadas->bind_param(
"i",
$id_sesion
);
$stmt_ocupadas->execute();
$ocupadas = (int) $stmt_ocupadas
->get_result()
->fetch_assoc()["total"];
$stmt_ocupadas->close();
if ($ocupadas >= (int) $sesion["aforo"]) {
break;
}
$sql_espera = "
SELECT id_espera, id_usuario
FROM lista_espera
WHERE id_sesion = ?
AND estado = 'esperando'
ORDER BY
fecha_solicitud ASC,
id_espera ASC
LIMIT 1
FOR UPDATE
";
$stmt_espera =
$conexion->prepare($sql_espera);
$stmt_espera->bind_param(
"i",
$id_sesion
);
$stmt_espera->execute();
$primera_espera = $stmt_espera
->get_result()
->fetch_assoc();
$stmt_espera->close();
if (!$primera_espera) {
break;
}
$id_promocionado = (int)
$primera_espera["id_usuario"];
$sql_anterior = "
SELECT id_reserva
FROM reservas
WHERE id_sesion = ?
AND id_usuario = ?
FOR UPDATE
";
$stmt_anterior =
$conexion->prepare($sql_anterior);
$stmt_anterior->bind_param(
"ii",
$id_sesion,
$id_promocionado
);
$stmt_anterior->execute();
$anterior = $stmt_anterior
->get_result()
->fetch_assoc();
$stmt_anterior->close();
$codigo = generarCodigoReserva();
if ($anterior) {
$sql_promocionar = "
UPDATE reservas
SET
estado = 'confirmada',
asistencia = 'pendiente',
fecha_reserva = NOW(),
codigo_reserva = ?,
cantidad = 1
WHERE id_reserva = ?
";
$stmt_promocionar =
$conexion->prepare(
$sql_promocionar
);
$stmt_promocionar->bind_param(
"si",
$codigo,
$anterior["id_reserva"]
);
} else {
$sql_promocionar = "
INSERT INTO reservas (
id_sesion,
id_usuario,
estado,
asistencia,
codigo_reserva
)
VALUES (
?,
?,
'confirmada',
'pendiente',
?
)
";
$stmt_promocionar =
$conexion->prepare(
$sql_promocionar
);
$stmt_promocionar->bind_param(
"iis",
$id_sesion,
$id_promocionado,
$codigo
);
}
$stmt_promocionar->execute();
$stmt_promocionar->close();
$sql_promocionada = "
UPDATE lista_espera
SET
estado = 'promocionada'
WHERE id_espera = ?
";
$stmt_promocionada =
$conexion->prepare(
$sql_promocionada
);
$stmt_promocionada->bind_param(
"i",
$primera_espera["id_espera"]
);
$stmt_promocionada->execute();
$stmt_promocionada->close();
}
/*
|--------------------------------------------------------------------------
| 6. Recalcular el estado
|--------------------------------------------------------------------------
*/

$sql_total = "
SELECT COALESCE(SUM(cantidad), 0) AS total
FROM reservas
WHERE id_sesion = ?
AND estado = 'confirmada'
";
$stmt_total =
$conexion->prepare($sql_total);
$stmt_total->bind_param(
"i",
$id_sesion
);
$stmt_total->execute();
$total = (int) $stmt_total
->get_result()
->fetch_assoc()["total"];
$stmt_total->close();
$nuevo_estado =
$total >= (int) $sesion["aforo"]
? "completa"
: "programada";
$sql_actualizar = "
UPDATE sesiones
SET estado = ?
WHERE id_sesion = ?
AND estado NOT IN (
'cancelada',
'finalizada'
)
";
$stmt_actualizar =
$conexion->prepare(
$sql_actualizar
);
$stmt_actualizar->bind_param(
"si",
$nuevo_estado,
$id_sesion
);
$stmt_actualizar->execute();
$stmt_actualizar->close();
$conexion->commit();
return $id_sesion;
} catch (Throwable $error) {
$conexion->rollback();
throw $error;
}
}