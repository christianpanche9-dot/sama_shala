<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';
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
Nuevo paquete | Sama Shala
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
<h1>Nuevo paquete</h1>
</div>
<?php if (isset($_GET['error'])): ?>
<div class="mensaje mensaje-error">
No se ha podido guardar el paquete.
Revisa los datos del formulario.
</div>
<?php endif; ?>
<form
class="formulario-admin"
action="guardar_paquete.php"
method="post"
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
value="8"
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
checked
>
Mostrar el paquete en el catálogo
</label>
</div>
<div class="campo-completo">
<button class="boton" type="submit">
Guardar paquete
</button>
</div>
</form>
</main>
</body>
</html>
