<?php
require_once "seguridad.php";
require_once "conexion.php";

$id_usuario = idUsuarioActual();
$inscripcion = obtenerInscripcion($conexion, $id_usuario);
$error = $_GET["error"] ?? "";

$nombre = $inscripcion["nombre"] ?? nombreUsuarioActual();
$email = $inscripcion["email"] ?? ($_SESSION["usuario"]["email"] ?? "");
$telefono = $inscripcion["telefono"] ?? "";
$fecha_nacimiento = $inscripcion["fecha_nacimiento"] ?? "";
$experiencia_previa = $inscripcion["experiencia_previa"] ?? "";
$tiene_lesion = $inscripcion["tiene_lesion"] ?? "";
$detalle_lesion = $inscripcion["detalle_lesion"] ?? "";
$tiene_cirugia = $inscripcion["tiene_cirugia"] ?? "";
$detalle_cirugia = $inscripcion["detalle_cirugia"] ?? "";
$hobbies = $inscripcion["hobbies"] ?? "";
$autorizacion_datos_imagen = $inscripcion["autorizacion_datos_imagen"] ?? "";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title><?= t("Formulario de inscripción | Sama Shala") ?></title>
<link rel="stylesheet" href="<?= urlEstilos() ?>">
<link rel="icon" type="image/png" sizes="32x32" href="imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="imagenes/favicon.ico">
</head>
<body>
<?php require "menu.php"; ?>
<main class="contenedor">
<h1><?= t("Formulario de inscripción") ?></h1>
<p>
<?= t("Completa tus datos de salud y la autorización para poder practicar en Sama Shala.") ?>
</p>
<?php if ($error === "datos"): ?>
<div class="mensaje mensaje-error">
<?= t("Revisa los datos introducidos.") ?>
</div>
<?php endif; ?>
<form
action="guardar_inscripcion.php"
method="post"
class="formulario"
>
<div class="campo">
<label for="nombre"><?= t("Nombre completo") ?></label>
<input
type="text"
id="nombre"
name="nombre"
maxlength="120"
value="<?= escapar($nombre) ?>"
required
>
</div>
<div class="campo">
<label for="email"><?= t("Correo electrónico") ?></label>
<input
type="email"
id="email"
name="email"
maxlength="150"
value="<?= escapar($email) ?>"
required
>
</div>
<div class="campo">
<label for="telefono"><?= t("Celular / Teléfono") ?></label>
<input
type="tel"
id="telefono"
name="telefono"
maxlength="30"
value="<?= escapar($telefono) ?>"
required
>
</div>
<div class="campo">
<label for="fecha_nacimiento"><?= t("Fecha de nacimiento") ?></label>
<input
type="date"
id="fecha_nacimiento"
name="fecha_nacimiento"
value="<?= escapar($fecha_nacimiento) ?>"
required
>
</div>
<div class="campo">
<label><?= t("¿Has practicado yoga o meditación antes?") ?></label>
<div class="opciones-radio">
<label>
<input
type="radio"
name="experiencia_previa"
value="si"
<?= $experiencia_previa === "si" ? "checked" : "" ?>
required
>
<?= t("Sí") ?>
</label>
<label>
<input
type="radio"
name="experiencia_previa"
value="no"
<?= $experiencia_previa === "no" ? "checked" : "" ?>
>
<?= t("No") ?>
</label>
</div>
</div>
<div class="campo">
<label><?= t("¿Tienes alguna lesión, dolor o condición física que debamos conocer?") ?></label>
<div class="opciones-radio">
<label>
<input
type="radio"
name="tiene_lesion"
value="si"
data-mostrar-detalle="detalle_lesion"
<?= $tiene_lesion === "si" ? "checked" : "" ?>
required
>
<?= t("Sí") ?>
</label>
<label>
<input
type="radio"
name="tiene_lesion"
value="no"
data-mostrar-detalle="detalle_lesion"
<?= $tiene_lesion === "no" ? "checked" : "" ?>
>
<?= t("No") ?>
</label>
</div>
</div>
<div class="campo" id="campo_detalle_lesion" <?= $tiene_lesion === "si" ? "" : "hidden" ?>>
<label for="detalle_lesion"><?= t("Cuéntanos más sobre tu lesión, dolor o condición física") ?></label>
<textarea
id="detalle_lesion"
name="detalle_lesion"
rows="3"
><?= escapar($detalle_lesion) ?></textarea>
</div>
<div class="campo">
<label><?= t("¿Has tenido alguna cirugía o condición médica relevante?") ?></label>
<div class="opciones-radio">
<label>
<input
type="radio"
name="tiene_cirugia"
value="si"
data-mostrar-detalle="detalle_cirugia"
<?= $tiene_cirugia === "si" ? "checked" : "" ?>
required
>
<?= t("Sí") ?>
</label>
<label>
<input
type="radio"
name="tiene_cirugia"
value="no"
data-mostrar-detalle="detalle_cirugia"
<?= $tiene_cirugia === "no" ? "checked" : "" ?>
>
<?= t("No") ?>
</label>
</div>
</div>
<div class="campo" id="campo_detalle_cirugia" <?= $tiene_cirugia === "si" ? "" : "hidden" ?>>
<label for="detalle_cirugia"><?= t("Cuéntanos más sobre esa cirugía o condición médica") ?></label>
<textarea
id="detalle_cirugia"
name="detalle_cirugia"
rows="3"
><?= escapar($detalle_cirugia) ?></textarea>
</div>
<div class="campo">
<label for="hobbies"><?= t("¿Cuáles son tus hobbies o intereses?") ?></label>
<textarea
id="hobbies"
name="hobbies"
rows="3"
><?= escapar($hobbies) ?></textarea>
</div>
<div class="campo campo-checkbox campo-checkbox-texto-largo">
<label>
<input
type="checkbox"
name="autorizacion_datos_imagen"
value="si"
<?= $autorizacion_datos_imagen === "si" ? "checked" : "" ?>
required
>
<?= t("De conformidad con la Ley Orgánica de Protección de Datos Personales, autorizo el uso de mis datos para registro, acompañamiento en la práctica y comunicación interna. También autorizo el uso de mi imagen (fotos o vídeos) para compartir en los medios de Sama Shala.") ?>
</label>
</div>
<button type="submit" class="boton">
<?= t("Guardar mis datos") ?>
</button>
</form>
</main>
<script>
(function () {
document.querySelectorAll("[data-mostrar-detalle]").forEach(function (radio) {
radio.addEventListener("change", function () {
var campo = document.getElementById("campo_" + radio.getAttribute("data-mostrar-detalle"));
if (!campo) {
return;
}
campo.hidden = radio.value !== "si" || !radio.checked;
});
});
})();
</script>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
