<?php
require_once __DIR__ . "/funciones.php";
?>
<div class="barra-utilidad">
<div class="contenedor barra-utilidad-interior">
<a
class="<?= idiomaActual() === 'es' ? 'activo' : '' ?>"
href="cambiar_idioma.php?idioma=es"
>
Español
</a>
<a
class="<?= idiomaActual() === 'en' ? 'activo' : '' ?>"
href="cambiar_idioma.php?idioma=en"
>
English
</a>
<?php if (!usuarioAutenticado()): ?>
<a href="login.php">
<?= t('Iniciar sesión') ?>
</a>
<?php endif; ?>
</div>
</div>
<header class="cabecera">
<div class="contenedor cabecera-interior">
<nav class="menu">
<a class="logo" href="index.php">
<img src="imagenes/logo-blanco.png" alt="Sama Shala">
</a>
<div class="menu-principal">
    <a href="actividades.php">
<?= t('Actividades') ?>
</a>
<a href="sesiones.php">
<?= t('Calendario') ?>
</a>
<a href="paquetes.php">
<?= t('Paquetes') ?>
</a>
<?php if (usuarioAutenticado()): ?>
<?php if (usuarioEsAdmin()): ?>
<a href="admin/index.php">
<?= t('Administración') ?>
</a>
<?php else: ?>
<a href="mi_cuenta.php">
<?= t('Mi cuenta') ?>
</a>
<a href="mis_reservas.php">
<?= t('Mis reservas') ?>
</a>
<?php endif; ?>
<span class="usuario-menu">
<?= escapar(
    nombreUsuarioActual()
) ?>
</span>
<a href="logout.php">
<?= t('Salir') ?>
</a>
<?php endif; ?>
</div>
</div>
</nav>
</header>