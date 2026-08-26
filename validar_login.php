<?php
session_start();

include("conexion.php");

$usuario = $_POST["usuario"];
$password = $_POST["password"];

// Esto se puede mejorar después con contraseñas cifradas

$sql = "SELECT * FROM usuarios
WHERE usuario='$usuario'
AND password='$password'";

$consulta = mysqli_query($conn, $sql);

if(mysqli_num_rows($consulta) > 0){

    $datos = mysqli_fetch_assoc($consulta);

    $_SESSION["id"] = $datos["id"];
    $_SESSION["usuario"] = $datos["usuario"];
    $_SESSION["rol"] = $datos["rol"];
    $_SESSION["club_id"] = $datos["club_id"];

    header("Location: index.php");

}else{

    echo "Usuario o Contraseña incorrectos.";

}
?>