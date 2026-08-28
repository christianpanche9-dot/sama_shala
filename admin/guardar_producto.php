<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../funciones.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: nuevo_producto.php');
exit;
}
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
header('Location: nuevo_producto.php?error=1');
exit;
}
$resultado_imagen = procesar_imagen_subida(
'imagen',
__DIR__ . '/../imagenes/productos',
'producto'
);
if (!$resultado_imagen['ok']) {
header('Location: nuevo_producto.php?error=1');
exit;
}
$imagen = $resultado_imagen['archivo'] ?? '';
$resultado_imagenes_detalle = procesar_imagenes_multiples_subidas(
'imagenes_detalle',
__DIR__ . '/../imagenes/productos',
'producto_detalle'
);
if (!$resultado_imagenes_detalle['ok']) {
header('Location: nuevo_producto.php?error=1');
exit;
}
$sql = "
INSERT INTO productos (
nombre,
descripcion,
categoria,
precio,
tallas,
imagen,
activo
)
VALUES (?, ?, ?, ?, ?, ?, ?)
";
$stmt = $conexion->prepare($sql);
$tallas_guardadas = $tallas !== '' ? $tallas : null;
$stmt->bind_param(
'sssdssi',
$nombre,
$descripcion,
$categoria,
$precio,
$tallas_guardadas,
$imagen,
$activo
);
$stmt->execute();
$id_producto = $conexion->insert_id;
if (!empty($resultado_imagenes_detalle['archivos'])) {
$sql_imagen_detalle = "
INSERT INTO producto_imagenes (id_producto, imagen, orden)
VALUES (?, ?, ?)
";
$stmt_imagen_detalle = $conexion->prepare($sql_imagen_detalle);
foreach ($resultado_imagenes_detalle['archivos'] as $orden => $archivo_detalle) {
$stmt_imagen_detalle->bind_param(
'isi',
$id_producto,
$archivo_detalle,
$orden
);
$stmt_imagen_detalle->execute();
}
$stmt_imagen_detalle->close();
}
header('Location: productos.php?mensaje=creado');
exit;
