<?php
require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/funciones.php";
require_once __DIR__ . "/google_config.php";

if (usuarioAutenticado()) {
header("Location: mi_cuenta.php");
exit;
}

$codigo = $_GET["code"] ?? "";
$estado = $_GET["state"] ?? "";
$estado_esperado = $_SESSION["google_oauth_estado"] ?? "";
unset($_SESSION["google_oauth_estado"]);

if (
$codigo === "" ||
$estado === "" ||
$estado_esperado === "" ||
!hash_equals($estado_esperado, $estado)
) {
header("Location: login.php?error=google");
exit;
}

$peticion_token = curl_init("https://oauth2.googleapis.com/token");
curl_setopt_array($peticion_token, [
CURLOPT_RETURNTRANSFER => true,
CURLOPT_POST => true,
CURLOPT_POSTFIELDS => http_build_query([
"code" => $codigo,
"client_id" => $google_client_id,
"client_secret" => $google_client_secret,
"redirect_uri" => $google_redirect_uri,
"grant_type" => "authorization_code",
]),
]);
$respuesta_token = curl_exec($peticion_token);
$token_ok = curl_getinfo($peticion_token, CURLINFO_HTTP_CODE) === 200;
curl_close($peticion_token);

$datos_token = $token_ok ? json_decode($respuesta_token, true) : null;
$token_acceso = $datos_token["access_token"] ?? "";
if ($token_acceso === "") {
header("Location: login.php?error=google");
exit;
}

$peticion_perfil = curl_init(
"https://www.googleapis.com/oauth2/v3/userinfo"
);
curl_setopt_array($peticion_perfil, [
CURLOPT_RETURNTRANSFER => true,
CURLOPT_HTTPHEADER => ["Authorization: Bearer " . $token_acceso],
]);
$respuesta_perfil = curl_exec($peticion_perfil);
$perfil_ok = curl_getinfo($peticion_perfil, CURLINFO_HTTP_CODE) === 200;
curl_close($peticion_perfil);

$perfil = $perfil_ok ? json_decode($respuesta_perfil, true) : null;
$email = strtolower(trim($perfil["email"] ?? ""));
if (
!$perfil ||
empty($perfil["email_verified"]) ||
!filter_var($email, FILTER_VALIDATE_EMAIL)
) {
header("Location: login.php?error=google");
exit;
}

$nombre = trim($perfil["given_name"] ?? "") ?: trim($perfil["name"] ?? "") ?: "Usuario";
$apellidos = trim($perfil["family_name"] ?? "");

$sql = "
SELECT
id_usuario,
nombre,
apellidos,
email,
rol,
activo,
totp_habilitado
FROM usuarios
WHERE email = ?
LIMIT 1
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) {
$password_aleatoria = password_hash(
bin2hex(random_bytes(32)),
PASSWORD_DEFAULT
);
$sql_insertar = "
INSERT INTO usuarios (nombre, apellidos, email, password, rol)
VALUES (?, ?, ?, ?, 'cliente')
";
$stmt_insertar = $conexion->prepare($sql_insertar);
$stmt_insertar->bind_param(
"ssss",
$nombre,
$apellidos,
$email,
$password_aleatoria
);
$stmt_insertar->execute();
$usuario = [
"id_usuario" => $conexion->insert_id,
"nombre" => $nombre,
"apellidos" => $apellidos,
"email" => $email,
"rol" => "cliente",
"activo" => 1,
"totp_habilitado" => 0,
];
$stmt_insertar->close();
}

$conexion->close();

if ((int) $usuario["activo"] !== 1) {
header("Location: login.php?error=inactivo");
exit;
}

completarLoginOExigirTotp($usuario);

if ($usuario["rol"] === "admin") {
header("Location: admin/index.php");
exit;
}
header("Location: mi_cuenta.php");
exit;
