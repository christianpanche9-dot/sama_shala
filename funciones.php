<?php
if (session_status() === PHP_SESSION_NONE) {
session_start();
}

function escapar(?string $texto): string
{
return htmlspecialchars(
$texto ?? "",
ENT_QUOTES,
"UTF-8"
);
}
function urlEstilos(string $prefijo = ''): string
{
$ruta = __DIR__ . '/estilos.css';
$version = file_exists($ruta) ? filemtime($ruta) : time();
return $prefijo . 'estilos.css?v=' . $version;
}
function idiomaActual(): string
{
$idioma = $_SESSION["idioma"] ?? "es";
return $idioma === "en" ? "en" : "es";
}
function t(string $texto): string
{
static $traducciones = null;
if (idiomaActual() !== "en") {
return $texto;
}
if ($traducciones === null) {
$traducciones = require __DIR__ . "/idiomas/en.php";
}
return $traducciones[$texto] ?? $texto;
}
function usuarioAutenticado(): bool
{
return isset($_SESSION["usuario"]["id_usuario"]);
}
function usuarioEsAdmin(): bool
{
return isset($_SESSION["usuario"]) &&
$_SESSION["usuario"]["rol"] === "admin";
}
function idUsuarioActual(): ?int
{
    return $_SESSION["usuario"]["id_usuario"] ?? null;
}
function idTenantActual(): int
{
    return 1;
}
function nombreUsuarioActual(): string
{
return $_SESSION["usuario"]["nombre"] ?? "";
}
function formatear_fecha(string $fecha): string
{
$objeto_fecha = DateTime::createFromFormat(
'Y-m-d',
$fecha
);
if (!$objeto_fecha) {
return $fecha;
}
$formato = idiomaActual() === 'en' ? 'm/d/Y' : 'd/m/Y';
return $objeto_fecha->format($formato);
}

function formatear_hora(string $hora): string
{
$objeto_hora = DateTime::createFromFormat(
'H:i:s',
$hora
);
if (!$objeto_hora) {
return substr($hora, 0, 5);
}
return $objeto_hora->format('H:i');
}

function formatear_precio(float $precio): string
{
return '$' . number_format($precio, 2, '.', ',');
}

function texto_nivel(string $nivel): string
{
$niveles = [
'todos' => 'Todos los niveles',
'inicial' => 'Nivel inicial',
'intermedio' => 'Nivel intermedio',
'avanzado' => 'Nivel avanzado'
];
return t($niveles[$nivel] ?? ucfirst($nivel));
}

function texto_tipo_actividad(string $tipo): string
{
$tipos = [
'clase' => 'Clase',
'evento' => 'Evento',
'terapia' => 'Terapia',
'taller' => 'Taller'
];
return t($tipos[$tipo] ?? ucfirst($tipo));
}

function texto_categoria_producto(string $categoria): string
{
    $categorias = [
        'bija' => 'Bija',
        'ayurveda' => 'Ayurveda',
        'fotografia' => 'Fotografía',
        'angyoga' => 'Angyoga'
    ];
    return t($categorias[$categoria] ?? ucfirst($categoria));
}

function obtenerCarritoDetallado(mysqli $conexion, array $carrito): array
{
    $items = [];
    $total = 0.0;
    if (empty($carrito)) {
        return ['items' => $items, 'total' => $total];
    }
    $ids_producto = array_values(array_unique(array_map(
        fn ($entrada) => (int) $entrada['id_producto'],
        $carrito
    )));
    $marcadores = implode(',', array_fill(0, count($ids_producto), '?'));
    $sql = "
        SELECT id_producto, nombre, imagen, precio
        FROM productos
        WHERE id_producto IN ($marcadores)
        AND activo = 1
    ";
    $stmt = $conexion->prepare($sql);
    $tipos = str_repeat('i', count($ids_producto));
    $stmt->bind_param($tipos, ...$ids_producto);
    $stmt->execute();
    $productos = [];
    foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $fila) {
        $productos[(int) $fila['id_producto']] = $fila;
    }
    foreach ($carrito as $clave => $entrada) {
        $id_producto = (int) $entrada['id_producto'];
        $cantidad = (int) $entrada['cantidad'];
        if (!isset($productos[$id_producto]) || $cantidad < 1) {
            continue;
        }
        $producto = $productos[$id_producto];
        $subtotal = (float) $producto['precio'] * $cantidad;
        $items[] = [
            'clave' => $clave,
            'id_producto' => $id_producto,
            'nombre' => $producto['nombre'],
            'imagen' => $producto['imagen'],
            'talla' => $entrada['talla'] ?? null,
            'precio' => (float) $producto['precio'],
            'cantidad' => $cantidad,
            'subtotal' => $subtotal
        ];
        $total += $subtotal;
    }
    return ['items' => $items, 'total' => $total];
}

function texto_dia_semana_abreviado(int $dia_semana): string
{
$dias = [
1 => 'Lun',
2 => 'Mar',
3 => 'Mié',
4 => 'Jue',
5 => 'Vie',
6 => 'Sáb',
7 => 'Dom'
];
return t($dias[$dia_semana] ?? '');
}

function texto_mes(int $mes): string
{
$meses = [
1 => 'Enero',
2 => 'Febrero',
3 => 'Marzo',
4 => 'Abril',
5 => 'Mayo',
6 => 'Junio',
7 => 'Julio',
8 => 'Agosto',
9 => 'Septiembre',
10 => 'Octubre',
11 => 'Noviembre',
12 => 'Diciembre'
];
return t($meses[$mes] ?? '');
}

function generar_calendario_mes(int $anio, int $mes): array
{
$primer_dia = new DateTime(
sprintf('%04d-%02d-01', $anio, $mes)
);
$dias_en_mes = (int) $primer_dia->format('t');
$dia_semana_inicio = (int) $primer_dia->format('N');
$semanas = [];
$semana_actual = array_fill(0, $dia_semana_inicio - 1, null);
for ($dia = 1; $dia <= $dias_en_mes; $dia++) {
$semana_actual[] = new DateTime(
sprintf('%04d-%02d-%02d', $anio, $mes, $dia)
);
if (count($semana_actual) === 7) {
$semanas[] = $semana_actual;
$semana_actual = [];
}
}
if (!empty($semana_actual)) {
while (count($semana_actual) < 7) {
$semana_actual[] = null;
}
$semanas[] = $semana_actual;
}
return $semanas;
}

function texto_estado_sesion(string $estado): string
{
$estados = [
'programada' => 'Programada',
'completa' => 'Completa',
'cancelada' => 'Cancelada',
'finalizada' => 'Finalizada'
];
return t($estados[$estado] ?? ucfirst($estado));
}

function clase_estado_sesion(string $estado): string
{
$estados_permitidos = [
'programada',
'completa',
'cancelada',
'finalizada'
];
if (!in_array($estado, $estados_permitidos, true)) {
return 'desconocido';
}
return $estado;

}
function descuento_terapia_evento_paquete(int $numero_usos): int
{
$descuentos = [
4 => 5,
8 => 10,
12 => 20
];
return $descuentos[$numero_usos] ?? 0;
}

function profesoresDeSesion(mysqli $conexion, int $id_sesion): array
{
$sql = "
SELECT p.id_profesor, p.nombre, p.apellidos, p.especialidad
FROM sesiones_profesores sp
INNER JOIN profesores p ON sp.id_profesor = p.id_profesor
WHERE sp.id_sesion = ?
ORDER BY p.apellidos, p.nombre
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_sesion);
$stmt->execute();
$profesores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
return $profesores;
}

function nombresProfesores(array $profesores): string
{
return implode(', ', array_map(
fn ($profesor) => trim($profesor['nombre'] . ' ' . $profesor['apellidos']),
$profesores
));
}

function mejorDescuentoEventoTerapia(
mysqli $conexion,
int $id_usuario
): ?array {
$sql = "
SELECT
bc.id_paquete_cliente,
tb.nombre AS nombre_paquete,
tb.numero_usos
FROM paquetes_clientes bc
INNER JOIN tipos_paquete tb
ON bc.id_tipo_paquete = tb.id_tipo_paquete
WHERE bc.id_usuario = ?
AND bc.estado = 'activo'
AND (
bc.fecha_caducidad IS NULL
OR bc.fecha_caducidad >= CURDATE()
)
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$paquetes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$mejor = null;
foreach ($paquetes as $paquete) {
$descuento = descuento_terapia_evento_paquete(
(int) $paquete['numero_usos']
);
if ($descuento > 0 && ($mejor === null || $descuento > $mejor['descuento'])) {
$mejor = [
'id_paquete_cliente' => (int) $paquete['id_paquete_cliente'],
'nombre_paquete' => $paquete['nombre_paquete'],
'descuento' => $descuento
];
}
}
return $mejor;
}

function precio_con_descuento(float $precio, int $descuento_porcentaje): float
{
return round($precio * (1 - $descuento_porcentaje / 100), 2);
}

const LIMITE_PLAZAS_POR_RESERVA = 10;

function precio_total_evento_terapia(
    float $precio_unitario,
    int $cantidad,
    int $descuento_porcentaje = 0
): float {
    $cantidad = max(1, $cantidad);
    $precio_primera_plaza = $descuento_porcentaje > 0
        ? precio_con_descuento($precio_unitario, $descuento_porcentaje)
        : $precio_unitario;
    $precio_resto_plazas = $precio_unitario * ($cantidad - 1);
    return round($precio_primera_plaza + $precio_resto_plazas, 2);
}

function calcular_porcentaje_ocupacion(
int $reservas,
int $aforo
): float {
if ($aforo <= 0) {
return 0;
}
$porcentaje = ($reservas / $aforo) * 100;
return min(100, round($porcentaje, 1));
}

function fecha_valida(string $fecha): bool
{
$objeto_fecha = DateTime::createFromFormat(
'Y-m-d',
$fecha
);
return $objeto_fecha !== false
&& $objeto_fecha->format('Y-m-d') === $fecha;
}

function hora_valida(string $hora): bool
{
$objeto_hora = DateTime::createFromFormat(
'H:i',
$hora
);
return $objeto_hora !== false
&& $objeto_hora->format('H:i') === $hora;
}
function entero_positivo(
mixed $valor,
int $minimo = 1
): bool {
return filter_var(
$valor,
FILTER_VALIDATE_INT,
[
'options' => [
'min_range' => $minimo
]
]
) !== false;
}

function procesar_imagen_subida(
string $campo,
string $carpeta_destino,
string $prefijo_archivo = 'imagen',
int $ancho_maximo = 1600
): array {
if (
!isset($_FILES[$campo]) ||
$_FILES[$campo]['error'] === UPLOAD_ERR_NO_FILE
) {
return ['ok' => true, 'archivo' => null];
}
$archivo = $_FILES[$campo];
if ($archivo['error'] !== UPLOAD_ERR_OK) {
return [
'ok' => false,
'error' => 'No se ha podido subir la imagen.'
];
}
if ($archivo['size'] > 5 * 1024 * 1024) {
return [
'ok' => false,
'error' => 'La imagen no puede superar los 5 MB.'
];
}
$informacion = @getimagesize($archivo['tmp_name']);
if ($informacion === false) {
return [
'ok' => false,
'error' => 'El archivo no es una imagen válida.'
];
}
$creadores_por_tipo = [
IMAGETYPE_JPEG => 'imagecreatefromjpeg',
IMAGETYPE_PNG => 'imagecreatefrompng',
IMAGETYPE_WEBP => 'imagecreatefromwebp'
];
$tipo_imagen = $informacion[2];
if (!isset($creadores_por_tipo[$tipo_imagen])) {
return [
'ok' => false,
'error' => 'Solo se permiten imágenes JPG, PNG o WEBP.'
];
}
$imagen_original = $creadores_por_tipo[$tipo_imagen](
$archivo['tmp_name']
);
if ($imagen_original === false) {
return [
'ok' => false,
'error' => 'No se ha podido procesar la imagen.'
];
}
$ancho_original = imagesx($imagen_original);
$alto_original = imagesy($imagen_original);
if ($ancho_original > $ancho_maximo) {
$ancho_final = $ancho_maximo;
$alto_final = (int) round(
$alto_original * ($ancho_maximo / $ancho_original)
);
} else {
$ancho_final = $ancho_original;
$alto_final = $alto_original;
}
$imagen_final = imagecreatetruecolor($ancho_final, $alto_final);
$fondo_blanco = imagecolorallocate($imagen_final, 255, 255, 255);
imagefill($imagen_final, 0, 0, $fondo_blanco);
imagecopyresampled(
$imagen_final,
$imagen_original,
0,
0,
0,
0,
$ancho_final,
$alto_final,
$ancho_original,
$alto_original
);
imagedestroy($imagen_original);
$nombre_archivo = uniqid($prefijo_archivo . '_') . '.jpg';
$guardado = imagejpeg(
$imagen_final,
$carpeta_destino . '/' . $nombre_archivo,
82
);
imagedestroy($imagen_final);
if (!$guardado) {
return [
'ok' => false,
'error' => 'No se ha podido guardar la imagen.'
];
}
return ['ok' => true, 'archivo' => $nombre_archivo];
}

function procesar_imagenes_multiples_subidas(
    string $campo,
    string $carpeta_destino,
    string $prefijo_archivo = 'imagen',
    int $ancho_maximo = 1600
): array {
    if (!isset($_FILES[$campo]['name']) || !is_array($_FILES[$campo]['name'])) {
        return ['ok' => true, 'archivos' => []];
    }
    $archivos = [];
    $total = count($_FILES[$campo]['name']);
    for ($i = 0; $i < $total; $i++) {
        if ($_FILES[$campo]['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $_FILES['__archivo_multiple_temporal__'] = [
            'name' => $_FILES[$campo]['name'][$i],
            'type' => $_FILES[$campo]['type'][$i],
            'tmp_name' => $_FILES[$campo]['tmp_name'][$i],
            'error' => $_FILES[$campo]['error'][$i],
            'size' => $_FILES[$campo]['size'][$i]
        ];
        $resultado = procesar_imagen_subida(
            '__archivo_multiple_temporal__',
            $carpeta_destino,
            $prefijo_archivo,
            $ancho_maximo
        );
        unset($_FILES['__archivo_multiple_temporal__']);
        if (!$resultado['ok']) {
            return ['ok' => false, 'error' => $resultado['error'], 'archivos' => $archivos];
        }
        if ($resultado['archivo'] !== null) {
            $archivos[] = $resultado['archivo'];
        }
    }
    return ['ok' => true, 'archivos' => $archivos];
}

// Placeholder hasta conectar un proveedor de correo (el hosting no
// soporta SMTP ni mail() de forma fiable). De momento solo registra el
// enlace en el log del servidor.
function enviar_correo_recuperacion(string $email, string $enlace): bool
{
error_log("[recuperacion_password] $email => $enlace");
return false;
}
