<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';

$codigo = strtoupper(trim($_GET['codigo'] ?? ''));
if ($codigo === '') {
http_response_code(404);
exit;
}

$sql = "
SELECT codigo, saldo_inicial, estado, fecha_caducidad
FROM tarjetas_regalo
WHERE codigo = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('s', $codigo);
$stmt->execute();
$tarjeta = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tarjeta || $tarjeta['estado'] === 'pendiente' || !function_exists('imagecreatetruecolor')) {
http_response_code(404);
exit;
}

function dibujarRectanguloRedondeado($imagen, $x1, $y1, $x2, $y2, $radio, $color): void
{
imagefilledrectangle($imagen, $x1 + $radio, $y1, $x2 - $radio, $y2, $color);
imagefilledrectangle($imagen, $x1, $y1 + $radio, $x2, $y2 - $radio, $color);
imagefilledellipse($imagen, $x1 + $radio, $y1 + $radio, $radio * 2, $radio * 2, $color);
imagefilledellipse($imagen, $x2 - $radio, $y1 + $radio, $radio * 2, $radio * 2, $color);
imagefilledellipse($imagen, $x1 + $radio, $y2 - $radio, $radio * 2, $radio * 2, $color);
imagefilledellipse($imagen, $x2 - $radio, $y2 - $radio, $radio * 2, $radio * 2, $color);
}

function textoCentrado($imagen, int $tamano, string $fuente, int $y, string $texto, int $color, int $ancho_lienzo): void
{
$caja = imagettfbbox($tamano, 0, $fuente, $texto);
$ancho_texto = abs($caja[2] - $caja[0]);
$x = (int) (($ancho_lienzo - $ancho_texto) / 2);
imagettftext($imagen, $tamano, 0, $x, $y, $color, $fuente, $texto);
}

$ancho = 1000;
$alto = 600;
$imagen = imagecreatetruecolor($ancho, $alto);
imagesavealpha($imagen, true);
imageantialias($imagen, true);

$fuente_titular = __DIR__ . '/fuentes/CS-Boris/CSBoris-Regular.otf';
$fuente_extra_bold = __DIR__ . '/fuentes/Archivo/Archivo-ExtraBold.ttf';
$fuente_bold = __DIR__ . '/fuentes/Archivo/Archivo-Bold.ttf';
$fuente_regular = __DIR__ . '/fuentes/Archivo/Archivo-Regular.ttf';

$verde_oscuro = imagecolorallocate($imagen, 0x57, 0x78, 0x5d);
$blanco = imagecolorallocate($imagen, 0xff, 0xff, 0xff);
$texto_color = imagecolorallocate($imagen, 0x17, 0x20, 0x33);
$texto_tenue = imagecolorallocate($imagen, 0x63, 0x70, 0x83);
$borde = imagecolorallocate($imagen, 0xdd, 0xd6, 0xcb);
$verde_claro = imagecolorallocate($imagen, 0xe7, 0xed, 0xe7);

for ($y = 0; $y < $alto; $y++) {
$factor = $y / $alto;
$r = (int) (0x57 + (0x2f - 0x57) * $factor);
$g = (int) (0x78 + (0x45 - 0x78) * $factor);
$b = (int) (0x5d + (0x33 - 0x5d) * $factor);
$color_fila = imagecolorallocate($imagen, max(0, $r), max(0, $g), max(0, $b));
imageline($imagen, 0, $y, $ancho, $y, $color_fila);
}

$margen = 40;
dibujarRectanguloRedondeado($imagen, $margen, $margen, $ancho - $margen, $alto - $margen, 24, $blanco);

textoCentrado($imagen, 16, $fuente_bold, $margen + 55, 'SAMA SHALA', $texto_tenue, $ancho);
textoCentrado($imagen, 38, $fuente_titular, $margen + 120, 'Tarjeta de regalo', $verde_oscuro, $ancho);

$monto = '$' . number_format((float) $tarjeta['saldo_inicial'], 2);
textoCentrado($imagen, 70, $fuente_extra_bold, $margen + 240, $monto, $texto_color, $ancho);

imagesetthickness($imagen, 2);
for ($x = $margen + 80; $x < $ancho - $margen - 80; $x += 18) {
imageline($imagen, $x, $margen + 275, $x + 9, $margen + 275, $borde);
}

textoCentrado($imagen, 14, $fuente_regular, $margen + 320, 'CÓDIGO PARA CANJEAR', $texto_tenue, $ancho);
textoCentrado($imagen, 36, $fuente_bold, $margen + 365, $tarjeta['codigo'], $texto_color, $ancho);

$validez = $tarjeta['fecha_caducidad']
? 'Válida hasta el ' . date('d/m/Y', strtotime($tarjeta['fecha_caducidad']))
: 'Sin fecha de caducidad';
textoCentrado($imagen, 16, $fuente_regular, $alto - $margen - 55, $validez, $texto_tenue, $ancho);
textoCentrado($imagen, 14, $fuente_regular, $alto - $margen - 25, 'Canjéala en samashala.com', $texto_tenue, $ancho);

if (($_GET['descargar'] ?? '') === '1') {
header('Content-Disposition: attachment; filename="tarjeta-regalo-' . preg_replace('/[^A-Z0-9-]/', '', $tarjeta['codigo']) . '.png"');
} else {
header('Content-Disposition: inline');
}
header('Content-Type: image/png');
imagepng($imagen);
imagedestroy($imagen);
