<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
$id_actividad = filter_input(
INPUT_GET,
'id',
FILTER_VALIDATE_INT
);
if (!$id_actividad || $id_actividad < 1) {
http_response_code(400);
die(t('El identificador no es válido.'));
}
$sql_actividad = "
SELECT
id_actividad,
nombre,
descripcion,
categoria,
tipo,
nivel,
duracion_minutos,
imagen
FROM actividades
WHERE id_actividad = ?
AND activa = 1
";
$stmt_actividad =
$conexion->prepare($sql_actividad);
$stmt_actividad->bind_param(
'i',
$id_actividad
);
$stmt_actividad->execute();
$resultado_actividad =
$stmt_actividad->get_result();
$actividad =
$resultado_actividad->fetch_assoc();
if (!$actividad) {
http_response_code(404);
die(t('La actividad no existe o no está disponible.'));
}
$sql_sesiones = "
SELECT
s.id_sesion,
s.fecha,
s.hora_inicio,
s.hora_fin,
s.aforo,
s.estado,
s.observaciones,
e.nombre AS espacio,
e.ubicacion,
CONCAT(
m.nombre,
' ',
m.apellidos
) AS profesor,
COUNT(r.id_reserva)
AS reservas_confirmadas,
GREATEST(
s.aforo - COUNT(r.id_reserva),
0
) AS plazas_disponibles
FROM sesiones AS s
INNER JOIN espacios AS e
ON s.id_espacio = e.id_espacio
INNER JOIN profesores AS m
ON s.id_profesor = m.id_profesor
LEFT JOIN reservas AS r
ON r.id_sesion = s.id_sesion
AND r.estado = 'confirmada'
WHERE s.id_actividad = ?
AND s.estado IN (
'programada',
'completa'
)
AND TIMESTAMP(
s.fecha,
s.hora_fin
) >= NOW()
GROUP BY
s.id_sesion,
s.fecha,
s.hora_inicio,
s.hora_fin,
s.aforo,
s.estado,
s.observaciones,
e.nombre,
e.ubicacion,
m.nombre,
m.apellidos
ORDER BY
s.fecha,
s.hora_inicio
";
$stmt_sesiones =
$conexion->prepare($sql_sesiones);
$stmt_sesiones->bind_param(
'i',
$id_actividad
);
$stmt_sesiones->execute();
$resultado_sesiones =
$stmt_sesiones->get_result();
$sesiones_actividad = $resultado_sesiones->fetch_all(MYSQLI_ASSOC);
$sesiones_por_pagina = 5;
$total_paginas_sesiones = (int) ceil(
count($sesiones_actividad) / $sesiones_por_pagina
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title>
<?= escapar($actividad['nombre']) ?> <?= t('| Sama Shala') ?>
</title>
<link rel="stylesheet" href="<?= urlEstilos() ?>">
<link rel="icon" type="image/png" sizes="32x32" href="imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu.php'; ?>
<main>
<section class="detalle-actividad">
<div class="contenedor detalle-cabecera">
<div>
<?php if (!empty($actividad['imagen'])): ?>
<img
class="imagen-detalle"
src="imagenes/actividades/<?= escapar(
$actividad['imagen']
) ?>"
alt="<?= escapar(
$actividad['nombre']
) ?>"
>
<?php endif; ?>
</div>
<div>
<div class="metadatos">
<span class="insignia">
    <?= escapar(
$actividad['categoria']
) ?>
</span>
<span class="insignia insignia-clara">
<?= escapar(
texto_tipo_actividad(
$actividad['tipo']
)
) ?>
</span>
<span class="insignia insignia-clara">
<?= escapar(
texto_nivel(
$actividad['nivel']
)
) ?>
</span>
</div>
<h1>
<?= escapar($actividad['nombre']) ?>
</h1>
<p class="descripcion-destacada">
<?= escapar(
$actividad['descripcion']
) ?>
</p>
<p>
<strong><?= t('Duración habitual:') ?></strong>
<?= (int) $actividad['duracion_minutos'] ?> <?= (int) $actividad['duracion_minutos'] === 1 ? t('minuto') : t('minutos') ?>
</p>
<a
class="enlace-volver"
href="actividades.php"
>
<?= t('← Volver a las actividades') ?>
</a>
</div>
</div>
</section>
<section class="contenedor seccion">
<h2><?= t('Próximas sesiones') ?></h2>
<?php if (count($sesiones_actividad) === 0): ?>

<div class="mensaje mensaje-aviso">
<?= t('Esta actividad todavía no tiene próximas sesiones disponibles.') ?>
</div>
<?php else: ?>
<div class="lista-sesiones" id="lista-sesiones">
<?php foreach ($sesiones_actividad as $indice_sesion => $sesion): ?>
<?php
$pagina_sesion = intdiv($indice_sesion, $sesiones_por_pagina);
$plazas = (int)
$sesion['plazas_disponibles'];
$reservas = (int)
$sesion['reservas_confirmadas'];
$aforo = (int)
$sesion['aforo'];
$porcentaje =
calcular_porcentaje_ocupacion(
$reservas,
$aforo
);
?>
<article class="tarjeta-sesion" data-pagina="<?= $pagina_sesion ?>">
<div class="fecha-sesion">
<span class="fecha-principal">
<?= escapar(
formatear_fecha(
$sesion['fecha']
)
) ?>
</span>
<span>
<?= escapar(
formatear_hora(
$sesion['hora_inicio']
)
) ?>
–
<?= escapar(
formatear_hora(
$sesion['hora_fin']
)
) ?>
</span>
</div>
<div class="datos-sesion">
<p>
<strong><?= t('Espacio:') ?></strong>
<?= escapar(
$sesion['espacio']
) ?>
</p>
<?php if (
    !empty($sesion['ubicacion'])
): ?>

<p>
<strong><?= t('Ubicación:') ?></strong>
<?= escapar(
$sesion['ubicacion']
) ?>
</p>
<?php endif; ?>
<p>
<strong><?= t('Profesor:') ?></strong>
<?= escapar(
$sesion['profesor']
) ?>
</p>
<p>
<strong><?= t('Aforo:') ?></strong>
<?= $aforo ?> <?= $aforo === 1 ? t('persona') : t('personas') ?>
</p>
<div class="barra-ocupacion">
<div
class="barra-ocupacion-interior"
style="width:
<?= $porcentaje ?>%"
></div>
</div>
<small>
<?= $reservas ?> <?= t('de') ?> <?= $aforo ?> <?= $aforo === 1 ? t('plaza ocupada') : t('plazas ocupadas') ?>
</small>
</div>
<div class="acciones-sesion">
<?php if ($plazas > 0): ?>
<span class="plazas-disponibles">
<?= $plazas ?> <?= $plazas === 1 ? t('plaza disponible') : t('plazas disponibles') ?>
</span>
<?php else: ?>
<span class="sesion-completa">
<?= t('Sesión completa') ?>
</span>
<?php endif; ?>
<a
class="boton"
href="detalle_sesion.php?id=<?= (int)
$sesion['id_sesion'] ?>"
>
<?= t('Ver sesión') ?>
</a>
</div>
</article>
<?php endforeach; ?>
</div>
<?php if ($total_paginas_sesiones > 1): ?>
<div class="paginacion-sesiones">
<button
type="button"
class="boton-mes"
id="pagina-sesiones-anterior"
aria-label="<?= t('Sesiones anteriores') ?>"
disabled
>
←
</button>
<span id="indicador-pagina-sesiones">
1 / <?= $total_paginas_sesiones ?>
</span>
<button
type="button"
class="boton-mes"
id="pagina-sesiones-siguiente"
aria-label="<?= t('Siguientes sesiones') ?>"
>
→
</button>
</div>
<script>
(function () {
var lista = document.getElementById('lista-sesiones');
var tarjetas = lista.querySelectorAll('[data-pagina]');
var totalPaginas = <?= $total_paginas_sesiones ?>;
var paginaActual = 0;
var indicador = document.getElementById('indicador-pagina-sesiones');
var btnAnterior = document.getElementById('pagina-sesiones-anterior');
var btnSiguiente = document.getElementById('pagina-sesiones-siguiente');
function actualizar() {
tarjetas.forEach(function (tarjeta) {
tarjeta.style.display =
parseInt(tarjeta.dataset.pagina, 10) === paginaActual
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
lista.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
});
btnSiguiente.addEventListener('click', function () {
if (paginaActual < totalPaginas - 1) {
paginaActual++;
actualizar();
lista.scrollIntoView({ behavior: 'smooth', block: 'start' });
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