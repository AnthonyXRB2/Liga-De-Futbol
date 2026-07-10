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
$ciudad = $_POST["ciudad"];

// Validación sencilla
if ($nombre != "") {

    mysqli_query(
        $conn,
        "INSERT INTO clubes(nombre, ciudad)
         VALUES('$nombre', '$ciudad')"
    );

    header("Location: clubes.php");

} else {

    echo "Tenés que escribir un nombre para el club.";

}