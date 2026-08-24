<?php
require_once __DIR__ . '/funciones.php';
$idioma = $_GET['idioma'] ?? 'es';
$_SESSION['idioma'] = $idioma === 'en' ? 'en' : 'es';
$destino = 'index.php';
$referer = $_SERVER['HTTP_REFERER'] ?? '';
if ($referer !== '') {
$partes_referer = parse_url($referer);
$mismo_sitio =
($partes_referer['host'] ?? '') === ($_SERVER['HTTP_HOST'] ?? '');
if ($mismo_sitio) {
$destino = ($partes_referer['path'] ?? 'index.php') .
(isset($partes_referer['query']) ? '?' . $partes_referer['query'] : '');
}
}
header('Location: ' . $destino);
exit;
