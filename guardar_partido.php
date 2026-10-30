<?php
// Autoriza el registro y recoge los datos del encuentro.
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION["rol"] != "admin") {
    die("Acceso denegado.");
}

include("conexion.php");

$local = filter_input(INPUT_POST, "local", FILTER_VALIDATE_INT);
$visitante = filter_input(INPUT_POST, "visitante", FILTER_VALIDATE_INT);
$fecha = $_POST["fecha"] ?? "";
$horaEntrada = $_POST["hora"] ?? "";
$categoria = $_POST["categoria"] ?? "";
$categoriaEdad = $_POST["categoria_edad"] ?? "";
$categoriasPermitidas = ["Estándar", "Copa Plata", "Copa Oro"];
$categoriasEdadPermitidas = array_merge(array_map("strval", range(2012, 2019)), ["Femenina"]);

$fechaValida = is_string($fecha) &&
    preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) &&
    checkdate((int) substr($fecha, 5, 2), (int) substr($fecha, 8, 2), (int) substr($fecha, 0, 4)) &&
    substr($fecha, 0, 4) === date("Y");
$horaValida = is_string($horaEntrada) &&
    preg_match('/^([1-9]|1[0-2]):([0-5][0-9])(A|P)m$/', trim($horaEntrada), $partesHora);
$categoriaEdadValida = is_string($categoriaEdad) &&
    in_array($categoriaEdad, $categoriasEdadPermitidas, true);

// Verifica el año de la fecha, el formato horario y los valores válidos.
if (!$fechaValida) {
    http_response_code(400);
    die("La fecha del partido debe pertenecer al año actual y ser válida.");
}

if ($local === false || $visitante === false || $local === null || $visitante === null ||
    !$horaValida || !in_array($categoria, $categoriasPermitidas, true) ||
    !$categoriaEdadValida) {
    http_response_code(400);
    die("Revisa los datos del partido e intenta nuevamente.");
}

[$coincidencia, $hora, $minutos, $periodo] = $partesHora;
$hora = (int) $hora % 12;
if ($periodo === "P") {
    $hora += 12;
}
$horaSql = sprintf("%02d:%s:00", $hora, $minutos);

// Evitar que un club juegue contra sí mismo
if ($local === $visitante) {
    die("El club local y el visitante no pueden ser el mismo.");
}

$sql = "INSERT INTO partidos
    (local_id, visitante_id, fecha, hora, estado, categoria, categoria_edad)
    VALUES (?, ?, ?, ?, 'Programado', ?, ?)";
$consulta = mysqli_prepare($conn, $sql);
// Inserta el partido programado y abre la competencia correspondiente.
if (!$consulta) {
    die("No se pudo preparar el registro del partido.");
}
mysqli_stmt_bind_param($consulta, "iissss", $local, $visitante, $fecha, $horaSql, $categoria, $categoriaEdad);
if (!mysqli_stmt_execute($consulta)) {
    mysqli_stmt_close($consulta);
    die("No se pudo guardar el partido.");
}
mysqli_stmt_close($consulta);

$torneos = [
    "Estándar" => "estandar",
    "Copa Plata" => "plata",
    "Copa Oro" => "oro",
];
header("Location: tabla_posiciones.php?torneo=" . $torneos[$categoria]);
exit();
?>