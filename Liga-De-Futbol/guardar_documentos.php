<?php

include("conexion.php");

session_start();

if(!isset($_SESSION["usuario"])){
    header("Location: login.php");
    exit();
}

$jugador_id = $_POST["jugador_id"];
$vencimiento_carnet = $_POST["vencimiento_carnet"];

// Fecha y hora actual del servidor
$fecha_actual = date("Y-m-d H:i:s");

// Obtenemos los nombres de los archivos
$nombre_ficha = $_FILES["ficha_medica"]["name"];
$nombre_carnet = $_FILES["carnet_salud"]["name"];

// Rutas donde se van a guardar
$ruta_ficha = "uploads/" . $nombre_ficha;
$ruta_carnet = "uploads/" . $nombre_carnet;

// Guardamos los archivos en la carpeta uploads
move_uploaded_file($_FILES["ficha_medica"]["tmp_name"], $ruta_ficha);
move_uploaded_file($_FILES["carnet_salud"]["tmp_name"], $ruta_carnet);

// Guardamos la información en la base de datos
$sql = "INSERT INTO documentos
(jugador_id, ficha_medica, fecha_subida_ficha, carnet_salud, vencimiento_carnet, fecha_subida_carnet)
VALUES
('$jugador_id', '$ruta_ficha', '$fecha_actual', '$ruta_carnet', '$vencimiento_carnet', '$fecha_actual')";

mysqli_query($conn, $sql);

// Esto se puede mejorar después para evitar archivos duplicados

header("Location: subir_documentos.php");

?>