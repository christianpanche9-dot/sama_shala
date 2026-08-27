<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';

$totales = [
'actividades' => 0,
'espacios' => 0,
'profesores' => 0,
'sesiones' => 0
];
$consultas = [
'actividades' => "
SELECT COUNT(*) AS total
FROM actividades
",
'espacios' => "
SELECT COUNT(*) AS total
FROM espacios
",
'profesores' => "
SELECT COUNT(*) AS total
FROM profesores
",
'sesiones' => "
SELECT COUNT(*) AS total
FROM sesiones
WHERE TIMESTAMP(fecha, hora_fin) >= NOW()
"
];
foreach ($consultas as $clave => $sql) {
$resultado = $conexion->query($sql);
$fila = $resultado->fetch_assoc();
$totales[$clave] = (int) $fila['total'];
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
<title>
Administración | Sama Shala
</title>
<link rel="stylesheet" href="<?= urlEstilos('../') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="../imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="../imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="../imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu_admin.php'; ?>
<main class="contenedor seccion">
<div class="encabezado-pagina">
<p class="etiqueta">
Panel de control
</p>
<h1>Administración</h1>
<p>
</p>
</div>
Gestiona las actividades, los espacios,
los profesores y los horarios del centro.
<div class="rejilla-resumen-admin">
<a class="tarjeta-resumen" href="actividades.php">
<span>Actividades</span>
<strong>
<?= $totales['actividades'] ?>
</strong>
</a>
<a class="tarjeta-resumen" href="espacios.php">
<span>Espacios</span>
<strong>
<?= $totales['espacios'] ?>
</strong>
</a>
<a class="tarjeta-resumen" href="profesores.php">
<span>Profesores</span>
<strong>
<?= $totales['profesores'] ?>
</strong>
</a>
<a class="tarjeta-resumen" href="sesiones.php">
<span>Próximas sesiones</span>
<strong>
<?= $totales['sesiones'] ?>
</strong>
</a>
</div>
<div class="rejilla-acciones-admin">
<a class="boton-accion-admin" href="nueva_actividad.php">
<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
<path d="M3 12h4l2.5-7 4 14 2.5-7H21"/>
</svg>
<span>Nueva actividad</span>
</a>
<a class="boton-accion-admin" href="nuevo_espacio.php">
<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
<path d="M4 11 12 4l8 7"/>
<path d="M6 10v9a1 1 0 0 0 1 1h3v-6h4v6h3a1 1 0 0 0 1-1v-9"/>
</svg>
<span>Nuevo espacio</span>
</a>
<a class="boton-accion-admin" href="nuevo_profesor.php">
<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
<circle cx="12" cy="8" r="4"/>
<path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/>
</svg>
<span>Nuevo profesor</span>
</a>
<a class="boton-accion-admin" href="nueva_sesion.php">
<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
<rect x="3" y="5" width="18" height="16" rx="2"/>
<path d="M3 10h18"/>
<path d="M8 3v4M16 3v4"/>
<path d="M12 14v5M9.5 16.5h5"/>
</svg>
<span>Programar sesión</span>
</a>
</div>
</main>
</body>
</html>