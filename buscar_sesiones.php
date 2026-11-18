<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';

header('Content-Type: application/json');

$busqueda = trim($_GET['q'] ?? '');
if (mb_strlen($busqueda) < 2) {
echo json_encode([]);
exit;
}

$hoy = (new DateTime('today'))->format('Y-m-d');
$comodin = '%' . $busqueda . '%';

$sql = "
SELECT
s.id_sesion,
s.fecha,
s.hora_inicio,
a.nombre AS actividad,
GROUP_CONCAT(
DISTINCT COALESCE(NULLIF(p.username, ''), CONCAT(p.nombre, ' ', p.apellidos))
ORDER BY p.apellidos, p.nombre
SEPARATOR ', '
) AS profesor
FROM sesiones AS s
INNER JOIN actividades AS a
ON s.id_actividad = a.id_actividad
INNER JOIN sesiones_profesores AS sp
ON sp.id_sesion = s.id_sesion
INNER JOIN profesores AS p
ON sp.id_profesor = p.id_profesor
WHERE a.activa = 1
AND s.estado IN ('programada', 'completa')
AND s.fecha >= ?
GROUP BY
s.id_sesion,
s.fecha,
s.hora_inicio,
a.nombre
HAVING a.nombre LIKE ? OR profesor LIKE ?
ORDER BY s.fecha, s.hora_inicio
LIMIT 15
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('sss', $hoy, $comodin, $comodin);
$stmt->execute();
$resultado = $stmt->get_result();

$sesiones = [];
while ($sesion = $resultado->fetch_assoc()) {
$sesiones[] = [
'id' => (int) $sesion['id_sesion'],
'actividad' => $sesion['actividad'],
'fecha' => formatear_fecha($sesion['fecha']),
'hora' => formatear_hora($sesion['hora_inicio']),
'profesor' => $sesion['profesor'],
];
}

echo json_encode($sesiones);
