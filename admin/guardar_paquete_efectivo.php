<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../funciones.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: usuarios.php');
exit;
}
validarCsrf();
$id_usuario = filter_var(
$_POST['id_usuario'] ?? null,
FILTER_VALIDATE_INT
);
$id_tipo_paquete = filter_var(
$_POST['id_tipo_paquete'] ?? null,
FILTER_VALIDATE_INT
);
if (!$id_usuario || !$id_tipo_paquete) {
header('Location: usuarios.php?error=datos');
exit;
}
$precio_pagado = filter_var(
$_POST['precio_pagado'] ?? '',
FILTER_VALIDATE_FLOAT
);
try {
$conexion->begin_transaction();
$sql_cliente = "SELECT id_usuario FROM usuarios WHERE id_usuario = ? AND rol = 'cliente'";
$stmt_cliente = $conexion->prepare($sql_cliente);
$stmt_cliente->bind_param('i', $id_usuario);
$stmt_cliente->execute();
$cliente_valido = $stmt_cliente->get_result()->fetch_assoc();
$stmt_cliente->close();
if (!$cliente_valido) {
throw new Exception('El cliente no es válido.');
}
$id_tenant = idTenantActual();
$sql_tipo = "
SELECT id_tipo_paquete, nombre, numero_usos, precio
FROM tipos_paquete
WHERE id_tipo_paquete = ?
AND activo = 1
AND id_tenant = ?
FOR UPDATE
";
$stmt_tipo = $conexion->prepare($sql_tipo);
$stmt_tipo->bind_param('ii', $id_tipo_paquete, $id_tenant);
$stmt_tipo->execute();
$tipo_paquete = $stmt_tipo->get_result()->fetch_assoc();
$stmt_tipo->close();
if (!$tipo_paquete) {
throw new Exception('El tipo de paquete no existe.');
}
if ($precio_pagado === false || $precio_pagado === null) {
$precio_pagado = (float) $tipo_paquete['precio'];
}
$numero_usos = (int) $tipo_paquete['numero_usos'];
$fecha_caducidad = date('Y-m-d', strtotime('+1 month'));
$referencia_pago = 'EFECTIVO-' . strtoupper(bin2hex(random_bytes(6)));
$sql_insertar = "
INSERT INTO paquetes_clientes (
id_usuario,
id_tipo_paquete,
fecha_compra,
fecha_caducidad,
usos_iniciales,
usos_disponibles,
precio_pagado,
metodo_pago,
referencia_pago,
estado
)
VALUES (
?,
?,
NOW(),
?,
?,
?,
?,
'efectivo',
?,
'activo'
)
";
$stmt_insertar = $conexion->prepare($sql_insertar);
$stmt_insertar->bind_param(
'iisiids',
$id_usuario,
$id_tipo_paquete,
$fecha_caducidad,
$numero_usos,
$numero_usos,
$precio_pagado,
$referencia_pago
);
$stmt_insertar->execute();
$stmt_insertar->close();
$conexion->commit();
notificarTelegram(
"<b>Paquete registrado en efectivo (admin)</b>\n" .
"Cliente: " . htmlspecialchars(nombreClienteActual($conexion, $id_usuario), ENT_QUOTES, 'UTF-8') . "\n" .
"Paquete: " . htmlspecialchars($tipo_paquete['nombre'], ENT_QUOTES, 'UTF-8') . "\n" .
"Monto: $" . number_format($precio_pagado, 2)
);
header("Location: editar_usuario.php?id_usuario={$id_usuario}&mensaje=paquete_creado");
exit;
} catch (Throwable $error) {
$conexion->rollback();
header("Location: editar_usuario.php?id_usuario={$id_usuario}&mensaje=error_paquete");
exit;
}
