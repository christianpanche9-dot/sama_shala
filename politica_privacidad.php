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
<title><?= t('Política de privacidad | Sama Shala') ?></title>
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
<h1><?= t('Política de privacidad') ?></h1>
</div>
<div class="contenido-blog">
<p>
Última actualización: <?= date('d/m/Y') ?>
</p>
<p>
En Sama Shala ("nosotros"), con sede en Los Álamos y Del Arrayán,
Cuenca – Ecuador, nos tomamos en serio la privacidad de tus datos.
Esta Política de Privacidad explica qué información recopilamos a
través de nuestro sitio web y plataforma de reservas, cómo la usamos y
qué derechos tienes sobre ella.
</p>

<h2>1. Datos que recopilamos</h2>
<p>
Recopilamos los datos que nos proporcionas directamente al crear una
cuenta o usar nuestros servicios: nombre, correo electrónico, número de
teléfono, y el historial de tus reservas, paquetes y compras.
</p>
<p>
Si inicias sesión con tu cuenta de Google, recibimos únicamente tu
nombre, dirección de correo electrónico y foto de perfil, provistos por
Google con tu autorización, para crear o identificar tu cuenta en Sama
Shala. No accedemos a tu contraseña de Google ni a ningún otro dato de
tu cuenta de Google, y no publicamos contenido en tu nombre.
</p>

<h2>2. Cómo usamos tus datos</h2>
<p>
Usamos tus datos exclusivamente para operar el servicio:
</p>
<ul>
<li>Crear y gestionar tu cuenta.</li>
<li>Procesar tus reservas de clases, paquetes y compras en la tienda.</li>
<li>Comunicarnos contigo sobre tus reservas, pagos o consultas.</li>
<li>Mejorar el funcionamiento de la plataforma.</li>
</ul>

<h2>3. Con quién compartimos tus datos</h2>
<p>
No vendemos ni compartimos tus datos personales con terceros con fines
comerciales. Solo compartimos información cuando es estrictamente
necesario para operar el servicio (por ejemplo, con el proveedor de
autenticación de Google, únicamente para validar tu inicio de sesión) o
cuando la ley así lo exija.
</p>

<h2>4. Almacenamiento y seguridad</h2>
<p>
Tus datos se almacenan en nuestra base de datos con acceso restringido
y se conservan mientras tu cuenta permanezca activa o mientras sean
necesarios para cumplir con obligaciones legales o contables.
</p>

<h2>5. Tus derechos</h2>
<p>
Puedes solicitar en cualquier momento el acceso, la corrección o la
eliminación de tus datos personales, así como revocar el acceso
otorgado mediante tu cuenta de Google, escribiendo a
<a href="mailto:samashalaec@gmail.com">samashalaec@gmail.com</a>.
Atenderemos tu solicitud en un plazo razonable.
</p>

<h2>6. Cookies</h2>
<p>
Utilizamos cookies estrictamente necesarias para mantener tu sesión
iniciada y recordar tu idioma de preferencia. No utilizamos cookies de
publicidad ni de rastreo de terceros.
</p>

<h2>7. Cambios a esta política</h2>
<p>
Podemos actualizar esta Política de Privacidad en cualquier momento.
Los cambios entrarán en vigor al publicarse en esta misma página.
</p>

<h2>8. Contacto</h2>
<p>
Si tienes preguntas sobre esta política, puedes escribirnos a
<a href="mailto:samashalaec@gmail.com">samashalaec@gmail.com</a> o
visitarnos en Los Álamos y Del Arrayán, Cuenca – Ecuador.
</p>
</div>
</main>
<?php require_once __DIR__ . '/pie.php'; ?>
</body>
</html>
