<?php

// Configura el acceso común a la base de datos de la liga.
$server = "localhost";
$user = "root";
$pass = "";
$bd = "liga_futbol";

$conn = mysqli_connect($server, $user, $pass, $bd);

// Detiene la página si no es posible establecer la conexión.
if (!$conn) {
    die("Error al conectar con la base de datos");
}

// Mantiene los textos en UTF-8 al leer y guardar datos.
mysqli_set_charset($conn, 'utf8mb4');

?>