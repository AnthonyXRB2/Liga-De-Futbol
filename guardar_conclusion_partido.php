<?php
// Valida el rol, el método y los datos de resultado o suspensión.
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

if (($_SESSION["rol"] ?? "") !== "admin") {
    http_response_code(403);
    exit("Acceso denegado.");
}

include("conexion.php");

$partidoId = filter_input(INPUT_POST, "partido_id", FILTER_VALIDATE_INT);
$torneo = $_POST["torneo"] ?? "estandar";
$torneosPermitidos = ["estandar", "plata", "oro"];
$estado = $_POST["estado"] ?? "";
$golesLocal = filter_input(INPUT_POST, "goles_local", FILTER_VALIDATE_INT);
$golesVisitante = filter_input(INPUT_POST, "goles_visitante", FILTER_VALIDATE_INT);
$motivoEntrada = $_POST["motivo_suspension"] ?? "";
$motivoSuspension = is_string($motivoEntrada) ? trim($motivoEntrada) : "";

if ($partidoId === false || $partidoId === null ||
    !is_string($torneo) || !in_array($torneo, $torneosPermitidos, true) ||
    !is_string($motivoEntrada) ||
    !in_array($estado, ["Jugado", "Suspendido"], true) ||
    ($estado === "Jugado" &&
        ($golesLocal === false || $golesVisitante === false ||
            $golesLocal === null || $golesVisitante === null ||
            $golesLocal < 0 || $golesVisitante < 0)) ||
    ($estado === "Suspendido" && strlen($motivoSuspension) > 255)) {
    http_response_code(400);
    exit("Revisa los datos de la conclusión del partido e intenta nuevamente.");
}

if ($estado === "Suspendido") {
    $golesLocal = null;
    $golesVisitante = null;
} else {
    $motivoSuspension = null;
}

// Actualiza el marcador de un partido jugado o su motivo de suspensión.
if ($estado === "Jugado") {
    $consulta = mysqli_prepare(
        $conn,
        "UPDATE partidos
         SET estado = ?, goles_local = ?, goles_visitante = ?, motivo_suspension = NULL
         WHERE id = ?"
    );
} else {
    $consulta = mysqli_prepare(
        $conn,
        "UPDATE partidos
         SET estado = ?, goles_local = NULL, goles_visitante = NULL, motivo_suspension = ?
         WHERE id = ?"
    );
}
if (!$consulta) {
    http_response_code(500);
    exit("No se pudo preparar la actualización del partido.");
}

if ($estado === "Jugado") {
    mysqli_stmt_bind_param($consulta, "siii", $estado, $golesLocal, $golesVisitante, $partidoId);
} else {
    mysqli_stmt_bind_param($consulta, "ssi", $estado, $motivoSuspension, $partidoId);
}
if (!mysqli_stmt_execute($consulta)) {
    mysqli_stmt_close($consulta);
    http_response_code(500);
    exit("No se pudo guardar la conclusión del partido.");
}
mysqli_stmt_close($consulta);

header("Location: tabla_posiciones.php?torneo=" . urlencode($torneo) . "#partidos");
exit();
?>
