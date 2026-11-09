<?php
// Carga los jugadores del club autenticado para actualizar sus fichas.
session_start();
include("restringir_club.php");

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

require_once("conexion.php");

$rol = $_SESSION["rol"] ?? "";
if ($rol !== "club") {
    http_response_code(403);
    die("Solo los clubes pueden subir fichas médicas.");
}
$mensaje = $_GET["error"] ?? "";
$exito = $_GET["ok"] ?? "";
$clubId = (int) ($_SESSION["club_id"] ?? 0);

$jugadoresStmt = mysqli_prepare($conn, "SELECT id, nombre, ci, categoria FROM jugadores WHERE club_id = ? ORDER BY nombre");
mysqli_stmt_bind_param($jugadoresStmt, "i", $clubId);
mysqli_stmt_execute($jugadoresStmt);
$jugadores = mysqli_stmt_get_result($jugadoresStmt);
$categoriasPermitidas = array_merge(array_map("strval", range(2013, 2019)), ["Femenina"]);
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subir ficha médica</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Actualizar documentación de un jugador</h2>

<!-- Informa el resultado de la carga y ofrece el formulario de imagen. -->
<p class="ayuda">Elige un jugador de tu club y carga su ficha médica. La fecha de expedición se asigna al guardar y la ficha vence automáticamente a los dos años. Si el jugador aún no aparece en la lista, solicita al administrador que lo registre.</p>

<?php if ($mensaje !== "") { ?>
    <p class="mensaje-error"><?php echo htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8"); ?></p>
<?php } ?>

<?php if ($exito !== "") { ?>
    <p class="mensaje-exito"><?php echo htmlspecialchars($exito, ENT_QUOTES, "UTF-8"); ?></p>
<?php } ?>

<?php if ($jugadores && mysqli_num_rows($jugadores) > 0) { ?>
    <form action="guardar_documentos.php" method="POST" enctype="multipart/form-data">
        <label for="categoria">Categoría</label>
        <select id="categoria" name="categoria" required>
            <option value="">Selecciona una categoría</option>
            <?php foreach ($categoriasPermitidas as $categoria) { ?>
                <option value="<?php echo htmlspecialchars((string) $categoria, ENT_QUOTES, "UTF-8"); ?>">
                    <?php echo htmlspecialchars((string) $categoria, ENT_QUOTES, "UTF-8"); ?>
                </option>
            <?php } ?>
        </select>

        <label for="jugador_id">Jugador</label>
        <select id="jugador_id" name="jugador_id" required disabled>
            <option value="">Selecciona un jugador</option>
            <?php while ($jugador = mysqli_fetch_assoc($jugadores)) { ?>
                <option value="<?php echo (int) $jugador["id"]; ?>" data-categoria="<?php echo htmlspecialchars((string) $jugador["categoria"], ENT_QUOTES, "UTF-8"); ?>" hidden disabled>
                    <?php echo htmlspecialchars($jugador["nombre"] . " — CI " . $jugador["ci"], ENT_QUOTES, "UTF-8"); ?>
                </option>
            <?php } ?>
        </select>
        <p id="aviso-jugadores-categoria" class="ayuda" aria-live="polite"></p>

        <label for="ficha_medica">Imagen de la ficha médica</label>
        <input id="ficha_medica" type="file" name="ficha_medica" accept="image/jpeg,image/png,image/webp" required>
        <p class="ayuda">Formatos permitidos: JPG, PNG o WEBP. Tamaño máximo: 10 MB.</p>
        <button type="submit">Guardar ficha médica</button>
    </form>
<?php } else { ?>
    <p class="aviso">Tu club todavía no tiene jugadores registrados.</p>
<?php } ?>

<script>
    const categoriaSelect = document.getElementById("categoria");
    const jugadorSelect = document.getElementById("jugador_id");
    const avisoJugadores = document.getElementById("aviso-jugadores-categoria");

    if (categoriaSelect && jugadorSelect) {
        const opcionesJugador = Array.from(jugadorSelect.querySelectorAll("option[data-categoria]"));
        categoriaSelect.addEventListener("change", function () {
            const categoriaSeleccionada = categoriaSelect.value;
            let jugadoresDisponibles = 0;
            jugadorSelect.value = "";

            opcionesJugador.forEach(function (opcion) {
                const coincide = opcion.dataset.categoria === categoriaSeleccionada;
                opcion.hidden = !coincide;
                opcion.disabled = !coincide;
                if (coincide) {
                    jugadoresDisponibles += 1;
                }
            });

            jugadorSelect.disabled = categoriaSeleccionada === "";
            avisoJugadores.textContent = categoriaSeleccionada !== "" && jugadoresDisponibles === 0
                ? "No hay jugadores registrados en esta categoría."
                : "";
        });
    }
</script>

</body>
</html>
