<?php
session_start();
include('restringir_club.php');
require_once('conexion.php');

$torneos = [
    'estandar' => 'Estándar',
    'plata' => 'Copa Plata',
    'oro' => 'Copa Oro'
];
$claveSolicitada = $_GET['torneo'] ?? 'estandar';
$claveTorneo = is_string($claveSolicitada) ? $claveSolicitada : 'estandar';
if (!isset($torneos[$claveTorneo])) {
    $claveTorneo = 'estandar';
}
$torneo = $torneos[$claveTorneo];

$sqlFixture = "SELECT p.id, p.fecha, p.hora, p.estado, p.motivo_suspension,
                      p.goles_local, p.goles_visitante, p.local_id, p.visitante_id,
                      c1.nombre AS nombre_local, c2.nombre AS nombre_visitante
               FROM partidos p
               INNER JOIN clubes c1 ON p.local_id = c1.id
               INNER JOIN clubes c2 ON p.visitante_id = c2.id
               WHERE COALESCE(NULLIF(p.categoria, ''), 'Estándar') = ?
               ORDER BY (p.fecha IS NULL), p.fecha ASC, p.hora ASC, p.id ASC";
$consultaFixture = mysqli_prepare($conn, $sqlFixture);
if (!$consultaFixture) {
    http_response_code(500);
    exit('No se pudo cargar el fixture.');
}
mysqli_stmt_bind_param($consultaFixture, 's', $torneo);
if (!mysqli_stmt_execute($consultaFixture)) {
    mysqli_stmt_close($consultaFixture);
    http_response_code(500);
    exit('No se pudo cargar el fixture.');
}
$resultadoFixture = mysqli_stmt_get_result($consultaFixture);
$fixture = mysqli_fetch_all($resultadoFixture, MYSQLI_ASSOC);
mysqli_stmt_close($consultaFixture);

$sqlSanciones = "SELECT t.tipo, t.motivo, j.nombre AS nombre_jugador,
                        c.nombre AS nombre_club, p.fecha AS fecha_partido,
                        c1.nombre AS nombre_local, c2.nombre AS nombre_visitante
                 FROM tarjetas t
                 INNER JOIN jugadores j ON j.id = t.jugador_id
                 INNER JOIN clubes c ON c.id = j.club_id
                 INNER JOIN partidos p ON p.id = t.partido_id
                 INNER JOIN clubes c1 ON c1.id = p.local_id
                 INNER JOIN clubes c2 ON c2.id = p.visitante_id
                 WHERE COALESCE(NULLIF(p.categoria, ''), 'Estándar') = ?
                 ORDER BY t.fecha_registro DESC, t.id DESC";
$consultaSanciones = mysqli_prepare($conn, $sqlSanciones);
if (!$consultaSanciones) {
    http_response_code(500);
    exit('No se pudieron cargar las sanciones.');
}
mysqli_stmt_bind_param($consultaSanciones, 's', $torneo);
if (!mysqli_stmt_execute($consultaSanciones)) {
    mysqli_stmt_close($consultaSanciones);
    http_response_code(500);
    exit('No se pudieron cargar las sanciones.');
}
$resultadoSanciones = mysqli_stmt_get_result($consultaSanciones);
$sanciones = mysqli_fetch_all($resultadoSanciones, MYSQLI_ASSOC);
mysqli_stmt_close($consultaSanciones);

$equipos = [];
if ($torneo === 'Estándar') {
    $resultadoClubes = mysqli_query($conn, 'SELECT id, nombre FROM clubes ORDER BY nombre');
    if (!$resultadoClubes) {
        http_response_code(500);
        exit('No se pudo cargar la clasificación.');
    }
    while ($club = mysqli_fetch_assoc($resultadoClubes)) {
        $equipos[(int) $club['id']] = [
            'club' => $club['nombre'], 'pj' => 0, 'pg' => 0, 'pe' => 0, 'pp' => 0,
            'gf' => 0, 'gc' => 0, 'pts' => 0
        ];
    }
}

foreach ($fixture as $partido) {
    foreach ([['local_id', 'nombre_local'], ['visitante_id', 'nombre_visitante']] as [$idCampo, $nombreCampo]) {
        $idClub = (int) $partido[$idCampo];
        if (!isset($equipos[$idClub])) {
            $equipos[$idClub] = [
                'club' => $partido[$nombreCampo], 'pj' => 0, 'pg' => 0, 'pe' => 0, 'pp' => 0,
                'gf' => 0, 'gc' => 0, 'pts' => 0
            ];
        }
    }

    if ($partido['estado'] !== 'Jugado') {
        continue;
    }

    $localId = (int) $partido['local_id'];
    $visitanteId = (int) $partido['visitante_id'];
    $golesLocal = (int) $partido['goles_local'];
    $golesVisitante = (int) $partido['goles_visitante'];
    $equipos[$localId]['pj']++;
    $equipos[$visitanteId]['pj']++;
    $equipos[$localId]['gf'] += $golesLocal;
    $equipos[$localId]['gc'] += $golesVisitante;
    $equipos[$visitanteId]['gf'] += $golesVisitante;
    $equipos[$visitanteId]['gc'] += $golesLocal;

    if ($golesLocal > $golesVisitante) {
        $equipos[$localId]['pg']++;
        $equipos[$localId]['pts'] += 3;
        $equipos[$visitanteId]['pp']++;
    } elseif ($golesLocal < $golesVisitante) {
        $equipos[$visitanteId]['pg']++;
        $equipos[$visitanteId]['pts'] += 3;
        $equipos[$localId]['pp']++;
    } else {
        $equipos[$localId]['pe']++;
        $equipos[$visitanteId]['pe']++;
        $equipos[$localId]['pts']++;
        $equipos[$visitanteId]['pts']++;
    }
}

$tabla = array_values($equipos);
usort($tabla, static function ($a, $b) {
    $diferenciaA = $a['gf'] - $a['gc'];
    $diferenciaB = $b['gf'] - $b['gc'];
    return ($b['pts'] <=> $a['pts'])
        ?: ($diferenciaB <=> $diferenciaA)
        ?: ($b['gf'] <=> $a['gf'])
        ?: strcasecmp($a['club'], $b['club']);
});

function escapar($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Competencia - <?php echo escapar($torneo); ?></title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
<?php include('menu.php'); ?>

<main class="pagina-contenido">
    <header class="portada-competencia">
        <div>
            <span class="etiqueta-seccion">TEMPORADA · <?php echo escapar($torneo); ?></span>
            <h2>Competencia</h2>
            <p class="ayuda">Tabla, partidos y sanciones de la liga en un solo lugar.</p>
        </div>
        <?php if (($_SESSION['rol'] ?? '') === 'admin') { ?>
            <div class="acciones-portada">
                <a class="boton" href="agregar_partido.php?origen=tabla&amp;torneo=<?php echo escapar($claveTorneo); ?>">＋ Agregar partido</a>
                <a class="boton boton-secundario" href="agregar_tarjeta.php?torneo=<?php echo escapar($claveTorneo); ?>">＋ Registrar sanción</a>
            </div>
        <?php } ?>
    </header>

    <nav class="selector-torneos" aria-label="Seleccionar competencia">
        <?php foreach ($torneos as $clave => $nombre) { ?>
            <a class="pestana-torneo <?php echo $clave === $claveTorneo ? 'activa' : ''; ?>"
               href="tabla_posiciones.php?torneo=<?php echo escapar($clave); ?>"
               <?php echo $clave === $claveTorneo ? 'aria-current="page"' : ''; ?>>
                <?php echo escapar($nombre); ?>
            </a>
        <?php } ?>
    </nav>

    <nav class="atajos-competencia" aria-label="Secciones de competencia">
        <a href="#clasificacion">Clasificación</a>
        <a href="#partidos">Partidos</a>
        <a href="#sanciones">Sanciones</a>
    </nav>

    <section class="panel-competencia" id="clasificacion" aria-labelledby="titulo-clasificacion">
        <h3 id="titulo-clasificacion">Clasificación - <?php echo escapar($torneo); ?></h3>
        <p class="regla-puntos">3 puntos por victoria, 1 por empate y 0 por derrota.</p>
        <?php if ($tabla) { ?>
            <div class="contenedor tabla-clasificacion">
                <table>
                    <thead>
                        <tr><th>Pos.</th><th>Club</th><th>PJ</th><th>PG</th><th>PE</th><th>PP</th><th>GF</th><th>GC</th><th>DG</th><th>Pts</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tabla as $indice => $equipo) { ?>
                            <tr>
                                <td><?php echo $indice + 1; ?></td>
                                <td class="nombre-equipo"><?php echo escapar($equipo['club']); ?></td>
                                <td><?php echo $equipo['pj']; ?></td>
                                <td><?php echo $equipo['pg']; ?></td>
                                <td><?php echo $equipo['pe']; ?></td>
                                <td><?php echo $equipo['pp']; ?></td>
                                <td><?php echo $equipo['gf']; ?></td>
                                <td><?php echo $equipo['gc']; ?></td>
                                <td><?php echo $equipo['gf'] - $equipo['gc']; ?></td>
                                <td class="puntos-equipo"><?php echo $equipo['pts']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <p class="aviso">Todavía no hay partidos registrados para esta competencia.</p>
        <?php } ?>
    </section>

    <section class="panel-competencia" id="partidos" aria-labelledby="titulo-partidos">
        <h3 id="titulo-partidos">Partidos y fixture <span><?php echo count($fixture); ?> en total</span></h3>
        <?php if ($fixture) { ?>
            <div class="contenedor tabla-fixture">
                <table>
                    <thead>
                        <tr><th>Fecha y hora</th><th>Local</th><th>Resultado</th><th>Visitante</th><th>Estado</th>
                            <?php if (($_SESSION['rol'] ?? '') === 'admin') { ?><th>Acciones</th><?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fixture as $partido) { ?>
                            <tr>
                                <td>
                                    <?php echo $partido['fecha'] ? date('d/m/Y', strtotime($partido['fecha'])) : 'Sin fecha'; ?>
                                    <?php if ($partido['hora']) { ?><br><small><?php echo date('h:i A', strtotime($partido['hora'])); ?></small><?php } ?>
                                </td>
                                <td><?php echo escapar($partido['nombre_local']); ?></td>
                                <td class="marcador-fixture">
                                    <?php echo $partido['estado'] === 'Jugado'
                                        ? (int) $partido['goles_local'] . ' - ' . (int) $partido['goles_visitante']
                                        : 'vs'; ?>
                                </td>
                                <td><?php echo escapar($partido['nombre_visitante']); ?></td>
                                <td>
                                    <span class="estado-partido estado-<?php echo escapar(strtolower(str_replace(' ', '-', $partido['estado']))); ?>">
                                        <?php echo escapar($partido['estado']); ?>
                                    </span>
                                    <?php if ($partido['estado'] === 'Suspendido' && $partido['motivo_suspension']) { ?>
                                        <small class="motivo-fixture"><?php echo escapar($partido['motivo_suspension']); ?></small>
                                    <?php } ?>
                                </td>
                                <?php if (($_SESSION['rol'] ?? '') === 'admin') { ?>
                                    <td class="acciones-partido">
                                        <div class="controles-partido">
                                            <details>
                                                <summary class="boton boton-editar" aria-label="Editar partido" title="Editar partido">📝</summary>
                                                <form class="form-concluir" action="guardar_conclusion_partido.php" method="POST">
                                                    <input type="hidden" name="partido_id" value="<?php echo (int) $partido['id']; ?>">
                                                    <input type="hidden" name="torneo" value="<?php echo escapar($claveTorneo); ?>">
                                                    <label>Goles del Local</label>
                                                    <input type="number" name="goles_local" min="0" value="<?php echo $partido['estado'] === 'Jugado' ? (int) $partido['goles_local'] : ''; ?>">
                                                    <label>Goles del Visitante</label>
                                                    <input type="number" name="goles_visitante" min="0" value="<?php echo $partido['estado'] === 'Jugado' ? (int) $partido['goles_visitante'] : ''; ?>">
                                                    <label>Estado</label>
                                                    <select name="estado" required>
                                                        <option value="Jugado" <?php echo $partido['estado'] === 'Jugado' ? 'selected' : ''; ?>>Partido jugado</option>
                                                        <option value="Suspendido" <?php echo $partido['estado'] === 'Suspendido' ? 'selected' : ''; ?>>Suspender partido</option>
                                                    </select>
                                                    <label>Motivo de suspensión (si corresponde)</label>
                                                    <input type="text" name="motivo_suspension" maxlength="255" value="<?php echo escapar($partido['motivo_suspension'] ?? ''); ?>">
                                                    <button type="submit">Guardar cambios</button>
                                                </form>
                                            </details>
                                            <form class="form-accion" action="eliminar_partido.php" method="POST"
                                                  onsubmit="return confirm('¿Eliminar este partido y las tarjetas/sanciones asociadas?');">
                                                <input type="hidden" name="partido_id" value="<?php echo (int) $partido['id']; ?>">
                                                <input type="hidden" name="torneo" value="<?php echo escapar($claveTorneo); ?>">
                                                <button class="boton-peligro boton-icono" type="submit"
                                                        aria-label="Eliminar partido" title="Eliminar partido">🗑️</button>
                                            </form>
                                        </div>
                                    </td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <p class="aviso">Todavía no hay partidos registrados para esta competencia.</p>
        <?php } ?>
    </section>

    <section class="panel-competencia" id="sanciones" aria-labelledby="titulo-sanciones">
        <h3 id="titulo-sanciones">Sanciones y tarjetas <span><?php echo count($sanciones); ?> registros</span></h3>
        <p class="ayuda">Registro disciplinario de los jugadores de esta competencia.</p>
        <?php if ($sanciones) { ?>
            <div class="contenedor tabla-sanciones">
                <table>
                    <thead>
                        <tr><th>Fecha del partido</th><th>Jugador</th><th>Club</th><th>Partido</th><th>Tarjeta</th><th>Motivo</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sanciones as $sancion) { ?>
                            <tr>
                                <td><?php echo $sancion['fecha_partido'] ? date('d/m/Y', strtotime($sancion['fecha_partido'])) : 'Sin fecha'; ?></td>
                                <td class="nombre-equipo"><?php echo escapar($sancion['nombre_jugador']); ?></td>
                                <td><?php echo escapar($sancion['nombre_club']); ?></td>
                                <td><?php echo escapar($sancion['nombre_local']); ?> <span class="separador-partido">vs</span> <?php echo escapar($sancion['nombre_visitante']); ?></td>
                                <td><span class="insignia-tarjeta tarjeta-<?php echo escapar(strtolower($sancion['tipo'])); ?>"><?php echo escapar($sancion['tipo']); ?></span></td>
                                <td><?php echo $sancion['motivo'] !== '' ? escapar($sancion['motivo']) : '—'; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <p class="aviso aviso-neutro">No hay sanciones registradas en esta competencia.</p>
        <?php } ?>
    </section>
</main>

</body>
</html>
