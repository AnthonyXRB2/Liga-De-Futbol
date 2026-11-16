<?php
require_once __DIR__ . '/../../Includes/rutas.php';
// Limita la administración de cuentas al usuario administrador.
session_start();

if (!isset($_SESSION["usuario"])) {
    header('Location: ' . app_url('Paginas/Ingreso-y-Usuarios/login.php'));
    exit();
}

if ($_SESSION["rol"] != "admin") {
    echo "<h2>Acceso denegado</h2>";
    exit();
}

require_once __DIR__ . '/../../Includes/conexion.php';

$mensajesError = [
    "nombre" => "Escribe un nombre válido: hasta 4 palabras, con 4 a 10 letras por palabra.",
    "duplicado" => "Ese nombre de usuario ya está en uso.",
    "contrasena" => "La contraseña del administrador no puede estar vacía.",
];
$mensajeError = $mensajesError[$_GET["error"] ?? ""] ?? "";
$escape = static fn($valor) => htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Usuarios</title>
    <link rel="stylesheet" href="<?php echo app_url('Assets-Css/estilo.css'); ?>">
</head>
<body>

<?php require_once __DIR__ . '/../../Includes/menu.php'; ?>

<h2>Usuarios Registrados</h2>

<div class="contenedor">

<?php if (($_GET["ok"] ?? "") === "1") { ?>
<p class="mensaje-exito">Usuario actualizado correctamente.</p>
<?php } ?>

<a href="<?php echo app_url('Paginas/Ingreso-y-Usuarios/registrar_usuario.php'); ?>">
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

// Cada cuenta se gestiona desde el formulario de edición.
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

<button
    type="button"
    data-editar-usuario
    data-id="<?php echo (int) $fila["id"]; ?>"
    data-usuario="<?php echo $escape($fila["usuario"]); ?>"
    data-rol="<?php echo $escape($fila["rol"]); ?>">
    Editar
</button>

</td>

</tr>

<?php } ?>

</table>

</div>

<dialog id="dialogo-editar-usuario" class="dialogo-editar-usuario" aria-labelledby="titulo-editar-usuario">
    <h3 id="titulo-editar-usuario">Editar Usuario</h3>
    <form id="form-editar-usuario" action="<?php echo app_url('Procesos/Usuarios/guardar_edicion_usuario.php'); ?>" method="POST">
        <input type="hidden" id="editar-usuario-id" name="id">
        <div id="campos-nombre-usuario">
            <label for="editar-usuario-nombre">Nombre</label>
            <input
                type="text"
                id="editar-usuario-nombre"
                name="usuario"
                maxlength="43"
                aria-describedby="ayuda-editar-usuario error-editar-usuario"
                required>
            <small id="ayuda-editar-usuario">Hasta 4 palabras, con 4 a 10 letras por palabra.</small>
        </div>
        <label for="editar-usuario-password">Contraseña</label>
        <input
            type="password"
            id="editar-usuario-password"
            name="password"
            autocomplete="new-password"
            placeholder="Dejar en blanco para mantener la actual">
        <p id="error-editar-usuario" class="mensaje-error" role="alert" <?php echo $mensajeError === "" ? "hidden" : ""; ?>><?php echo $escape($mensajeError); ?></p>
        <div class="acciones-editar-usuario">
            <button type="submit">Guardar</button>
            <button type="button" class="boton-cancelar-edicion" data-cerrar-edicion>Cancelar</button>
            <button
                type="submit"
                formaction="<?php echo app_url('Procesos/Usuarios/eliminar_usuario.php'); ?>"
                formmethod="POST"
                formnovalidate
                data-eliminar-usuario>
                Eliminar
            </button>
        </div>
    </form>
</dialog>

<script>
const dialogoEditarUsuario = document.getElementById("dialogo-editar-usuario");
const formularioEditarUsuario = document.getElementById("form-editar-usuario");
const campoNombreUsuario = document.getElementById("editar-usuario-nombre");
const campoIdUsuario = document.getElementById("editar-usuario-id");
const campoPasswordUsuario = document.getElementById("editar-usuario-password");
const errorEditarUsuario = document.getElementById("error-editar-usuario");
const botonEliminarUsuario = document.querySelector("[data-eliminar-usuario]");
const camposNombreUsuario = document.getElementById("campos-nombre-usuario");

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
        const esAdministrador = (boton.dataset.rol || "").trim().toLowerCase() === "admin";
        campoIdUsuario.value = boton.dataset.id;
        campoNombreUsuario.value = boton.dataset.usuario;
        campoNombreUsuario.disabled = esAdministrador;
        camposNombreUsuario.hidden = esAdministrador;
        camposNombreUsuario.style.display = esAdministrador ? "none" : "";
        campoPasswordUsuario.value = "";
        campoPasswordUsuario.required = esAdministrador;
        campoPasswordUsuario.placeholder = esAdministrador
            ? ""
            : "Dejar en blanco para mantener la actual";
        botonEliminarUsuario.hidden = esAdministrador;
        botonEliminarUsuario.style.display = esAdministrador ? "none" : "";
        botonEliminarUsuario.disabled = esAdministrador
            || boton.dataset.id === "<?php echo (int) $_SESSION["id"]; ?>";
        botonEliminarUsuario.title = botonEliminarUsuario.disabled
            ? "No podés eliminar tu propio usuario."
            : "";
        campoNombreUsuario.setCustomValidity("");
        errorEditarUsuario.hidden = true;
        errorEditarUsuario.textContent = "";
        dialogoEditarUsuario.showModal();
        const campoInicial = esAdministrador ? campoPasswordUsuario : campoNombreUsuario;
        campoInicial.focus();
        if (!esAdministrador) {
            campoNombreUsuario.select();
        }
    });
});

document.querySelector("[data-cerrar-edicion]").addEventListener("click", () => {
    dialogoEditarUsuario.close();
});

botonEliminarUsuario.addEventListener("click", (evento) => {
    if (!confirm("¿Eliminar este usuario?")) {
        evento.preventDefault();
    }
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
    if (!campoNombreUsuario.disabled) {
        validarNombreUsuario();
    }
});

campoNombreUsuario.addEventListener("blur", validarNombreUsuario);
formularioEditarUsuario.addEventListener("submit", (evento) => {
    if (evento.submitter === botonEliminarUsuario) {
        return;
    }

    if (!campoNombreUsuario.disabled) {
        validarNombreUsuario();
    }
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
