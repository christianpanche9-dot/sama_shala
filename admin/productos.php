<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../funciones.php';
$buscar = trim($_GET['buscar'] ?? '');
$categoria = trim($_GET['categoria'] ?? '');
$estado = trim($_GET['estado'] ?? '');
$categorias_permitidas = ['bija', 'ayurveda', 'fotografia', 'angyoga'];
$estados_permitidos = ['', 'activo', 'inactivo'];
if (!in_array($categoria, $categorias_permitidas, true)) {
$categoria = '';
}
if (!in_array($estado, $estados_permitidos, true)) {
$estado = '';
}
$activo_filtro = $estado === 'activo'
? 1
: ($estado === 'inactivo' ? 0 : -1);
$patron = '%' . $buscar . '%';
$sql = "
SELECT id_producto, nombre, categoria, precio, activo
FROM productos
WHERE (? = '' OR nombre LIKE ?)
AND (? = '' OR categoria = ?)
AND (? = -1 OR activo = ?)
ORDER BY nombre
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param(
'ssssii',
$buscar,
$patron,
$categoria,
$categoria,
$activo_filtro,
$activo_filtro
);
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
<title>Tienda | Administración</title>
<link rel="stylesheet" href="<?= urlEstilos('../') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="../imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="../imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="../imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu_admin.php'; ?>
<main class="contenedor seccion">
<div class="encabezado-con-accion">
<div>
<p class="etiqueta">
Administración
</p>
<h1>Tienda</h1>
</div>
<a class="boton" href="nuevo_producto.php">
Nuevo producto
</a>
</div>
<?php if (($_GET['mensaje'] ?? '') === 'creado'): ?>
<div class="mensaje mensaje-exito">
El producto se ha creado correctamente.
</div>
<?php endif; ?>
<?php if (($_GET['mensaje'] ?? '') === 'actualizado'): ?>
<div class="mensaje mensaje-exito">
El producto se ha actualizado correctamente.
</div>
<?php endif; ?>
<?php if (($_GET['mensaje'] ?? '') === 'activado'): ?>
<div class="mensaje mensaje-exito">
El producto se ha activado.
</div>
<?php endif; ?>
<?php if (($_GET['mensaje'] ?? '') === 'desactivado'): ?>
<div class="mensaje mensaje-exito">
El producto se ha desactivado.
</div>
<?php endif; ?>
<?php if (($_GET['mensaje'] ?? '') === 'eliminado'): ?>
<div class="mensaje mensaje-exito">
El producto se ha eliminado correctamente.
</div>
<?php endif; ?>
<?php if (($_GET['error'] ?? '') === 'en_uso'): ?>
<div class="mensaje mensaje-error">
No se puede eliminar el producto porque tiene compras asociadas.
Desactívalo si no quieres que se siga vendiendo.
</div>
<?php endif; ?>
<form method="get" class="filtros">
<div class="campo">
<label for="buscar">
Nombre del producto
</label>
<input
type="search"
id="buscar"
name="buscar"
value="<?= escapar($buscar) ?>"
placeholder="Buscar por nombre"
>
</div>
<div class="campo">
<label for="categoria">
Categoría
</label>
<select id="categoria" name="categoria">
<option value="">
Todas
</option>
<?php foreach ($categorias_permitidas as $valor_categoria): ?>
<option
value="<?= escapar($valor_categoria) ?>"
<?= $categoria === $valor_categoria ? 'selected' : '' ?>
>
<?= escapar(texto_categoria_producto($valor_categoria)) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="campo">
<label for="estado">
Estado
</label>
<select id="estado" name="estado">
<option value="">
Todos
</option>
<option value="activo" <?= $estado === 'activo' ? 'selected' : '' ?>>
Activo
</option>
<option value="inactivo" <?= $estado === 'inactivo' ? 'selected' : '' ?>>
Inactivo
</option>
</select>
</div>
<div class="campo campo-acciones-filtro">
<div class="acciones-filtro">
<button type="submit" class="boton">
Aplicar filtros
</button>
<a
href="productos.php"
class="boton boton-secundario"
>
Limpiar
</a>
</div>
</div>
</form>
<?php if ($resultado->num_rows === 0): ?>
<p>No se han encontrado productos.</p>
<?php else: ?>
<div class="tabla-responsive">
<table class="tabla-admin">
<thead>
<tr>
<th>Producto</th>
<th>Categoría</th>
<th>Precio</th>
<th>Estado</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php while ($producto = $resultado->fetch_assoc()): ?>
<tr>
<td>
<?= escapar($producto['nombre']) ?>
</td>
<td>
<?= escapar(texto_categoria_producto($producto['categoria'])) ?>
</td>
<td>
<?= formatear_precio((float) $producto['precio']) ?>
</td>
<td>
<?php if ((int) $producto['activo'] === 1): ?>
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
<div class="menu-fila-acciones">
<button
type="button"
class="boton-acciones-fila"
aria-haspopup="true"
aria-expanded="false"
>
Acciones
<span class="flecha-menu-mas">▾</span>
</button>
<div class="menu-fila-desplegable">
<a href="editar_producto.php?id_producto=<?= (int) $producto['id_producto'] ?>">
Editar
</a>
<form action="alternar_producto.php" method="post">
<input
type="hidden"
name="id_producto"
value="<?= (int) $producto['id_producto'] ?>"
>
<button type="submit">
<?= (int) $producto['activo'] === 1 ? 'Desactivar' : 'Activar' ?>
</button>
</form>
<form
action="eliminar_producto.php"
method="post"
onsubmit="return confirm('¿Seguro que quieres eliminar este producto?');"
>
<input
type="hidden"
name="id_producto"
value="<?= (int) $producto['id_producto'] ?>"
>
<button type="submit" class="peligro-texto">
Eliminar
</button>
</form>
</div>
</div>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</main>
<script>
(function () {
var abiertos = [];
function cerrarTodos() {
abiertos.forEach(function (item) {
item.desplegable.classList.remove("abierto");
item.boton.classList.remove("abierto");
item.boton.setAttribute("aria-expanded", "false");
});
}
document.querySelectorAll(".menu-fila-acciones").forEach(function (contenedor) {
var boton = contenedor.querySelector(".boton-acciones-fila");
var desplegable = contenedor.querySelector(".menu-fila-desplegable");
if (!boton || !desplegable) {
return;
}
document.body.appendChild(desplegable);
abiertos.push({ boton: boton, desplegable: desplegable, contenedor: contenedor });
boton.addEventListener("click", function (evento) {
evento.stopPropagation();
var yaAbierto = desplegable.classList.contains("abierto");
cerrarTodos();
if (yaAbierto) {
return;
}
var rect = boton.getBoundingClientRect();
desplegable.style.top = rect.bottom + 6 + "px";
desplegable.style.right = window.innerWidth - rect.right + "px";
desplegable.classList.add("abierto");
boton.classList.add("abierto");
boton.setAttribute("aria-expanded", "true");
});
});
document.addEventListener("click", function (evento) {
var dentro = abiertos.some(function (item) {
return item.contenedor.contains(evento.target) || item.desplegable.contains(evento.target);
});
if (!dentro) {
cerrarTodos();
}
});
document.addEventListener("keydown", function (evento) {
if (evento.key === "Escape") {
cerrarTodos();
}
});
window.addEventListener("scroll", cerrarTodos, true);
window.addEventListener("resize", cerrarTodos);
})();
</script>
</body>
</html>
