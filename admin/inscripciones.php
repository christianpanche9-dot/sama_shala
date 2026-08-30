<?php
require_once "seguridad_admin.php";
require_once "../conexion.php";
require_once "../funciones.php";
$buscar = trim($_GET["buscar"] ?? "");
$patron = "%" . $buscar . "%";
$sql = "
SELECT
u.id_usuario,
u.nombre,
u.apellidos,
u.email,
fi.fecha_envio,
fi.fecha_actualizacion
FROM usuarios u
LEFT JOIN formulario_inscripcion fi
ON fi.id_usuario = u.id_usuario
WHERE (
? = ''
OR u.nombre LIKE ?
OR u.apellidos LIKE ?
OR u.email LIKE ?
)
ORDER BY fi.fecha_envio IS NULL, fi.fecha_envio DESC, u.apellidos ASC, u.nombre ASC
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param(
"ssss",
$buscar,
$patron,
$patron,
$patron
);
$stmt->execute();
$filas = $stmt->get_result();
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
<a class="enlace-volver" href="reservas.php">
← Volver a reservas
</a>
<h1>Respuestas del formulario de inscripción</h1>
<form method="get" class="filtros">
<div class="campo">
<label for="buscar">
Buscar usuario
</label>
<input
type="search"
id="buscar"
name="buscar"
value="<?= escapar($buscar) ?>"
placeholder="Nombre, apellidos o correo"
>
</div>
<div class="campo campo-acciones-filtro">
<div class="acciones-filtro">
<button type="submit" class="boton">
Aplicar filtros
</button>
<a
href="inscripciones.php"
class="boton boton-secundario"
>
Limpiar
</a>
</div>
</div>
</form>
<?php if ($filas->num_rows === 0): ?>
<p>No se han encontrado usuarios.</p>
<?php else: ?>
<div class="tabla-responsive">
<table class="tabla-admin">
<thead>
<tr>
<th>Usuario</th>
<th>Correo</th>
<th>Estado</th>
<th>Última actualización</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php while ($fila = $filas->fetch_assoc()): ?>
<tr>
<td>
<?= escapar($fila['nombre'] . ' ' . $fila['apellidos']) ?>
</td>
<td>
<?= escapar($fila['email']) ?>
</td>
<td>
<?= $fila['fecha_envio'] !== null
    ? 'Completado'
    : 'Pendiente' ?>
</td>
<td>
<?= $fila['fecha_actualizacion'] !== null
    ? date('d/m/Y H:i', strtotime($fila['fecha_actualizacion']))
    : '—' ?>
</td>
<td class="acciones-tabla">
<a
class="boton boton-secundario"
href="ver_inscripcion.php?id_usuario=<?= (int) $fila['id_usuario'] ?>"
>
Ver respuestas
</a>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</main>
</body>
</html>
