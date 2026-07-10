<?php

session_start();

if(!isset($_SESSION["usuario"])){
    header("Location: login.php");
    exit();
}

if($_SESSION["rol"]!="admin"){
    die("Acceso denegado.");
}

include("conexion.php");

$id = $_GET["id"];

// Buscamos el estado actual
$consulta = mysqli_query($conn, "SELECT activo FROM clubes WHERE id = '$id'");
$club = mysqli_fetch_assoc($consulta);

// Si está activo lo pasamos a inactivo y viceversa
if($club["activo"] == 1){
    $nuevo_estado = 0;
}else{
    $nuevo_estado = 1;
}

mysqli_query($conn, "UPDATE clubes SET activo = '$nuevo_estado' WHERE id = '$id'");

header("Location: clubes.php");

?>