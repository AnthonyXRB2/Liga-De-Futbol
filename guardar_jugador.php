<?php

include("conexion.php");
include("datos_jugador.php");

session_start();

if(!isset($_SESSION["usuario"])){
    header("Location: login.php");
    exit();
}

if (($_SESSION["rol"] ?? "") !== "admin") {
    die("Acceso denegado.");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: jugadores.php");
    exit();
}

$nombre = trim($_POST["nombre"] ?? "");
$datosNacimiento = calcular_datos_nacimiento($_POST["fecha_nacimiento"] ?? "");
if ($datosNacimiento === false) {
    die("La fecha de nacimiento debe ser válida y estar entre el 1 de enero de 2013 y el 31 de diciembre de 2019.");
}
$edad = $datosNacimiento["edad"];
$posicion = trim($_POST["posicion"] ?? "");
$club_id = (int) ($_POST["club_id"] ?? 0);
$ci = trim($_POST["ci"] ?? "");
$categoria = $datosNacimiento["categoria"];
$altura = (float) ($_POST["altura"] ?? 0);
$masa = (float) ($_POST["masa"] ?? 0);
$fuerza_peso = $masa * 9.8;
$velocidad = (float) ($_POST["velocidad"] ?? 0);

if ($nombre === "" || $posicion === "" || $ci === "" || $club_id <= 0) {
    die("Completa todos los datos obligatorios del jugador.");
}

$club = mysqli_prepare($conn, "SELECT id FROM clubes WHERE id = ? AND activo = 1");
mysqli_stmt_bind_param($club, "i", $club_id);
mysqli_stmt_execute($club);
mysqli_stmt_store_result($club);
$clubActivo = mysqli_stmt_num_rows($club) === 1;
mysqli_stmt_close($club);
if (!$clubActivo) {
    die("Selecciona un club activo.");
}

$consulta = mysqli_prepare($conn, "INSERT INTO jugadores (nombre, edad, posicion, club_id, ci, categoria, altura, masa, fuerza_peso, velocidad) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($consulta, "sisissdddd", $nombre, $edad, $posicion, $club_id, $ci, $categoria, $altura, $masa, $fuerza_peso, $velocidad);
mysqli_stmt_execute($consulta);
mysqli_stmt_close($consulta);

header("Location: jugadores.php");

?>
