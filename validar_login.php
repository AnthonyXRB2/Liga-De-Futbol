<?php
session_start();

include("conexion.php");

$usuario = $_POST["usuario"] ?? '';
$password = $_POST["password"] ?? '';

$stmt = $conn->prepare("SELECT id, usuario, rol, club_id FROM usuarios WHERE usuario = ? AND password = ?");
$stmt->bind_param("ss", $usuario, $password);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado && $resultado->num_rows > 0) {
    $datos = $resultado->fetch_assoc();

    $_SESSION["id"] = $datos["id"];
    $_SESSION["usuario"] = $datos["usuario"];
    $_SESSION["rol"] = $datos["rol"];
    $_SESSION["club_id"] = $datos["club_id"];

    header("Location: index.php");
    exit();
}

$_SESSION['error_login'] = 'Usuario o contraseña incorrectos.';
header('Location: login.php');
exit();
?>