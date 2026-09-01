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
a.tipo AS tipo_actividad,
a.precio AS precio_actividad,
e.nombre AS espacio,
(
SELECT COALESCE(SUM(r.cantidad), 0)
FROM reservas r
WHERE r.id_sesion = s.id_sesion
AND r.estado = 'confirmada'
) AS plazas_ocupadas
FROM sesiones s
INNER JOIN actividades a
ON s.id_actividad = a.id_actividad
INNER JOIN espacios e
ON s.id_espacio = e.id_espacio
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
$profesores_sesion_reserva = profesoresDeSesion($conexion, $id_sesion);
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
$max_plazas_compra = min(
$plazas_disponibles,
LIMITE_PLAZAS_POR_RESERVA
);
$pago_con_precio_fijo =
$sesion["tipo_actividad"] !== "clase" &&
$sesion["precio_actividad"] !== null;
$descuento_usuario = null;
$precio_final = null;
if ($pago_con_precio_fijo) {
$descuento_usuario = mejorDescuentoEventoTerapia(
$conexion,
idUsuarioActual()
);
$precio_final = $descuento_usuario !== null
? precio_con_descuento(
(float) $sesion["precio_actividad"],
$descuento_usuario["descuento"]
)
: (float) $sesion["precio_actividad"];
}
$paquetes_disponibles = [];
if (!$pago_con_precio_fijo) {
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
}
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
<strong>
<?= count($profesores_sesion_reserva) === 1 ? t("Profesor:") : t("Profesores:") ?>
</strong>
<?= escapar(nombresProfesores($profesores_sesion_reserva)) ?>
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
enctype="multipart/form-data"
id="formulario-reserva"
>
<input
type="hidden"
name="id_sesion"
value="<?= $sesion["id_sesion"] ?>"
>
<?php if ($plazas_disponibles <= 0): ?>
<button type="submit" class="boton">
<?= t("Confirmar solicitud") ?>
</button>
<?php elseif ($pago_con_precio_fijo): ?>
<input type="hidden" name="metodo_pago" value="evento">
<fieldset class="campo-completo">
<legend><?= t("Precio de esta sesión") ?></legend>
<p class="precio-sesion">
<?php if ($descuento_usuario !== null): ?>
<span class="precio-tachado">
<?= formatear_precio((float) $sesion["precio_actividad"]) ?>
</span>
<strong><?= formatear_precio($precio_final) ?></strong>
<?php else: ?>
<strong>
<?= formatear_precio((float) $sesion["precio_actividad"]) ?>
</strong>
<?php endif; ?>
</p>
<?php if ($descuento_usuario !== null): ?>
<div class="mensaje mensaje-exito">
<?= sprintf(
t("Tienes un %d%% de descuento en este evento o terapia gracias a tu paquete %s."),
$descuento_usuario["descuento"],
escapar($descuento_usuario["nombre_paquete"])
) ?>
</div>
<?php endif; ?>
<p>
<?= t("Esta sesión se paga con este precio fijo. No admite pago con paquetes ni clase suelta.") ?>
</p>
<?php if ($max_plazas_compra > 1): ?>
<div class="campo">
<label for="cantidad">
<?= t("Número de plazas") ?>
</label>
<input
type="number"
id="cantidad"
name="cantidad"
value="1"
min="1"
max="<?= $max_plazas_compra ?>"
data-precio-primera="<?= $precio_final ?>"
data-precio-unidad="<?= (float) $sesion["precio_actividad"] ?>"
oninput="actualizarTotalReserva(this)"
>
<?php if ($descuento_usuario !== null): ?>
<small>
<?= t("El descuento solo se aplica a la primera plaza; el resto se paga al precio completo.") ?>
</small>
<?php endif; ?>
</div>
<p>
<?= t("Total a pagar:") ?>
<strong id="total-reserva">
<?= formatear_precio($precio_final) ?>
</strong>
</p>
<script>
function actualizarTotalReserva(campo) {
var cantidad = parseInt(campo.value, 10) || 1;
var precioPrimera = parseFloat(campo.dataset.precioPrimera);
var precioUnidad = parseFloat(campo.dataset.precioUnidad);
var total = precioPrimera + precioUnidad * (cantidad - 1);
var totalFormateado = "$" + total.toFixed(2);
document.getElementById("total-reserva").textContent = totalFormateado;
document.getElementById("total-boton").textContent = totalFormateado;
}
</script>
<?php endif; ?>
</fieldset>
<fieldset class="campo-completo">
<legend><?= t("¿Cómo quieres pagar?") ?></legend>
<label class="opcion-pago">
<input
type="radio"
name="metodo_pago_compra"
value="simulado"
checked
data-metodo-pago
>
<?= t("Pago simulado") ?>
</label>
<label class="opcion-pago">
<input
type="radio"
name="metodo_pago_compra"
value="transferencia"
data-metodo-pago
>
<?= t("Transferencia bancaria") ?>
</label>
</fieldset>
<div id="bloque-pago-simulado">
<p>
<?= t("Este proyecto no cobra dinero real: al confirmar se registra el pago directamente como pagado.") ?>
</p>
<div class="campo">
<label for="titular">
<?= t("Nombre del titular") ?>
</label>
<input
type="text"
id="titular"
name="titular"
maxlength="150"
data-requerido-simulado
>
</div>
<div class="campo">
<label for="tarjeta">
<?= t("Número de tarjeta (simulado)") ?>
</label>
<input
type="text"
id="tarjeta"
name="tarjeta"
maxlength="30"
data-requerido-simulado
>
</div>
</div>
<div id="bloque-pago-transferencia" hidden>
<?php require __DIR__ . '/datos_transferencia.php'; ?>
<div class="campo">
<label for="comprobante_pago">
<?= t("Foto del comprobante de la transferencia") ?>
</label>
<input
type="file"
id="comprobante_pago"
name="comprobante_pago"
accept="image/*"
data-requerido-transferencia
>
</div>
<p class="ayuda">
<?= t("Tu reserva quedará pendiente de revisión hasta que confirmemos el pago.") ?>
</p>
</div>
<script>
(function () {
var formulario = document.getElementById('formulario-reserva');
var bloqueSimulado = document.getElementById('bloque-pago-simulado');
var bloqueTransferencia = document.getElementById('bloque-pago-transferencia');
var radios = formulario.querySelectorAll('[data-metodo-pago]');
function actualizarModoPago() {
var esTransferencia = formulario.querySelector('[data-metodo-pago]:checked').value === 'transferencia';
bloqueSimulado.hidden = esTransferencia;
bloqueTransferencia.hidden = !esTransferencia;
formulario.querySelectorAll('[data-requerido-simulado]').forEach(function (campo) {
campo.required = !esTransferencia;
});
formulario.querySelectorAll('[data-requerido-transferencia]').forEach(function (campo) {
campo.required = esTransferencia;
});
}
radios.forEach(function (radio) {
radio.addEventListener('change', actualizarModoPago);
});
actualizarModoPago();
})();
</script>
<button type="submit" class="boton">
<?= t("Pagar") ?>
<span id="total-boton"><?= formatear_precio($precio_final) ?></span>
<?= t("y confirmar") ?>
</button>
<?php else: ?>
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
<?php endif; ?>
</form>
</section>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>