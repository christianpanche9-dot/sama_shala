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
<a href="../logout.php">
Salir
</a>
<div class="menu-mas" id="menu-mas-admin">
<button
type="button"
class="boton-menu-mas"
id="boton-menu-mas-admin"
aria-haspopup="true"
aria-expanded="false"
aria-controls="menu-mas-desplegable-admin"
>
Más
<span class="flecha-menu-mas">▾</span>
</button>
<div class="menu-mas-desplegable" id="menu-mas-desplegable-admin"></div>
</div>
</nav>
</div>
</header>
<script>
(function () {
const boton = document.querySelector("#boton-menu-movil-admin");
const menu = document.querySelector("#menu-principal-admin");
if (boton && menu) {
boton.addEventListener("click", function () {
const abierto = menu.classList.toggle("abierto");
boton.classList.toggle("abierto", abierto);
boton.setAttribute("aria-expanded", abierto ? "true" : "false");
});
}

const contenedorMas = document.querySelector("#menu-mas-admin");
const botonMas = document.querySelector("#boton-menu-mas-admin");
const desplegableMas = document.querySelector("#menu-mas-desplegable-admin");
if (!menu || !contenedorMas || !botonMas || !desplegableMas) {
return;
}

const puntoCorte = 850;
const enlaces = Array.from(menu.querySelectorAll("a"));

document.body.appendChild(desplegableMas);

function posicionarMas() {
const rect = botonMas.getBoundingClientRect();
desplegableMas.style.top = rect.bottom + 8 + "px";
desplegableMas.style.right = window.innerWidth - rect.right + "px";
}

function cerrarMas() {
desplegableMas.classList.remove("abierto");
botonMas.classList.remove("abierto");
botonMas.setAttribute("aria-expanded", "false");
}

botonMas.addEventListener("click", function () {
posicionarMas();
const abierto = desplegableMas.classList.toggle("abierto");
botonMas.classList.toggle("abierto", abierto);
botonMas.setAttribute("aria-expanded", abierto ? "true" : "false");
});

document.addEventListener("click", function (evento) {
if (
!contenedorMas.contains(evento.target) &&
!desplegableMas.contains(evento.target)
) {
cerrarMas();
}
});

document.addEventListener("keydown", function (evento) {
if (evento.key === "Escape") {
cerrarMas();
}
});

function ajustarMenu() {
enlaces.forEach(function (enlace) {
enlace.classList.remove("oculto-por-espacio");
if (enlace.parentElement !== menu) {
menu.insertBefore(enlace, contenedorMas);
}
});
desplegableMas.innerHTML = "";
botonMas.classList.remove("tiene-oculto");
cerrarMas();

if (window.innerWidth <= puntoCorte) {
return;
}

const anchoTotal = enlaces.reduce(function (suma, enlace) {
return suma + enlace.offsetWidth + 6;
}, 0);
if (anchoTotal <= menu.clientWidth) {
return;
}

const anchoDisponible = menu.clientWidth - (botonMas.offsetWidth || 90) - 6;
let usado = 0;
const desbordados = [];
enlaces.forEach(function (enlace) {
const ancho = enlace.offsetWidth + 6;
if (!desbordados.length && usado + ancho <= anchoDisponible) {
usado += ancho;
} else {
desbordados.push(enlace);
}
});

desbordados.forEach(function (enlace) {
enlace.classList.add("oculto-por-espacio");
desplegableMas.appendChild(enlace);
});
botonMas.classList.add("tiene-oculto");
}

let temporizador = null;
window.addEventListener("resize", function () {
clearTimeout(temporizador);
temporizador = setTimeout(ajustarMenu, 150);
});
window.addEventListener("load", ajustarMenu);
if (document.fonts && document.fonts.ready) {
document.fonts.ready.then(ajustarMenu);
}
ajustarMenu();
})();
</script>