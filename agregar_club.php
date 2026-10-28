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
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agregar Club</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Agregar Club</h2>

<form id="form-club" action="guardar_club.php" method="POST" novalidate>

    <label for="nombre">Nombre del club</label><br>
    <input type="text" id="nombre" name="nombre" maxlength="21" aria-describedby="nombre-ayuda nombre-espacio-error" required>
    <small id="nombre-ayuda">Hasta 2 palabras, máximo 10 letras cada una. Cada palabra comienza con mayúscula.</small>
    <span id="nombre-espacio-error" class="ayuda-fecha" role="alert" hidden></span>

    <br><br>

    <label for="director_tecnico">Director técnico</label><br>
    <input type="text" id="director_tecnico" name="director_tecnico" maxlength="43" aria-describedby="director-ayuda director-espacio-error" required>
    <small id="director-ayuda">Hasta 4 palabras, máximo 10 letras cada una. Cada palabra comienza con mayúscula.</small>
    <span id="director-espacio-error" class="ayuda-fecha" role="alert" hidden></span>

    <br><br>

    <label id="departamento-etiqueta">Departamento</label>
    <input type="hidden" id="departamento" name="departamento" value="">
    <details class="selector-departamento" id="selector-departamento">
        <summary aria-labelledby="departamento-etiqueta" aria-controls="opciones-departamento">Selecciona un departamento</summary>
        <div class="opciones-departamento" id="opciones-departamento">
            <button type="button" data-departamento="Artigas">Artigas</button>
            <button type="button" data-departamento="Canelones">Canelones</button>
            <button type="button" data-departamento="Cerro Largo">Cerro Largo</button>
            <button type="button" data-departamento="Colonia">Colonia</button>
            <button type="button" data-departamento="Durazno">Durazno</button>
            <button type="button" data-departamento="Flores">Flores</button>
            <button type="button" data-departamento="Florida">Florida</button>
            <button type="button" data-departamento="Lavalleja">Lavalleja</button>
            <button type="button" data-departamento="Maldonado">Maldonado</button>
            <button type="button" data-departamento="Montevideo">Montevideo</button>
            <button type="button" data-departamento="Paysandú">Paysandú</button>
            <button type="button" data-departamento="Río Negro">Río Negro</button>
            <button type="button" data-departamento="Rivera">Rivera</button>
            <button type="button" data-departamento="Rocha">Rocha</button>
            <button type="button" data-departamento="Salto">Salto</button>
            <button type="button" data-departamento="San José">San José</button>
            <button type="button" data-departamento="Soriano">Soriano</button>
            <button type="button" data-departamento="Tacuarembó">Tacuarembó</button>
            <button type="button" data-departamento="Treinta y Tres">Treinta y Tres</button>
        </div>
    </details>

    <br><br>

    <label for="ciudad">Ciudad</label><br>
    <input type="text" id="ciudad" name="ciudad" maxlength="100" required>

    <br><br>

    <button type="submit">Guardar</button>

</form>

<script>
function configurarNombreCompuesto(campo, maxEspacios, avisoEspacio) {
    function validar(mostrarAvisoEspacio = false) {
        const palabras = campo.value.split(" ");
        const patron = new RegExp(
            "^\\p{Lu}[\\p{Ll}\\p{M}]*(?: \\p{Lu}[\\p{Ll}\\p{M}]*){0," + maxEspacios + "}$",
            "u"
        );
        const invalido = !patron.test(campo.value)
            || palabras.length > maxEspacios + 1
            || palabras.some((palabra) => Array.from(palabra.matchAll(/\p{L}/gu)).length > 10);
        const mensaje = `Usa hasta ${maxEspacios + 1} palabras, con un máximo de 10 letras por palabra y mayúscula inicial.`;
        const espacioFinal = campo.value.endsWith(" ");

        campo.setCustomValidity(
            espacioFinal ? "Quita el espacio final antes de guardar."
                : invalido ? mensaje : ""
        );
        avisoEspacio.textContent = "Quita el espacio final antes de guardar.";
        avisoEspacio.hidden = !mostrarAvisoEspacio || !espacioFinal;
    }

    campo.addEventListener("input", () => {
        let texto = "";
        let letrasEnPalabra = 0;
        let espacios = 0;
        let ultimoFueEspacio = false;

        for (const caracter of campo.value) {
            if (/\p{L}/u.test(caracter)) {
                if (letrasEnPalabra >= 10) {
                    continue;
                }

                const convertido = letrasEnPalabra === 0
                    ? caracter.toLocaleUpperCase("es-UY")
                    : caracter.toLocaleLowerCase("es-UY");
                if (!/^\p{L}$/u.test(convertido)) {
                    continue;
                }
                texto += convertido;
                letrasEnPalabra += 1;
                ultimoFueEspacio = false;
            } else if (
                /\p{M}/u.test(caracter)
                && letrasEnPalabra > 0
                && !ultimoFueEspacio
            ) {
                texto += caracter;
            } else if (
                caracter === " "
                && letrasEnPalabra > 0
                && espacios < maxEspacios
                && !ultimoFueEspacio
            ) {
                texto += caracter;
                espacios += 1;
                letrasEnPalabra = 0;
                ultimoFueEspacio = true;
            }
        }

        campo.value = texto;
        validar();
    });

    campo.addEventListener("blur", validar);
    return validar;
}

const validarNombreClub = configurarNombreCompuesto(
    document.getElementById("nombre"),
    1,
    document.getElementById("nombre-espacio-error")
);
const validarDirectorTecnico = configurarNombreCompuesto(
    document.getElementById("director_tecnico"),
    3,
    document.getElementById("director-espacio-error")
);

const selectorDepartamento = document.getElementById("selector-departamento");
const departamentoSeleccionado = document.getElementById("departamento");
const formularioClub = document.getElementById("form-club");

selectorDepartamento.querySelectorAll("[data-departamento]").forEach((opcion) => {
    opcion.addEventListener("click", () => {
        departamentoSeleccionado.value = opcion.dataset.departamento;
        selectorDepartamento.querySelector("summary").textContent = opcion.textContent;
        selectorDepartamento.open = false;
        selectorDepartamento.querySelector("summary").focus();
    });
});

document.addEventListener("click", (evento) => {
    if (!selectorDepartamento.contains(evento.target)) {
        selectorDepartamento.open = false;
    }
});

formularioClub.addEventListener("submit", (evento) => {
    validarNombreClub(true);
    validarDirectorTecnico(true);

    const espacioFinal = document.getElementById("nombre").value.endsWith(" ")
        || document.getElementById("director_tecnico").value.endsWith(" ");
    if (espacioFinal) {
        evento.preventDefault();
        return;
    }

    if (departamentoSeleccionado.value === "") {
        evento.preventDefault();
        selectorDepartamento.open = true;
        selectorDepartamento.querySelector("summary").focus();
    }

    if (!formularioClub.reportValidity()) {
        evento.preventDefault();
    }
});
</script>

<style>
.selector-departamento {
    position: relative;
    margin-bottom: 16px;
}
.selector-departamento summary {
    min-height: 44px;
    padding: 10px 12px;
    border: 1px solid #c6d8c9;
    border-radius: 8px;
    color: var(--tinta);
    background: #fbfdfb;
    cursor: pointer;
    list-style: none;
}
.selector-departamento summary::-webkit-details-marker { display: none; }
.selector-departamento summary::after { content: " ▾"; float: right; }
.selector-departamento[open] summary::after { content: " ▴"; }
.opciones-departamento {
    position: absolute;
    z-index: 5;
    top: calc(100% + 4px);
    right: 0;
    left: 0;
    display: grid;
    max-height: 260px;
    overflow-y: auto;
    padding: 6px;
    border: 1px solid #c6d8c9;
    border-radius: 8px;
    background: var(--superficie);
    box-shadow: var(--sombra);
}
.opciones-departamento button {
    justify-content: flex-start;
    min-height: 38px;
    border-radius: 5px;
    color: var(--tinta);
    background: var(--superficie);
    font-weight: 400;
}
.opciones-departamento button:hover,
.opciones-departamento button:focus-visible {
    transform: none;
    color: var(--tinta);
    background: var(--fondo);
    box-shadow: none;
}
</style>

</body>
</html>
