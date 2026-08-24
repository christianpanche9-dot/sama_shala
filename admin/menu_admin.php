<header class="cabecera cabecera-admin">
<div class="contenedor cabecera-interior">
<a class="logo" href="index.php">
<img src="../imagenes/logo-blanco.png" alt="Sama Shala">
<span>· Administración</span>
</a>
<button
type="button"
class="boton-menu-movil"
id="boton-menu-movil-admin"
aria-expanded="false"
aria-controls="menu-principal-admin"
aria-label="Abrir menú"
>
<span></span>
<span></span>
<span></span>
</button>
<nav class="menu-principal" id="menu-principal-admin">
<a href="index.php">
Inicio
</a>
<a href="actividades.php">
Actividades
</a>
<a href="espacios.php">
Espacios
</a>
<a href="profesores.php">
Profesores
</a>
<a href="sesiones.php">
Calendario
</a>
<a href="reservas.php">
Reservas
</a>
<a href="paquetes.php">
Paquetes
</a>
<a href="pagos.php">
Pagos
</a>
<a href="estadisticas.php">
Estadísticas
</a>
<a href="../index.php">
Ver web pública
</a>
</nav>
</div>
</header>
<script>
(function () {
const boton = document.querySelector("#boton-menu-movil-admin");
const menu = document.querySelector("#menu-principal-admin");
if (!boton || !menu) {
return;
}
boton.addEventListener("click", function () {
const abierto = menu.classList.toggle("abierto");
boton.classList.toggle("abierto", abierto);
boton.setAttribute("aria-expanded", abierto ? "true" : "false");
});
})();
</script>