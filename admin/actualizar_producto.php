<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: productos.php');
exit;
}

$id_producto = filter_var(
$_POST['id_producto'] ?? '',
FILTER_VALIDATE_INT
);
if (!$id_producto) {
header('Location: productos.php?error=no_encontrado');
exit;
}

$sql_actual = "SELECT imagen FROM productos WHERE id_producto = ?";
$stmt_actual = $conexion->prepare($sql_actual);
$stmt_actual->bind_param('i', $id_producto);
$stmt_actual->execute();
$producto_actual = $stmt_actual->get_result()->fetch_assoc();
$stmt_actual->close();
if (!$producto_actual) {
header('Location: productos.php?error=no_encontrado');
exit;
}
$imagen_actual = $producto_actual['imagen'];

$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');
$precio = trim($_POST['precio'] ?? '');
$tallas = trim($_POST['tallas'] ?? '');
$activo = isset($_POST['activo']) ? 1 : 0;
$categorias_validas = ['bija', 'ayurveda', 'fotografia', 'angyoga'];
$errores = [];
if ($nombre === '') {
$errores[] = 'El nombre es obligatorio.';
}
if (mb_strlen($nombre) > 150) {
$errores[] = 'El nombre es demasiado largo.';
}
if ($descripcion === '') {
$errores[] = 'La descripción es obligatoria.';
}
if (!in_array($categoria, $categorias_validas, true)) {
$errores[] = 'La categoría no es válida.';
}
$precio = filter_var(
$precio,
FILTER_VALIDATE_FLOAT,
[
'options' => [
'min_range' => 0.01
]
]
);
if ($precio === false) {
$errores[] = 'El precio es obligatorio y debe ser mayor que 0.';
}
if (mb_strlen($tallas) > 100) {
$errores[] = 'Las tallas son demasiado largas.';
}
if ($errores) {
header("Location: editar_producto.php?id_producto=$id_producto&error=1");
exit;
}

$resultado_imagen = procesar_imagen_subida(
'imagen',
__DIR__ . '/../imagenes/productos',
'producto'
);
if (!$resultado_imagen['ok']) {
header("Location: editar_producto.php?id_producto=$id_producto&error=1");
exit;
}
if ($resultado_imagen['archivo'] !== null) {
if (
!empty($imagen_actual) &&
file_exists(__DIR__ . '/../imagenes/productos/' . $imagen_actual)
) {
unlink(__DIR__ . '/../imagenes/productos/' . $imagen_actual);
}
$imagen = $resultado_imagen['archivo'];
} else {
$imagen = $imagen_actual;
}

$resultado_imagenes_detalle = procesar_imagenes_multiples_subidas(
'imagenes_detalle',
__DIR__ . '/../imagenes/productos',
'producto_detalle'
);
if (!$resultado_imagenes_detalle['ok']) {
header("Location: editar_producto.php?id_producto=$id_producto&error=1");
exit;
}

$ids_eliminar = $_POST['eliminar_imagenes'] ?? [];
if (is_array($ids_eliminar) && !empty($ids_eliminar)) {
$ids_eliminar = array_filter(array_map('intval', $ids_eliminar));
if (!empty($ids_eliminar)) {
$marcadores = implode(',', array_fill(0, count($ids_eliminar), '?'));
$sql_imagenes_borrar = "
SELECT id_imagen, imagen
FROM producto_imagenes
WHERE id_producto = ?
AND id_imagen IN ($marcadores)
";
$stmt_imagenes_borrar = $conexion->prepare($sql_imagenes_borrar);
$tipos = 'i' . str_repeat('i', count($ids_eliminar));
$stmt_imagenes_borrar->bind_param($tipos, $id_producto, ...$ids_eliminar);
$stmt_imagenes_borrar->execute();
$imagenes_a_borrar = $stmt_imagenes_borrar->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_imagenes_borrar->close();
foreach ($imagenes_a_borrar as $imagen_a_borrar) {
if (file_exists(__DIR__ . '/../imagenes/productos/' . $imagen_a_borrar['imagen'])) {
unlink(__DIR__ . '/../imagenes/productos/' . $imagen_a_borrar['imagen']);
}
}
$sql_borrar = "
DELETE FROM producto_imagenes
WHERE id_producto = ?
AND id_imagen IN ($marcadores)
";
$stmt_borrar = $conexion->prepare($sql_borrar);
$stmt_borrar->bind_param($tipos, $id_producto, ...$ids_eliminar);
$stmt_borrar->execute();
$stmt_borrar->close();
}
}

if (!empty($resultado_imagenes_detalle['archivos'])) {
$sql_orden_maximo = "
SELECT COALESCE(MAX(orden), -1) AS orden_maximo
FROM producto_imagenes
WHERE id_producto = ?
";
$stmt_orden_maximo = $conexion->prepare($sql_orden_maximo);
$stmt_orden_maximo->bind_param('i', $id_producto);
$stmt_orden_maximo->execute();
$orden_maximo = (int) $stmt_orden_maximo->get_result()->fetch_assoc()['orden_maximo'];
$stmt_orden_maximo->close();
$sql_imagen_detalle = "
INSERT INTO producto_imagenes (id_producto, imagen, orden)
VALUES (?, ?, ?)
";
$stmt_imagen_detalle = $conexion->prepare($sql_imagen_detalle);
foreach ($resultado_imagenes_detalle['archivos'] as $archivo_detalle) {
$orden_maximo++;
$stmt_imagen_detalle->bind_param(
'isi',
$id_producto,
$archivo_detalle,
$orden_maximo
);
$stmt_imagen_detalle->execute();
}
$stmt_imagen_detalle->close();
}

$tallas_guardadas = $tallas !== '' ? $tallas : null;
$sql = "
UPDATE productos SET
nombre = ?,
descripcion = ?,
categoria = ?,
precio = ?,
tallas = ?,
imagen = ?,
activo = ?
WHERE id_producto = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param(
'sssdssii',
$nombre,
$descripcion,
$categoria,
$precio,
$tallas_guardadas,
$imagen,
$activo,
$id_producto
);
$stmt->execute();
header('Location: productos.php?mensaje=actualizado');
exit;
