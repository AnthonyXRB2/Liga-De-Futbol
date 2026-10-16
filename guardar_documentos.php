<?php
session_start();
include("restringir_club.php");

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

if (($_SESSION["rol"] ?? "") !== "club") {
    http_response_code(403);
    die("Solo los clubes pueden subir fichas médicas.");
}

require_once("conexion.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: subir_documentos.php");
    exit();
}

$clubId = (int) ($_SESSION["club_id"] ?? 0);
$jugadorId = (int) ($_POST["jugador_id"] ?? 0);

if ($clubId <= 0 || $jugadorId <= 0) {
    header("Location: subir_documentos.php?error=" . urlencode("Selecciona un jugador de tu club."));
    exit();
}

$jugadorStmt = mysqli_prepare($conn, "SELECT id FROM jugadores WHERE id = ? AND club_id = ?");
mysqli_stmt_bind_param($jugadorStmt, "ii", $jugadorId, $clubId);
mysqli_stmt_execute($jugadorStmt);
mysqli_stmt_store_result($jugadorStmt);
$jugadorExiste = mysqli_stmt_num_rows($jugadorStmt) === 1;
mysqli_stmt_close($jugadorStmt);
if (!$jugadorExiste) {
    http_response_code(403);
    die("No puedes actualizar la ficha de un jugador que no pertenece a tu club.");
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

try {
    $fechaActual = date("Y-m-d H:i:s");
    $buscarDocumento = mysqli_prepare($conn, "SELECT id FROM documentos WHERE jugador_id = ? ORDER BY id DESC LIMIT 1");
    mysqli_stmt_bind_param($buscarDocumento, "i", $jugadorId);
    mysqli_stmt_execute($buscarDocumento);
    $documentos = mysqli_stmt_get_result($buscarDocumento);
    $documentoExistente = mysqli_fetch_assoc($documentos);
    mysqli_stmt_close($buscarDocumento);

    if ($documentoExistente) {
        $actualizar = mysqli_prepare($conn, "UPDATE documentos SET ficha_medica = ?, fecha_subida_ficha = ? WHERE id = ?");
        mysqli_stmt_bind_param($actualizar, "ssi", $rutaRelativa, $fechaActual, $documentoExistente["id"]);
        mysqli_stmt_execute($actualizar);
        mysqli_stmt_close($actualizar);
    } else {
        $insertar = mysqli_prepare($conn, "INSERT INTO documentos (jugador_id, ficha_medica, fecha_subida_ficha) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($insertar, "iss", $jugadorId, $rutaRelativa, $fechaActual);
        mysqli_stmt_execute($insertar);
        mysqli_stmt_close($insertar);
    }
} catch (Throwable $error) {
    unlink($rutaCompleta);
    header("Location: subir_documentos.php?error=" . urlencode("No se pudo guardar la ficha médica. Inténtalo nuevamente."));
    exit();
}

header("Location: subir_documentos.php?ok=" . urlencode("La ficha médica se guardó correctamente."));
exit();

?>