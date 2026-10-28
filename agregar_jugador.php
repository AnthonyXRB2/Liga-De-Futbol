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
<input type="text" id="nombre" name="nombre" maxlength="43" aria-describedby="nombre_error" required>
<span id="nombre_error" class="ayuda-fecha" role="alert" hidden></span><br><br>

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
<input id="ci" type="text" name="ci" inputmode="numeric" autocomplete="off" maxlength="8" pattern="[0-9]{8}" aria-describedby="ci_error" required>
<span id="ci_error" class="ayuda-fecha" role="alert" hidden></span>

<label>Altura (metros)</label><br>
<input type="text" name="altura" inputmode="decimal" maxlength="4" pattern="(?:1,[0-9]{1,2}|2,(?:0[0-9]?|10?))" data-decimal="altura" aria-describedby="altura_error" placeholder="Ej: 1,75">
<span id="altura_error" class="ayuda-fecha" role="alert" hidden></span><br><br>

<label>Peso (kg)</label><br>
<input type="text" name="masa" inputmode="decimal" maxlength="5" pattern="(?:1[5-9]|[2-8][0-9]|9[0-5]),[0-9]" data-decimal="masa" aria-describedby="masa_error" placeholder="Ej: 70,5">
<span id="masa_error" class="ayuda-fecha" role="alert" hidden></span><br><br>

<label>Velocidad promedio (m/s)</label><br>
<input type="text" name="velocidad" inputmode="decimal" maxlength="3" pattern="[1-9],[0-9]" data-decimal="velocidad" aria-describedby="velocidad_error" placeholder="Ej: 8,5">
<span id="velocidad_error" class="ayuda-fecha" role="alert" hidden></span><br><br>

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
const nombre = document.getElementById("nombre");
const errorNombre = document.getElementById("nombre_error");
const cedula = document.getElementById("ci");
const errorCedula = document.getElementById("ci_error");
const camposFisicos = document.querySelectorAll("[data-decimal]");

function validarNombre(mostrarErrorEspacioFinal = false) {
    const palabras = nombre.value.split(" ");
    const nombreInvalido = !/^\p{Lu}[\p{L}\p{M} ]*$/u.test(nombre.value)
        || palabras.length > 4
        || palabras.some((palabra) => {
            const letras = Array.from(palabra.matchAll(/\p{L}/gu)).length;
            return !/^\p{Lu}[\p{Ll}\p{M}]*$/u.test(palabra) || letras < 3 || letras > 10;
        });
    const espacioFinal = nombre.value.endsWith(" ");
    const mensajeEspacioFinal = "Borra el espacio de mas.";

    nombre.setCustomValidity(
        espacioFinal
            ? mensajeEspacioFinal
            : nombreInvalido
                ? "El nombre debe tener de 1 a 4 palabras, con 3 a 10 letras cada una."
                : ""
    );
    errorNombre.textContent = espacioFinal && mostrarErrorEspacioFinal ? mensajeEspacioFinal : "";
    errorNombre.hidden = !(espacioFinal && mostrarErrorEspacioFinal);
}

nombre.addEventListener("input", () => {
    let texto = "";
    let letrasEnPalabra = 0;
    let espacios = 0;
    let ultimaCaracterFueEspacio = false;

    for (const caracter of nombre.value) {
        if (/\p{L}/u.test(caracter)) {
            if (letrasEnPalabra < 10) {
                texto += letrasEnPalabra === 0 ? caracter.toLocaleUpperCase("es") : caracter.toLocaleLowerCase("es");
                letrasEnPalabra += 1;
                ultimaCaracterFueEspacio = false;
            }
        } else if (/\p{M}/u.test(caracter) && letrasEnPalabra > 0 && !ultimaCaracterFueEspacio) {
            texto += caracter;
        } else if (
            caracter === " "
            && letrasEnPalabra >= 3
            && letrasEnPalabra <= 10
            && espacios < 3
            && !ultimaCaracterFueEspacio
        ) {
            texto += caracter;
            espacios += 1;
            letrasEnPalabra = 0;
            ultimaCaracterFueEspacio = true;
        }
    }

    nombre.value = texto;
    validarNombre();
});
nombre.addEventListener("blur", () => validarNombre(true));

function mostrarErrorCedula(mensaje) {
    cedula.setCustomValidity(mensaje);
    errorCedula.textContent = mensaje;
    errorCedula.hidden = mensaje === "";
}

async function validarCedulaUnica() {
    if (!/^[0-9]{8}$/.test(cedula.value)) {
        mostrarErrorCedula("La cédula debe contener exactamente 8 dígitos.");
        return false;
    }

    if (/^(?:([0-9])\1{7}|01234567|12345678|23456789|98765432|87654321|76543210)$/.test(cedula.value)) {
        mostrarErrorCedula("La cédula no puede repetir el mismo dígito ni ser una secuencia consecutiva.");
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

function validarCampoFisico(campo) {
    const error = document.getElementById(campo.name + "_error");
    const valor = campo.value;
    let mensaje = "";
    const reglas = {
        altura: { formato: /^(?:1,[0-9]{1,2}|2,(?:0[0-9]?|10?))$/, minimo: 1, maximo: 2.1, rango: "La altura debe estar entre 1,00 y 2,10 m." },
        masa: { formato: /^(?:1[5-9]|[2-8][0-9]|9[0-5]),[0-9]$/, minimo: 15, maximo: 95.9, rango: "El peso debe estar entre 15,0 y 95,9 kg." },
        velocidad: { formato: /^[1-9],[0-9]$/, minimo: 1, maximo: 9.9, rango: "La velocidad debe estar entre 1,0 y 9,9 m/s." }
    };
    const regla = reglas[campo.name];

    if (valor !== "" && !regla.formato.test(valor)) {
        mensaje = "Completa el valor con el formato indicado y usando una coma decimal.";
    } else if (valor !== "") {
        const numero = Number(valor.replace(",", "."));
        if (numero < regla.minimo || numero > regla.maximo) {
            mensaje = regla.rango;
        }
    }

    campo.setCustomValidity(mensaje);
    error.textContent = mensaje;
    error.hidden = mensaje === "";
}

camposFisicos.forEach((campo) => {
    campo.addEventListener("input", () => {
        const reglas = {
            altura: { primerDigito: /^[12]$/, digitosEnteros: 1, decimales: 2 },
            masa: { primerDigito: /^[1-9]$/, digitosEnteros: 2, decimales: 1 },
            velocidad: { primerDigito: /^[1-9]$/, digitosEnteros: 1, decimales: 1 }
        };
        const regla = reglas[campo.name];
        const entrada = campo.value.replace(/[^0-9,]/g, "");
        const partes = entrada.split(",");
        let enteros = "";

        for (const digito of partes[0]) {
            if (enteros.length === 0 && !regla.primerDigito.test(digito)) {
                break;
            }
            if (campo.name === "masa" && enteros === "9" && !/[0-5]/.test(digito)) {
                break;
            }
            if (campo.name === "masa" && enteros === "1" && !/[5-9]/.test(digito)) {
                break;
            }
            if (enteros.length === regla.digitosEnteros) {
                break;
            }
            enteros += digito;
        }

        const comaIngresada = partes.length > 1 && enteros.length === regla.digitosEnteros;
        let decimales = (partes.slice(1).join("").match(/[0-9]/g) || []).slice(0, regla.decimales).join("");
        if (campo.name === "altura" && enteros === "2") {
            if (decimales.length > 0 && !/^[01]/.test(decimales)) {
                decimales = "";
            } else if (decimales.startsWith("1") && decimales.length > 1 && decimales[1] !== "0") {
                decimales = decimales.slice(0, 1);
            }
        }
        campo.value = enteros + (comaIngresada ? "," + decimales : "");
        validarCampoFisico(campo);
    });
});

formularioJugador.addEventListener("submit", async (evento) => {
    validarNombre(true);
    camposFisicos.forEach(validarCampoFisico);

    if (!formularioJugador.reportValidity()) {
        evento.preventDefault();
        return;
    }

    if (formularioJugador.dataset.ciVerificada === cedula.value) {
        delete formularioJugador.dataset.ciVerificada;
        return;
    }

    evento.preventDefault();
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
