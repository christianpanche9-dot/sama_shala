<?php
require_once "seguridad.php";
require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: formulario_inscripcion.php");
    exit;
}

$id_usuario = idUsuarioActual();
$nombre = trim($_POST["nombre"] ?? "");
$email = strtolower(trim($_POST["email"] ?? ""));
$telefono = trim($_POST["telefono"] ?? "");
$fecha_nacimiento = trim($_POST["fecha_nacimiento"] ?? "");
$experiencia_previa = $_POST["experiencia_previa"] ?? "";
$tiene_lesion = $_POST["tiene_lesion"] ?? "";
$detalle_lesion = trim($_POST["detalle_lesion"] ?? "");
$tiene_cirugia = $_POST["tiene_cirugia"] ?? "";
$detalle_cirugia = trim($_POST["detalle_cirugia"] ?? "");
$hobbies = trim($_POST["hobbies"] ?? "");
$autorizacion_datos_imagen = $_POST["autorizacion_datos_imagen"] ?? "";

$opciones_si_no = ["si", "no"];

if (
    $nombre === "" ||
    $email === "" ||
    $telefono === "" ||
    $fecha_nacimiento === "" ||
    !in_array($experiencia_previa, $opciones_si_no, true) ||
    !in_array($tiene_lesion, $opciones_si_no, true) ||
    !in_array($tiene_cirugia, $opciones_si_no, true) ||
    $autorizacion_datos_imagen !== "si"
) {
    header("Location: formulario_inscripcion.php?error=datos");
    exit;
}
if (
    mb_strlen($nombre) > 120 ||
    mb_strlen($email) > 150 ||
    mb_strlen($telefono) > 30 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    !fecha_valida($fecha_nacimiento)
) {
    header("Location: formulario_inscripcion.php?error=datos");
    exit;
}

if ($tiene_lesion !== "si") {
    $detalle_lesion = "";
}
if ($tiene_cirugia !== "si") {
    $detalle_cirugia = "";
}

$sql = "
    INSERT INTO formulario_inscripcion (
        id_usuario,
        nombre,
        email,
        telefono,
        fecha_nacimiento,
        experiencia_previa,
        tiene_lesion,
        detalle_lesion,
        tiene_cirugia,
        detalle_cirugia,
        hobbies,
        autorizacion_datos_imagen
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        nombre = VALUES(nombre),
        email = VALUES(email),
        telefono = VALUES(telefono),
        fecha_nacimiento = VALUES(fecha_nacimiento),
        experiencia_previa = VALUES(experiencia_previa),
        tiene_lesion = VALUES(tiene_lesion),
        detalle_lesion = VALUES(detalle_lesion),
        tiene_cirugia = VALUES(tiene_cirugia),
        detalle_cirugia = VALUES(detalle_cirugia),
        hobbies = VALUES(hobbies),
        autorizacion_datos_imagen = VALUES(autorizacion_datos_imagen)
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param(
    "isssssssssss",
    $id_usuario,
    $nombre,
    $email,
    $telefono,
    $fecha_nacimiento,
    $experiencia_previa,
    $tiene_lesion,
    $detalle_lesion,
    $tiene_cirugia,
    $detalle_cirugia,
    $hobbies,
    $autorizacion_datos_imagen
);
$stmt->execute();
$stmt->close();
$conexion->close();
header("Location: formulario_inscripcion.php?mensaje=guardado");
exit;
