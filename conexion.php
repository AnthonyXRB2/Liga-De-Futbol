<?php

$server = "localhost";
$user = "root";
$pass = "";
$bd = "liga_futbol";

$conn = mysqli_connect($server, $user, $pass, $bd);

if (!$conn) {
    die("Error al conectar con la base de datos");
}

// Más adelante podríamos mejorar el manejo de errores.

?>