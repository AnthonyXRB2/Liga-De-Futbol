<?php
// Verifica que solo un administrador gestione claves de otras cuentas.
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION["rol"] != "admin") {
    echo "<h2>Acceso denegado</h2>";
    exit();
}

include("conexion.php");
require_once(__DIR__ . "/password_storage.php");

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

// Carga el nombre de la cuenta que se va a actualizar.
$consulta = $conn->prepare("SELECT usuario FROM usuarios WHERE id = ?");
$consulta->bind_param("i", $id);
$consulta->execute();
$usuarioSeleccionado = $consulta->get_result()->fetch_assoc();

if (!$usuarioSeleccionado) {
    http_response_code(404);
    exit("Usuario no encontrado.");
}

if(isset($_POST["guardar"])){

    // Valida y almacena la nueva contraseña con el formato protegido.
    $password = $_POST["password"] ?? "";

    if ($password === "") {
        http_response_code(400);
        exit("La contraseña no puede estar vacía.");
    }

    $passwordHash = hash_user_password($password);
    $actualizar = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
    $actualizar->bind_param("si", $passwordHash, $id);
    $actualizar->execute();

    header("Location: usuarios.php");
    exit();
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

<!-- Identifica la cuenta objetivo antes de capturar la nueva clave. -->
<p><b>Usuario:</b> <?php echo $usuarioSeleccionado["usuario"]; ?></p>

<form method="POST">

<label>Nueva contraseña</label><br>

<input type="password" name="password" required>

<br><br>

<button type="submit" name="guardar">

Guardar

</button>

</form>

</body>
</html>
