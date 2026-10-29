<?php
// Autoriza la edición y limita la actualización a solicitudes POST.
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
    header('Location: jugadores.php');
    exit();
}

require_once('conexion.php');

$jugadorId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
$edad = filter_input(INPUT_POST, 'edad', FILTER_VALIDATE_INT);
$clubId = filter_input(INPUT_POST, 'club_id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
$nombre = $_POST['nombre'] ?? null;
$posicion = $_POST['posicion'] ?? '';
$ci = $_POST['ci'] ?? null;
$categoria = $_POST['categoria'] ?? '';
$valoresFisicos = [];

if (!is_string($nombre) || !is_string($ci)) {
    http_response_code(400);
    exit('Revisa el nombre y la cédula del jugador.');
}
$nombre = trim($nombre);
$ci = trim($ci);

// Convierte y valida las medidas que llegan desde el formulario.
foreach (['altura', 'masa', 'velocidad'] as $campo) {
    $valor = $_POST[$campo] ?? null;
    if (!is_string($valor) || !is_numeric($valor)) {
        http_response_code(400);
        exit('Revisa los datos físicos del jugador.');
    }
    $valoresFisicos[$campo] = (float) $valor;
}

$posicionesPermitidas = ['Arquero', 'Defensa', 'Mediocampista', 'Delantero'];
$nombreValido = preg_match('/\A[\p{L}\p{M}][\p{L}\p{M} .\'-]{0,99}\z/uD', $nombre) === 1;
$categoriaValida = is_string($categoria) && preg_match('/\A(?:201[2-9])\z/D', $categoria) === 1;

// Valida rangos, opciones del formulario y formato de la identificación.
if ($jugadorId === false || $jugadorId === null ||
    $edad === false || $edad === null || $edad < 0 || $edad > 100 ||
    $clubId === false || $clubId === null ||
    !$nombreValido ||
    !is_string($posicion) || !in_array($posicion, $posicionesPermitidas, true) ||
    !preg_match('/\A[0-9]{8}\z/D', $ci) ||
    !$categoriaValida ||
    $valoresFisicos['altura'] < 0 || $valoresFisicos['altura'] > 2.1 ||
    $valoresFisicos['masa'] < 0 || $valoresFisicos['masa'] > 70.9 ||
    $valoresFisicos['velocidad'] < 0 || $valoresFisicos['velocidad'] > 9.9) {
    http_response_code(400);
    exit('Revisa los datos del jugador e intenta nuevamente.');
}

if (preg_match('/\A(?:([0-9])\1{7}|01234567|12345678|23456789|98765432|87654321|76543210)\z/D', $ci)) {
    http_response_code(400);
    exit('La cédula no puede repetir el mismo dígito ni ser una secuencia consecutiva.');
}

$consultaClub = mysqli_prepare($conn, 'SELECT id FROM clubes WHERE id = ? AND activo = 1');
// Confirma que el club destino siga activo antes de modificar el jugador.
if (!$consultaClub) {
    http_response_code(500);
    exit('No se pudo validar el club seleccionado.');
}
mysqli_stmt_bind_param($consultaClub, 'i', $clubId);
if (!mysqli_stmt_execute($consultaClub)) {
    mysqli_stmt_close($consultaClub);
    http_response_code(500);
    exit('No se pudo validar el club seleccionado.');
}
$clubValido = mysqli_fetch_assoc(mysqli_stmt_get_result($consultaClub));
mysqli_stmt_close($consultaClub);
if (!$clubValido) {
    http_response_code(400);
    exit('Selecciona un club activo.');
}

$consultaActual = mysqli_prepare($conn, 'SELECT ci FROM jugadores WHERE id = ? LIMIT 1');
// Carga el registro y, si cambió, verifica que la nueva cédula sea única.
if (!$consultaActual) {
    http_response_code(500);
    exit('No se pudo comprobar el jugador.');
}
mysqli_stmt_bind_param($consultaActual, 'i', $jugadorId);
if (!mysqli_stmt_execute($consultaActual)) {
    mysqli_stmt_close($consultaActual);
    http_response_code(500);
    exit('No se pudo comprobar el jugador.');
}
$datosActuales = mysqli_fetch_assoc(mysqli_stmt_get_result($consultaActual));
mysqli_stmt_close($consultaActual);
if (!$datosActuales) {
    http_response_code(404);
    exit('Jugador no encontrado.');
}

if ($ci !== $datosActuales['ci']) {
    $consultaCedula = mysqli_prepare($conn, 'SELECT id FROM jugadores WHERE ci = ? AND id <> ? LIMIT 1');
    if (!$consultaCedula) {
        http_response_code(500);
        exit('No se pudo validar la cédula.');
    }
    mysqli_stmt_bind_param($consultaCedula, 'si', $ci, $jugadorId);
    if (!mysqli_stmt_execute($consultaCedula)) {
        mysqli_stmt_close($consultaCedula);
        http_response_code(500);
        exit('No se pudo validar la cédula.');
    }
    $cedulaDuplicada = mysqli_fetch_assoc(mysqli_stmt_get_result($consultaCedula));
    mysqli_stmt_close($consultaCedula);
    if ($cedulaDuplicada) {
        http_response_code(409);
        exit('Esta cédula ya está registrada.');
    }
}

$altura = $valoresFisicos['altura'];
$masa = $valoresFisicos['masa'];
$fuerzaPeso = $masa * 9.8;
$velocidad = $valoresFisicos['velocidad'];
$consulta = mysqli_prepare(
    $conn,
    'UPDATE jugadores
     SET nombre = ?, edad = ?, posicion = ?, club_id = ?, ci = ?, categoria = ?,
         altura = ?, masa = ?, fuerza_peso = ?, velocidad = ?
     WHERE id = ?'
);
// Guarda conjuntamente el perfil y las magnitudes físicas calculadas.
if (!$consulta) {
    http_response_code(500);
    exit('No se pudo preparar la actualización del jugador.');
}
mysqli_stmt_bind_param(
    $consulta,
    'sisissddddi',
    $nombre,
    $edad,
    $posicion,
    $clubId,
    $ci,
    $categoria,
    $altura,
    $masa,
    $fuerzaPeso,
    $velocidad,
    $jugadorId
);
if (!mysqli_stmt_execute($consulta)) {
    mysqli_stmt_close($consulta);
    http_response_code(500);
    exit('No se pudo guardar los cambios del jugador.');
}
mysqli_stmt_close($consulta);

header('Location: jugadores.php');
exit();
