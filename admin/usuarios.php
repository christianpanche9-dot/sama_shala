<?php
require_once "seguridad_admin.php";
require_once "../conexion.php";
require_once "../funciones.php";
$buscar = trim($_GET["buscar"] ?? "");
$rol = trim($_GET["rol"] ?? "");
$roles_permitidos = ["", "cliente", "admin"];
if (!in_array($rol, $roles_permitidos, true)) {
$rol = "";
}
$patron = "%" . $buscar . "%";
$sql = "
SELECT
id_usuario,
nombre,
apellidos,
email,
telefono,
rol,
activo,
fecha_registro
FROM usuarios
WHERE (? = '' OR rol = ?)
AND (
? = ''
OR nombre LIKE ?
OR apellidos LIKE ?
OR email LIKE ?
)
ORDER BY fecha_registro DESC
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param(
"ssssss",
$rol,
$rol,
$buscar,
$patron,
$patron,
$patron
);
$stmt->execute();
$usuarios = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title>Usuarios | Administración</title>
<link rel="stylesheet" href="<?= urlEstilos('../') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="../imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="../imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="../imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu_admin.php'; ?>
<main class="contenedor seccion">
<h1>Usuarios</h1>
<?php if (($_GET['mensaje'] ?? '') === 'actualizado'): ?>
<div class="mensaje mensaje-exito">
El usuario se ha actualizado correctamente.
</div>
<?php endif; ?>
<?php if (($_GET['mensaje'] ?? '') === 'activado'): ?>
<div class="mensaje mensaje-exito">
El usuario se ha activado.
</div>
<?php endif; ?>
<?php if (($_GET['mensaje'] ?? '') === 'desactivado'): ?>
<div class="mensaje mensaje-exito">
El usuario se ha desactivado.
</div>
<?php endif; ?>
<?php if (($_GET['mensaje'] ?? '') === 'eliminado'): ?>
<div class="mensaje mensaje-exito">
El usuario se ha eliminado correctamente.
</div>
<?php endif; ?>
<?php if (($_GET['error'] ?? '') === 'en_uso'): ?>
<div class="mensaje mensaje-error">
No se puede eliminar el usuario porque tiene reservas asociadas.
Desactívalo si no quieres que pueda iniciar sesión.
</div>
<?php endif; ?>
<?php if (($_GET['error'] ?? '') === 'propio_usuario'): ?>
<div class="mensaje mensaje-error">
No puedes desactivar ni eliminar tu propio usuario.
</div>
<?php endif; ?>
<form method="get" class="filtros">
<div class="campo">
<label for="buscar">
Buscar usuario
</label>
<input
type="search"
id="buscar"
name="buscar"
value="<?= escapar($buscar) ?>"
placeholder="Nombre, apellidos o correo"
>
</div>
<div class="campo">
<label for="rol">
Rol
</label>
<select id="rol" name="rol">
<option value="">
Todos
</option>
<option
value="cliente"
<?= $rol === "cliente" ? "selected" : "" ?>
>
Cliente
</option>
<option
value="admin"
<?= $rol === "admin" ? "selected" : "" ?>
>
Administrador
</option>
</select>
</div>
<div class="campo campo-acciones-filtro">
<div class="acciones-filtro">
<button type="submit" class="boton">
Aplicar filtros
</button>
<a
href="usuarios.php"
class="boton boton-secundario"
>
Limpiar
</a>
</div>
</div>
</form>
<?php if ($usuarios->num_rows === 0): ?>
<p>No se han encontrado usuarios.</p>
<?php else: ?>
<div class="tabla-responsive">
<table class="tabla-admin">
<thead>
<tr>
<th>Usuario</th>
<th>Correo</th>
<th>Teléfono</th>
<th>Rol</th>
<th>Estado</th>
<th>Registro</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php while (
$usuario = $usuarios->fetch_assoc()
): ?>
<tr>
<td>
<?= escapar(
$usuario['nombre'] . ' ' .
$usuario['apellidos']
) ?>
</td>
<td>
<?= escapar($usuario['email']) ?>
</td>
<td>
<?= escapar(
$usuario['telefono'] ?? '—'
) ?>
</td>
<td>
<?= $usuario['rol'] === 'admin'
? 'Administrador'
: 'Cliente' ?>
</td>
<td>
<?= (int) $usuario['activo'] === 1
? 'Activo'
: 'Inactivo' ?>
</td>
<td>
<?= date(
'd/m/Y',
strtotime($usuario['fecha_registro'])
) ?>
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
<a href="editar_usuario.php?id_usuario=<?= (int) $usuario['id_usuario'] ?>">
Editar
</a>
<a href="ver_inscripcion.php?id_usuario=<?= (int) $usuario['id_usuario'] ?>">
Ver inscripción
</a>
<?php if ((int) $usuario['id_usuario'] === idUsuarioActual()): ?>
<span class="insignia insignia-clara">Tu usuario</span>
<?php else: ?>
<form action="alternar_usuario.php" method="post">
<input
type="hidden"
name="id_usuario"
value="<?= (int) $usuario['id_usuario'] ?>"
>
<button type="submit">
<?= (int) $usuario['activo'] === 1
? 'Desactivar'
: 'Activar' ?>
</button>
</form>
<form
action="eliminar_usuario.php"
method="post"
onsubmit="return confirm('¿Seguro que quieres eliminar este usuario?');"
>
<input
type="hidden"
name="id_usuario"
value="<?= (int) $usuario['id_usuario'] ?>"
>
<button type="submit" class="peligro-texto">
Eliminar
</button>
</form>
<?php endif; ?>
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
