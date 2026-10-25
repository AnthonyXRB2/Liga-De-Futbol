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

$departamentos = [
    "Artigas", "Canelones", "Cerro Largo", "Colonia", "Durazno",
    "Flores", "Florida", "Lavalleja", "Maldonado", "Montevideo",
    "Paysandú", "Río Negro", "Rivera", "Rocha", "Salto", "San José",
    "Soriano", "Tacuarembó", "Treinta y Tres"
];

$nombre = $_POST["nombre"] ?? "";
$directorTecnico = $_POST["director_tecnico"] ?? "";
$departamento = $_POST["departamento"] ?? "";
$ciudad = $_POST["ciudad"] ?? "";

if (!is_string($nombre) || !is_string($directorTecnico) || !is_string($departamento) || !is_string($ciudad)) {
    http_response_code(400);
    die("Los datos enviados no son válidos.");
}

$departamento = trim($departamento);
$ciudad = trim($ciudad);

function nombreCompuestoValido(string $valor, int $maxPalabras): bool
{
    $patron = '/^\p{Lu}[\p{Ll}\p{M}]*(?: \p{Lu}[\p{Ll}\p{M}]*){0,' . ($maxPalabras - 1) . '}$/uD';
    if (preg_match($patron, $valor) !== 1) {
        return false;
    }

    foreach (explode(" ", $valor) as $palabra) {
        if (preg_match_all('/\p{L}/u', $palabra) > 10) {
            return false;
        }
    }

    return true;
}

if (
    !nombreCompuestoValido($nombre, 2)
    || !nombreCompuestoValido($directorTecnico, 4)
    || !in_array($departamento, $departamentos, true)
    || $ciudad === ""
) {
    http_response_code(400);
    die("Revisa los datos: el club y el director técnico deben respetar el formato indicado y debes seleccionar un departamento de Uruguay.");
}

$consulta = mysqli_prepare(
    $conn,
    "INSERT INTO clubes (nombre, ciudad, director_tecnico, departamento) VALUES (?, ?, ?, ?)"
);
if (!$consulta) {
    die("No se pudo preparar el registro del club.");
}

mysqli_stmt_bind_param($consulta, "ssss", $nombre, $ciudad, $directorTecnico, $departamento);
if (!mysqli_stmt_execute($consulta)) {
    mysqli_stmt_close($consulta);
    die("No se pudo guardar el club.");
}

mysqli_stmt_close($consulta);
header("Location: clubes.php");
exit();