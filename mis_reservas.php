<?php
require_once "seguridad.php";
require_once "conexion.php";
require_once "funciones.php";
$id_usuario = idUsuarioActual();
$sql_reservas = "
SELECT
r.id_reserva,
r.fecha_reserva,
r.estado,
r.estado_pago,
r.asistencia,
r.codigo_reserva,
r.tipo_pago,
r.cantidad,
r.precio_pagado,
r.id_recurrente,
s.id_sesion,
s.fecha,
s.hora_inicio,
s.hora_fin,
s.estado AS estado_sesion,
a.nombre AS actividad,
e.nombre AS espacio
FROM reservas r
INNER JOIN sesiones s
ON r.id_sesion = s.id_sesion
INNER JOIN actividades a
ON s.id_actividad = a.id_actividad
INNER JOIN espacios e
ON s.id_espacio = e.id_espacio
WHERE r.id_usuario = ?
ORDER BY
s.fecha DESC,
s.hora_inicio DESC
";
$stmt_reservas = $conexion->prepare($sql_reservas);
$stmt_reservas->bind_param("i", $id_usuario);
$stmt_reservas->execute();
$todas_las_reservas = $stmt_reservas->get_result()->fetch_all(MYSQLI_ASSOC);
$ahora = new DateTime();
$reservas_activas = [];
$reservas_historial = [];
foreach ($todas_las_reservas as $reserva) {
$inicio_sesion = new DateTime(
$reserva["fecha"] . " " . $reserva["hora_inicio"]
);
$es_historial =
$reserva["estado"] === "cancelada" ||
$reserva["estado_sesion"] === "cancelada" ||
$inicio_sesion <= $ahora;
if ($es_historial) {
$reservas_historial[] = $reserva;
} else {
$reservas_activas[] = $reserva;
}
}
usort(
$reservas_activas,
fn ($a, $b) => strcmp(
$a["fecha"] . $a["hora_inicio"],
$b["fecha"] . $b["hora_inicio"]
)
);
$reservas_por_dia_calendario = [];
foreach ($reservas_activas as $reserva) {
$reservas_por_dia_calendario[$reserva["fecha"]][] = $reserva;
}
$hoy_calendario = new DateTime('today');
$mes_calendario = filter_input(
INPUT_GET,
'mes',
FILTER_VALIDATE_INT,
['options' => ['min_range' => 1, 'max_range' => 12]]
);
$anio_calendario = filter_input(
INPUT_GET,
'anio',
FILTER_VALIDATE_INT,
['options' => ['min_range' => 2020, 'max_range' => 2100]]
);
if (!$mes_calendario || !$anio_calendario) {
$mes_calendario = (int) $hoy_calendario->format('n');
$anio_calendario = (int) $hoy_calendario->format('Y');
}
$primer_dia_mes_visible_calendario = new DateTime(
sprintf('%04d-%02d-01', $anio_calendario, $mes_calendario)
);
$primer_dia_mes_actual_calendario = new DateTime($hoy_calendario->format('Y-m-01'));
if ($primer_dia_mes_visible_calendario < $primer_dia_mes_actual_calendario) {
$mes_calendario = (int) $hoy_calendario->format('n');
$anio_calendario = (int) $hoy_calendario->format('Y');
$primer_dia_mes_visible_calendario = clone $primer_dia_mes_actual_calendario;
}
$mes_anterior_calendario = $mes_calendario === 1 ? 12 : $mes_calendario - 1;
$anio_mes_anterior_calendario = $mes_calendario === 1 ? $anio_calendario - 1 : $anio_calendario;
$mes_siguiente_calendario = $mes_calendario === 12 ? 1 : $mes_calendario + 1;
$anio_mes_siguiente_calendario = $mes_calendario === 12 ? $anio_calendario + 1 : $anio_calendario;
$primer_dia_mes_anterior_calendario = new DateTime(
sprintf('%04d-%02d-01', $anio_mes_anterior_calendario, $mes_anterior_calendario)
);
$mostrar_mes_anterior_calendario = $primer_dia_mes_anterior_calendario >= $primer_dia_mes_actual_calendario;
$dias_en_mes_calendario = (int) $primer_dia_mes_visible_calendario->format('t');
$dias_mes_calendario = [];
for ($d = 1; $d <= $dias_en_mes_calendario; $d++) {
$dias_mes_calendario[] = new DateTime(
sprintf('%04d-%02d-%02d', $anio_calendario, $mes_calendario, $d)
);
}
$semanas_mes_calendario = generar_calendario_mes($anio_calendario, $mes_calendario);
$fecha_activa_calendario = $primer_dia_mes_visible_calendario == $primer_dia_mes_actual_calendario
? clone $hoy_calendario
: clone $primer_dia_mes_visible_calendario;
$clave_fecha_activa_calendario = $fecha_activa_calendario->format('Y-m-d');
$hay_reservas_en_mes_calendario = false;
foreach ($dias_mes_calendario as $dia_calendario) {
if (!empty($reservas_por_dia_calendario[$dia_calendario->format('Y-m-d')])) {
$hay_reservas_en_mes_calendario = true;
break;
}
}
$historial_por_mes = [];
foreach ($reservas_historial as $reserva) {
$clave_mes = substr($reserva["fecha"], 0, 7);
if (!isset($historial_por_mes[$clave_mes])) {
$historial_por_mes[$clave_mes] = [];
}
$historial_por_mes[$clave_mes][] = $reserva;
}
$meses_por_pagina_historial = 3;
$total_paginas_historial = (int) ceil(
count($historial_por_mes) / $meses_por_pagina_historial
);
function tarjeta_reserva(array $reserva, bool $colapsable = false): void
{
$inicio = new DateTime(
$reserva["fecha"] . " " . $reserva["hora_inicio"]
);
$puede_cancelar =
in_array($reserva["estado"], ["confirmada", "pre_reserva"], true) &&
$reserva["estado_sesion"] !== "cancelada" &&
($reserva["estado"] === "pre_reserva" ||
$inicio > (new DateTime())->modify("+15 minutes"));
$etiqueta = $colapsable ? 'details' : 'article';
?>
<<?= $etiqueta ?> class="tarjeta-reserva">
<?php if ($colapsable): ?>
<summary class="resumen-tarjeta-reserva">
<span class="resumen-reserva-fecha">
<?= date("d/m/Y", strtotime($reserva["fecha"])) ?>
</span>
<span class="resumen-reserva-nombre">
<?= escapar($reserva["actividad"]) ?>
</span>
</summary>
<div class="detalle-tarjeta-reserva">
<?php else: ?>
<h3>
    <?= escapar(
        $reserva["actividad"]
) ?>
</h3>


<?php endif; ?>
<p>
<strong><?= t('Fecha:') ?></strong>
<?= date(
    "d/m/Y",
strtotime($reserva["fecha"])
) ?>
</p>
<p>
<strong><?= t('Horario:') ?></strong>
<?= substr(
$reserva["hora_inicio"],
0,
5
) ?>
–
<?= substr(
$reserva["hora_fin"],
0,
5
) ?>
</p>
<p>
<strong><?= t('Espacio:') ?></strong>
<?= escapar(
$reserva["espacio"]
) ?>
</p>
<p>
<strong><?= t('Estado:') ?></strong>
<?= $reserva["estado"] === "pre_reserva"
? t("Pendiente de paquete")
: escapar(t(ucfirst($reserva["estado"]))) ?>
</p>
<?php if ($reserva["estado"] === "pre_reserva"): ?>
<div class="mensaje mensaje-aviso">
<?= t('Se confirmará automáticamente cuando actives un paquete con usos disponibles.') ?>
</div>
<?php endif; ?>
<p>
<strong><?= t('Pago:') ?></strong>
<?php if ($reserva["tipo_pago"] === "paquete"): ?>
<?= t("Con paquete") ?>
<?php elseif ($reserva["tipo_pago"] === "evento"): ?>
<?= $reserva["precio_pagado"] !== null
? formatear_precio((float) $reserva["precio_pagado"])
: t("Precio fijo") ?>
<?php else: ?>
<?= t("Clase suelta") ?>
<?php endif; ?>
</p>
<?php if (
$reserva["tipo_pago"] === "evento" &&
(int) $reserva["cantidad"] > 1
): ?>
<p>
<strong><?= t('Plazas:') ?></strong>
<?= (int) $reserva["cantidad"] ?>
</p>
<?php endif; ?>
<?php if (
$reserva["estado_pago"] === "pendiente" &&
$reserva["estado"] === "confirmada"
): ?>
<div class="mensaje mensaje-aviso">
<?= t('Pago pendiente de revisión.') ?>
</div>
<?php endif; ?>
<?php if (
    $reserva["estado"] === "confirmada"
): ?>
<p class="codigo-reserva">
<?= t('Código:') ?> <?= escapar(
$reserva["codigo_reserva"]
) ?>
</p>
<?php endif; ?>
<?php if ($puede_cancelar): ?>
<form
action="cancelar_reserva.php"
method="post"
>
<input
type="hidden"
name="id_reserva"
value="<?=
$reserva["id_reserva"]
?>"
>
<input
type="hidden"
name="csrf_token"
value="<?= escapar(tokenCsrf()) ?>"
>
<button
type="submit"
class="boton peligro"
>
<?= t('Cancelar reserva') ?>
</button>
</form>
<?php endif; ?>
<?php if ($colapsable): ?>
</div>
<?php endif; ?>
</<?= $etiqueta ?>>
<?php
}
$sql_espera = "
SELECT
le.id_espera,
le.fecha_solicitud,
le.estado,
s.id_sesion,
s.fecha,
s.hora_inicio,
s.hora_fin,
a.nombre AS actividad,
e.nombre AS espacio,
(
SELECT COUNT(*)
FROM lista_espera anterior
WHERE anterior.id_sesion = le.id_sesion
AND anterior.estado = 'esperando'
AND (
anterior.fecha_solicitud
< le.fecha_solicitud
OR (
anterior.fecha_solicitud
= le.fecha_solicitud
AND anterior.id_espera
<= le.id_espera
)
)
) AS posicion
FROM lista_espera le
INNER JOIN sesiones s
ON le.id_sesion = s.id_sesion
INNER JOIN actividades a
ON s.id_actividad = a.id_actividad
INNER JOIN espacios e
ON s.id_espacio = e.id_espacio
WHERE le.id_usuario = ?
AND le.estado = 'esperando'
ORDER BY
s.fecha ASC,
s.hora_inicio ASC
";
$stmt_espera = $conexion->prepare($sql_espera);
$stmt_espera->bind_param("i", $id_usuario);
$stmt_espera->execute();
$lista_espera = $stmt_espera->get_result();
$mensaje = $_GET["mensaje"] ?? "";
$pago_pendiente = ($_GET["pago"] ?? "") === "pendiente";
$resumen_recurrente = trim($_GET["recurrente"] ?? "");

$sql_recurrentes = "
SELECT
rr.id_recurrente,
rr.dia_semana,
rr.hora_inicio,
a.nombre AS actividad
FROM reservas_recurrentes rr
INNER JOIN actividades a
ON rr.id_actividad = a.id_actividad
WHERE rr.id_usuario = ?
AND rr.estado = 'activa'
ORDER BY rr.dia_semana ASC, rr.hora_inicio ASC
";
$stmt_recurrentes = $conexion->prepare($sql_recurrentes);
$stmt_recurrentes->bind_param("i", $id_usuario);
$stmt_recurrentes->execute();
$reservas_recurrentes = $stmt_recurrentes->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_recurrentes->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title><?= t('Mis reservas') ?></title>
<link rel="stylesheet" href="<?= urlEstilos() ?>">
<link rel="icon" type="image/png" sizes="32x32" href="imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="imagenes/favicon.ico">
</head>
<body>
<?php require "menu.php"; ?>
<main class="contenedor">
<h1><?= t('Mis reservas') ?></h1>
<?php if ($mensaje === "confirmada" && $pago_pendiente): ?>
<div class="mensaje mensaje-aviso">
<?= t('Hemos registrado tu reserva por transferencia bancaria. Quedará pendiente de revisión hasta que confirmemos el pago.') ?>
</div>
<?php elseif ($mensaje === "confirmada"): ?>
<div class="mensaje mensaje-exito">
<?= t('La reserva se ha confirmado correctamente.') ?>
</div>
<?php elseif ($mensaje === "espera"): ?>
<div class="mensaje mensaje-aviso">
<?= t('La sesión está completa. Te hemos añadido a la lista de espera.') ?>
</div>
<?php elseif ($mensaje === "cancelada"): ?>
<div class="mensaje mensaje-exito">
<?= t('La reserva se ha cancelado.') ?>
</div>
<?php elseif ($mensaje === "recurrente_cancelada"): ?>
<div class="mensaje mensaje-exito">
<?= t('La clase recurrente y sus reservas futuras se han cancelado.') ?>
</div>
<?php endif; ?>
<?php if ($resumen_recurrente !== ""): ?>
<?php [$cantidad_confirmadas, $cantidad_pre_reservas] = array_map('intval', explode('-', $resumen_recurrente)); ?>
<?php if ($cantidad_confirmadas > 0 || $cantidad_pre_reservas > 0): ?>
<div class="mensaje mensaje-exito">
<?= sprintf(
t('Además reservamos %d clases futuras del mismo horario. %d quedaron pendientes de que actives un paquete con usos disponibles.'),
$cantidad_confirmadas + $cantidad_pre_reservas,
$cantidad_pre_reservas
) ?>
</div>
<?php endif; ?>
<?php endif; ?>
<section>
<h2><?= t('Reservas') ?></h2>
<?php if (count($reservas_activas) === 0): ?>
    <p><?= t('Todavía no tienes reservas.') ?></p>
<?php else: ?>
<div class="navegacion-mes">
<?php if ($mostrar_mes_anterior_calendario): ?>
<a
class="boton-mes"
href="mis_reservas.php?mes=<?= $mes_anterior_calendario ?>&anio=<?= $anio_mes_anterior_calendario ?>"
aria-label="<?= t('Mes anterior') ?>"
>
←
</a>
<?php else: ?>
<span class="boton-mes boton-mes-deshabilitado" aria-hidden="true">
←
</span>
<?php endif; ?>
<span class="navegacion-mes-titulo">
<?= escapar(texto_mes($mes_calendario)) ?> <?= $anio_calendario ?>
</span>
<a
class="boton-mes"
href="mis_reservas.php?mes=<?= $mes_siguiente_calendario ?>&anio=<?= $anio_mes_siguiente_calendario ?>"
aria-label="<?= t('Mes siguiente') ?>"
>
→
</a>
</div>
<?php if (!$hay_reservas_en_mes_calendario): ?>
<div class="mensaje mensaje-aviso">
<?= t('No tienes reservas programadas este mes.') ?>
</div>
<?php endif; ?>
<div class="vista-calendario-escritorio">
<div class="calendario-mes-publico">
<div class="calendario-publico-cabecera">
<?php for ($d = 1; $d <= 7; $d++): ?>
<span><?= escapar(texto_dia_semana_abreviado($d)) ?></span>
<?php endfor; ?>
</div>
<div class="calendario-publico-grilla">
<?php foreach ($semanas_mes_calendario as $semana): ?>
<?php foreach ($semana as $dia_calendario): ?>
<?php if ($dia_calendario === null): ?>
<div class="dia-calendario-publico dia-calendario-publico-vacio">
</div>
<?php else: ?>
<?php
$clave_dia_calendario = $dia_calendario->format('Y-m-d');
$es_hoy_calendario = $clave_dia_calendario === $hoy_calendario->format('Y-m-d');
$es_pasado_calendario = $dia_calendario < $hoy_calendario;
?>
<div class="dia-calendario-publico<?= $es_hoy_calendario ? ' dia-calendario-publico-hoy' : '' ?><?= $es_pasado_calendario ? ' dia-calendario-publico-pasado' : '' ?>">
<span class="dia-calendario-publico-numero">
<?= (int) $dia_calendario->format('j') ?>
</span>
<?php if (!empty($reservas_por_dia_calendario[$clave_dia_calendario])): ?>
<div class="sesiones-dia-calendario">
<?php foreach ($reservas_por_dia_calendario[$clave_dia_calendario] as $reserva_dia): ?>
<button
type="button"
class="sesion-calendario-chip sesion-calendario-chip-clase<?= $reserva_dia['estado'] === 'pre_reserva' ? ' sesion-calendario-chip-pendiente' : '' ?>"
data-id-reserva="<?= (int) $reserva_dia['id_reserva'] ?>"
>
<span class="sesion-calendario-hora">
<?= escapar(formatear_hora($reserva_dia['hora_inicio'])) ?>
</span>
<span class="sesion-calendario-nombre">
<?= escapar($reserva_dia['actividad']) ?>
</span>
</button>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
<?php endif; ?>
<?php endforeach; ?>
<?php endforeach; ?>
</div>
</div>
</div>
<div class="vista-calendario-movil">
<p class="navegacion-semana-titulo" id="etiqueta-semana-activa-reservas">
<?= etiqueta_semana_de($fecha_activa_calendario) ?>
</p>
<div class="calendario-semana-contenedor">
<span class="flecha-semana flecha-semana-izquierda" aria-hidden="true">‹</span>
<div class="calendario-semana">
<?php foreach ($dias_mes_calendario as $dia_calendario): ?>
<?php $clave_dia_calendario = $dia_calendario->format('Y-m-d'); ?>
<button
type="button"
class="dia-semana-boton dia-semana-boton-reservas<?= $clave_dia_calendario === $clave_fecha_activa_calendario ? ' activo' : '' ?><?= $dia_calendario < $hoy_calendario ? ' dia-semana-boton-pasado' : '' ?>"
data-fecha="<?= $clave_dia_calendario ?>"
>
<span class="dia-semana-abrev">
<?= escapar(
texto_dia_semana_abreviado(
(int) $dia_calendario->format('N')
)
) ?>
</span>
<span class="dia-semana-numero">
<?= $dia_calendario->format('j') ?>
</span>
<?php if (!empty($reservas_por_dia_calendario[$clave_dia_calendario])): ?>
<span class="punto-dia-con-reserva" aria-hidden="true"></span>
<?php endif; ?>
</button>
<?php endforeach; ?>
</div>
<span class="flecha-semana flecha-semana-derecha" aria-hidden="true">›</span>
</div>
<div class="dias-actividades">
<?php foreach ($dias_mes_calendario as $dia_calendario): ?>
<?php $clave_dia_calendario = $dia_calendario->format('Y-m-d'); ?>
<div
class="dia-actividades dia-actividades-reservas<?= $clave_dia_calendario === $clave_fecha_activa_calendario ? ' activo' : '' ?>"
data-fecha="<?= $clave_dia_calendario ?>"
>
<?php if (empty($reservas_por_dia_calendario[$clave_dia_calendario])): ?>
<p class="sin-sesiones">
<?= t('No hay actividades programadas ese día.') ?>
</p>
<?php else: ?>
<?php foreach ($reservas_por_dia_calendario[$clave_dia_calendario] as $reserva_dia): ?>
<button
type="button"
class="item-actividad-dia<?= $reserva_dia['estado'] === 'pre_reserva' ? ' item-actividad-pendiente' : '' ?>"
data-id-reserva="<?= (int) $reserva_dia['id_reserva'] ?>"
>
<span class="item-actividad-hora">
<?= escapar(formatear_hora($reserva_dia['hora_inicio'])) ?> – <?= escapar(formatear_hora($reserva_dia['hora_fin'])) ?>
</span>
<span class="item-actividad-nombre">
<?= escapar($reserva_dia['actividad']) ?>
</span>
</button>
<?php endforeach; ?>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>
</div>
<div class="detalle-reserva-calendario">
<p class="aviso-selecciona-reserva" id="aviso-selecciona-reserva">
<?= t('Selecciona una clase del calendario para ver los detalles.') ?>
</p>
<?php foreach ($reservas_activas as $reserva): ?>
<div
class="detalle-reserva-item"
id="detalle-reserva-<?= (int) $reserva['id_reserva'] ?>"
data-detalle-reserva
hidden
>
<?php tarjeta_reserva($reserva); ?>
</div>
<?php endforeach; ?>
</div>
<script>
(function () {
const botonesReserva = document.querySelectorAll('[data-id-reserva]');
const detalles = document.querySelectorAll('[data-detalle-reserva]');
const aviso = document.getElementById('aviso-selecciona-reserva');
const contenedorDetalle = document.querySelector('.detalle-reserva-calendario');
botonesReserva.forEach(function (boton) {
boton.addEventListener('click', function () {
const idReserva = boton.getAttribute('data-id-reserva');
detalles.forEach(function (detalle) {
detalle.hidden = detalle.id !== 'detalle-reserva-' + idReserva;
});
if (aviso) {
aviso.hidden = true;
}
botonesReserva.forEach(function (b) {
b.classList.toggle('activo', b === boton);
});
if (contenedorDetalle) {
contenedorDetalle.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
});
});

const idioma = "<?= idiomaActual() === 'en' ? 'en' : 'es' ?>";
const conectorDe = idioma === "en" ? "" : " " + <?= json_encode(t('de')) ?>;
const botonesDiasReservas = document.querySelectorAll(".dia-semana-boton-reservas");
const panelesDiasReservas = document.querySelectorAll(".dia-actividades-reservas");
const etiquetaSemanaReservas = document.querySelector("#etiqueta-semana-activa-reservas");
const botonActivoReservas = document.querySelector(".dia-semana-boton-reservas.activo");
if (botonActivoReservas) {
botonActivoReservas.scrollIntoView({ inline: "center", block: "nearest" });
}

function calcularEtiquetaSemanaReservas(fechaStr) {
const fecha = new Date(fechaStr + "T00:00:00");
const diaIso = (fecha.getDay() + 6) % 7;
const lunes = new Date(fecha);
lunes.setDate(fecha.getDate() - diaIso);
const domingo = new Date(lunes);
domingo.setDate(lunes.getDate() + 6);
const formatoMes = new Intl.DateTimeFormat(idioma, { month: "long" });
if (lunes.getMonth() === domingo.getMonth()) {
return lunes.getDate() + " - " + domingo.getDate() +
conectorDe + " " + formatoMes.format(lunes);
}
return lunes.getDate() + conectorDe + " " + formatoMes.format(lunes) +
" - " + domingo.getDate() + conectorDe + " " + formatoMes.format(domingo);
}

botonesDiasReservas.forEach(function (boton) {
boton.addEventListener("click", function () {
const fecha = boton.getAttribute("data-fecha");
botonesDiasReservas.forEach(function (b) {
b.classList.toggle("activo", b === boton);
});
panelesDiasReservas.forEach(function (panel) {
panel.classList.toggle(
"activo",
panel.getAttribute("data-fecha") === fecha
);
});
if (etiquetaSemanaReservas) {
etiquetaSemanaReservas.textContent = calcularEtiquetaSemanaReservas(fecha);
}
});
});
})();
</script>
<?php endif; ?>
</section>

<section>
<h2><?= t('Clases recurrentes') ?></h2>
<?php if (empty($reservas_recurrentes)): ?>
<p>
<?= t('No tienes ninguna clase reservada de forma recurrente.') ?>
</p>
<?php else: ?>
<div class="rejilla-reservas">
<?php foreach ($reservas_recurrentes as $recurrente): ?>
<article class="tarjeta-reserva">
<h3>
<?= escapar($recurrente['actividad']) ?>
</h3>
<p>
<?= sprintf(
t('Todos los %s a las %s'),
texto_dia_semana((int) $recurrente['dia_semana']),
substr($recurrente['hora_inicio'], 0, 5)
) ?>
</p>
<form action="cancelar_recurrente.php" method="post">
<input
type="hidden"
name="id_recurrente"
value="<?= (int) $recurrente['id_recurrente'] ?>"
>
<input
type="hidden"
name="csrf_token"
value="<?= escapar(tokenCsrf()) ?>"
>
<button type="submit" class="boton peligro">
<?= t('Cancelar clase recurrente') ?>
</button>
</form>
</article>
<?php endforeach; ?>
</div>
<?php endif; ?>
</section>

<section>
<h2><?= t('Lista de espera') ?></h2>
<?php if ($lista_espera->num_rows === 0): ?>
<p>
<?= t('No estás esperando plaza en ninguna sesión.') ?>
</p>
<?php else: ?>
<div class="rejilla-reservas">
<?php while (
$espera = $lista_espera->fetch_assoc()
): ?>
<article class="tarjeta-reserva espera">
<h3>
    <?= escapar(
$espera["actividad"]
) ?>
</h3>

<p>
<?= date(
    "d/m/Y",
strtotime($espera["fecha"])
) ?>
·
<?= substr(
$espera["hora_inicio"],
0,
5
) ?>
</p>
<p>
<strong><?= t('Posición:') ?></strong>
<?= (int) $espera["posicion"] ?>
</p>
</article>
<?php endwhile; ?>
</div>
<?php endif; ?>
</section>

<section>
<h2><?= t('Historial de reservas') ?></h2>
<?php if (count($reservas_historial) === 0): ?>
<p>
<?= t('Todavía no tienes reservas en tu historial.') ?>
</p>
<?php elseif (count($historial_por_mes) === 1): ?>
<div class="rejilla-reservas">
<?php foreach ($reservas_historial as $reserva): ?>
<?php tarjeta_reserva($reserva, true); ?>
<?php endforeach; ?>
</div>
<?php else: ?>
<?php $indice_mes_historial = 0; ?>
<?php foreach ($historial_por_mes as $clave_mes => $reservas_del_mes): ?>
<?php
$fecha_mes = DateTime::createFromFormat('Y-m-d', $clave_mes . '-01');
$pagina_mes = intdiv($indice_mes_historial, $meses_por_pagina_historial);
?>
<div class="grupo-historial-mes" data-pagina="<?= $pagina_mes ?>">
<h3 class="titulo-mes-historial">
<?= escapar(texto_mes((int) $fecha_mes->format('n'))) ?> <?= $fecha_mes->format('Y') ?>
</h3>
<div class="rejilla-reservas">
<?php foreach ($reservas_del_mes as $reserva): ?>
<?php tarjeta_reserva($reserva, true); ?>
<?php endforeach; ?>
</div>
</div>
<?php $indice_mes_historial++; ?>
<?php endforeach; ?>
<?php if ($total_paginas_historial > 1): ?>
<div class="paginacion-sesiones">
<button
type="button"
class="boton-mes"
id="pagina-historial-anterior"
aria-label="<?= t('Meses anteriores') ?>"
disabled
>
←
</button>
<span id="indicador-pagina-historial">
1 / <?= $total_paginas_historial ?>
</span>
<button
type="button"
class="boton-mes"
id="pagina-historial-siguiente"
aria-label="<?= t('Meses siguientes') ?>"
>
→
</button>
</div>
<script>
(function () {
var grupos = document.querySelectorAll('.grupo-historial-mes');
var totalPaginas = <?= $total_paginas_historial ?>;
var paginaActual = 0;
var indicador = document.getElementById('indicador-pagina-historial');
var btnAnterior = document.getElementById('pagina-historial-anterior');
var btnSiguiente = document.getElementById('pagina-historial-siguiente');
function actualizar() {
grupos.forEach(function (grupo) {
grupo.style.display =
parseInt(grupo.dataset.pagina, 10) === paginaActual
? ''
: 'none';
});
indicador.textContent = (paginaActual + 1) + ' / ' + totalPaginas;
btnAnterior.disabled = paginaActual === 0;
btnSiguiente.disabled = paginaActual === totalPaginas - 1;
}
btnAnterior.addEventListener('click', function () {
if (paginaActual > 0) {
paginaActual--;
actualizar();
}
});
btnSiguiente.addEventListener('click', function () {
if (paginaActual < totalPaginas - 1) {
paginaActual++;
actualizar();
}
});
actualizar();
})();
</script>
<?php endif; ?>
<?php endif; ?>
</section>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
<?php
$stmt_reservas->close();
$stmt_espera->close();
$conexion->close();