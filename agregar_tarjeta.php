<?php
// Carga los encuentros y jugadores elegibles para registrar sanciones.
session_start();
include('restringir_club.php');
require_once('conexion.php');

if (($_SESSION['rol'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Acceso denegado.');
}

$torneos = [
    'estandar' => 'Estándar',
    'plata' => 'Copa Plata',
    'oro' => 'Copa Oro'
];
$categoriasEdad = range(2012, 2019);
$claveSolicitada = $_GET['torneo'] ?? 'estandar';
$claveTorneo = is_string($claveSolicitada) && isset($torneos[$claveSolicitada])
    ? $claveSolicitada
    : 'estandar';
$torneo = $torneos[$claveTorneo];

$consultaPartidos = mysqli_prepare(
    $conn,
    "SELECT p.id, p.fecha, p.local_id, p.visitante_id, p.categoria_edad, p.estado,
            c1.nombre AS nombre_local, c2.nombre AS nombre_visitante
     FROM partidos p
     INNER JOIN clubes c1 ON c1.id = p.local_id
     INNER JOIN clubes c2 ON c2.id = p.visitante_id
     WHERE COALESCE(NULLIF(p.categoria, ''), 'Estándar') = ?
       AND p.categoria_edad IS NOT NULL
       AND p.categoria_edad <> ''
     ORDER BY p.fecha DESC, p.id DESC"
);
if (!$consultaPartidos) {
    http_response_code(500);
    exit('No se pudieron cargar los partidos.');
}
mysqli_stmt_bind_param($consultaPartidos, 's', $torneo);
if (!mysqli_stmt_execute($consultaPartidos)) {
    mysqli_stmt_close($consultaPartidos);
    http_response_code(500);
    exit('No se pudieron cargar los partidos.');
}
$partidos = mysqli_fetch_all(mysqli_stmt_get_result($consultaPartidos), MYSQLI_ASSOC);
mysqli_stmt_close($consultaPartidos);

$resultadoJugadores = mysqli_query(
    $conn,
    'SELECT j.id, j.nombre, j.club_id, j.categoria, c.nombre AS nombre_club
     FROM jugadores j
     INNER JOIN clubes c ON c.id = j.club_id
     WHERE c.activo = 1
     ORDER BY c.nombre, j.nombre'
);
if (!$resultadoJugadores) {
    http_response_code(500);
    exit('No se pudieron cargar los jugadores.');
}
$jugadores = mysqli_fetch_all($resultadoJugadores, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrar sanción</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
<?php include('menu.php'); ?>

<main class="pagina-contenido">
    <!-- El formulario comienza bloqueado hasta elegir partido y categoría. -->
    <h2>Registrar sanción</h2>
    <p class="ayuda">Registra una tarjeta asociada a un jugador y al partido correspondiente.</p>

    <?php if (!$partidos || !$jugadores) { ?>
        <p class="aviso">Para registrar una sanción debe haber partidos y jugadores de clubes activos.</p>
    <?php } ?>

    <form action="guardar_tarjeta.php" method="POST">
        <input type="hidden" name="torneo" value="<?php echo htmlspecialchars($claveTorneo, ENT_QUOTES, 'UTF-8'); ?>">

        <label for="categoria">Categoría</label>
        <select id="categoria" name="categoria_edad" required>
            <option value="">Selecciona una categoría</option>
            <?php foreach ($categoriasEdad as $categoriaEdad) { ?>
                <option value="<?php echo $categoriaEdad; ?>"><?php echo $categoriaEdad; ?></option>
            <?php } ?>
        </select>

        <label for="partido">Partido</label>
        <select id="partido" name="partido_id" required disabled>
            <?php if (!$partidos) { ?>
                <option value="">No hay partidos en esta competencia</option>
            <?php } else { ?>
                <option value="">Selecciona un partido</option>
                <?php foreach ($partidos as $partido) { ?>
                    <option value="<?php echo (int) $partido['id']; ?>"
                            data-categoria="<?php echo htmlspecialchars((string) $partido['categoria_edad'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-local="<?php echo (int) $partido['local_id']; ?>"
                            data-visitante="<?php echo (int) $partido['visitante_id']; ?>"
                            data-estado="<?php echo htmlspecialchars((string) $partido['estado'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php
                        echo htmlspecialchars(
                            ($partido['fecha'] ? date('d/m/Y', strtotime($partido['fecha'])) . ' · ' : '') .
                            $partido['nombre_local'] . ' vs ' . $partido['nombre_visitante'] .
                            ' · Categoría ' . $partido['categoria_edad'],
                            ENT_QUOTES,
                            'UTF-8'
                        );
                        ?>
                    </option>
                <?php } ?>
            <?php } ?>
        </select>

        <label for="jugador">Jugador</label>
        <select id="jugador" name="jugador_id" required disabled>
            <?php if (!$jugadores) { ?>
                <option value="">No hay jugadores disponibles</option>
            <?php } else { ?>
                <option value="">Selecciona primero un partido</option>
                <?php foreach ($jugadores as $jugador) { ?>
                    <option value="<?php echo (int) $jugador['id']; ?>"
                            data-club="<?php echo (int) $jugador['club_id']; ?>"
                            data-categoria="<?php echo htmlspecialchars((string) $jugador['categoria'], ENT_QUOTES, 'UTF-8'); ?>"
                            hidden disabled>
                        <?php echo htmlspecialchars($jugador['nombre'] . ' · ' . $jugador['nombre_club'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php } ?>
            <?php } ?>
        </select>

        <label for="tipo">Tipo de tarjeta</label>
        <select id="tipo" name="tipo" required disabled>
            <option value="Amarilla">Amarilla</option>
            <option value="Roja">Roja</option>
        </select>

        <label for="motivo">Motivo (opcional)</label>
        <textarea id="motivo" name="motivo" maxlength="255" rows="3" placeholder="Describe brevemente el motivo" disabled></textarea>

        <div class="acciones-formulario">
            <button id="guardar-sancion" type="submit" disabled>Guardar sanción</button>
            <a href="tabla_posiciones.php?torneo=<?php echo htmlspecialchars($claveTorneo, ENT_QUOTES, 'UTF-8'); ?>#sanciones">Volver a la competencia</a>
        </div>
    </form>
</main>

<script>
// Filtra partidos y jugadores según categoría, clubes y estado del encuentro.
const categoriaSelect = document.getElementById("categoria");
const partidoSelect = document.getElementById("partido");
const jugadorSelect = document.getElementById("jugador");
const tipoSelect = document.getElementById("tipo");
const motivoInput = document.getElementById("motivo");
const guardarSancion = document.getElementById("guardar-sancion");
const formularioSancion = jugadorSelect.form;
const opcionJugadorVacia = jugadorSelect.options[0];

function filtrarJugadores() {
    const partido = partidoSelect.selectedOptions[0];
    const hayPartido = partido && partido.value !== "";
    const partidoJugado = hayPartido && partido.dataset.estado === "Jugado";
    let jugadoresDisponibles = 0;

    Array.from(jugadorSelect.options).slice(1).forEach((opcion) => {
        const perteneceAlPartido = partidoJugado
            && (opcion.dataset.club === partido.dataset.local || opcion.dataset.club === partido.dataset.visitante)
            && opcion.dataset.categoria === categoriaSelect.value;
        opcion.hidden = !perteneceAlPartido;
        opcion.disabled = !perteneceAlPartido;
        if (perteneceAlPartido) {
            jugadoresDisponibles++;
        }
    });

    opcionJugadorVacia.textContent = !hayPartido
        ? "Selecciona primero un partido"
        : !partidoJugado
            ? "El partido debe estar jugado"
        : jugadoresDisponibles
            ? "Selecciona un jugador"
            : "No hay jugadores de esta categoría en el partido";
    jugadorSelect.value = "";
    jugadorSelect.disabled = !partidoJugado || jugadoresDisponibles === 0;
    tipoSelect.disabled = !partidoJugado;
    motivoInput.disabled = !partidoJugado;
    guardarSancion.disabled = !partidoJugado || jugadoresDisponibles === 0 || jugadorSelect.value === "";
}

function filtrarPartidos() {
    let partidosDisponibles = 0;
    Array.from(partidoSelect.options).slice(1).forEach((opcion) => {
        const perteneceALaCategoria = opcion.dataset.categoria === categoriaSelect.value;
        opcion.hidden = !perteneceALaCategoria;
        opcion.disabled = !perteneceALaCategoria;
        if (perteneceALaCategoria) {
            partidosDisponibles++;
        }
    });
    partidoSelect.value = "";
    partidoSelect.disabled = categoriaSelect.value === "" || partidosDisponibles === 0;
    filtrarJugadores();
}

categoriaSelect.addEventListener("change", filtrarPartidos);
partidoSelect.addEventListener("change", () => {
    const partido = partidoSelect.selectedOptions[0];
    if (partido && partido.value !== "" && partido.dataset.estado !== "Jugado") {
        window.alert("El partido no ha sido jugado. Solo puedes registrar sanciones en partidos jugados.");
        partidoSelect.value = "";
    }
    filtrarJugadores();
});
jugadorSelect.addEventListener("change", () => {
    guardarSancion.disabled = jugadorSelect.value === "";
});
formularioSancion.addEventListener("submit", (evento) => {
    const partido = partidoSelect.selectedOptions[0];
    if (!partido || partido.dataset.estado !== "Jugado" || jugadorSelect.value === "") {
        evento.preventDefault();
        window.alert("Selecciona un partido jugado y un jugador para registrar la sanción.");
    }
});
</script>

</body>
</html>
