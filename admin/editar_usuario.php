<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';

$id_usuario = filter_input(
INPUT_GET,
'id_usuario',
FILTER_VALIDATE_INT
);
if (!$id_usuario) {
header('Location: usuarios.php');
exit;
}

$sql = "
SELECT
id_usuario,
nombre,
apellidos,
email,
telefono,
rol,
activo
FROM usuarios
WHERE id_usuario = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) {
header('Location: usuarios.php?error=no_encontrado');
exit;
}

$paquetes_cliente = [];
$tipos_paquete_disponibles = [];
if ($usuario['rol'] === 'cliente') {
$sql_paquetes_cliente = "
SELECT
bc.id_paquete_cliente,
bc.usos_iniciales,
bc.usos_disponibles,
bc.fecha_caducidad,
bc.estado,
bc.metodo_pago,
tb.nombre AS nombre_paquete
FROM paquetes_clientes bc
INNER JOIN tipos_paquete tb
ON bc.id_tipo_paquete = tb.id_tipo_paquete
WHERE bc.id_usuario = ?
ORDER BY bc.fecha_compra DESC
";
$stmt_paquetes_cliente = $conexion->prepare($sql_paquetes_cliente);
$stmt_paquetes_cliente->bind_param('i', $id_usuario);
$stmt_paquetes_cliente->execute();
$paquetes_cliente = $stmt_paquetes_cliente->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_paquetes_cliente->close();

$sql_tipos_paquete = "
SELECT id_tipo_paquete, nombre, numero_usos, precio
FROM tipos_paquete
WHERE activo = 1
ORDER BY nombre
";
$tipos_paquete_disponibles = $conexion->query($sql_tipos_paquete)->fetch_all(MYSQLI_ASSOC);
}
$mensaje = $_GET['mensaje'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title>Editar usuario | Sama Shala</title>
<link rel="stylesheet" href="<?= urlEstilos('../') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="../imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="../imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="../imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu_admin.php'; ?>
<main class="contenedor seccion">
<a class="enlace-volver" href="usuarios.php">
← Volver a usuarios
</a>
<h1>Editar usuario</h1>
<?php if (($_GET['error'] ?? '') === 'datos'): ?>
<div class="mensaje mensaje-error">
Revisa los datos del formulario.
</div>
<?php endif; ?>
<?php if (($_GET['error'] ?? '') === 'email'): ?>
<div class="mensaje mensaje-error">
Ya existe otro usuario con ese correo.
</div>
<?php endif; ?>
<form
class="formulario-admin"
action="actualizar_usuario.php"
method="post"
>
<input
type="hidden"
name="id_usuario"
value="<?= (int) $usuario['id_usuario'] ?>"
>
<input
type="hidden"
name="csrf_token"
value="<?= escapar(tokenCsrf()) ?>"
>
<div class="campo">
<label for="nombre">Nombre</label>
<input
type="text"
id="nombre"
name="nombre"
maxlength="80"
value="<?= escapar($usuario['nombre']) ?>"
required
>
</div>
<div class="campo">
<label for="apellidos">Apellidos</label>
<input
type="text"
id="apellidos"
name="apellidos"
maxlength="120"
value="<?= escapar($usuario['apellidos']) ?>"
required
>
</div>
<div class="campo">
<label for="email">Correo electrónico</label>
<input
type="email"
id="email"
name="email"
maxlength="150"
value="<?= escapar($usuario['email']) ?>"
required
>
</div>
<div class="campo">
<label for="telefono">Teléfono</label>
<input
type="tel"
id="telefono"
name="telefono"
maxlength="30"
value="<?= escapar($usuario['telefono']) ?>"
>
</div>
<div class="campo">
<label for="rol">Rol</label>
<select id="rol" name="rol" <?= (int) $usuario['id_usuario'] === idUsuarioActual() ? 'disabled' : '' ?>>
<option
value="cliente"
<?= $usuario['rol'] === 'cliente' ? 'selected' : '' ?>
>
Cliente
</option>
<option
value="admin"
<?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>
>
Administrador
</option>
</select>
<?php if ((int) $usuario['id_usuario'] === idUsuarioActual()): ?>
<input type="hidden" name="rol" value="<?= escapar($usuario['rol']) ?>">
<small>
No puedes cambiar el rol de tu propio usuario.
</small>
<?php endif; ?>
</div>
<div class="campo-completo">
<button class="boton" type="submit">
Guardar cambios
</button>
</div>
</form>
<?php if ($usuario['rol'] === 'cliente'): ?>
<h2>Paquetes</h2>
<?php if ($mensaje === 'paquete_creado'): ?>
<div class="mensaje mensaje-exito">
El paquete se registró como pagado en efectivo.
</div>
<?php elseif ($mensaje === 'error_paquete'): ?>
<div class="mensaje mensaje-error">
No se pudo registrar el paquete. Revisa el tipo de paquete seleccionado.
</div>
<?php endif; ?>
<?php if (empty($paquetes_cliente)): ?>
<p>Este cliente no tiene paquetes.</p>
<?php else: ?>
<div class="tabla-responsive">
<table class="tabla-admin">
<thead>
<tr>
<th>Paquete</th>
<th>Usos</th>
<th>Caduca</th>
<th>Estado</th>
<th>Método de pago</th>
</tr>
</thead>
<tbody>
<?php foreach ($paquetes_cliente as $paquete): ?>
<tr>
<td><?= escapar($paquete['nombre_paquete']) ?></td>
<td><?= (int) $paquete['usos_disponibles'] ?> / <?= (int) $paquete['usos_iniciales'] ?></td>
<td>
<?= $paquete['fecha_caducidad']
? date('d/m/Y', strtotime($paquete['fecha_caducidad']))
: '—' ?>
</td>
<td><?= escapar(ucfirst($paquete['estado'])) ?></td>
<td><?= escapar(ucfirst($paquete['metodo_pago'])) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>
<?php if (empty($tipos_paquete_disponibles)): ?>
<p>No hay tipos de paquete activos para registrar.</p>
<?php else: ?>
<form
action="guardar_paquete_efectivo.php"
method="post"
class="formulario-inline"
>
<input type="hidden" name="id_usuario" value="<?= (int) $usuario['id_usuario'] ?>">
<input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
<select name="id_tipo_paquete" required>
<option value="">Selecciona un paquete</option>
<?php foreach ($tipos_paquete_disponibles as $tipo): ?>
<option value="<?= $tipo['id_tipo_paquete'] ?>">
<?= escapar($tipo['nombre']) ?>
(<?= (int) $tipo['numero_usos'] ?> usos) — $<?= number_format((float) $tipo['precio'], 2) ?>
</option>
<?php endforeach; ?>
</select>
<label for="precio_pagado_paquete">Monto cobrado ($)</label>
<input
type="number"
step="0.01"
min="0"
name="precio_pagado"
id="precio_pagado_paquete"
placeholder="Precio de lista si se deja vacío"
>
<button type="submit" class="boton">
Registrar paquete pagado en efectivo
</button>
</form>
<?php endif; ?>
<?php endif; ?>
</main>
</body>
</html>
