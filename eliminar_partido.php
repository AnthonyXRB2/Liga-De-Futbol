<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

if (($_SESSION["rol"] ?? "") !== "admin") {
    http_response_code(403);
    exit("Acceso denegado.");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    header("Allow: POST");
    exit("Método no permitido.");
}

$partidoId = filter_input(INPUT_POST, "partido_id", FILTER_VALIDATE_INT, [
    "options" => ["min_range" => 1],
]);
$torneo = $_POST["torneo"] ?? "estandar";
$torneosPermitidos = ["estandar", "plata", "oro"];

if ($partidoId === false || $partidoId === null ||
    !is_string($torneo) || !in_array($torneo, $torneosPermitidos, true)) {
    http_response_code(400);
    exit("Partido no válido.");
}

include("conexion.php");

if (!mysqli_begin_transaction($conn)) {
    http_response_code(500);
    exit("No se pudo iniciar la eliminación del partido.");
}

$consultaTarjetas = mysqli_prepare($conn, "DELETE FROM tarjetas WHERE partido_id = ?");
if (!$consultaTarjetas) {
    mysqli_rollback($conn);
    http_response_code(500);
    exit("No se pudieron preparar las operaciones de eliminación.");
}
mysqli_stmt_bind_param($consultaTarjetas, "i", $partidoId);
if (!mysqli_stmt_execute($consultaTarjetas)) {
    mysqli_stmt_close($consultaTarjetas);
    mysqli_rollback($conn);
    http_response_code(500);
    exit("No se pudieron eliminar las tarjetas asociadas al partido.");
}
mysqli_stmt_close($consultaTarjetas);

$consultaPartido = mysqli_prepare($conn, "DELETE FROM partidos WHERE id = ?");
if (!$consultaPartido) {
    mysqli_rollback($conn);
    http_response_code(500);
    exit("No se pudo preparar la eliminación del partido.");
}
mysqli_stmt_bind_param($consultaPartido, "i", $partidoId);
if (!mysqli_stmt_execute($consultaPartido)) {
    mysqli_stmt_close($consultaPartido);
    mysqli_rollback($conn);
    http_response_code(500);
    exit("No se pudo eliminar el partido.");
}

if (mysqli_stmt_affected_rows($consultaPartido) !== 1) {
    mysqli_stmt_close($consultaPartido);
    mysqli_rollback($conn);
    http_response_code(404);
    exit("No se encontró el partido que intentas eliminar.");
}
mysqli_stmt_close($consultaPartido);

if (!mysqli_commit($conn)) {
    mysqli_rollback($conn);
    http_response_code(500);
    exit("No se pudo confirmar la eliminación del partido.");
}

header("Location: tabla_posiciones.php?torneo=" . urlencode($torneo) . "#partidos");
exit();
?>
