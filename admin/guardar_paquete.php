<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../funciones.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: nuevo_paquete.php');
exit;
}
$nombre = trim($_POST['nombre'] ?? '');
$numero_usos = $_POST['numero_usos'] ?? '';
$precio = $_POST['precio'] ?? '';
$dias_validez = trim($_POST['dias_validez'] ?? '');
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
if ($dias_validez !== '') {
$dias_validez = filter_var(
$dias_validez,
FILTER_VALIDATE_INT,
['options' => ['min_range' => 1, 'max_range' => 730]]
);
if ($dias_validez === false) {
$errores[] = 'La validez en días no es válida.';
}
} else {
$dias_validez = null;
}
if ($errores) {
header('Location: nuevo_paquete.php?error=1');
exit;
}
$id_tenant = idTenantActual();
$sql = "
INSERT INTO tipos_paquete (
id_tenant,
nombre,
numero_usos,
precio,
dias_validez,
activo
)
VALUES (?, ?, ?, ?, ?, ?)
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param(
'isidii',
$id_tenant,
$nombre,
$numero_usos,
$precio,
$dias_validez,
$activo
);
$stmt->execute();
header('Location: paquetes.php?mensaje=creado');
exit;
