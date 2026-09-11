<?php
declare(strict_types=1);

const TOTP_BASE32_ALFABETO = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
const TOTP_PASO_SEGUNDOS = 30;

function totpGenerarSecreto(int $bytesAleatorios = 20): string
{
    return totpBase32Codificar(random_bytes($bytesAleatorios));
}

function totpBase32Codificar(string $datos): string
{
    $alfabeto = TOTP_BASE32_ALFABETO;
    $binario = '';
    foreach (str_split($datos) as $caracter) {
        $binario .= str_pad(decbin(ord($caracter)), 8, '0', STR_PAD_LEFT);
    }
    $salida = '';
    foreach (str_split($binario, 5) as $trozo) {
        $trozo = str_pad($trozo, 5, '0', STR_PAD_RIGHT);
        $salida .= $alfabeto[bindec($trozo)];
    }
    return $salida;
}

function totpBase32Decodificar(string $base32): string
{
    $alfabeto = TOTP_BASE32_ALFABETO;
    $base32 = strtoupper((string) preg_replace('/[^A-Za-z2-7]/', '', $base32));
    $binario = '';
    foreach (str_split($base32) as $caracter) {
        $posicion = strpos($alfabeto, $caracter);
        if ($posicion === false) {
            continue;
        }
        $binario .= str_pad(decbin($posicion), 5, '0', STR_PAD_LEFT);
    }
    $datos = '';
    foreach (str_split($binario, 8) as $byte) {
        if (strlen($byte) < 8) {
            continue;
        }
        $datos .= chr(bindec($byte));
    }
    return $datos;
}

function totpCodigoParaPaso(string $secretoBase32, int $paso): string
{
    $claveBinaria = totpBase32Decodificar($secretoBase32);
    $contador = pack('N*', 0) . pack('N*', $paso);
    $hash = hash_hmac('sha1', $contador, $claveBinaria, true);
    $desplazamiento = ord($hash[19]) & 0x0F;
    $codigoBinario =
        ((ord($hash[$desplazamiento]) & 0x7F) << 24) |
        ((ord($hash[$desplazamiento + 1]) & 0xFF) << 16) |
        ((ord($hash[$desplazamiento + 2]) & 0xFF) << 8) |
        (ord($hash[$desplazamiento + 3]) & 0xFF);
    $codigo = $codigoBinario % 1000000;
    return str_pad((string) $codigo, 6, '0', STR_PAD_LEFT);
}

function totpVerificarCodigo(string $secretoBase32, string $codigo, int $ventana = 1): bool
{
    $codigo = trim($codigo);
    if (!preg_match('/^\d{6}$/', $codigo)) {
        return false;
    }
    $pasoActual = (int) floor(time() / TOTP_PASO_SEGUNDOS);
    for ($i = -$ventana; $i <= $ventana; $i++) {
        if (hash_equals(totpCodigoParaPaso($secretoBase32, $pasoActual + $i), $codigo)) {
            return true;
        }
    }
    return false;
}

function totpSecretoLegible(string $secretoBase32): string
{
    return trim((string) chunk_split($secretoBase32, 4, ' '));
}
