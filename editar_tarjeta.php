<?php
// Valida la operación y conserva filtros para volver a la lista de sanciones.
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
    http_response_code(405);
    header('Allow: POST');
    exit('Método no permitido.');
}

require_once('conexion.php');

$sancionId = filter_input(INPUT_POST, 'sancion_id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
$tipo = $_POST['tipo'] ?? null;
$motivo = $_POST['motivo'] ?? null;
$torneo = $_POST['torneo'] ?? 'estandar';
$torneosPermitidos = ['estandar', 'plata', 'oro'];
$categoriasEdad = array_map('strval', range(2012, 2019));
$categoriaPartidos = $_POST['categoria_partidos'] ?? '';
$clubSancion = $_POST['club_sancion'] ?? '';
$categoriaSancion = $_POST['categoria_sancion'] ?? '';

if ($sancionId === false || $sancionId === null ||
    !is_string($tipo) || !in_array($tipo, ['Amarilla', 'Roja'], true) ||
    !is_string($motivo) || preg_match('/\A.{0,255}\z/us', trim($motivo)) !== 1) {
    http_response_code(400);
    exit('Revisa los datos de la sanción e intenta nuevamente.');
}
$motivo = trim($motivo);

$parametrosRetorno = ['torneo' => is_string($torneo) && in_array($torneo, $torneosPermitidos, true) ? $torneo : 'estandar'];
if (is_string($categoriaPartidos) && in_array($categoriaPartidos, $categoriasEdad, true)) {
    $parametrosRetorno['categoria_partidos'] = $categoriaPartidos;
}
if (is_string($clubSancion) && ctype_digit($clubSancion) && (int) $clubSancion > 0) {
    $parametrosRetorno['club_sancion'] = (int) $clubSancion;
}
if (is_string($categoriaSancion) && in_array($categoriaSancion, $categoriasEdad, true)) {
    $parametrosRetorno['categoria_sancion'] = $categoriaSancion;
}
$urlRetorno = 'tabla_posiciones.php?' . http_build_query($parametrosRetorno) . '#sanciones';

// Actualiza el tipo y motivo dentro de una transacción protegida.
if (!mysqli_begin_transaction($conn)) {
    http_response_code(500);
    exit('No se pudo iniciar la actualización de la sanción.');
}

try {
    $consultaActual = mysqli_prepare(
        $conn,
        'SELECT t.jugador_id, t.tipo, p.categoria, p.estado
         FROM tarjetas t
         INNER JOIN partidos p ON p.id = t.partido_id
         WHERE t.id = ?
         FOR UPDATE'
    );
    if (!$consultaActual) {
        throw new RuntimeException('No se pudo preparar la consulta de sanción: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($consultaActual, 'i', $sancionId);
    if (!mysqli_stmt_execute($consultaActual)) {
        throw new RuntimeException('No se pudo cargar la sanción: ' . mysqli_stmt_error($consultaActual));
    }
    $sancionActual = mysqli_fetch_assoc(mysqli_stmt_get_result($consultaActual));
    mysqli_stmt_close($consultaActual);
    if (!$sancionActual) {
        throw new DomainException('No se encontró la sanción que intentas editar.');
    }
    if ($sancionActual['estado'] !== 'Jugado') {
        throw new DomainException('Solo se pueden editar sanciones de partidos jugados.');
    }

    if ($sancionActual['tipo'] !== $tipo) {
        // Al cambiar el tipo, comprueba los límites disciplinarios restantes.
        $categoriaCompetencia = $sancionActual['categoria'] ?: 'Estándar';
        $consultaConteo = mysqli_prepare(
            $conn,
            "SELECT
                COALESCE(SUM(t.tipo = 'Amarilla'), 0) AS amarillas,
                COALESCE(SUM(t.tipo = 'Roja'), 0) AS rojas
             FROM tarjetas t
             INNER JOIN partidos p ON p.id = t.partido_id
             WHERE t.jugador_id = ?
               AND t.id <> ?
               AND COALESCE(NULLIF(p.categoria, ''), 'Estándar') = ?"
        );
        if (!$consultaConteo) {
            throw new RuntimeException('No se pudo comprobar el historial disciplinario: ' . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($consultaConteo, 'iis', $sancionActual['jugador_id'], $sancionId, $categoriaCompetencia);
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
    }

    $consultaActualizar = mysqli_prepare($conn, 'UPDATE tarjetas SET tipo = ?, motivo = ? WHERE id = ?');
    if (!$consultaActualizar) {
        throw new RuntimeException('No se pudo preparar la actualización de la sanción: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($consultaActualizar, 'ssi', $tipo, $motivo, $sancionId);
    if (!mysqli_stmt_execute($consultaActualizar)) {
        throw new RuntimeException('No se pudo actualizar la sanción: ' . mysqli_stmt_error($consultaActualizar));
    }
    mysqli_stmt_close($consultaActualizar);

    if (!mysqli_commit($conn)) {
        throw new RuntimeException('No se pudo confirmar la actualización de la sanción: ' . mysqli_error($conn));
    }
} catch (DomainException $error) {
    mysqli_rollback($conn);
    http_response_code(409);
    exit($error->getMessage());
} catch (Throwable $error) {
    mysqli_rollback($conn);
    error_log($error->getMessage());
    http_response_code(500);
    exit('No se pudo actualizar la sanción.');
}

header('Location: ' . $urlRetorno);
exit();
