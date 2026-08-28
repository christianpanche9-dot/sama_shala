<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';

$id_producto = filter_input(
INPUT_GET,
'id_producto',
FILTER_VALIDATE_INT
);
if (!$id_producto) {
header('Location: productos.php');
exit;
}

$sql = "
SELECT
id_producto,
nombre,
descripcion,
categoria,
precio,
tallas,
imagen,
activo
FROM productos
WHERE id_producto = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_producto);
$stmt->execute();
$producto = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$producto) {
header('Location: productos.php?error=no_encontrado');
exit;
}

$sql_imagenes = "
SELECT id_imagen, imagen
FROM producto_imagenes
WHERE id_producto = ?
ORDER BY orden, id_imagen
";
$stmt_imagenes = $conexion->prepare($sql_imagenes);
$stmt_imagenes->bind_param('i', $id_producto);
$stmt_imagenes->execute();
$imagenes_detalle = $stmt_imagenes->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_imagenes->close();
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
Editar producto | Sama Shala
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
<a class="enlace-volver" href="productos.php">
← Volver a la tienda
</a>
<div class="encabezado-pagina">
<p class="etiqueta">
Tienda
</p>
<h1>Editar producto</h1>
</div>
<?php if (isset($_GET['error'])): ?>
<div class="mensaje mensaje-error">
No se ha podido actualizar el producto.
Revisa los datos del formulario.
</div>
<?php endif; ?>
<form
class="formulario-admin"
action="actualizar_producto.php"
method="post"
enctype="multipart/form-data"
>
<input
type="hidden"
name="id_producto"
value="<?= (int) $producto['id_producto'] ?>"
>
<div class="campo">
<label for="nombre">
Nombre
</label>
<input
type="text"
id="nombre"
name="nombre"
maxlength="150"
value="<?= escapar($producto['nombre']) ?>"
required
>
</div>
<div class="campo">
<label for="categoria">
Categoría
</label>
<select id="categoria" name="categoria" required>
<option value="bija" <?= $producto['categoria'] === 'bija' ? 'selected' : '' ?>>
Bija
</option>
<option value="ayurveda" <?= $producto['categoria'] === 'ayurveda' ? 'selected' : '' ?>>
Ayurveda
</option>
<option value="fotografia" <?= $producto['categoria'] === 'fotografia' ? 'selected' : '' ?>>
Fotografía
</option>
<option value="angyoga" <?= $producto['categoria'] === 'angyoga' ? 'selected' : '' ?>>
Angyoga
</option>
</select>
</div>
<div class="campo">
<label for="precio">
Precio
</label>
<input
type="number"
id="precio"
name="precio"
min="0"
step="0.01"
value="<?= (float) $producto['precio'] ?>"
required
>
</div>
<div class="campo">
<label for="tallas">
Tallas (opcional)
</label>
<input
type="text"
id="tallas"
name="tallas"
maxlength="100"
value="<?= escapar($producto['tallas'] ?? '') ?>"
placeholder="S, M, L, XL"
>
<small>
Sepáralas con comas. Déjalo vacío si el producto no
maneja tallas.
</small>
</div>
<div class="campo campo-completo">
<label for="descripcion">
Descripción
</label>
<textarea
id="descripcion"
name="descripcion"
rows="6"
required
><?= escapar($producto['descripcion']) ?></textarea>
</div>
<div class="campo campo-completo">
<label for="imagen">
Foto de la tienda
</label>
<?php if (!empty($producto['imagen'])): ?>
<img
class="miniatura-imagen-actual"
src="../imagenes/productos/<?= escapar($producto['imagen']) ?>"
alt=""
>
<?php endif; ?>
<input
type="file"
id="imagen"
name="imagen"
accept="image/jpeg,image/png,image/webp"
>
<small>
Deja este campo vacío para mantener la foto actual.
JPG, PNG o WEBP, máximo 5 MB.
</small>
</div>
<?php if (!empty($imagenes_detalle)): ?>
<div class="campo campo-completo">
<label>
Fotos del detalle actuales
</label>
<div class="rejilla-imagenes-detalle">
<?php foreach ($imagenes_detalle as $imagen_detalle): ?>
<label class="miniatura-imagen-detalle">
<img
src="../imagenes/productos/<?= escapar($imagen_detalle['imagen']) ?>"
alt=""
>
<span class="etiqueta-eliminar-imagen-detalle">
<input
type="checkbox"
name="eliminar_imagenes[]"
value="<?= (int) $imagen_detalle['id_imagen'] ?>"
>
Eliminar
</span>
</label>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>
<div class="campo campo-completo">
<label for="imagenes_detalle">
Agregar fotos del detalle
</label>
<input
type="file"
id="imagenes_detalle"
name="imagenes_detalle[]"
accept="image/jpeg,image/png,image/webp"
multiple
>
<small>
Puedes seleccionar varias fotos. Se suman a las fotos
del detalle que ya tiene el producto.
</small>
</div>
<div class="campo-checkbox campo-completo">
<label>
<input
type="checkbox"
name="activo"
value="1"
<?= (int) $producto['activo'] === 1 ? 'checked' : '' ?>
>
Mostrar el producto en la tienda
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
