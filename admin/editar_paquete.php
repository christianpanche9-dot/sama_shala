<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';

$id_tipo_paquete = filter_input(
INPUT_GET,
'id_tipo_paquete',
FILTER_VALIDATE_INT
);
if (!$id_tipo_paquete) {
header('Location: paquetes.php');
exit;
}

$sql = "
SELECT
id_tipo_paquete,
nombre,
numero_usos,
precio,
activo
FROM tipos_paquete
WHERE id_tipo_paquete = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_tipo_paquete);
$stmt->execute();
$paquete = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$paquete) {
header('Location: paquetes.php?error=no_encontrado');
exit;
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
Editar paquete | Sama Shala
</title>
<link rel="stylesheet" href="../estilos.css">
<link rel="icon" type="image/png" sizes="32x32" href="../imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="../imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="../imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu_admin.php'; ?>
<main class="contenedor seccion">
<a class="enlace-volver" href="paquetes.php">
← Volver a paquetes
</a>
<div class="encabezado-pagina">
<p class="etiqueta">
Paquetes
</p>
<h1>Editar paquete</h1>
</div>
<?php if (isset($_GET['error'])): ?>
<div class="mensaje mensaje-error">
No se ha podido guardar el paquete.
Revisa los datos del formulario.
</div>
<?php endif; ?>
<form
class="formulario-admin"
action="actualizar_paquete.php"
method="post"
>
<input
type="hidden"
name="id_tipo_paquete"
value="<?= (int) $paquete['id_tipo_paquete'] ?>"
>
<div class="campo">
<label for="nombre">
Nombre
</label>
<input
type="text"
id="nombre"
name="nombre"
maxlength="100"
value="<?= escapar($paquete['nombre']) ?>"
required
>
</div>
<div class="campo">
<label for="numero_usos">
Número de clases
</label>
<input
type="number"
id="numero_usos"
name="numero_usos"
min="1"
max="365"
value="<?= (int) $paquete['numero_usos'] ?>"
required
>
</div>
<div class="campo">
<label for="precio">
Precio (USD)
</label>
<input
type="number"
id="precio"
name="precio"
min="0"
step="0.01"
value="<?= escapar((string) $paquete['precio']) ?>"
required
>
</div>
<div class="campo campo-completo">
<label>
Validez
</label>
<p>Todos los paquetes duran 1 mes desde la fecha de compra.</p>
</div>
<div class="campo-checkbox campo-completo">
<label>
<input
type="checkbox"
name="activo"
value="1"
<?= (int) $paquete['activo'] === 1 ? 'checked' : '' ?>
>
Mostrar el paquete en el catálogo
</label>
</div>
<div class="campo-completo">
<button class="boton" type="submit">
Guardar cambios
</button>
</div>
</form>
</main>
</body>
</html>
