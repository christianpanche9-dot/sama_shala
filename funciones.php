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
return isset($_SESSION["usuario"]);
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
'terapia' => 'Terapia'
];
return t($tipos[$tipo] ?? ucfirst($tipo));
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
