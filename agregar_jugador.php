<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION["rol"] != "admin") {
    echo "<h2>Acceso denegado</h2>";
    exit();
}

include("conexion.php");
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agregar Jugador</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Agregar Jugador</h2>

<form id="form-jugador" action="guardar_jugador.php" method="POST">

<label>Nombre</label><br>
<input type="text" name="nombre" required><br><br>

<label>Fecha de nacimiento</label><br>
<input type="date" name="fecha_nacimiento" id="fecha_nacimiento" min="2013-01-01" max="2019-12-31" aria-describedby="fecha_error" required>
<span id="fecha_error" class="ayuda-fecha" role="alert" hidden></span><br><br>

<label>Categoría calculada</label><br>
<input type="text" id="categoria_preview" readonly><br><br>

<label>Posición</label><br>
<select name="posicion" required>
    <option>Arquero</option>
    <option>Defensa</option>
    <option>Mediocampista</option>
    <option>Delantero</option>
</select>

<br><br>

<label>Club</label><br>

<select name="club_id" required>

<?php

$clubes = mysqli_query($conn, "SELECT id, nombre FROM clubes WHERE activo = 1 ORDER BY nombre");

while($club = mysqli_fetch_assoc($clubes)){
?>

<option value="<?php echo $club["id"]; ?>">
    <?php echo $club["nombre"]; ?>
</option>

<?php
}
?>

</select>

<br><br>

<label for="ci">Cédula</label>
<input id="ci" type="text" name="ci" inputmode="numeric" autocomplete="off" maxlength="8" pattern="[0-9]{1,8}" aria-describedby="ci_error" required>
<span id="ci_error" class="ayuda-fecha" role="alert" hidden></span>

<label>Altura (metros)</label><br>
<input type="number" step="0.01" name="altura" placeholder="Ej: 1.75"><br><br>

<label>Masa (kg)</label><br>
<input type="number" step="0.1" name="masa" placeholder="Ej: 70.5"><br><br>

<label>Velocidad promedio (m/s)</label><br>
<input type="number" step="0.1" name="velocidad" placeholder="Ej: 8.5"><br><br>

<button type="submit">Guardar</button>

</form>

<script>
const fechaNacimiento = document.getElementById("fecha_nacimiento");
const categoriaPreview = document.getElementById("categoria_preview");

function validarFechaNacimiento() {
    const anio = Number(fechaNacimiento.value.slice(0, 4));
    const fechaInvalida = fechaNacimiento.value && (anio < 2013 || anio > 2019);
    const mensaje = "El año permitido para la categoría es de 2013 a 2019.";

    fechaNacimiento.setCustomValidity(fechaInvalida ? mensaje : "");
    document.getElementById("fecha_error").textContent = fechaInvalida ? mensaje : "";
    document.getElementById("fecha_error").hidden = !fechaInvalida;
    categoriaPreview.value = fechaNacimiento.value && !fechaInvalida ? String(anio) : "";
}

fechaNacimiento.addEventListener("input", validarFechaNacimiento);
fechaNacimiento.addEventListener("change", validarFechaNacimiento);

const formularioJugador = document.getElementById("form-jugador");
const cedula = document.getElementById("ci");
const errorCedula = document.getElementById("ci_error");

function mostrarErrorCedula(mensaje) {
    cedula.setCustomValidity(mensaje);
    errorCedula.textContent = mensaje;
    errorCedula.hidden = mensaje === "";
}

async function validarCedulaUnica() {
    if (!/^[0-9]{1,8}$/.test(cedula.value)) {
        mostrarErrorCedula("La cédula solo puede tener números y hasta 8 dígitos.");
        return false;
    }

    mostrarErrorCedula("");
    try {
        const respuesta = await fetch("verificar_ci.php?ci=" + encodeURIComponent(cedula.value), {
            headers: { "Accept": "application/json" }
        });
        if (!respuesta.ok) {
            throw new Error("No se pudo verificar la cédula.");
        }
        const resultado = await respuesta.json();
        if (!resultado.disponible) {
            mostrarErrorCedula("Esta cédula ya está registrada. Cada jugador debe tener una cédula única.");
            return false;
        }
        mostrarErrorCedula("");
        return true;
    } catch (error) {
        mostrarErrorCedula("No se pudo comprobar la cédula. Intenta nuevamente.");
        return false;
    }
}

cedula.addEventListener("input", () => {
    cedula.value = cedula.value.replace(/[^0-9]/g, "").slice(0, 8);
    mostrarErrorCedula("");
});
cedula.addEventListener("blur", validarCedulaUnica);

formularioJugador.addEventListener("submit", async (evento) => {
    if (formularioJugador.dataset.ciVerificada === cedula.value) {
        delete formularioJugador.dataset.ciVerificada;
        return;
    }

    evento.preventDefault();
    if (!formularioJugador.reportValidity()) {
        return;
    }
    if (await validarCedulaUnica()) {
        formularioJugador.dataset.ciVerificada = cedula.value;
        formularioJugador.requestSubmit();
    } else {
        cedula.reportValidity();
    }
});
</script>

</body>
</html>
