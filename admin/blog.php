<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../funciones.php';
$buscar = trim($_GET['buscar'] ?? '');
$estado = trim($_GET['estado'] ?? '');
$estados_permitidos = ['', 'activo', 'inactivo'];
if (!in_array($estado, $estados_permitidos, true)) {
$estado = '';
}
$activo_filtro = $estado === 'activo'
? 1
: ($estado === 'inactivo' ? 0 : -1);
$patron = '%' . $buscar . '%';
$sql = "
SELECT id_entrada, titulo, portada, activo, fecha_creacion
FROM blog_entradas
WHERE (? = '' OR titulo LIKE ?)
AND (? = -1 OR activo = ?)
ORDER BY fecha_creacion DESC
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param(
'ssii',
$buscar,
$patron,
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
<title>Blog | Administración</title>
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
<h1>Blog</h1>
</div>
<a class="boton" href="nuevo_blog.php">
Nueva entrada
</a>
</div>
<?php if (($_GET['mensaje'] ?? '') === 'creado'): ?>
<div class="mensaje mensaje-exito">
La entrada se ha creado correctamente.
</div>
<?php endif; ?>
<?php if (($_GET['mensaje'] ?? '') === 'actualizado'): ?>
<div class="mensaje mensaje-exito">
La entrada se ha actualizado correctamente.
</div>
<?php endif; ?>
<?php if (($_GET['mensaje'] ?? '') === 'activado'): ?>
<div class="mensaje mensaje-exito">
La entrada se ha activado.
</div>
<?php endif; ?>
<?php if (($_GET['mensaje'] ?? '') === 'desactivado'): ?>
<div class="mensaje mensaje-exito">
La entrada se ha desactivado.
</div>
<?php endif; ?>
<?php if (($_GET['mensaje'] ?? '') === 'eliminado'): ?>
<div class="mensaje mensaje-exito">
La entrada se ha eliminado correctamente.
</div>
<?php endif; ?>
<form method="get" class="filtros">
<div class="campo">
<label for="buscar">
Título
</label>
<input
type="search"
id="buscar"
name="buscar"
value="<?= escapar($buscar) ?>"
placeholder="Buscar por título"
>
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
href="blog.php"
class="boton boton-secundario"
>
Limpiar
</a>
</div>
</div>
</form>
<?php if ($resultado->num_rows === 0): ?>
<p>No se han encontrado entradas.</p>
<?php else: ?>
<div class="tabla-responsive">
<table class="tabla-admin">
<thead>
<tr>
<th>Portada</th>
<th>Título</th>
<th>Fecha</th>
<th>Estado</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php while ($entrada = $resultado->fetch_assoc()): ?>
<tr>
<td>
<?php if (!empty($entrada['portada'])): ?>
<img
class="miniatura-imagen-actual"
src="../imagenes/blog/<?= escapar($entrada['portada']) ?>"
alt=""
>
<?php endif; ?>
</td>
<td>
<?= escapar($entrada['titulo']) ?>
</td>
<td>
<?= escapar(formatear_fecha(substr($entrada['fecha_creacion'], 0, 10))) ?>
</td>
<td>
<?php if ((int) $entrada['activo'] === 1): ?>
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
<a
href="../blog_detalle.php?id=<?= (int) $entrada['id_entrada'] ?>"
target="_blank"
rel="noopener"
>
Ver en el sitio
</a>
<a href="editar_blog.php?id_entrada=<?= (int) $entrada['id_entrada'] ?>">
Editar
</a>
<form action="alternar_blog.php" method="post">
<input
type="hidden"
name="id_entrada"
value="<?= (int) $entrada['id_entrada'] ?>"
>
<input
type="hidden"
name="csrf_token"
value="<?= escapar(tokenCsrf()) ?>"
>
<button type="submit">
<?= (int) $entrada['activo'] === 1 ? 'Desactivar' : 'Activar' ?>
</button>
</form>
<form
action="eliminar_blog.php"
method="post"
onsubmit="return confirm('¿Seguro que quieres eliminar esta entrada?');"
>
<input
type="hidden"
name="id_entrada"
value="<?= (int) $entrada['id_entrada'] ?>"
>
<input
type="hidden"
name="csrf_token"
value="<?= escapar(tokenCsrf()) ?>"
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
desplegable.classList.add("abierto");
boton.classList.add("abierto");
boton.setAttribute("aria-expanded", "true");
var altura = desplegable.offsetHeight;
var top = rect.bottom + 6;
if (top + altura > window.innerHeight) {
top = rect.top - altura - 6;
}
if (top < 6) {
top = 6;
}
desplegable.style.top = top + "px";
desplegable.style.right = window.innerWidth - rect.right + "px";
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
