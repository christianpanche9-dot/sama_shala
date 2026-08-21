<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../funciones.php';
$id_tenant = idTenantActual();
$sql = "
SELECT
id_tipo_paquete,
nombre,
numero_usos,
precio,
dias_validez,
activo
FROM tipos_paquete
WHERE id_tenant = ?
ORDER BY precio
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_tenant);
$stmt->execute();
$resultado = $stmt->get_result();
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
Paquetes | Administración
</title>
<link rel="stylesheet" href="../estilos.css">
</head>
<body>
<?php require_once __DIR__ . '/menu_admin.php'; ?>
<main class="contenedor seccion">
<div class="encabezado-con-accion">
<div>
<p class="etiqueta">
Administración
</p>
<h1>Paquetes</h1>
</div>
<a class="boton" href="nuevo_paquete.php">
Nuevo paquete
</a>
</div>
<?php if (($_GET['mensaje'] ?? '') === 'creado'): ?>
<div class="mensaje mensaje-exito">
El paquete se ha creado correctamente.
</div>
<?php endif; ?>
<?php if (($_GET['mensaje'] ?? '') === 'actualizado'): ?>
<div class="mensaje mensaje-exito">
El paquete se ha actualizado correctamente.
</div>
<?php endif; ?>
<?php if (($_GET['mensaje'] ?? '') === 'eliminado'): ?>
<div class="mensaje mensaje-exito">
El paquete se ha eliminado correctamente.
</div>
<?php endif; ?>
<?php if (($_GET['error'] ?? '') === 'en_uso'): ?>
<div class="mensaje mensaje-error">
No se puede eliminar el paquete porque algún cliente
ya lo ha comprado. Desactívalo si no quieres
seguir ofreciéndolo.
</div>
<?php endif; ?>
<div class="tabla-responsive">
<table class="tabla-admin">
<thead>
<tr>
<th>Paquete</th>
<th>Clases</th>
<th>Validez</th>
<th>Precio</th>
<th>Estado</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php while ($paquete = $resultado->fetch_assoc()): ?>
<tr>
<td>
<?= escapar($paquete['nombre']) ?>
</td>
<td>
<?= (int) $paquete['numero_usos'] ?>
</td>
<td>
<?= $paquete['dias_validez'] !== null
? (int) $paquete['dias_validez'] . ' días'
: 'Sin caducidad' ?>
</td>
<td>
<?= number_format(
(float) $paquete['precio'],
2,
',',
'.'
) ?> €
</td>
<td>
<?php if ((int) $paquete['activo'] === 1): ?>
<span class="estado estado-programada">
Activo
</span>
<?php else: ?>
<span class="estado estado-finalizada">
Inactivo
</span>
<?php endif; ?>
</td>
<td class="acciones-tabla">
<a
class="boton boton-secundario boton-pequeno"
href="editar_paquete.php?id_tipo_paquete=<?= (int) $paquete['id_tipo_paquete'] ?>"
>
Editar
</a>
<form
action="eliminar_paquete.php"
method="post"
onsubmit="return confirm('¿Seguro que quieres eliminar este paquete?');"
>
<input
type="hidden"
name="id_tipo_paquete"
value="<?= (int) $paquete['id_tipo_paquete'] ?>"
>
<button class="boton peligro boton-pequeno" type="submit">
Eliminar
</button>
</form>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</main>
</body>
</html>
