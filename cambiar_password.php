<?php
// Limita este formulario de cambio de clave a administradores autenticados.
session_start();
include('restringir_club.php');
if(!isset($_SESSION["usuario"])){
    header("Location: login.php");
    exit();
}

if (($_SESSION["rol"] ?? "") !== "admin") {
    http_response_code(403);
    die("Solo el administrador puede cambiar su contraseña.");
}

require_once("conexion.php");
require_once(__DIR__ . "/password_storage.php");

if (isset($_POST["guardar"])) {

    // Protege la nueva clave y guarda el cambio en la cuenta actual.
    $nueva = $_POST["nueva"] ?? "";

    if ($nueva === "") {
        http_response_code(400);
        die("La contraseña no puede estar vacía.");
    }

    $nuevaHash = hash_user_password($nueva);
    $id = (int) $_SESSION["id"];

    $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $nuevaHash, $id);
    $stmt->execute();

    echo "<p>Contraseña actualizada correctamente.</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cambiar Contraseña</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Cambiar Contraseña</h2>

<!-- Solicita una nueva clave al administrador. -->
<form method="POST">

    <label>Nueva contraseña</label><br>
    <input type="password" name="nueva" required>

    <br><br>

    <button type="submit" name="guardar">
        Guardar
    </button>

</form>

</body>
</html>
