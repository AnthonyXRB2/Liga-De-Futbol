<?php
// Valida permisos e identifica la sanción y filtros para la redirección.
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
$torneo = $_POST['torneo'] ?? 'estandar';
$torneosPermitidos = ['estandar', 'plata', 'oro'];
$categoriasEdad = array_merge(array_map('strval', range(2012, 2019)), ['Femenina']);
$categoriaPartidos = $_POST['categoria_partidos'] ?? '';
$clubSancion = $_POST['club_sancion'] ?? '';
$categoriaSancion = $_POST['categoria_sancion'] ?? '';

if ($sancionId === false || $sancionId === null) {
    http_response_code(400);
    exit('La sanción seleccionada no es válida.');
}

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

// Elimina la tarjeta seleccionada y detecta si ya no existía.
$consultaEliminar = mysqli_prepare($conn, 'DELETE FROM tarjetas WHERE id = ?');
if (!$consultaEliminar) {
    http_response_code(500);
    exit('No se pudo preparar la eliminación de la sanción.');
}
mysqli_stmt_bind_param($consultaEliminar, 'i', $sancionId);
if (!mysqli_stmt_execute($consultaEliminar)) {
    mysqli_stmt_close($consultaEliminar);
    http_response_code(500);
    exit('No se pudo eliminar la sanción.');
}
$filasEliminadas = mysqli_stmt_affected_rows($consultaEliminar);
mysqli_stmt_close($consultaEliminar);

if ($filasEliminadas !== 1) {
    http_response_code(404);
    exit('No se encontró la sanción que intentas eliminar.');
}

header('Location: ' . $urlRetorno);
exit();
