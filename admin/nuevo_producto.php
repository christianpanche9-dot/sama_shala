<?php
require_once "seguridad_admin.php";
require_once "../conexion.php";
require_once __DIR__ . '/../funciones.php';
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
Nuevo producto | Sama Shala
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
<h1>Nuevo producto</h1>
</div>
<?php if (isset($_GET['error'])): ?>
<div class="mensaje mensaje-error">
No se ha podido guardar el producto.
Revisa los datos del formulario.
</div>
<?php endif; ?>
<form
class="formulario-admin"
action="guardar_producto.php"
method="post"
enctype="multipart/form-data"
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
required
>
</div>
<div class="campo">
<label for="categoria">
Categoría
</label>
<select id="categoria" name="categoria" required>
<option value="">
Selecciona una categoría
</option>
<option value="bija">Bija</option>
<option value="ayurveda">Ayurveda</option>
<option value="fotografia">Fotografía</option>
<option value="angyoga">Angyoga</option>
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
></textarea>
</div>
<div class="campo">
<label for="imagen">
Foto de la tienda
</label>
<input
type="file"
id="imagen"
name="imagen"
accept="image/jpeg,image/png,image/webp"
required
>
<small>
Es la foto que se muestra en la tarjeta del producto.
JPG, PNG o WEBP, máximo 5 MB.
</small>
</div>
<div class="campo">
<label for="imagenes_detalle">
Fotos del detalle del producto
</label>
<input
type="file"
id="imagenes_detalle"
name="imagenes_detalle[]"
accept="image/jpeg,image/png,image/webp"
multiple
>
<small>
Puedes seleccionar varias fotos. Se muestran en la
página del producto además de la foto de la tienda.
</small>
</div>
<div class="campo-checkbox campo-completo">
<label>
<input
type="checkbox"
name="activo"
value="1"
checked
>
Mostrar el producto en la tienda
</label>
</div>
<div class="campo-completo">
<button class="boton" type="submit">
Guardar producto
</button>
</div>
</form>
</main>
</body>
</html>
