<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';

$id_tipo_bono = filter_input(
INPUT_GET,
'id_tipo_bono',
FILTER_VALIDATE_INT
);
if (!$id_tipo_bono) {
header('Location: bonos.php');
exit;
}

$sql = "
SELECT
id_tipo_bono,
nombre,
numero_usos,
precio,
dias_validez,
activo
FROM tipos_bono
WHERE id_tipo_bono = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_tipo_bono);
$stmt->execute();
$bono = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$bono) {
header('Location: bonos.php?error=no_encontrado');
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
Editar bono | Sama Shala
</title>
<link rel="stylesheet" href="../estilos.css">
</head>
<body>
<?php require_once __DIR__ . '/menu_admin.php'; ?>
<main class="contenedor seccion">
<a class="enlace-volver" href="bonos.php">
← Volver a bonos
</a>
<div class="encabezado-pagina">
<p class="etiqueta">
Bonos
</p>
<h1>Editar bono</h1>
</div>
<?php if (isset($_GET['error'])): ?>
<div class="mensaje mensaje-error">
No se ha podido guardar el bono.
Revisa los datos del formulario.
</div>
<?php endif; ?>
<form
class="formulario-admin"
action="actualizar_bono.php"
method="post"
>
<input
type="hidden"
name="id_tipo_bono"
value="<?= (int) $bono['id_tipo_bono'] ?>"
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
value="<?= escapar($bono['nombre']) ?>"
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
value="<?= (int) $bono['numero_usos'] ?>"
required
>
</div>
<div class="campo">
<label for="precio">
Precio (€)
</label>
<input
type="number"
id="precio"
name="precio"
min="0"
step="0.01"
value="<?= escapar((string) $bono['precio']) ?>"
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
value="<?= $bono['dias_validez'] !== null
? (int) $bono['dias_validez']
: '' ?>"
>
</div>
<div class="campo-checkbox campo-completo">
<label>
<input
type="checkbox"
name="activo"
value="1"
<?= (int) $bono['activo'] === 1 ? 'checked' : '' ?>
>
Mostrar el bono en el catálogo
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
