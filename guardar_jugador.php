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

$nombre = $_POST["nombre"];
$edad = $_POST["edad"];
$posicion = $_POST["posicion"];
$club_id = $_POST["club_id"];
$ci = $_POST["ci"];
$categoria = $_POST["categoria"];

// Esto se puede mejorar después con más validaciones

$sql = "INSERT INTO jugadores
(nombre, edad, posicion, club_id, ci, categoria)
VALUES
('$nombre', '$edad', '$posicion', '$club_id', '$ci', '$categoria')";

mysqli_query($conn, $sql);

header("Location: jugadores.php");

?>