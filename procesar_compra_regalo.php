<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
require_once __DIR__ . '/funciones_payphone.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: comprar_regalo.php');
exit;
}
validarCsrf();
$es_invitado = !usuarioAutenticado();
if ($es_invitado) {
if (trim($_POST['sitio_web'] ?? '') !== '') {
header('Location: comprar_regalo.php');
exit;
}
$resultado_invitado = obtenerOCrearUsuarioInvitado(
$conexion,
trim($_POST['nombre'] ?? ''),
trim($_POST['apellidos'] ?? ''),
trim($_POST['email'] ?? ''),
trim($_POST['telefono'] ?? '')
);
if (!$resultado_invitado['ok']) {
header('Location: comprar_regalo.php?error=' . urlencode($resultado_invitado['error']));
exit;
}
$id_usuario = $resultado_invitado['id_usuario'];
} else {
$id_usuario = idUsuarioActual();
}
if (!$id_usuario) {
header('Location: comprar_regalo.php?error=pago');
exit;
}
$categoria = trim($_POST['categoria'] ?? '');
$id_item = filter_var($_POST['id_item'] ?? null, FILTER_VALIDATE_INT);
$categorias_validas = ['tienda', 'paquetes', 'eventos', 'terapias'];
if (!in_array($categoria, $categorias_validas, true) || !$id_item) {
header('Location: comprar_regalo.php?error=' . urlencode('Selecciona qué deseas regalar.'));
exit;
}
$id_tenant = idTenantActual();
$nombre_item = null;
$precio_item = null;
if ($categoria === 'tienda') {
$sql = "SELECT nombre, precio FROM productos WHERE id_producto = ? AND activo = 1";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_item);
} elseif ($categoria === 'paquetes') {
$sql = "SELECT nombre, precio FROM tipos_paquete WHERE id_tipo_paquete = ? AND activo = 1 AND id_tenant = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('ii', $id_item, $id_tenant);
} else {
$tipo_actividad = $categoria === 'terapias' ? 'terapia' : null;
if ($tipo_actividad !== null) {
$sql = "SELECT nombre, precio FROM actividades WHERE id_actividad = ? AND tipo = ? AND precio IS NOT NULL AND activa = 1 AND id_tenant = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('isi', $id_item, $tipo_actividad, $id_tenant);
} else {
$sql = "SELECT nombre, precio FROM actividades WHERE id_actividad = ? AND tipo IN ('evento', 'taller') AND precio IS NOT NULL AND activa = 1 AND id_tenant = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('ii', $id_item, $id_tenant);
}
}
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$item || $item['precio'] === null) {
header('Location: comprar_regalo.php?error=' . urlencode('El artículo seleccionado no es válido.'));
exit;
}
$nombre_item = $item['nombre'];
$precio_item = (float) $item['precio'];
try {
$conexion->begin_transaction();
$codigo = generarCodigoTarjetaRegalo();
$referencia_pago = generarReferenciaPayphone();
$descripcion = 'Tarjeta de regalo: ' . $nombre_item;
$sql_insertar = "
INSERT INTO tarjetas_regalo (
id_usuario,
codigo,
categoria_origen,
descripcion_origen,
saldo_inicial,
saldo_disponible,
metodo_pago,
referencia_pago,
estado
)
VALUES (?, ?, ?, ?, ?, ?, 'payphone', ?, 'pendiente')
";
$stmt_insertar = $conexion->prepare($sql_insertar);
$stmt_insertar->bind_param(
'isssdds',
$id_usuario,
$codigo,
$categoria,
$nombre_item,
$precio_item,
$precio_item,
$referencia_pago
);
$stmt_insertar->execute();
$id_tarjeta_regalo = (int) $conexion->insert_id;
$stmt_insertar->close();
$conexion->commit();
$resultado_pago = payphonePrepararPago($precio_item, $referencia_pago, $descripcion);
if (!$resultado_pago['ok']) {
$sql_cancelar = "DELETE FROM tarjetas_regalo WHERE id_tarjeta_regalo = ? AND estado = 'pendiente'";
$stmt_cancelar = $conexion->prepare($sql_cancelar);
$stmt_cancelar->bind_param('i', $id_tarjeta_regalo);
$stmt_cancelar->execute();
$stmt_cancelar->close();
header('Location: comprar_regalo.php?error=' . urlencode('No se pudo iniciar el pago con tarjeta. Intenta de nuevo.'));
exit;
}
header('Location: ' . $resultado_pago['url_pago']);
exit;
} catch (Throwable $error) {
$conexion->rollback();
header('Location: comprar_regalo.php?error=' . urlencode('No se pudo registrar la tarjeta de regalo.'));
exit;
}
