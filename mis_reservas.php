<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
$reserva["estado"] === "confirmada" &&
$reserva["estado_sesion"] !== "cancelada" &&
$inicio > (new DateTime())->modify("+15 minutes");
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
<?= escapar(
t(ucfirst($reserva["estado"]))
) ?>
</p>
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
<?php endif; ?>
<section>
<h2><?= t('Reservas') ?></h2>
<?php if (count($reservas_activas) === 0): ?>
    <p><?= t('Todavía no tienes reservas.') ?></p>
<?php else: ?>
<div class="rejilla-reservas">
<?php foreach ($reservas_activas as $reserva): ?>
<?php tarjeta_reserva($reserva); ?>
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