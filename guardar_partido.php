<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION["rol"] != "admin") {
    die("Acceso denegado.");
}

include("conexion.php");

$local = filter_input(INPUT_POST, "local", FILTER_VALIDATE_INT);
$visitante = filter_input(INPUT_POST, "visitante", FILTER_VALIDATE_INT);
$fecha = $_POST["fecha"] ?? "";
$estado = $_POST["estado"] ?? "";
$motivo_suspension = trim($_POST["motivo_suspension"] ?? "");
$goles_local = filter_input(INPUT_POST, "goles_local", FILTER_VALIDATE_INT);
$goles_visitante = filter_input(INPUT_POST, "goles_visitante", FILTER_VALIDATE_INT);
$categoria = $_POST["categoria"] ?? "";
$categoriasPermitidas = ["Estándar", "Copa Plata", "Copa Oro"];
$estadosPermitidos = ["Programado", "Sin fecha", "Pendiente", "Suspendido", "Jugado"];

if ($local === false || $visitante === false || $local === null || $visitante === null || $fecha === "" ||
    !in_array($estado, $estadosPermitidos, true) || !in_array($categoria, $categoriasPermitidas, true) ||
    $goles_local === false || $goles_visitante === false || $goles_local < 0 || $goles_visitante < 0) {
    die("Revisa los datos del partido e intenta nuevamente.");
}

// Evitar que un club juegue contra sí mismo
if ($local === $visitante) {
    die("El club local y el visitante no pueden ser el mismo.");
}

$sql = "INSERT INTO partidos
    (local_id, visitante_id, fecha, estado, motivo_suspension, goles_local, goles_visitante, categoria)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$consulta = mysqli_prepare($conn, $sql);
if (!$consulta) {
    die("No se pudo preparar el registro del partido.");
}
mysqli_stmt_bind_param($consulta, "iisssiis", $local, $visitante, $fecha, $estado, $motivo_suspension, $goles_local, $goles_visitante, $categoria);
if (!mysqli_stmt_execute($consulta)) {
    mysqli_stmt_close($consulta);
    die("No se pudo guardar el partido.");
}
mysqli_stmt_close($consulta);

$torneos = [
    "Estándar" => "estandar",
    "Copa Plata" => "plata",
    "Copa Oro" => "oro",
];
header("Location: tabla_posiciones.php?torneo=" . $torneos[$categoria]);
exit();
?>