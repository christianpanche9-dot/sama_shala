<?php
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
header("Location: registro.php");
exit;
}
require_once "conexion.php";
require_once "funciones.php";
$nombre = trim($_POST["nombre"] ?? "");
$apellidos = trim($_POST["apellidos"] ?? "");
$email = trim($_POST["email"] ?? "");
$telefono = trim($_POST["telefono"] ?? "");
$direccion = trim($_POST["direccion"] ?? "");
$password = $_POST["password"] ?? "";
$repetir_password = $_POST["repetir_password"] ?? "";
$errores = [];
if ($nombre === "") {
$errores[] = t("El nombre es obligatorio.");
}
if ($apellidos === "") {
$errores[] = t("Los apellidos son obligatorios.");
}
if ($email === "") {
$errores[] = t("El correo electrónico es obligatorio.");
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
$errores[] = t("El correo electrónico no es válido.");
}
if (strlen($password) < 8) {
$errores[] = t("La contraseña debe tener al menos 8 caracteres.");
}
if ($password !== $repetir_password) {
$errores[] = t("Las contraseñas no coinciden.");
}
if (!empty($errores)) {
echo "<h1>" . t("No se ha podido crear la cuenta") . "</h1>";
echo "<ul>";
foreach ($errores as $error) {
echo "<li>" . htmlspecialchars($error) . "</li>";
}
echo "</ul>";
echo '<a href="registro.php">' . t("Volver") . '</a>';
exit;
}
$sql = "SELECT id_usuario
FROM usuarios
WHERE email = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();
if ($resultado->num_rows > 0) {
echo "<h1>" . t("El correo ya está registrado") . "</h1>";
echo '<a href="login.php">' . t("Iniciar sesión") . '</a>';
exit;
}
$password_hash = password_hash(
$password,
PASSWORD_DEFAULT
);
$sql = "INSERT INTO usuarios
(nombre, apellidos, email, password,
telefono, direccion)
VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param(
"ssssss",
$nombre,
$apellidos,
$email,
$password_hash,
$telefono,
$direccion
);
$stmt->execute();
if ($stmt->affected_rows === 1) {
echo "<h1>" . t("Cuenta creada correctamente") . "</h1>";
echo "<p>" . t("Ya puedes iniciar sesión.") . "</p>";
echo '<a href="login.php">' . t("Iniciar sesión") . '</a>';
} else {
echo "<h1>" . t("No se ha podido crear la cuenta") . "</h1>";
}