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
<link rel="stylesheet" href="../estilos.css">
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
<div class="grupo-botones">
<a class="boton" href="nueva_actividad.php">
Nueva actividad
</a>
<a class="boton" href="nuevo_espacio.php">
Nuevo espacio
</a>
<a class="boton" href="nuevo_profesor.php">
Nuevo profesor
</a>
<a class="boton" href="nueva_sesion.php">
Programar sesión
</a>
</div>
</main>
</body>
</html>