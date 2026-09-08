<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../funciones.php';

header('Content-Type: application/json');

$resultado = procesar_imagen_subida(
'archivo',
__DIR__ . '/../imagenes/blog',
'blogimg'
);
if (!$resultado['ok'] || $resultado['archivo'] === null) {
echo json_encode([
'ok' => false,
'error' => $resultado['error'] ?? 'No se ha podido subir la imagen.'
]);
exit;
}
echo json_encode([
'ok' => true,
'url' => '/imagenes/blog/' . $resultado['archivo']
]);
