<?php
// Prepara las competencias y categorías disponibles para el nuevo partido.
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION["rol"] !== "admin") {
    echo "<h2>Acceso denegado</h2>";
    exit();
}

include("conexion.php");

$torneos = [
    "estandar" => "Estándar",
    "plata" => "Copa Plata",
    "oro" => "Copa Oro"
];
$claveSolicitada = $_GET["torneo"] ?? "estandar";
$claveTorneo = is_string($claveSolicitada) && isset($torneos[$claveSolicitada])
    ? $claveSolicitada
    : "estandar";
$categoriaSeleccionada = $torneos[$claveTorneo];
$categoriasEdad = array_merge(array_map("strval", range(2012, 2019)), ["Femenina"]);
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agregar Partido</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Agregar Partido</h2>

<!-- Recoge clubes, categoría, fecha, hora y competencia. -->
<form action="guardar_partido.php" method="POST">
<?php if (($_GET["origen"] ?? "") === "tabla") { ?>
<input type="hidden" name="volver_tabla" value="1">
<?php } ?>

<label for="categoria_edad">Categoría</label><br>
<select id="categoria_edad" name="categoria_edad" required>
    <option value="">Selecciona una categoría</option>
    <?php foreach ($categoriasEdad as $categoriaEdad) { ?>
        <option value="<?php echo $categoriaEdad; ?>"><?php echo $categoriaEdad; ?></option>
    <?php } ?>
</select>

<br><br>

<label>Club Local</label><br>
<select name="local">
<?php
$clubes = mysqli_query($conn, "SELECT * FROM clubes WHERE activo = 1");
while($club = mysqli_fetch_assoc($clubes)){
?>
<option value="<?php echo $club["id"]; ?>">
    <?php echo $club["nombre"]; ?>
</option>
<?php } ?>
</select>

<br><br>

<label>Club Visitante</label><br>
<select name="visitante">
<?php
$clubes2 = mysqli_query($conn, "SELECT * FROM clubes WHERE activo = 1");
while($club2 = mysqli_fetch_assoc($clubes2)){
?>
<option value="<?php echo $club2["id"]; ?>">
    <?php echo $club2["nombre"]; ?>
</option>
<?php } ?>
</select>

<br><br>

<label>Fecha</label><br>
<input type="date" id="fecha" name="fecha" min="<?php echo date("Y"); ?>-01-01"
       max="<?php echo date("Y"); ?>-12-31"
       aria-describedby="fecha-error" required>
<small id="fecha-error" class="ayuda-fecha" hidden></small>

<br><br>

<label for="hora">Hora del partido (AM/PM)</label><br>
<input type="text" id="hora" name="hora" placeholder="8:30Am" maxlength="7"
       autocomplete="off" autocapitalize="characters" aria-describedby="hora-error"
       required title="Escribe una hora entre 1:00 y 12:59, seguida de A o P.">
<small id="hora-error" class="ayuda-fecha" hidden></small>

<br><br>

<label>Competencia</label><br>
<select name="categoria" required>
    <?php foreach ($torneos as $categoria) { ?>
        <option value="<?php echo htmlspecialchars($categoria, ENT_QUOTES, "UTF-8"); ?>"
            <?php echo $categoria === $categoriaSeleccionada ? "selected" : ""; ?>>
            <?php echo htmlspecialchars($categoria, ENT_QUOTES, "UTF-8"); ?>
        </option>
    <?php } ?>
</select>

<br><br>

<button type="submit">Guardar Partido</button>

</form>

<script>
// Valida fecha y hora y da formato a la entrada de hora mientras se escribe.
const fechaPartido = document.getElementById("fecha");
const errorFecha = document.getElementById("fecha-error");
const horaPartido = document.getElementById("hora");
const errorHora = document.getElementById("hora-error");
const formularioPartido = horaPartido.form;
const anioActual = "<?php echo date("Y"); ?>";
const mensajeFecha = "La fecha del partido debe pertenecer al año actual (" + anioActual + ").";
const formatoHoraValido = /^(?:[1-9]|1[0-2]):[0-5][0-9](?:Am|Pm)$/;

function validarFechaPartido(mostrarMensaje = false) {
    const fechaInvalida = fechaPartido.value &&
        (fechaPartido.value < fechaPartido.min || fechaPartido.value > fechaPartido.max);
    const fechaIncompleta = mostrarMensaje && !fechaPartido.value;
    const mensaje = fechaInvalida
        ? mensajeFecha
        : fechaIncompleta ? "Debes ingresar la fecha del partido." : "";

    fechaPartido.setCustomValidity(mensaje);
    errorFecha.textContent = mensaje;
    errorFecha.hidden = !mensaje;
    if (mensaje && mostrarMensaje) {
        fechaPartido.reportValidity();
    }
    return !mensaje;
}

// Convierte variantes de entrada en una hora de reloj legible y consistente.
function formatearHora(valor) {
    const entrada = valor.replace(/m/gi, "").replace(/[^0-9:ap]/gi, "").toUpperCase();
    const separador = entrada.indexOf(":");
    let hora;
    let minutosEntrada;
    let sufijoEntrada;

    if (separador >= 0) {
        hora = entrada.slice(0, separador).replace(/\D/g, "").slice(0, 2);
        minutosEntrada = entrada.slice(separador + 1).replace(/\D/g, "").slice(0, 2);
        sufijoEntrada = entrada.slice(separador + 1).match(/[AP]/);
    } else {
        const digitos = entrada.replace(/\D/g, "").slice(0, 4);
        if (!digitos) {
            return "";
        }
        if (digitos[0] === "1") {
            if (digitos.length === 1) {
                return "1";
            }
            if (!/[0-2]/.test(digitos[1])) {
                return "1:";
            }
            hora = digitos.slice(0, 2);
            minutosEntrada = digitos.slice(2);
        } else {
            hora = digitos[0];
            minutosEntrada = digitos.slice(1);
        }
        sufijoEntrada = entrada.match(/[AP]/);
    }

    if (!hora || hora[0] === "0" || (hora.length === 2 && !/^1[0-2]$/.test(hora))) {
        return "";
    }

    let minutos = "";
    if (minutosEntrada) {
        if (minutosEntrada[0] > "5") {
            minutosEntrada = minutosEntrada.slice(1);
        }
        minutos = minutosEntrada.slice(0, 2);
    }
    if (minutos.length < 2) {
        sufijoEntrada = null;
    }

    return hora + ":" + minutos + (sufijoEntrada ? sufijoEntrada[0] : "");
}

fechaPartido.addEventListener("input", () => validarFechaPartido());
fechaPartido.addEventListener("change", () => validarFechaPartido(true));
fechaPartido.addEventListener("blur", () => validarFechaPartido(true));

horaPartido.addEventListener("input", () => {
    const valorFormateado = formatearHora(horaPartido.value);
    horaPartido.value = valorFormateado;
    horaPartido.setSelectionRange(valorFormateado.length, valorFormateado.length);
    errorHora.hidden = true;
    horaPartido.setCustomValidity("");
});

function finalizarHora() {
    const coincidencia = horaPartido.value.match(/^((?:[1-9]|1[0-2]):[0-5][0-9])([AP])$/);
    if (coincidencia) {
        horaPartido.value = coincidencia[1] + coincidencia[2] + "m";
    }
    const horaCorrecta = formatoHoraValido.test(horaPartido.value);
    const mensaje = horaPartido.value && !horaCorrecta
        ? "Completa la hora con minutos y A o P, por ejemplo 8:40Am."
        : "";
    horaPartido.setCustomValidity(mensaje);
    errorHora.textContent = mensaje;
    errorHora.hidden = !mensaje;
}

horaPartido.addEventListener("blur", finalizarHora);

// Revisa nuevamente ambos campos antes de permitir el envío.
formularioPartido.addEventListener("submit", (evento) => {
    const fechaCorrecta = validarFechaPartido();
    finalizarHora();
    const horaCorrecta = formatoHoraValido.test(horaPartido.value);
    const mensaje = "Ingresa una hora válida, por ejemplo 8:40Am, 10:40Am o 12:40Pm.";

    horaPartido.setCustomValidity(horaCorrecta ? "" : mensaje);
    errorHora.textContent = horaCorrecta ? "" : mensaje;
    errorHora.hidden = horaCorrecta;

    if (!fechaCorrecta || !horaCorrecta) {
        evento.preventDefault();
        if (!fechaCorrecta) {
            fechaPartido.reportValidity();
        } else {
            horaPartido.reportValidity();
        }
    }
});
</script>

</body>
</html>
