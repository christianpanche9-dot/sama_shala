<?php
require_once __DIR__ . '/funciones.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title><?= t('Términos y condiciones | Sama Shala') ?></title>
<link rel="stylesheet" href="<?= urlEstilos() ?>">
<link rel="icon" type="image/png" sizes="32x32" href="imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu.php'; ?>
<main class="contenedor seccion">
<div class="encabezado-pagina">
<p class="etiqueta"><?= t('Legal') ?></p>
<h1><?= t('Términos y condiciones') ?></h1>
</div>
<div class="contenido-blog">
<p>
Última actualización: <?= date('d/m/Y') ?>
</p>
<p>
Estos Términos y Condiciones regulan el uso del sitio web y la plataforma
de reservas de Sama Shala (en adelante, "Sama Shala", "nosotros"),
ubicado en Los Álamos y Del Arrayán, Cuenca – Ecuador. Al crear una
cuenta, iniciar sesión (incluyendo el inicio de sesión con Google) o
utilizar cualquiera de nuestros servicios, aceptas estos términos en su
totalidad. Si no estás de acuerdo, por favor no utilices la plataforma.
</p>

<h2>1. Descripción del servicio</h2>
<p>
Sama Shala ofrece un espacio de yoga y bienestar. A través de esta
plataforma puedes consultar el calendario de actividades, reservar
clases, adquirir paquetes de sesiones, comprar productos de nuestra
tienda y gestionar tus reservas.
</p>

<h2>2. Registro de cuenta e inicio de sesión con Google</h2>
<p>
Para reservar clases o comprar paquetes es necesario crear una cuenta.
Puedes registrarte con tu correo electrónico o, si está disponible,
iniciar sesión utilizando tu cuenta de Google. Al usar el inicio de
sesión con Google, solo solicitamos acceso a tu nombre, dirección de
correo electrónico y foto de perfil para crear e identificar tu cuenta
en Sama Shala. No accedemos a tu contraseña de Google, ni a otros datos
de tu cuenta de Google, y no publicamos nada en tu nombre.
</p>
<p>
Eres responsable de mantener la confidencialidad de tus credenciales de
acceso y de la exactitud de los datos que registras (nombre, correo y
teléfono de contacto).
</p>

<h2>3. Reservas, cupos y cancelaciones</h2>
<p>
Las reservas están sujetas al cupo disponible en cada sesión. Puedes
cancelar una reserva desde "Mis reservas" antes del inicio de la clase;
al cancelar, el cupo se libera y puede asignarse a la lista de espera.
El uso de un paquete se descuenta al confirmarse la reserva y se
devuelve si la cancelas con anticipación, de acuerdo con la política
vigente informada en la plataforma.
</p>

<h2>4. Paquetes y pagos</h2>
<p>
Los paquetes de clases tienen una vigencia y un número de usos
definidos al momento de la compra. Los pagos pueden realizarse mediante
transferencia bancaria u otros medios habilitados en la plataforma. Un
paquete pendiente de aprobación (por ejemplo, en revisión de
transferencia) no habilita reservas hasta ser confirmado por el equipo
de Sama Shala.
</p>

<h2>5. Uso aceptable</h2>
<p>
Te comprometes a utilizar la plataforma de forma lícita, a no suplantar
la identidad de otra persona, a no intentar vulnerar la seguridad del
sitio y a proporcionar información veraz. Sama Shala puede suspender o
cancelar cuentas que incumplan estos términos.
</p>

<h2>6. Salud y responsabilidad</h2>
<p>
La práctica de yoga y actividades de bienestar implica esfuerzo físico.
Es tu responsabilidad consultar a un profesional de la salud antes de
participar si tienes alguna condición médica que pueda verse afectada.
Sama Shala no se hace responsable por lesiones derivadas del
incumplimiento de las indicaciones del instructor o de condiciones de
salud no informadas previamente.
</p>

<h2>7. Privacidad de tus datos</h2>
<p>
Utilizamos tus datos (nombre, correo, teléfono y datos de reservas)
únicamente para operar el servicio: gestionar tu cuenta, tus reservas,
tus paquetes y comunicarnos contigo. No vendemos ni compartimos tus
datos con terceros, salvo cuando sea necesario para el funcionamiento
del servicio (por ejemplo, el proveedor de autenticación de Google) o
cuando la ley lo exija. Puedes solicitar la eliminación de tu cuenta y
tus datos escribiendo a <a href="mailto:samashalaec@gmail.com">samashalaec@gmail.com</a>.
</p>

<h2>8. Propiedad intelectual</h2>
<p>
El contenido de este sitio (textos, imágenes, logotipo y diseño) es
propiedad de Sama Shala y no puede reproducirse sin autorización.
</p>

<h2>9. Modificaciones</h2>
<p>
Podemos actualizar estos Términos y Condiciones en cualquier momento.
Los cambios entrarán en vigor al publicarse en esta misma página.
</p>

<h2>10. Contacto</h2>
<p>
Si tienes preguntas sobre estos términos, puedes escribirnos a
<a href="mailto:samashalaec@gmail.com">samashalaec@gmail.com</a> o
visitarnos en Los Álamos y Del Arrayán, Cuenca – Ecuador.
</p>
</div>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
