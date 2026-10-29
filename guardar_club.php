<?php

// Autoriza la operación y define los departamentos válidos del país.
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
$fechaFundacion = $_POST["fecha_fundacion"] ?? "";
$id = $_POST["id"] ?? null;

// Rechaza tipos de entrada inesperados antes de validar los campos.
if (
    !is_string($nombre)
    || !is_string($directorTecnico)
    || !is_string($departamento)
    || !is_string($fechaFundacion)
    || ($id !== null && (!is_string($id) || !ctype_digit($id) || (int) $id < 1))
) {
    http_response_code(400);
    die("Los datos enviados no son válidos.");
}

$departamento = trim($departamento);
$fechaFundacion = trim($fechaFundacion);

function fechaFundacionValida(string $fecha): bool
{
    // Limita la fecha al formato ISO y al intervalo permitido por el sitio.
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/D', $fecha, $partes) !== 1) {
        return false;
    }

    $anio = (int) $partes[1];
    $mes = (int) $partes[2];
    $dia = (int) $partes[3];

    return $anio >= 2000
        && $anio <= 2032
        && checkdate($mes, $dia, $anio)
        && $fecha <= "2032-12-31";
}

function nombreCompuestoValido(string $valor, int $maxPalabras): bool
{
    // Verifica mayúsculas iniciales y longitud de cada palabra.
    $patron = '/^\p{Lu}[\p{Ll}\p{M}]*(?: \p{Lu}[\p{Ll}\p{M}]*){0,' . ($maxPalabras - 1) . '}$/uD';
    if (preg_match($patron, $valor) !== 1) {
        return false;
    }

    foreach (explode(" ", $valor) as $palabra) {
        $cantidadLetras = preg_match_all('/\p{L}/u', $palabra);
        if ($cantidadLetras < 4 || $cantidadLetras > 10) {
            return false;
        }
    }

    return true;
}

if (
    !nombreCompuestoValido($nombre, 2)
    || !nombreCompuestoValido($directorTecnico, 4)
    || !in_array($departamento, $departamentos, true)
    || !fechaFundacionValida($fechaFundacion)
) {
    http_response_code(400);
    die("Revisa los datos: el club y el director técnico deben respetar el formato indicado, selecciona un departamento de Uruguay y proporciona una fecha de fundación válida desde el 01/01/2000.");
}

$consulta = $id === null
    // Inserta un club nuevo o actualiza el existente.
    ? mysqli_prepare(
        $conn,
        "INSERT INTO clubes (nombre, fecha_fundacion, director_tecnico, departamento) VALUES (?, ?, ?, ?)"
    )
    : mysqli_prepare(
        $conn,
        "UPDATE clubes SET nombre = ?, fecha_fundacion = ?, director_tecnico = ?, departamento = ? WHERE id = ?"
    );
if (!$consulta) {
    die($id === null ? "No se pudo preparar el registro del club." : "No se pudo preparar la actualización del club.");
}

if ($id === null) {
    mysqli_stmt_bind_param($consulta, "ssss", $nombre, $fechaFundacion, $directorTecnico, $departamento);
} else {
    $id = (int) $id;
    mysqli_stmt_bind_param($consulta, "ssssi", $nombre, $fechaFundacion, $directorTecnico, $departamento, $id);
}
if (!mysqli_stmt_execute($consulta)) {
    mysqli_stmt_close($consulta);
    die($id === null ? "No se pudo guardar el club." : "No se pudo actualizar el club.");
}

mysqli_stmt_close($consulta);
header("Location: clubes.php");
exit();