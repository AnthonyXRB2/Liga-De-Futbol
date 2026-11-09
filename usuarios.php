<?php
// Limita la administración de cuentas al usuario administrador.
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

$mensajesError = [
    "nombre" => "Escribe un nombre válido: hasta 4 palabras, con 4 a 10 letras por palabra.",
    "duplicado" => "Ese nombre de usuario ya está en uso.",
];
$mensajeError = $mensajesError[$_GET["error"] ?? ""] ?? "";
$escape = static fn($valor) => htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Usuarios</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Usuarios Registrados</h2>

<div class="contenedor">

<?php if (($_GET["ok"] ?? "") === "1") { ?>
<p class="mensaje-exito">Usuario actualizado correctamente.</p>
<?php } ?>

<a href="registrar_usuario.php">
    <button>➕ Registrar Usuario</button>
</a>

<br><br>

<table>

<tr>
    <th>Usuario</th>
    <th>Rol</th>
    <th>Club</th>
    <th>Acciones</th>
</tr>

<?php

$sql="

SELECT
usuarios.*,
clubes.nombre AS club

FROM usuarios

LEFT JOIN clubes
ON usuarios.club_id=clubes.id

ORDER BY usuarios.usuario

";

// Relaciona cada cuenta con el nombre de su club para la tabla.
$consulta=mysqli_query($conn,$sql);

// Muestra acciones de contraseña y eliminación para cada usuario.
while($fila=mysqli_fetch_assoc($consulta)){
?>

<tr>

<td><?php echo htmlspecialchars($fila["usuario"], ENT_QUOTES, "UTF-8"); ?></td>

<td><?php echo ucfirst($fila["rol"]); ?></td>

<td>

<?php

if($fila["club"]!=""){
    echo $fila["club"];
}else{
    echo "-";
}

?>

</td>

<td>

<?php if ($fila["rol"] !== "admin") { ?>
<button
    type="button"
    data-editar-usuario
    data-id="<?php echo (int) $fila["id"]; ?>"
    data-usuario="<?php echo $escape($fila["usuario"]); ?>">
    📝 Editar
</button>
<?php } ?>

<a href="cambiar_password_usuario.php?id=<?php echo $fila["id"]; ?>">

<button type="button">
🔒 Contraseña
</button>

</a>

<?php if($fila["id"] != $_SESSION["id"]){ ?>

<a href="eliminar_usuario.php?id=<?php echo $fila["id"]; ?>"
onclick="return confirm('¿Eliminar este usuario?');">

<button type="button">
🗑 Eliminar
</button>

</a>

<?php } ?>

</td>

</tr>

<?php } ?>

</table>

</div>

<dialog id="dialogo-editar-usuario" class="dialogo-editar-usuario" aria-labelledby="titulo-editar-usuario">
    <h3 id="titulo-editar-usuario">Editar usuario</h3>
    <form id="form-editar-usuario" action="guardar_edicion_usuario.php" method="POST">
        <input type="hidden" id="editar-usuario-id" name="id">
        <label for="editar-usuario-nombre">Nuevo nombre de usuario</label>
        <input
            type="text"
            id="editar-usuario-nombre"
            name="usuario"
            maxlength="43"
            aria-describedby="ayuda-editar-usuario error-editar-usuario"
            required>
        <small id="ayuda-editar-usuario">Hasta 4 palabras, con 4 a 10 letras por palabra.</small>
        <p id="error-editar-usuario" class="mensaje-error" role="alert" <?php echo $mensajeError === "" ? "hidden" : ""; ?>><?php echo $escape($mensajeError); ?></p>
        <div class="acciones-editar-usuario">
            <button type="submit">Guardar</button>
            <button type="button" class="boton-cancelar-edicion" data-cerrar-edicion>Cancelar</button>
        </div>
    </form>
</dialog>

<script>
const dialogoEditarUsuario = document.getElementById("dialogo-editar-usuario");
const formularioEditarUsuario = document.getElementById("form-editar-usuario");
const campoNombreUsuario = document.getElementById("editar-usuario-nombre");
const campoIdUsuario = document.getElementById("editar-usuario-id");
const errorEditarUsuario = document.getElementById("error-editar-usuario");

function normalizarNombreUsuario(valor) {
    let texto = "";
    let letrasEnPalabra = 0;
    let espacios = 0;
    let ultimoFueEspacio = false;

    for (const caracter of valor) {
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
            && espacios < 3
            && !ultimoFueEspacio
        ) {
            texto += caracter;
            espacios += 1;
            letrasEnPalabra = 0;
            ultimoFueEspacio = true;
        }
    }

    return texto;
}

function validarNombreUsuario() {
    const palabras = campoNombreUsuario.value.split(" ");
    const patron = /^\p{Lu}[\p{Ll}\p{M}]*(?: \p{Lu}[\p{Ll}\p{M}]*){0,3}$/u;
    const invalido = !patron.test(campoNombreUsuario.value)
        || palabras.some((palabra) => {
            const letras = Array.from(palabra.matchAll(/\p{L}/gu)).length;
            return letras < 4 || letras > 10;
        });

    campoNombreUsuario.setCustomValidity(
        campoNombreUsuario.value.endsWith(" ")
            ? "Quita el espacio final antes de guardar."
            : invalido
                ? "Usa hasta 4 palabras, con un mínimo de 4 y un máximo de 10 letras por palabra."
                : ""
    );
}

document.querySelectorAll("[data-editar-usuario]").forEach((boton) => {
    boton.addEventListener("click", () => {
        campoIdUsuario.value = boton.dataset.id;
        campoNombreUsuario.value = boton.dataset.usuario;
        campoNombreUsuario.setCustomValidity("");
        errorEditarUsuario.hidden = true;
        errorEditarUsuario.textContent = "";
        dialogoEditarUsuario.showModal();
        campoNombreUsuario.focus();
        campoNombreUsuario.select();
    });
});

document.querySelector("[data-cerrar-edicion]").addEventListener("click", () => {
    dialogoEditarUsuario.close();
});

campoNombreUsuario.addEventListener("input", () => {
    const posicion = campoNombreUsuario.selectionStart;
    const normalizado = normalizarNombreUsuario(campoNombreUsuario.value);
    if (normalizado !== campoNombreUsuario.value) {
        campoNombreUsuario.value = normalizado;
        campoNombreUsuario.setSelectionRange(
            normalizarNombreUsuario(campoNombreUsuario.value.slice(0, posicion)).length,
            normalizarNombreUsuario(campoNombreUsuario.value.slice(0, posicion)).length
        );
    }
    validarNombreUsuario();
});

campoNombreUsuario.addEventListener("blur", validarNombreUsuario);
formularioEditarUsuario.addEventListener("submit", (evento) => {
    validarNombreUsuario();
    if (!formularioEditarUsuario.reportValidity()) {
        evento.preventDefault();
    }
});

const usuarioConError = new URLSearchParams(window.location.search).get("edit");
if (usuarioConError) {
    const botonConError = Array.from(document.querySelectorAll("[data-editar-usuario]"))
        .find((boton) => boton.dataset.id === usuarioConError);
    if (botonConError) {
        botonConError.click();
        errorEditarUsuario.textContent = <?php echo json_encode($mensajeError, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        errorEditarUsuario.hidden = false;
    }
}
</script>

</body>
</html>
