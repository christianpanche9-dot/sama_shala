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
<div class="campo">
<label for="dias_validez">
Validez en días
</label>
<input
type="number"
id="dias_validez"
name="dias_validez"
min="1"
max="730"
placeholder="Déjalo en blanco si no caduca"
>
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
