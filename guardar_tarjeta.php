<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

if (($_SESSION['rol'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Acceso denegado.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tabla_posiciones.php#sanciones');
    exit();
}

require_once('conexion.php');

$jugadorId = filter_input(INPUT_POST, 'jugador_id', FILTER_VALIDATE_INT);
$partidoId = filter_input(INPUT_POST, 'partido_id', FILTER_VALIDATE_INT);
$tipo = $_POST['tipo'] ?? null;
$motivo = $_POST['motivo'] ?? '';

if ($jugadorId === false || $jugadorId === null || $jugadorId < 1 ||
    $partidoId === false || $partidoId === null || $partidoId < 1 ||
    !is_string($tipo) || !is_string($motivo) ||
    !in_array($tipo, ['Amarilla', 'Roja'], true) ||
    preg_match('/\A.{0,255}\z/us', trim($motivo)) !== 1) {
    http_response_code(400);
    exit('Revisa los datos de la sanción e intenta nuevamente.');
}
$motivo = trim($motivo);

if (!mysqli_begin_transaction($conn)) {
    http_response_code(500);
    exit('No se pudo iniciar el registro de la sanción.');
}

try {
    $consultaJugador = mysqli_prepare($conn, 'SELECT id, club_id FROM jugadores WHERE id = ? FOR UPDATE');
    if (!$consultaJugador) {
        throw new RuntimeException('No se pudo validar el jugador: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($consultaJugador, 'i', $jugadorId);
    if (!mysqli_stmt_execute($consultaJugador)) {
        throw new RuntimeException('No se pudo validar el jugador: ' . mysqli_stmt_error($consultaJugador));
    }
    $jugador = mysqli_fetch_assoc(mysqli_stmt_get_result($consultaJugador));
    mysqli_stmt_close($consultaJugador);
    if (!$jugador) {
        throw new DomainException('El jugador seleccionado no existe.');
    }

    $consultaPartido = mysqli_prepare(
        $conn,
        'SELECT p.categoria
         FROM partidos p
         WHERE p.id = ? AND (p.local_id = ? OR p.visitante_id = ?)
         LIMIT 1'
    );
    if (!$consultaPartido) {
        throw new RuntimeException('No se pudo validar el partido: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($consultaPartido, 'iii', $partidoId, $jugador['club_id'], $jugador['club_id']);
    if (!mysqli_stmt_execute($consultaPartido)) {
        throw new RuntimeException('No se pudo validar el partido: ' . mysqli_stmt_error($consultaPartido));
    }
    $partido = mysqli_fetch_assoc(mysqli_stmt_get_result($consultaPartido));
    mysqli_stmt_close($consultaPartido);
    if (!$partido) {
        throw new DomainException('El jugador debe pertenecer a uno de los clubes del partido.');
    }

    $categoria = $partido['categoria'] ?: 'Estándar';
    $consultaConteo = mysqli_prepare(
        $conn,
        "SELECT
            COALESCE(SUM(t.tipo = 'Amarilla'), 0) AS amarillas,
            COALESCE(SUM(t.tipo = 'Roja'), 0) AS rojas
         FROM tarjetas t
         INNER JOIN partidos p ON p.id = t.partido_id
         WHERE t.jugador_id = ?
           AND COALESCE(NULLIF(p.categoria, ''), 'Estándar') = ?"
    );
    if (!$consultaConteo) {
        throw new RuntimeException('No se pudo comprobar el historial disciplinario: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($consultaConteo, 'is', $jugadorId, $categoria);
    if (!mysqli_stmt_execute($consultaConteo)) {
        throw new RuntimeException('No se pudo comprobar el historial disciplinario: ' . mysqli_stmt_error($consultaConteo));
    }
    $conteo = mysqli_fetch_assoc(mysqli_stmt_get_result($consultaConteo));
    mysqli_stmt_close($consultaConteo);
    $amarillas = (int) $conteo['amarillas'];
    $rojas = (int) $conteo['rojas'];

    if ($tipo === 'Amarilla' && $amarillas >= 2) {
        throw new DomainException('Este jugador ya tiene dos tarjetas amarillas en esta competencia.');
    }
    if ($tipo === 'Amarilla' && $amarillas === 1 && $rojas >= 1) {
        throw new DomainException('No se puede completar la segunda amarilla porque el jugador ya tiene una roja en esta competencia.');
    }
    if ($tipo === 'Roja' && $rojas >= 1) {
        throw new DomainException('Este jugador ya tiene una tarjeta roja en esta competencia.');
    }

    $consulta = mysqli_prepare(
        $conn,
        'INSERT INTO tarjetas (jugador_id, partido_id, tipo, motivo) VALUES (?, ?, ?, ?)'
    );
    if (!$consulta) {
        throw new RuntimeException('No se pudo preparar el registro de la sanción: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($consulta, 'iiss', $jugadorId, $partidoId, $tipo, $motivo);
    if (!mysqli_stmt_execute($consulta)) {
        throw new RuntimeException('No se pudo guardar la sanción: ' . mysqli_stmt_error($consulta));
    }

    if ($tipo === 'Amarilla' && $amarillas === 1) {
        $tipoRoja = 'Roja';
        $motivoRoja = 'Roja automática por acumulación de dos tarjetas amarillas.';
        mysqli_stmt_bind_param($consulta, 'iiss', $jugadorId, $partidoId, $tipoRoja, $motivoRoja);
        if (!mysqli_stmt_execute($consulta)) {
            throw new RuntimeException('No se pudo registrar la roja automática: ' . mysqli_stmt_error($consulta));
        }
    }
    mysqli_stmt_close($consulta);
    if (!mysqli_commit($conn)) {
        throw new RuntimeException('No se pudo confirmar el registro de la sanción: ' . mysqli_error($conn));
    }
} catch (DomainException $error) {
    mysqli_rollback($conn);
    http_response_code(409);
    exit($error->getMessage());
} catch (Throwable $error) {
    mysqli_rollback($conn);
    error_log($error->getMessage());
    http_response_code(500);
    exit('No se pudo guardar la sanción.');
}

$torneos = [
    'Estándar' => 'estandar',
    'Copa Plata' => 'plata',
    'Copa Oro' => 'oro',
];
$claveTorneo = $torneos[$partido['categoria'] ?? ''] ?? 'estandar';
header('Location: tabla_posiciones.php?torneo=' . $claveTorneo . '#sanciones');
exit();
