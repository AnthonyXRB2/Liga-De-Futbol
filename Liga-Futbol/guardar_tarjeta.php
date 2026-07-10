<?php

include("conexion.php");

session_start();

if(!isset($_SESSION["usuario"])){
    header("Location: login.php");
    exit();
}

if($_SESSION["rol"]!="admin"){
    die("Acceso denegado.");
}

$jugador_id = $_POST["jugador_id"];
$partido_id = $_POST["partido_id"];
$tipo = $_POST["tipo"];
$motivo = $_POST["motivo"];

$sql = "INSERT INTO tarjetas
(jugador_id, partido_id, tipo, motivo)
VALUES
('$jugador_id', '$partido_id', '$tipo', '$motivo')";

mysqli_query($conn, $sql);

header("Location: agregar_tarjeta.php");

?>