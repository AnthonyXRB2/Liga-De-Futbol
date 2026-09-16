<?php

include("conexion.php");
include("procesar_ficha_medica.php");

session_start();

if(!isset($_SESSION["usuario"])){
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: subir_documentos.php");
    exit();
}

$rol = $_SESSION["rol"] ?? "";
$clubId = $rol === "club" ? (int) ($_SESSION["club_id"] ?? 0) : (int) ($_POST["club_id"] ?? 0);

if ($clubId <= 0) {
    header("Location: subir_documentos.php?error=" . urlencode("Debes seleccionar un club válido."));
    exit();
}

if (!isset($_FILES["ficha_medica"]) || $_FILES["ficha_medica"]["error"] !== UPLOAD_ERR_OK) {
    header("Location: subir_documentos.php?error=" . urlencode("Debes seleccionar una imagen válida."));
    exit();
}

$archivo = $_FILES["ficha_medica"];
if ($archivo["size"] > 10 * 1024 * 1024) {
    header("Location: subir_documentos.php?error=" . urlencode("La imagen no puede superar los 10 MB."));
    exit();
}

$mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo["tmp_name"]);
$extensiones = ["image/jpeg" => "jpg", "image/png" => "png", "image/webp" => "webp"];
if (!isset($extensiones[$mime]) || @getimagesize($archivo["tmp_name"]) === false) {
    header("Location: subir_documentos.php?error=" . urlencode("La ficha debe ser una imagen JPG, PNG o WEBP."));
    exit();
}

try {
    $texto = ejecutar_ocr_ficha($archivo["tmp_name"]);
    $datos = extraer_datos_ficha($texto);
} catch (Throwable $error) {
    header("Location: subir_documentos.php?error=" . urlencode($error->getMessage()));
    exit();
}

if ($datos["nombre"] === "" || strlen($datos["ci"]) < 5) {
    header("Location: subir_documentos.php?error=" . urlencode("No se pudieron leer el nombre y el CI. Toma una foto más nítida y vuelve a intentarlo."));
    exit();
}

$carpeta = __DIR__ . DIRECTORY_SEPARATOR . "uploads";
if (!is_dir($carpeta) && !mkdir($carpeta, 0755, true)) {
    header("Location: subir_documentos.php?error=" . urlencode("No se pudo crear la carpeta de documentos."));
    exit();
}

$nombreArchivo = "ficha_" . date("YmdHis") . "_" . bin2hex(random_bytes(8)) . "." . $extensiones[$mime];
$rutaRelativa = "uploads/" . $nombreArchivo;
$rutaCompleta = $carpeta . DIRECTORY_SEPARATOR . $nombreArchivo;

if (!move_uploaded_file($archivo["tmp_name"], $rutaCompleta)) {
    header("Location: subir_documentos.php?error=" . urlencode("No se pudo guardar la imagen."));
    exit();
}

$fecha_actual = date("Y-m-d H:i:s");
$consulta = mysqli_prepare($conn, "SELECT id FROM jugadores WHERE club_id = ? AND ci = ? LIMIT 1");
mysqli_stmt_bind_param($consulta, "is", $clubId, $datos["ci"]);
mysqli_stmt_execute($consulta);
mysqli_stmt_store_result($consulta);
if (mysqli_stmt_num_rows($consulta) > 0) {
    mysqli_stmt_close($consulta);
    unlink($rutaCompleta);
    header("Location: subir_documentos.php?error=" . urlencode("Ya existe un jugador con ese CI en el club."));
    exit();
}
mysqli_stmt_close($consulta);

mysqli_begin_transaction($conn);
try {
    $jugador = mysqli_prepare($conn, "INSERT INTO jugadores (nombre, edad, posicion, club_id, ci, categoria) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($jugador, "sisiss", $datos["nombre"], $datos["edad"], $datos["posicion"], $clubId, $datos["ci"], $datos["categoria"]);
    mysqli_stmt_execute($jugador);
    $jugadorId = mysqli_insert_id($conn);
    mysqli_stmt_close($jugador);

    $documento = mysqli_prepare($conn, "INSERT INTO documentos (jugador_id, ficha_medica, fecha_subida_ficha, carnet_salud, vencimiento_carnet, fecha_subida_carnet) VALUES (?, ?, ?, '', '', '')");
    mysqli_stmt_bind_param($documento, "iss", $jugadorId, $rutaRelativa, $fecha_actual);
    mysqli_stmt_execute($documento);
    mysqli_stmt_close($documento);
    mysqli_commit($conn);
} catch (Throwable $error) {
    mysqli_rollback($conn);
    unlink($rutaCompleta);
    header("Location: subir_documentos.php?error=" . urlencode("No se pudo crear el jugador: " . $error->getMessage()));
    exit();
}

$mensaje = "Jugador creado: " . $datos["nombre"] . ". CI: " . $datos["ci"];
header("Location: subir_documentos.php?ok=" . urlencode($mensaje));
exit();

?>