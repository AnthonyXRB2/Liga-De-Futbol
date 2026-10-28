<?php
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

$usuario = trim($_POST["usuario"] ?? "");
$password = $_POST["password"] ?? "";
$club_id = $_POST["club_id"] ?? "";

$existe = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ?");
$existe->bind_param("s", $usuario);
$existe->execute();
$resultado = $existe->get_result();

if ($usuario === "" || $password === "" || $resultado->num_rows > 0) {

    echo "<h2>" . ($resultado->num_rows > 0 ? "Ese usuario ya existe." : "Completa todos los campos.") . "</h2>";
    echo "<br>";
    echo "<a href='registrar_usuario.php'>Volver</a>";
    exit();

}

$passwordHash = hash_user_password($password);
$stmt = $conn->prepare("INSERT INTO usuarios (usuario, password, rol, club_id) VALUES (?, ?, 'club', ?)");
$stmt->bind_param("sss", $usuario, $passwordHash, $club_id);
$stmt->execute();

header("Location: registrar_usuario.php?ok=1");
exit();
?>