<?php
require_once __DIR__ . "/funciones.php";
?>
<footer class="pie">
<div class="contenedor pie-interior">
<p class="pie-etiqueta">
<?= t('Síguenos') ?>
</p>
<div class="pie-redes">
<a
class="pie-icono-red"
href="https://www.facebook.com/share/15Po1JmEK3/"
target="_blank"
rel="noopener"
aria-label="Facebook"
>
<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
<path d="M22 12.06C22 6.51 17.52 2 12 2S2 6.51 2 12.06c0 5 3.66 9.15 8.44 9.94v-7.03H7.9v-2.91h2.54V9.85c0-2.51 1.5-3.9 3.78-3.9 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.78-1.63 1.57v1.88h2.78l-.44 2.91h-2.34V22c4.78-.79 8.44-4.94 8.44-9.94Z"/>
</svg>
</a>
<a
class="pie-icono-red"
href="https://www.instagram.com/sama.shala"
target="_blank"
rel="noopener"
aria-label="Instagram"
>
<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6">
<rect x="3" y="3" width="18" height="18" rx="5"/>
<circle cx="12" cy="12" r="4.2"/>
<circle cx="17.2" cy="6.8" r="1.1" fill="currentColor" stroke="none"/>
</svg>
</a>
<a
class="pie-icono-red"
href="https://wa.me/593987021868"
target="_blank"
rel="noopener"
aria-label="WhatsApp"
>
<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
<path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5.06-1.33A10 10 0 1 0 12 2Zm5.68 14.24c-.24.68-1.4 1.3-1.93 1.38-.5.08-1.12.11-1.8-.11a15.6 15.6 0 0 1-3.9-2.1 12.7 12.7 0 0 1-2.6-3.3c-.34-.55-.6-1.13-.6-1.8 0-.66.35-1.06.63-1.34.24-.24.5-.28.68-.28h.5c.16 0 .38-.02.58.4.24.5.78 1.9.85 2.05.07.14.12.32.02.5-.1.19-.16.3-.3.46-.16.17-.32.38-.46.5-.15.15-.3.3-.13.6.2.36.75 1.24 1.62 2 1.13 1 2.06 1.32 2.4 1.47.26.11.42.09.58-.06.19-.2.63-.73.8-.98.17-.25.34-.2.56-.12.24.08 1.5.7 1.76.84.26.13.43.2.5.32.06.13.06.7-.18 1.38Z"/>
</svg>
</a>
</div>
<div class="pie-idioma">
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
</div>
<div class="pie-contacto">
<p>
+593 99 980 6435 · +593 98 760 6615
</p>
<p>
<a href="https://maps.google.com/?q=Los+%C3%81lamos+y+Ordo%C3%B1ez+Lasso%2C+Cuenca%2C+Ecuador" target="_blank" rel="noopener">
Los Álamos y Ordoñez Lasso, Cuenca – Ecuador
</a>
</p>
<p>
<a href="mailto:samashalaec@gmail.com">samashalaec@gmail.com</a>
</p>
</div>
<nav class="pie-enlaces">
<a href="actividades.php"><?= t('Actividades') ?></a>
<a href="sesiones.php"><?= t('Calendario') ?></a>
<a href="paquetes.php"><?= t('Paquetes') ?></a>
</nav>
<p class="pie-copyright">
&copy; <?= date('Y') ?> Sama Shala
</p>
</div>
</footer>
