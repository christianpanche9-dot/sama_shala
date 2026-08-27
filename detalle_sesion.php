<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
$id_sesion = filter_input(
INPUT_GET,
'id',
FILTER_VALIDATE_INT
);
if (!$id_sesion || $id_sesion < 1) {
http_response_code(400);
die(t('El identificador de la sesión no es válido.'));
}
$sql = "
SELECT
s.id_sesion,
s.fecha,
s.hora_inicio,
s.hora_fin,
s.aforo,
s.estado,
s.observaciones,
TIMESTAMPDIFF(
MINUTE,
s.hora_inicio,
s.hora_fin
) AS duracion_real,
a.id_actividad,
a.nombre AS actividad,
a.descripcion,
a.categoria,
a.tipo,
a.precio,
a.nivel,
a.duracion_minutos,
a.imagen,
e.nombre AS espacio,
e.ubicacion,
e.descripcion AS descripcion_espacio,
e.aforo_maximo,
CONCAT(
m.nombre,
' ',
m.apellidos
) AS profesor,
m.especialidad,
COUNT(r.id_reserva)
AS reservas_confirmadas,
GREATEST(
s.aforo - COUNT(r.id_reserva),
0
) AS plazas_disponibles
FROM sesiones AS s
INNER JOIN actividades AS a
ON s.id_actividad = a.id_actividad
INNER JOIN espacios AS e
ON s.id_espacio = e.id_espacio
INNER JOIN profesores AS m
ON s.id_profesor = m.id_profesor
LEFT JOIN reservas AS r
ON r.id_sesion = s.id_sesion
AND r.estado = 'confirmada'
WHERE s.id_sesion = ?
AND a.activa = 1
GROUP BY
s.id_sesion,
s.fecha,
s.hora_inicio,
s.hora_fin,
s.aforo,
s.estado,
s.observaciones,
a.id_actividad,
a.nombre,
a.descripcion,
a.categoria,
a.tipo,
a.precio,
a.nivel,
a.duracion_minutos,
a.imagen,
e.nombre,
e.ubicacion,
e.descripcion,
e.aforo_maximo,
m.nombre,
m.apellidos,
m.especialidad
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param(
'i',
$id_sesion
);
$stmt->execute();
$resultado = $stmt->get_result();
$sesion = $resultado->fetch_assoc();
if (!$sesion) {
http_response_code(404);
die(t('La sesión solicitada no existe.'));
}
$plazas =
(int) $sesion['plazas_disponibles'];
$reservas =
(int) $sesion['reservas_confirmadas'];
$aforo =
(int) $sesion['aforo'];
$porcentaje =
calcular_porcentaje_ocupacion(
$reservas,
$aforo
);
$fecha_final = new DateTime(
$sesion['fecha'] . ' ' .
$sesion['hora_fin']
);
$sesion_terminada =
$fecha_final < new DateTime();
$puede_reservarse =
!$sesion_terminada
&& $sesion['estado'] === 'programada'
&& $plazas > 0;
$usuario_autenticado = usuarioAutenticado();
$error_reserva = trim($_GET['error'] ?? '');
$descuento_usuario = null;
if (
$usuario_autenticado &&
$sesion['tipo'] !== 'clase' &&
$sesion['precio'] !== null
) {
$descuento_usuario = mejorDescuentoEventoTerapia(
$conexion,
idUsuarioActual()
);
}

$lista_espera =
    !$sesion_terminada &&
    $sesion['estado'] === 'completa' &&
    $plazas === 0;
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
<?= escapar($sesion['actividad']) ?> <?= t('| Sama Shala') ?>
</title>
<link rel="stylesheet" href="<?= urlEstilos() ?>">
<link rel="icon" type="image/png" sizes="32x32" href="imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu.php'; ?>
<main class="contenedor seccion">
<a
class="enlace-volver"
href="detalle_actividad.php?id=<?= (int)
$sesion['id_actividad'] ?>"
>
<?= t('← Volver a la actividad') ?>
</a>
<?php if ($error_reserva !== ''): ?>
<div class="mensaje mensaje-error">
<?= escapar($error_reserva) ?>
</div>
<?php endif; ?>
<div class="ficha-sesion">
<section class="informacion-sesion">
<div class="metadatos">
<span class="insignia">
<?= escapar(
$sesion['categoria']
) ?>
</span>
<span class="insignia insignia-clara">
<?= escapar(
texto_nivel(
$sesion['nivel']
)
) ?>
</span>
</div>
<h1>
    <?= escapar($sesion['actividad']) ?>
</h1>

<p class="descripcion-destacada">
<?= escapar(
$sesion['descripcion']
) ?>
</p>
<div class="rejilla-datos">
    <div class="dato">
<span><?= t('Fecha') ?></span>
<strong>
<?= escapar(
formatear_fecha(
$sesion['fecha']
)
) ?>
</strong>
</div>
<div class="dato">
<span><?= t('Horario') ?></span>
<strong>
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
</strong>
</div>
<div class="dato">
<span><?= t('Duración') ?></span>
<strong>
<?= (int) $sesion['duracion_real'] ?> <?= (int) $sesion['duracion_real'] === 1 ? t('minuto') : t('minutos') ?>
</strong>
</div>
<div class="dato">
<span><?= t('Espacio') ?></span>
<strong>
<?= escapar(
$sesion['espacio']
) ?>
</strong>
</div>
<div class="dato">
<span><?= t('Ubicación') ?></span>
<strong>
<?= escapar(
$sesion['ubicacion']
) ?>
</strong>
</div>
<div class="dato">
<span><?= t('Profesor') ?></span>
<strong>
<?= escapar(
$sesion['profesor']
) ?>
</strong>
</div>
</div>
<?php if (
    !empty($sesion['especialidad'])
): ?>

<p>
<strong><?= t('Especialidad del profesor:') ?></strong>
<?= escapar(
$sesion['especialidad']
) ?>
</p>
<?php endif; ?>
<?php if (
    !empty($sesion['observaciones'])
): ?>

<div class="mensaje mensaje-aviso">
<strong><?= t('Observaciones:') ?></strong>
<?= escapar(
$sesion['observaciones']
) ?>
</div>
<?php endif; ?>
</section>
<aside class="panel-reserva">
<span
class="estado estado-<?= escapar(
clase_estado_sesion(
$sesion['estado']
)
) ?>"
>
<?= escapar(
texto_estado_sesion(
    $sesion['estado']
)
) ?>
</span>
<?php if ($sesion['tipo'] !== 'clase' && $sesion['precio'] !== null): ?>
<p class="precio-sesion">
<?= t('Precio:') ?>
<?php if ($descuento_usuario !== null): ?>
<span class="precio-tachado">
<?= formatear_precio((float) $sesion['precio']) ?>
</span>
<strong>
<?= formatear_precio(
precio_con_descuento(
(float) $sesion['precio'],
$descuento_usuario['descuento']
)
) ?>
</strong>
<?php else: ?>
<strong><?= formatear_precio((float) $sesion['precio']) ?></strong>
<?php endif; ?>
</p>
<?php if ($descuento_usuario !== null): ?>
<div class="mensaje mensaje-exito">
<?= sprintf(
t('Tienes un %d%% de descuento en este evento o terapia gracias a tu paquete %s.'),
$descuento_usuario['descuento'],
escapar($descuento_usuario['nombre_paquete'])
) ?>
</div>
<?php endif; ?>
<?php endif; ?>
<h2><?= t('Disponibilidad') ?></h2>
<p class="numero-plazas">
<?= $plazas ?>
</p>
<p>
    <?= $plazas === 1 ? t('plaza disponible de') : t('plazas disponibles de') ?> <?= $aforo ?>
</p>

<div class="barra-ocupacion">
<div
class="barra-ocupacion-interior"
style="width:
<?= $porcentaje ?>%"
></div>
</div>
<p>
    <?= $reservas ?>
 <?= $reservas === 1 ? t('reserva confirmada') : t('reservas confirmadas') ?>
</p>
<?php if ($sesion_terminada): ?>

    <div class="mensaje mensaje-aviso">
        <?= t('Esta sesión ya ha finalizado.') ?>
    </div>

<?php elseif ($sesion['estado'] === 'cancelada'): ?>

    <div class="mensaje mensaje-error">
        <?= t('Esta sesión ha sido cancelada.') ?>
    </div>

<?php elseif ($plazas > 0): ?>

    <?php if ($usuario_autenticado): ?>

        <a
            class="boton boton-bloque"
            href="reservar.php?id=<?= (int)$sesion['id_sesion'] ?>"
        >
            <?= t('Reservar una plaza') ?>
        </a>

    <?php else: ?>

        <a
            class="boton boton-espera boton-bloque"
            href="login.php?error=acceso&volver=<?= urlencode('/detalle_sesion.php?id=' . $sesion['id_sesion']) ?>"
        >
            <?= t('Inicia sesión para reservar') ?>
        </a>

    <?php endif; ?>

<?php elseif ($lista_espera): ?>

    <a
        class="boton secundario"
        href="apuntar_lista_espera.php?id=<?= (int)$sesion['id_sesion'] ?>"
    >
        <?= t('Apuntarme a la lista de espera') ?>
    </a>

<?php endif; ?>
</aside>
</div>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>