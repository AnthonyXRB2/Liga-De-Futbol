<?php
require_once __DIR__ . '/../../Includes/rutas.php';
// Limita este formulario al cambio de contraseña de la cuenta autenticada.
session_start();
require_once __DIR__ . '/../../Includes/restringir_club.php';
if(!isset($_SESSION["usuario"])){
    header('Location: ' . app_url('Paginas/Ingreso-y-Usuarios/login.php'));
    exit();
}

if (!in_array($_SESSION["rol"] ?? "", ["admin", "club"], true)) {
    http_response_code(403);
    die("No tienes permiso para cambiar esta contraseña.");
}

require_once __DIR__ . '/../../Includes/conexion.php';
require_once __DIR__ . '/../../Includes/password_storage.php';

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
    <link rel="stylesheet" href="<?php echo app_url('Assets-Css/estilo.css'); ?>">
</head>
<body>

<?php require_once __DIR__ . '/../../Includes/menu.php'; ?>

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
