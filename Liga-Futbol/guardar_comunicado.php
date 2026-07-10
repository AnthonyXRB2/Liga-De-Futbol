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

$titulo = $_POST["titulo"];
$mensaje = $_POST["mensaje"];

// Validación sencilla
if ($titulo != "" && $mensaje != "") {

    mysqli_query(
        $conn,
        "INSERT INTO comunicados(titulo, mensaje)
         VALUES('$titulo', '$mensaje')"
    );

    header("Location: comunicados.php");

} else {

    echo "Tenés que completar todos los campos.";

}

?>