<?php
require_once __DIR__ . "/funciones.php";
require_once __DIR__ . "/google_config.php";

if (usuarioAutenticado()) {
header("Location: mi_cuenta.php");
exit;
}

$estado = bin2hex(random_bytes(16));
$_SESSION["google_oauth_estado"] = $estado;

$parametros = http_build_query([
"client_id" => $google_client_id,
"redirect_uri" => $google_redirect_uri,
"response_type" => "code",
"scope" => "openid email profile",
"state" => $estado,
"prompt" => "select_account",
]);

header("Location: https://accounts.google.com/o/oauth2/v2/auth?" . $parametros);
exit;
