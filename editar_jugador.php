<?php
// Valida permisos e identifica el jugador y clubes disponibles para editar.
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

if (($_SESSION['rol'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Acceso denegado.');
}

$jugadorId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
if ($jugadorId === false || $jugadorId === null) {
    http_response_code(400);
    exit('Jugador no válido.');
}

require_once('conexion.php');

$consultaJugador = mysqli_prepare($conn, 'SELECT * FROM jugadores WHERE id = ? LIMIT 1');
if (!$consultaJugador) {
    http_response_code(500);
    exit('No se pudo cargar el jugador.');
}
mysqli_stmt_bind_param($consultaJugador, 'i', $jugadorId);
if (!mysqli_stmt_execute($consultaJugador)) {
    mysqli_stmt_close($consultaJugador);
    http_response_code(500);
    exit('No se pudo cargar el jugador.');
}
$jugador = mysqli_fetch_assoc(mysqli_stmt_get_result($consultaJugador));
mysqli_stmt_close($consultaJugador);
if (!$jugador) {
    http_response_code(404);
    exit('Jugador no encontrado.');
}

$resultadoClubes = mysqli_query($conn, 'SELECT id, nombre FROM clubes WHERE activo = 1 ORDER BY nombre');
if (!$resultadoClubes) {
    http_response_code(500);
    exit('No se pudieron cargar los clubes.');
}
$clubes = mysqli_fetch_all($resultadoClubes, MYSQLI_ASSOC);
$escape = static fn($valor) => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar jugador</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
<?php include('menu.php'); ?>

<main class="pagina-contenido">
    <!-- Muestra los datos actuales junto con las opciones válidas de edición. -->
    <h2>Editar jugador</h2>
    <p class="ayuda">Actualiza la información del jugador y guarda los cambios.</p>

    <form id="form-editar-jugador" action="guardar_edicion_jugador.php" method="POST">
        <input type="hidden" name="id" value="<?php echo (int) $jugador['id']; ?>">

        <label for="nombre">Nombre</label>
        <input id="nombre" type="text" name="nombre" maxlength="100" value="<?php echo $escape($jugador['nombre']); ?>" required>

        <label for="edad">Edad</label>
        <input id="edad" type="number" name="edad" min="0" max="100" value="<?php echo (int) $jugador['edad']; ?>" required>

        <label for="posicion">Posición</label>
        <select id="posicion" name="posicion" required>
            <?php foreach (['Arquero', 'Defensa', 'Mediocampista', 'Delantero'] as $posicion) { ?>
                <option value="<?php echo $escape($posicion); ?>" <?php echo $jugador['posicion'] === $posicion ? 'selected' : ''; ?>>
                    <?php echo $escape($posicion); ?>
                </option>
            <?php } ?>
        </select>

        <label for="club_id">Club</label>
        <select id="club_id" name="club_id" required>
            <?php foreach ($clubes as $club) { ?>
                <option value="<?php echo (int) $club['id']; ?>" <?php echo (int) $jugador['club_id'] === (int) $club['id'] ? 'selected' : ''; ?>>
                    <?php echo $escape($club['nombre']); ?>
                </option>
            <?php } ?>
        </select>

        <label for="ci">Cédula</label>
        <input id="ci" type="text" name="ci" inputmode="numeric" autocomplete="off" pattern="[0-9]{8}" maxlength="8" value="<?php echo $escape($jugador['ci']); ?>" aria-describedby="ci_error" required>
        <span id="ci_error" class="ayuda-fecha" role="alert" hidden></span>

        <label for="categoria">Categoría</label>
        <select id="categoria" name="categoria" required>
            <?php for ($anio = 2012; $anio <= 2019; $anio++) { ?>
                <option value="<?php echo $anio; ?>" <?php echo (string) $jugador['categoria'] === (string) $anio ? 'selected' : ''; ?>>
                    <?php echo $anio; ?>
                </option>
            <?php } ?>
        </select>

        <label for="altura">Altura (metros)</label>
        <input id="altura" type="number" name="altura" min="0" max="2.1" step="0.01" value="<?php echo $escape($jugador['altura']); ?>">

        <label for="masa">Peso (kg)</label>
        <input id="masa" type="number" name="masa" min="0" max="70.9" step="0.1" value="<?php echo $escape($jugador['masa']); ?>">

        <label for="velocidad">Velocidad promedio (m/s)</label>
        <input id="velocidad" type="number" name="velocidad" min="0" max="9.9" step="0.1" value="<?php echo $escape($jugador['velocidad']); ?>">

        <div class="acciones-formulario">
            <button type="submit">Guardar cambios</button>
            <a href="jugadores.php">Cancelar</a>
        </div>
    </form>
</main>
<script>
// Conserva la cédula original y prepara la validación de cambios.
const formularioEdicionJugador = document.getElementById("form-editar-jugador");
const cedula = document.getElementById("ci");
const errorCedula = document.getElementById("ci_error");
const cedulaOriginal = cedula.value;

function mostrarErrorCedula(mensaje) {
    cedula.setCustomValidity(mensaje);
    errorCedula.textContent = mensaje;
    errorCedula.hidden = mensaje === "";
}

async function validarCedula() {
    // Permite mantener la cédula actual y verifica las nuevas contra el servidor.
    if (!/^[0-9]{8}$/.test(cedula.value)) {
        mostrarErrorCedula("La cédula debe contener exactamente 8 dígitos.");
        return false;
    }

    if (/^(?:([0-9])\1{7}|01234567|12345678|23456789|98765432|87654321|76543210)$/.test(cedula.value)) {
        mostrarErrorCedula("La cédula no puede repetir el mismo dígito ni ser una secuencia consecutiva.");
        return false;
    }

    if (cedula.value === cedulaOriginal) {
        mostrarErrorCedula("");
        return true;
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
cedula.addEventListener("blur", validarCedula);

formularioEdicionJugador.addEventListener("submit", async (evento) => {
    if (formularioEdicionJugador.dataset.ciVerificada === cedula.value) {
        delete formularioEdicionJugador.dataset.ciVerificada;
        return;
    }

    evento.preventDefault();
    if (await validarCedula()) {
        formularioEdicionJugador.dataset.ciVerificada = cedula.value;
        formularioEdicionJugador.requestSubmit();
    } else {
        cedula.reportValidity();
    }
});
</script>
</body>
</html>
