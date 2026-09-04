<?php
$servidor = 'localhost';
$usuario = 'root';
$password = '';
$base_datos = 'sama_shala';
$conexion = new mysqli(
$servidor,
$usuario,
$password,
$base_datos
);
if ($conexion->connect_error) {
die(
'No se ha podido conectar con la base de datos.'
);
}
$conexion->set_charset('utf8mb4');
