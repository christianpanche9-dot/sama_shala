<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
$sql_productos = "SELECT id_producto AS id, nombre, precio, imagen FROM productos WHERE activo = 1 ORDER BY nombre";
$productos = $conexion->query($sql_productos)->fetch_all(MYSQLI_ASSOC);
$id_tenant = idTenantActual();
$sql_paquetes = "SELECT id_tipo_paquete AS id, nombre, precio, numero_usos FROM tipos_paquete WHERE activo = 1 AND id_tenant = ? ORDER BY precio";
$stmt_paquetes = $conexion->prepare($sql_paquetes);
$stmt_paquetes->bind_param('i', $id_tenant);
$stmt_paquetes->execute();
$paquetes = $stmt_paquetes->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_paquetes->close();
$sql_actividades = "SELECT id_actividad AS id, nombre, precio, tipo, imagen FROM actividades WHERE id_tenant = ? AND precio IS NOT NULL AND activa = 1 ORDER BY nombre";
$stmt_actividades = $conexion->prepare($sql_actividades);
$stmt_actividades->bind_param('i', $id_tenant);
$stmt_actividades->execute();
$actividades_con_precio = $stmt_actividades->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_actividades->close();
$eventos = array_values(array_filter($actividades_con_precio, fn ($a) => $a['tipo'] === 'evento' || $a['tipo'] === 'taller'));
$terapias = array_values(array_filter($actividades_con_precio, fn ($a) => $a['tipo'] === 'terapia'));
$categorias = [
'tienda' => ['etiqueta' => t('Tienda'), 'items' => $productos, 'carpeta_imagen' => 'productos'],
'paquetes' => ['etiqueta' => t('Paquetes'), 'items' => $paquetes, 'carpeta_imagen' => null],
'eventos' => ['etiqueta' => t('Eventos y talleres'), 'items' => $eventos, 'carpeta_imagen' => 'actividades'],
'terapias' => ['etiqueta' => t('Terapias'), 'items' => $terapias, 'carpeta_imagen' => 'actividades'],
];
$hay_items = array_sum(array_map(fn ($c) => count($c['items']), $categorias)) > 0;
$es_invitado = !usuarioAutenticado();
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= t('Tarjetas de regalo | Sama Shala') ?></title>
<link rel="stylesheet" href="<?= urlEstilos() ?>">
<link rel="icon" type="image/png" sizes="32x32" href="imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu.php'; ?>
<main class="contenedor seccion">
<h1><?= t('🎁 Tarjetas de regalo') ?></h1>
<p>
<?= t('Regala saldo para usar en la tienda, paquetes, eventos o terapias. Quien la reciba puede usarla en lo que quiera, no solo en lo que elijas aquí.') ?>
</p>
<?php if (!$hay_items): ?>
<div class="mensaje mensaje-aviso">
<?= t('No hay artículos disponibles para elegir en este momento.') ?>
</div>
<?php else: ?>
<div class="ficha-sesion ficha-pago">
<section class="informacion-sesion">
<form
action="procesar_compra_regalo.php"
method="post"
class="formulario-admin ancho-completo"
id="formulario-regalo"
>
<input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
<input type="hidden" name="id_item" id="item_regalo" value="">
<div class="campo campo-completo">
<label for="categoria_regalo"><?= t('¿Qué deseas regalar?') ?></label>
<select id="categoria_regalo" name="categoria">
<option value="" selected disabled>
<?= t('Selecciona una categoría') ?>
</option>
<?php foreach ($categorias as $clave => $categoria): ?>
<option value="<?= escapar($clave) ?>" <?= empty($categoria['items']) ? 'disabled' : '' ?>>
<?= escapar($categoria['etiqueta']) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="campo-completo">
<?php foreach ($categorias as $clave => $categoria): ?>
<div
class="rejilla-actividades rejilla-regalo"
data-grid-categoria="<?= escapar($clave) ?>"
hidden
>
<?php if (empty($categoria['items'])): ?>
<p><?= t('No hay artículos disponibles en esta categoría.') ?></p>
<?php endif; ?>
<?php foreach ($categoria['items'] as $item): ?>
<article
class="tarjeta-actividad tarjeta-seleccionable-regalo"
data-id="<?= (int) $item['id'] ?>"
data-precio="<?= (float) $item['precio'] ?>"
tabindex="0"
role="button"
>
<?php if ($categoria['carpeta_imagen'] !== null && !empty($item['imagen'])): ?>
<img
class="imagen-actividad"
loading="lazy"
src="imagenes/<?= escapar($categoria['carpeta_imagen']) ?>/<?= escapar($item['imagen']) ?>"
alt="<?= escapar($item['nombre']) ?>"
>
<?php elseif ($clave === 'paquetes'): ?>
<div class="imagen-sin-contenido">
<?= sprintf(
(int) $item['numero_usos'] === 1 ? t('%d clase') : t('%d clases'),
(int) $item['numero_usos']
) ?>
</div>
<?php else: ?>
<div class="imagen-sin-contenido">
<?= t('Sin imagen') ?>
</div>
<?php endif; ?>
<div class="contenido-tarjeta">
<h2><?= escapar($item['nombre']) ?></h2>
<p class="precio-sesion">
<strong><?= formatear_precio((float) $item['precio']) ?></strong>
</p>
</div>
</article>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
</div>
<div class="campo-completo">
<p class="precio-sesion">
<?= t('Valor de la tarjeta:') ?>
<strong id="valor-regalo">$0.00</strong>
</p>
<p class="mensaje mensaje-error" id="aviso-sin-seleccion" hidden>
<?= t('Elige qué deseas regalar antes de continuar.') ?>
</p>
</div>
<?php if ($es_invitado): ?>
<div class="campo">
<label for="nombre_regalo"><?= t('Tu nombre') ?></label>
<input type="text" id="nombre_regalo" name="nombre" maxlength="80" required>
</div>
<div class="campo">
<label for="apellidos_regalo"><?= t('Tus apellidos') ?></label>
<input type="text" id="apellidos_regalo" name="apellidos" maxlength="120" required>
</div>
<div class="campo">
<label for="email_regalo"><?= t('Tu correo electrónico') ?></label>
<input type="email" id="email_regalo" name="email" maxlength="150" required>
</div>
<div class="campo">
<label for="telefono_regalo"><?= t('Tu teléfono') ?></label>
<input type="tel" id="telefono_regalo" name="telefono" maxlength="30">
</div>
<p class="campo-trampa-bot" aria-hidden="true">
<label for="sitio_web">Sitio web</label>
<input type="text" id="sitio_web" name="sitio_web" tabindex="-1" autocomplete="off">
</p>
<?php endif; ?>
<?php if ($error !== ''): ?>
<div class="mensaje mensaje-error campo-completo">
<?= escapar($error) ?>
</div>
<?php endif; ?>
<div class="campo-completo">
<button type="submit" class="boton">
<?= t('Pagar') ?>
<span id="total-boton-regalo">$0.00</span>
<?= t('con tarjeta') ?>
</button>
</div>
</form>
</section>
</div>
<script>
(function () {
var selectCategoria = document.getElementById('categoria_regalo');
var campoItem = document.getElementById('item_regalo');
var valorRegalo = document.getElementById('valor-regalo');
var totalBoton = document.getElementById('total-boton-regalo');
var avisoSinSeleccion = document.getElementById('aviso-sin-seleccion');
var formulario = document.getElementById('formulario-regalo');
var rejillas = document.querySelectorAll('.rejilla-regalo');
function formatearPrecio(precio) {
return '$' + parseFloat(precio).toFixed(2);
}
function actualizarValor(precio) {
valorRegalo.textContent = formatearPrecio(precio);
totalBoton.textContent = formatearPrecio(precio);
}
function limpiarSeleccion() {
document.querySelectorAll('.tarjeta-seleccionable-regalo.seleccionada').forEach(function (tarjeta) {
tarjeta.classList.remove('seleccionada');
});
campoItem.value = '';
actualizarValor(0);
}
function mostrarRejillaActiva() {
rejillas.forEach(function (rejilla) {
rejilla.hidden = rejilla.dataset.gridCategoria !== selectCategoria.value;
});
limpiarSeleccion();
}
function seleccionarTarjeta(tarjeta) {
document.querySelectorAll('.tarjeta-seleccionable-regalo.seleccionada').forEach(function (otra) {
otra.classList.remove('seleccionada');
});
tarjeta.classList.add('seleccionada');
campoItem.value = tarjeta.dataset.id;
actualizarValor(parseFloat(tarjeta.dataset.precio || '0'));
avisoSinSeleccion.hidden = true;
}
document.querySelectorAll('.tarjeta-seleccionable-regalo').forEach(function (tarjeta) {
tarjeta.addEventListener('click', function () {
seleccionarTarjeta(tarjeta);
});
tarjeta.addEventListener('keydown', function (evento) {
if (evento.key === 'Enter' || evento.key === ' ') {
evento.preventDefault();
seleccionarTarjeta(tarjeta);
}
});
});
selectCategoria.addEventListener('change', mostrarRejillaActiva);
formulario.addEventListener('submit', function (evento) {
if (!campoItem.value) {
evento.preventDefault();
avisoSinSeleccion.hidden = false;
avisoSinSeleccion.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
});
})();
</script>
<?php endif; ?>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
