<?php
require_once "seguridad.php";
require_once "conexion.php";
require_once "funciones.php";
$id_sesion = filter_input(
INPUT_GET,
"id",
FILTER_VALIDATE_INT
);
if (!$id_sesion) {
http_response_code(400);
exit(t("Identificador de sesión no válido."));
}
$sql = "
SELECT
s.id_sesion,
s.fecha,
s.hora_inicio,
s.hora_fin,
s.aforo,
s.estado,
a.nombre AS actividad,
e.nombre AS espacio,
m.nombre AS profesor_nombre,
m.apellidos AS profesor_apellidos,
(
SELECT COUNT(*)
FROM reservas r
WHERE r.id_sesion = s.id_sesion
AND r.estado = 'confirmada'
) AS plazas_ocupadas
FROM sesiones s
INNER JOIN actividades a
ON s.id_actividad = a.id_actividad
INNER JOIN espacios e
ON s.id_espacio = e.id_espacio
INNER JOIN profesores m
ON s.id_profesor = m.id_profesor
WHERE s.id_sesion = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_sesion);
$stmt->execute();
$resultado = $stmt->get_result();
$sesion = $resultado->fetch_assoc();
$stmt->close();
if (!$sesion) {
http_response_code(404);
exit(t("La sesión no existe."));
}
$inicio = new DateTime(
$sesion["fecha"] . " " . $sesion["hora_inicio"]
);
if (
!in_array(
$sesion["estado"],
["programada", "completa"],
true
) ||
$inicio <= new DateTime()
) {
exit(t("Esta sesión ya no admite reservas."));
}
$plazas_disponibles = max(
0,
(int) $sesion["aforo"] -
(int) $sesion["plazas_ocupadas"]
);
$sql_paquetes = "
SELECT
bc.id_paquete_cliente,
bc.usos_disponibles,
tb.nombre AS nombre_paquete
FROM paquetes_clientes bc
INNER JOIN tipos_paquete tb
ON bc.id_tipo_paquete = tb.id_tipo_paquete
WHERE bc.id_usuario = ?
AND bc.estado = 'activo'
AND bc.usos_disponibles > 0
AND (
bc.fecha_caducidad IS NULL
OR bc.fecha_caducidad >= CURDATE()
)
ORDER BY
bc.fecha_caducidad IS NULL,
bc.fecha_caducidad ASC
";
$stmt_paquetes = $conexion->prepare($sql_paquetes);
$id_usuario_actual = idUsuarioActual();
$stmt_paquetes->bind_param("i", $id_usuario_actual);
$stmt_paquetes->execute();
$paquetes_disponibles = $stmt_paquetes
->get_result()
->fetch_all(MYSQLI_ASSOC);
$stmt_paquetes->close();
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title><?= t("Confirmar reserva") ?></title>
<link rel="stylesheet" href="<?= urlEstilos() ?>">
<link rel="icon" type="image/png" sizes="32x32" href="imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="imagenes/favicon.ico">
</head>
<body>
    <?php require "menu.php"; ?>
<main class="contenedor">
<h1><?= t("Confirmar solicitud") ?></h1>
<section class="resumen-reserva">
<h2>
<?= escapar($sesion["actividad"]) ?>
</h2>
<p>
<strong><?= t("Fecha:") ?></strong>
<?= formatear_fecha($sesion["fecha"]) ?>
</p>
<p>
<strong><?= t("Horario:") ?></strong>
<?= substr($sesion["hora_inicio"], 0, 5) ?>
–
<?= substr($sesion["hora_fin"], 0, 5) ?>
</p>
<p>
<strong><?= t("Espacio:") ?></strong>
<?= escapar($sesion["espacio"]) ?>
</p>
<p>
<strong><?= t("Profesor:") ?></strong>
<?= escapar(
    $sesion["profesor_nombre"] .
" " .
$sesion["profesor_apellidos"]
) ?>
</p>
<?php if ($plazas_disponibles > 0): ?>
<div class="mensaje exito">
<?= $plazas_disponibles === 1
? t("Queda 1 plaza disponible.")
: sprintf(t("Quedan %d plazas disponibles."), $plazas_disponibles) ?>
</div>
<p>
<?= t("Al confirmar se creará una reserva.") ?>
</p>
<?php else: ?>
<div class="mensaje aviso">
<?= t("La sesión está completa.") ?>
</div>
<p>
<?= t("Al confirmar entrarás en la lista de espera.") ?>
</p>
<?php endif; ?>
<form
action="procesar_reserva.php"
method="post"
>
<input
type="hidden"
name="id_sesion"
value="<?= $sesion["id_sesion"] ?>"
>
<fieldset class="campo-completo">
<legend><?= t("¿Cómo quieres pagar esta clase?") ?></legend>
<label class="opcion-pago">
<input
type="radio"
name="metodo_pago"
value="suelta"
checked
>
<?= t("Clase suelta") ?>
</label>
<?php foreach ($paquetes_disponibles as $paquete): ?>
<label class="opcion-pago">
<input
type="radio"
name="metodo_pago"
value="paquete:<?= (int) $paquete["id_paquete_cliente"] ?>"
>
<?= escapar($paquete["nombre_paquete"]) ?> <?= sprintf(t("(quedan %d usos)"), (int) $paquete["usos_disponibles"]) ?>
</label>
<?php endforeach; ?>
</fieldset>
<button type="submit" class="boton">
<?= t("Confirmar solicitud") ?>
</button>
</form>
</section>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>