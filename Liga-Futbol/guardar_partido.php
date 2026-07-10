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

$local = $_POST["local"];
$visitante = $_POST["visitante"];
$fecha = $_POST["fecha"];
$estado = $_POST["estado"];
$motivo_suspension = $_POST["motivo_suspension"];
$goles_local = $_POST["goles_local"];
$goles_visitante = $_POST["goles_visitante"];

// Evitar que un club juegue contra sí mismo
if ($local == $visitante) {
    die("El club local y el visitante no pueden ser el mismo.");
}

$sql = "INSERT INTO partidos
(local_id, visitante_id, fecha, estado, motivo_suspension, goles_local, goles_visitante)
VALUES
('$local','$visitante','$fecha','$estado','$motivo_suspension','$goles_local','$goles_visitante')";

mysqli_query($conn, $sql);

header("Location: partidos.php");
exit();
?>