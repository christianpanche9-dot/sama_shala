<?php
if (($_GET['clave'] ?? '') !== '0df67b23b0eaf95f197f6f2b1137b832') {
    http_response_code(404);
    exit;
}
header('Content-Type: text/plain; charset=utf-8');

$archivo_config = __DIR__ . '/telegram_config.php';
echo "telegram_config.php existe: " . (file_exists($archivo_config) ? 'si' : 'no') . "\n";
if (!file_exists($archivo_config)) {
    exit;
}
require $archivo_config;
echo "token definido: " . (!empty($telegram_bot_token) ? 'si (longitud ' . strlen($telegram_bot_token) . ')' : 'NO') . "\n";
echo "chat_id definido: " . (!empty($telegram_chat_id) ? "si ($telegram_chat_id)" : 'NO') . "\n";
echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? 'on' : 'OFF') . "\n";
echo "curl disponible: " . (function_exists('curl_init') ? 'si' : 'NO') . "\n";
echo "openssl disponible: " . (extension_loaded('openssl') ? 'si' : 'NO') . "\n";

echo "\n--- Intento con cURL ---\n";
if (function_exists('curl_init')) {
    $ch = curl_init("https://api.telegram.org/bot{$telegram_bot_token}/sendMessage");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'chat_id' => $telegram_chat_id,
        'text' => 'Diagnostico via cURL desde test_telegram.php',
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $respuesta = curl_exec($ch);
    $error = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    echo "curl_error: " . ($error ?: '(ninguno)') . "\n";
    echo "http_code: " . ($info['http_code'] ?? 'n/a') . "\n";
    echo "respuesta: " . $respuesta . "\n";
} else {
    echo "cURL no disponible en este servidor.\n";
}

echo "\n--- Intento con file_get_contents ---\n";
$url = "https://api.telegram.org/bot{$telegram_bot_token}/sendMessage";
$contexto = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => http_build_query([
            'chat_id' => $telegram_chat_id,
            'text' => 'Diagnostico via file_get_contents desde test_telegram.php',
        ]),
        'timeout' => 10,
        'ignore_errors' => true,
    ],
]);
$resultado = @file_get_contents($url, false, $contexto);
echo "resultado: " . var_export($resultado, true) . "\n";
if (isset($http_response_header)) {
    echo "headers: " . implode(' | ', $http_response_header) . "\n";
}
$ultimo_error = error_get_last();
if ($ultimo_error) {
    echo "ultimo error PHP: " . $ultimo_error['message'] . "\n";
}
