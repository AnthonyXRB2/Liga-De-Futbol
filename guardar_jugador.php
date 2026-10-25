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
$alturaIngresada = trim($_POST["altura"] ?? "");
$masaIngresada = trim($_POST["masa"] ?? "");
$velocidadIngresada = trim($_POST["velocidad"] ?? "");
$altura = (float) str_replace(",", ".", $alturaIngresada);
$masa = (float) str_replace(",", ".", $masaIngresada);
$fuerza_peso = $masa * 9.8;
$velocidad = (float) str_replace(",", ".", $velocidadIngresada);

if ($nombre === "" || $posicion === "" || $ci === "" || $club_id <= 0) {
    die("Completa todos los datos obligatorios del jugador.");
}

if (!preg_match('/^[0-9]{8}$/D', $ci)) {
    die("La cédula debe contener exactamente 8 dígitos.");
}

$palabrasNombre = explode(" ", $nombre);
$nombreInvalido = !preg_match('/^\p{Lu}[\p{L}\p{M} ]*$/uD', $nombre)
    || count($palabrasNombre) > 4;
foreach ($palabrasNombre as $palabraNombre) {
    $cantidadLetras = preg_match_all('/\p{L}/u', $palabraNombre);
    if (!preg_match('/^\p{Lu}[\p{Ll}\p{M}]*$/uD', $palabraNombre) || $cantidadLetras < 3 || $cantidadLetras > 10) {
        $nombreInvalido = true;
        break;
    }
}
if ($nombreInvalido) {
    die("Cada palabra debe tener entre 3 y 10 letras. El nombre debe comenzar con mayúscula y usar de 1 a 3 espacios entre palabras.");
}

$limitesFisicos = [
    "altura" => [$alturaIngresada, '/^(?:1,[0-9]{1,2}|2,(?:0[0-9]?|10?))$/D', 1, 2.1, "La altura debe estar entre 1,00 y 2,10 metros."],
    "masa" => [$masaIngresada, '/^(?:1[5-9]|[2-6][0-9]|70),[0-9]$/D', 15, 70.9, "El peso debe estar entre 15,0 y 70,9 kg."],
    "velocidad" => [$velocidadIngresada, '/^[1-9],[0-9]$/D', 1, 9.9, "La velocidad debe estar entre 1,0 y 9,9 m/s."],
];
foreach ($limitesFisicos as [$valorIngresado, $formato, $minimo, $maximo, $mensajeError]) {
    if ($valorIngresado === "") {
        continue;
    }
    if (!preg_match($formato, $valorIngresado)) {
        die("Los datos físicos no tienen el formato permitido. Usa la coma decimal y respeta los dígitos indicados.");
    }
    $valorNumerico = (float) str_replace(",", ".", $valorIngresado);
    if ($valorNumerico < $minimo || $valorNumerico > $maximo) {
        die($mensajeError);
    }
}

$buscarCedula = mysqli_prepare($conn, "SELECT id FROM jugadores WHERE ci = ? LIMIT 1");
mysqli_stmt_bind_param($buscarCedula, "s", $ci);
mysqli_stmt_execute($buscarCedula);
mysqli_stmt_store_result($buscarCedula);
$cedulaDuplicada = mysqli_stmt_num_rows($buscarCedula) > 0;
mysqli_stmt_close($buscarCedula);
if ($cedulaDuplicada) {
    die("Esta cédula ya está registrada.");
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
