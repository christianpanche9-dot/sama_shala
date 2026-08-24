<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: paquetes.php');
exit;
}

$id_tipo_paquete = filter_input(
INPUT_POST,
'id_tipo_paquete',
FILTER_VALIDATE_INT
);
if (!$id_tipo_paquete) {
header('Location: paquetes.php?error=no_encontrado');
exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$numero_usos = $_POST['numero_usos'] ?? '';
$precio = $_POST['precio'] ?? '';
$activo = isset($_POST['activo']) ? 1 : 0;
$errores = [];
if ($nombre === '') {
$errores[] = 'El nombre es obligatorio.';
}
if (mb_strlen($nombre) > 100) {
$errores[] = 'El nombre es demasiado largo.';
}
$numero_usos = filter_var(
$numero_usos,
FILTER_VALIDATE_INT,
['options' => ['min_range' => 1, 'max_range' => 365]]
);
if ($numero_usos === false) {
$errores[] = 'El número de clases debe estar entre 1 y 365.';
}
$precio = filter_var(
$precio,
FILTER_VALIDATE_FLOAT,
['options' => ['min_range' => 0]]
);
if ($precio === false) {
$errores[] = 'El precio no es válido.';
}
if ($errores) {
header("Location: editar_paquete.php?id_tipo_paquete=$id_tipo_paquete&error=1");
exit;
}
$sql = "
UPDATE tipos_paquete SET
nombre = ?,
numero_usos = ?,
precio = ?,
activo = ?
WHERE id_tipo_paquete = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param(
'sidii',
$nombre,
$numero_usos,
$precio,
$activo,
$id_tipo_paquete
);
$stmt->execute();
header('Location: paquetes.php?mensaje=actualizado');
exit;
