<?php
header('Content-Type: text/plain; charset=UTF-8');
echo "PHP version: " . phpversion() . "\n";
echo "mbstring loaded: " . (extension_loaded('mbstring') ? 'yes' : 'no') . "\n";
echo "function_exists mb_substr: " . (function_exists('mb_substr') ? 'yes' : 'no') . "\n";
echo "function_exists mb_strtoupper: " . (function_exists('mb_strtoupper') ? 'yes' : 'no') . "\n";
echo "disable_functions: " . ini_get('disable_functions') . "\n";
echo "loaded extensions: " . implode(', ', get_loaded_extensions()) . "\n";
try {
    $r = mb_substr('Christian', 0, 1);
    echo "mb_substr result: " . $r . "\n";
} catch (\Throwable $e) {
    echo "mb_substr threw: " . get_class($e) . " - " . $e->getMessage() . "\n";
}
