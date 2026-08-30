<?php
require_once "seguridad_admin.php";
require_once "../conexion.php";
require_once "../funciones.php";

$id_usuario = filter_input(
    INPUT_GET,
    'id_usuario',
    FILTER_VALIDATE_INT
);
if (!$id_usuario) {
    header('Location: usuarios.php');
    exit;
}

$sql = "
SELECT id_usuario, nombre, apellidos, email
FROM usuarios
WHERE id_usuario = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) {
    header('Location: usuarios.php?error=no_encontrado');
    exit;
}

$inscripcion = obtenerInscripcion($conexion, $id_usuario);

function textoSiNo(?string $valor): string
{
    return $valor === 'si' ? 'Sí' : 'No';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title>Formulario de inscripción | Administración</title>
<link rel="stylesheet" href="<?= urlEstilos('../') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="../imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="../imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="../imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu_admin.php'; ?>
<main class="contenedor seccion">
<a class="enlace-volver" href="inscripciones.php">
← Volver a respuestas
</a>
<h1>
Formulario de inscripción de
<?= escapar($usuario['nombre'] . ' ' . $usuario['apellidos']) ?>
</h1>
<?php if (!$inscripcion): ?>
<div class="mensaje mensaje-aviso">
Este usuario todavía no ha completado el formulario de inscripción.
</div>
<?php else: ?>
<div class="panel-cuenta">
<dl>
<dt>Nombre completo</dt>
<dd><?= escapar($inscripcion['nombre']) ?></dd>
<dt>Correo electrónico</dt>
<dd><?= escapar($inscripcion['email']) ?></dd>
<dt>Celular / Teléfono</dt>
<dd><?= escapar($inscripcion['telefono']) ?></dd>
<dt>Fecha de nacimiento</dt>
<dd><?= date('d/m/Y', strtotime($inscripcion['fecha_nacimiento'])) ?></dd>
<dt>¿Ha practicado yoga o meditación antes?</dt>
<dd><?= textoSiNo($inscripcion['experiencia_previa']) ?></dd>
<dt>¿Tiene alguna lesión, dolor o condición física?</dt>
<dd>
<?= textoSiNo($inscripcion['tiene_lesion']) ?>
<?php if ($inscripcion['tiene_lesion'] === 'si' && $inscripcion['detalle_lesion'] !== null && $inscripcion['detalle_lesion'] !== ''): ?>
— <?= escapar($inscripcion['detalle_lesion']) ?>
<?php endif; ?>
</dd>
<dt>¿Ha tenido alguna cirugía o condición médica relevante?</dt>
<dd>
<?= textoSiNo($inscripcion['tiene_cirugia']) ?>
<?php if ($inscripcion['tiene_cirugia'] === 'si' && $inscripcion['detalle_cirugia'] !== null && $inscripcion['detalle_cirugia'] !== ''): ?>
— <?= escapar($inscripcion['detalle_cirugia']) ?>
<?php endif; ?>
</dd>
<dt>Hobbies o intereses</dt>
<dd><?= $inscripcion['hobbies'] !== null && $inscripcion['hobbies'] !== '' ? escapar($inscripcion['hobbies']) : '—' ?></dd>
<dt>Autorización de datos e imagen</dt>
<dd><?= textoSiNo($inscripcion['autorizacion_datos_imagen']) ?></dd>
<dt>Enviado</dt>
<dd><?= date('d/m/Y H:i', strtotime($inscripcion['fecha_envio'])) ?></dd>
<dt>Última actualización</dt>
<dd><?= date('d/m/Y H:i', strtotime($inscripcion['fecha_actualizacion'])) ?></dd>
</dl>
</div>
<?php endif; ?>
</main>
</body>
</html>
