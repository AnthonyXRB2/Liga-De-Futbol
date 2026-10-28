<?php
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
$claveSolicitada = $_GET['torneo'] ?? 'estandar';
$claveTorneo = is_string($claveSolicitada) && isset($torneos[$claveSolicitada])
    ? $claveSolicitada
    : 'estandar';
$torneo = $torneos[$claveTorneo];

$consultaPartidos = mysqli_prepare(
    $conn,
    "SELECT p.id, p.fecha, c1.nombre AS nombre_local, c2.nombre AS nombre_visitante
     FROM partidos p
     INNER JOIN clubes c1 ON c1.id = p.local_id
     INNER JOIN clubes c2 ON c2.id = p.visitante_id
     WHERE COALESCE(NULLIF(p.categoria, ''), 'Estándar') = ?
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
    'SELECT j.id, j.nombre, c.nombre AS nombre_club
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
    <h2>Registrar sanción</h2>
    <p class="ayuda">Registra una tarjeta asociada a un jugador y al partido correspondiente.</p>

    <?php if (!$partidos || !$jugadores) { ?>
        <p class="aviso">Para registrar una sanción debe haber partidos y jugadores de clubes activos.</p>
    <?php } ?>

    <form action="guardar_tarjeta.php" method="POST">
        <input type="hidden" name="torneo" value="<?php echo htmlspecialchars($claveTorneo, ENT_QUOTES, 'UTF-8'); ?>">

        <label for="partido">Partido</label>
        <select id="partido" name="partido_id" required>
            <?php if (!$partidos) { ?>
                <option value="">No hay partidos en esta competencia</option>
            <?php } else { ?>
                <option value="">Selecciona un partido</option>
                <?php foreach ($partidos as $partido) { ?>
                    <option value="<?php echo (int) $partido['id']; ?>">
                        <?php
                        echo htmlspecialchars(
                            ($partido['fecha'] ? date('d/m/Y', strtotime($partido['fecha'])) . ' · ' : '') .
                            $partido['nombre_local'] . ' vs ' . $partido['nombre_visitante'],
                            ENT_QUOTES,
                            'UTF-8'
                        );
                        ?>
                    </option>
                <?php } ?>
            <?php } ?>
        </select>

        <label for="jugador">Jugador</label>
        <select id="jugador" name="jugador_id" required>
            <?php if (!$jugadores) { ?>
                <option value="">No hay jugadores disponibles</option>
            <?php } else { ?>
                <option value="">Selecciona un jugador</option>
                <?php foreach ($jugadores as $jugador) { ?>
                    <option value="<?php echo (int) $jugador['id']; ?>">
                        <?php echo htmlspecialchars($jugador['nombre'] . ' · ' . $jugador['nombre_club'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php } ?>
            <?php } ?>
        </select>

        <label for="tipo">Tipo de tarjeta</label>
        <select id="tipo" name="tipo" required>
            <option value="Amarilla">Amarilla</option>
            <option value="Roja">Roja</option>
        </select>

        <label for="motivo">Motivo (opcional)</label>
        <textarea id="motivo" name="motivo" maxlength="255" rows="3" placeholder="Describe brevemente el motivo"></textarea>

        <div class="acciones-formulario">
            <button type="submit" <?php echo (!$partidos || !$jugadores) ? 'disabled' : ''; ?>>Guardar sanción</button>
            <a href="tabla_posiciones.php?torneo=<?php echo htmlspecialchars($claveTorneo, ENT_QUOTES, 'UTF-8'); ?>#sanciones">Volver a la competencia</a>
        </div>
    </form>
</main>

</body>
</html>
