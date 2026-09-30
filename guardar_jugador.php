<?php

include("conexion.php");
include("procesar_ficha_medica.php");

session_start();

if(!isset($_SESSION["usuario"])){
    header("Location: login.php");
    exit();
}

if($_SESSION["rol"]!="admin"){
    die("Acceso denegado.");
}

$nombre = $_POST["nombre"];
$datosNacimiento = calcular_datos_nacimiento($_POST["fecha_nacimiento"] ?? "");
if ($datosNacimiento === false) {
    die("Debes ingresar una fecha de nacimiento válida y no futura.");
}
$edad = $datosNacimiento["edad"];
$posicion = $_POST["posicion"];
$club_id = $_POST["club_id"];
$ci = $_POST["ci"];
$categoria = $datosNacimiento["categoria"];
$altura = (float) ($_POST["altura"] ?? 0);
$masa = (float) ($_POST["masa"] ?? 0);
$fuerza_peso = $masa * 9.8;
$velocidad = (float) ($_POST["velocidad"] ?? 0);

$consulta = mysqli_prepare($conn, "INSERT INTO jugadores (nombre, edad, posicion, club_id, ci, categoria, altura, masa, fuerza_peso, velocidad) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($consulta, "sisissdddd", $nombre, $edad, $posicion, $club_id, $ci, $categoria, $altura, $masa, $fuerza_peso, $velocidad);
mysqli_stmt_execute($consulta);
mysqli_stmt_close($consulta);

header("Location: jugadores.php");

?>
