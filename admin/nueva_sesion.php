<?php
require_once "seguridad_admin.php";
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../funciones.php';
$sql_actividades = "
SELECT
id_actividad,
nombre,
duracion_minutos
FROM actividades
WHERE activa = 1
ORDER BY nombre
";
$sql_espacios = "
SELECT
id_espacio,
nombre,
ubicacion,
aforo_maximo
FROM espacios
WHERE activo = 1
ORDER BY nombre
";
$sql_profesores = "
SELECT
id_profesor,
nombre,
apellidos,
especialidad
FROM profesores
WHERE activo = 1
ORDER BY apellidos, nombre
";
$actividades =
$conexion->query($sql_actividades);
$espacios =
$conexion->query($sql_espacios);
$profesores =
$conexion->query($sql_profesores);
$puede_crearse =
$actividades->num_rows > 0
&& $espacios->num_rows > 0
&& $profesores->num_rows > 0;
$hoy = new DateTime('today');
$meses_calendario = [];
for ($i = 0; $i <= 5; $i++) {
$mes_recorrido = (clone $hoy)->modify("+$i month");
$meses_calendario[] = [
'anio' => (int) $mes_recorrido->format('Y'),
'mes' => (int) $mes_recorrido->format('n')
];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>
<title>
</title>
Programar sesión | Sama Shala
<link rel="stylesheet" href="<?= urlEstilos('../') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="../imagenes/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../imagenes/favicon-16x16.png">
<link rel="apple-touch-icon" href="../imagenes/apple-touch-icon.png">
<link rel="shortcut icon" href="../imagenes/favicon.ico">
</head>
<body>
<?php require_once __DIR__ . '/menu_admin.php'; ?>
<main class="contenedor seccion">
<a class="enlace-volver" href="sesiones.php">
← Volver al calendario
</a>
<p class="etiqueta">
Horarios
</p>
<h1>Programar una sesión</h1>
<?php if (!$puede_crearse): ?>
<div class="mensaje mensaje-aviso">
Para crear una sesión debe existir, como
mínimo, una actividad activa, un espacio
disponible y un profesor activo.
</div>
<?php else: ?>
<?php if (isset($_GET['error'])): ?>
<div class="mensaje mensaje-error">
No se ha podido programar la sesión.
Revisa la fecha, el horario y el aforo.
</div>
<?php endif; ?>
<form
class="formulario-admin"
action="guardar_sesion.php"
method="post"
>
<input
type="hidden"
name="csrf_token"
value="<?= escapar(tokenCsrf()) ?>"
>
<div class="campo">
<label for="id_actividad">
Actividad
</label>
<select
id="id_actividad"
name="id_actividad"
required
>
<option value="">
Selecciona una actividad
</option>
<?php while (
$actividad =
$actividades->fetch_assoc()
): ?>
<option
value="<?= (int)
$actividad['id_actividad'] ?>"
data-duracion="<?= (int)
$actividad['duracion_minutos'] ?>"
>
<?= escapar(
$actividad['nombre']
) ?>
—
<?= (int)
$actividad['duracion_minutos'] ?>
min
</option>
<?php endwhile; ?>
</select>
</div>
<div class="campo">
<label for="id_espacio">
Espacio
</label>
<select
id="id_espacio"
name="id_espacio"
required
>
<option value="">
Selecciona un espacio
</option>
<?php while (
$espacio =
$espacios->fetch_assoc()
): ?>
<option
value="<?= (int)
$espacio['id_espacio'] ?>"
data-aforo="<?= (int)
$espacio['aforo_maximo'] ?>"
>
<?= escapar(
$espacio['nombre']
) ?>
—
máximo
<?= (int)
$espacio['aforo_maximo'] ?>
</option>
<?php endwhile; ?>
</select>
</div>
<div class="campo campo-completo">
<label>
Profesores
</label>
<div class="lista-profesores-checkbox">
<?php while (
$profesor =
$profesores->fetch_assoc()
): ?>
<div class="campo-checkbox">
<label>
<input
type="checkbox"
name="profesores[]"
value="<?= (int)
$profesor['id_profesor'] ?>"
>
<span>
<?= escapar(
$profesor['nombre'] . ' ' .
$profesor['apellidos']
) ?>
<?php if (
!empty(
$profesor['especialidad']
)
): ?>
—
<?= escapar(
$profesor['especialidad']
) ?>
<?php endif; ?>
</span>
</label>
</div>
<?php endwhile; ?>
</div>
<small>
Selecciona uno o más profesores para esta sesión.
</small>
</div>
<div class="campo-checkbox campo-completo">
<label>
<input
type="checkbox"
id="es_recurrente"
name="es_recurrente"
value="1"
>
¿Es una sesión recurrente (varias fechas y horarios)?
</label>
</div>
<div class="campo-completo bloque-fecha-unica" id="bloque-fecha-unica">
<div class="campo">
<label for="fecha">
Fecha
</label>
<input
type="date"
id="fecha"
name="fecha"
min="<?= date('Y-m-d') ?>"
required
>
</div>
<div class="campo">
<label for="hora_inicio">
Hora de inicio
</label>
<input
type="time"
id="hora_inicio"
name="hora_inicio"
required
>
<p class="ayuda">
El sistema calculará automáticamente la hora final y comprobará que el espacio y el profesor estén disponibles.
</p>
<p>
Hora final prevista:
<strong id="hora-final">--:--</strong>
</p>

</div>
</div>
<fieldset
class="campo-completo bloque-regular"
id="bloque-recurrente"
>
<legend>
Programar varias sesiones
</legend>
<p class="ayuda">
Activa los días de la semana en los que se repite esta
sesión y elige la hora de cada uno — pueden ser distintas.
Si un día tiene varios horarios (por ejemplo lunes 9:00 y
lunes 18:30), pulsa "Añadir horario" para sumar más. Al
activarlos se marcarán automáticamente esos días en el
calendario; puedes ajustar fechas sueltas a mano. Se creará
una sesión para cada fecha marcada y cada horario de su día
de la semana, con la misma actividad, espacio, profesores,
duración y aforo indicados arriba.
</p>
<div class="campo campo-completo">
<label>
Horario por día de la semana
</label>
<div class="horarios-dias-semana">
<?php for ($dia_semana = 1; $dia_semana <= 7; $dia_semana++): ?>
<div class="horario-dia-semana">
<label class="campo-checkbox">
<input
type="checkbox"
class="entrada-dia-activo"
name="dias_recurrentes[]"
value="<?= $dia_semana ?>"
data-dia="<?= $dia_semana ?>"
>
<span><?= escapar(texto_dia_semana($dia_semana)) ?></span>
</label>
<div
class="horario-dia-semana-horas"
data-dia="<?= $dia_semana ?>"
>
<div class="horario-dia-semana-hora">
<input
type="time"
class="entrada-hora-dia"
name="horas_recurrente_<?= $dia_semana ?>[]"
data-dia="<?= $dia_semana ?>"
disabled
>
<button
type="button"
class="boton-quitar-hora"
data-dia="<?= $dia_semana ?>"
hidden
>
&times;
</button>
</div>
</div>
<button
type="button"
class="boton-agregar-hora"
data-dia="<?= $dia_semana ?>"
disabled
>
+ Añadir horario
</button>
</div>
<?php endfor; ?>
</div>
</div>
<div class="calendarios-regular">
<?php foreach ($meses_calendario as $mes_info): ?>
<div class="calendario-mes">
<p class="calendario-mes-titulo">
<?= escapar(texto_mes($mes_info['mes'])) ?> <?= $mes_info['anio'] ?>
</p>
<div class="calendario-mes-cabecera">
<?php for ($d = 1; $d <= 7; $d++): ?>
<span>
<?= escapar(texto_dia_semana_abreviado($d)) ?>
</span>
<?php endfor; ?>
</div>
<div class="calendario-mes-grilla">
<?php
$semanas = generar_calendario_mes(
$mes_info['anio'],
$mes_info['mes']
);
?>
<?php foreach ($semanas as $semana): ?>
<?php foreach ($semana as $dia): ?>
<?php if ($dia === null): ?>
<span class="dia-calendario dia-calendario-vacio"></span>
<?php elseif ($dia < $hoy): ?>
<span class="dia-calendario dia-calendario-pasado">
<?= (int) $dia->format('j') ?>
</span>
<?php else: ?>
<label class="dia-calendario">
<input
type="checkbox"
name="fechas_recurrentes[]"
value="<?= $dia->format('Y-m-d') ?>"
class="entrada-dia-calendario"
data-dia-semana="<?= (int) $dia->format('N') ?>"
>
<span><?= (int) $dia->format('j') ?></span>
</label>
<?php endif; ?>
<?php endforeach; ?>
<?php endforeach; ?>
</div>
</div>
<?php endforeach; ?>
</div>
</fieldset>
<div class="campo">
<label for="duracion">
Duración en minutos
</label>
<input
type="number"
id="duracion"
name="duracion"
min="15"
max="480"
step="15"
value="60"
required
>
</div>
<div class="campo">
<label for="aforo">
Aforo de la sesión
</label>
<input
type="number"
id="aforo"
name="aforo"
min="1"
required
>
<small id="ayuda-aforo">
Selecciona un espacio para conocer
su aforo máximo.
</small>
</div>
<div class="campo campo-completo">
<label for="observaciones">
Observaciones
</label>
<textarea
id="observaciones"
name="observaciones"
rows="5"
placeholder="Material necesario, indicaciones de acceso..."
></textarea>
</div>
<div class="campo-completo">
<button class="boton" type="submit">
Programar sesión
</button>
</div>
</form>
<?php endif; ?>
<script>
const campoHora = document.querySelector("#hora_inicio");
const campoDuracion = document.querySelector("#duracion");
const salida = document.querySelector("#hora-final");

function calcularHoraFinal() {
    if (!campoHora.value || !campoDuracion.value) {
        salida.textContent = "--:--";
        return;
    }

    const [horas, minutos] =
        campoHora.value.split(":").map(Number);

    const fecha = new Date();
    fecha.setHours(horas, minutos, 0, 0);

    fecha.setMinutes(
        fecha.getMinutes() + Number(campoDuracion.value)
    );

    salida.textContent = fecha.toLocaleTimeString("es-ES", {
        hour: "2-digit",
        minute: "2-digit"
    });
}

campoHora.addEventListener("input", calcularHoraFinal);
campoDuracion.addEventListener("input", calcularHoraFinal);

(function () {
    const casillaRecurrente = document.querySelector("#es_recurrente");
    const bloqueFechaUnica = document.querySelector("#bloque-fecha-unica");
    const bloqueRecurrente = document.querySelector("#bloque-recurrente");
    if (casillaRecurrente && bloqueFechaUnica && bloqueRecurrente) {
        function actualizarModoRecurrente() {
            const recurrente = casillaRecurrente.checked;
            bloqueFechaUnica.classList.toggle("oculto", recurrente);
            bloqueRecurrente.classList.toggle("visible", recurrente);
            campoHora.disabled = recurrente;
            campoHora.required = !recurrente;
            const campoFechaUnica = document.querySelector("#fecha");
            if (campoFechaUnica) {
                campoFechaUnica.disabled = recurrente;
                campoFechaUnica.required = !recurrente;
            }
        }
        casillaRecurrente.addEventListener("change", actualizarModoRecurrente);
        actualizarModoRecurrente();
    }

    const casillasDiaActivo = document.querySelectorAll(
        ".entrada-dia-activo"
    );
    function diasCalendarioDelDia(dia) {
        return document.querySelectorAll(
            '.entrada-dia-calendario[data-dia-semana="' + dia + '"]'
        );
    }
    function contenedorHorasDelDia(dia) {
        return document.querySelector(
            '.horario-dia-semana-horas[data-dia="' + dia + '"]'
        );
    }
    function crearFilaHora(dia) {
        const fila = document.createElement("div");
        fila.className = "horario-dia-semana-hora";
        const entrada = document.createElement("input");
        entrada.type = "time";
        entrada.className = "entrada-hora-dia";
        entrada.name = "horas_recurrente_" + dia + "[]";
        entrada.setAttribute("data-dia", dia);
        const botonQuitar = document.createElement("button");
        botonQuitar.type = "button";
        botonQuitar.className = "boton-quitar-hora";
        botonQuitar.setAttribute("data-dia", dia);
        botonQuitar.innerHTML = "&times;";
        botonQuitar.addEventListener("click", function () {
            fila.remove();
        });
        fila.appendChild(entrada);
        fila.appendChild(botonQuitar);
        return { fila, entrada };
    }
    casillasDiaActivo.forEach(function (casillaDia) {
        const dia = casillaDia.getAttribute("data-dia");
        const contenedorHoras = contenedorHorasDelDia(dia);
        const botonAgregar = document.querySelector(
            '.boton-agregar-hora[data-dia="' + dia + '"]'
        );
        casillaDia.addEventListener("change", function () {
            const activo = casillaDia.checked;
            if (contenedorHoras) {
                const entradas = contenedorHoras.querySelectorAll(
                    ".entrada-hora-dia"
                );
                entradas.forEach(function (entrada, indice) {
                    entrada.disabled = !activo;
                    entrada.required = activo && indice === 0;
                });
            }
            if (botonAgregar) {
                botonAgregar.disabled = !activo;
            }
            diasCalendarioDelDia(dia).forEach(function (diaCalendario) {
                if (!diaCalendario.disabled) {
                    diaCalendario.checked = activo;
                }
            });
        });
    });
    document.querySelectorAll(".boton-agregar-hora").forEach(function (
        boton
    ) {
        const dia = boton.getAttribute("data-dia");
        boton.addEventListener("click", function () {
            const contenedorHoras = contenedorHorasDelDia(dia);
            if (!contenedorHoras) {
                return;
            }
            const { fila, entrada } = crearFilaHora(dia);
            contenedorHoras.appendChild(fila);
            entrada.focus();
        });
    });
})();
</script>
</main>