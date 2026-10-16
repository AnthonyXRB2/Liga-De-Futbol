<?php
session_start();

include("conexion.php");

$usuario = $_POST["usuario"] ?? '';
$password = $_POST["password"] ?? '';

$stmt = $conn->prepare("SELECT usuarios.id, usuarios.usuario, usuarios.rol, usuarios.club_id, clubes.activo FROM usuarios LEFT JOIN clubes ON clubes.id = usuarios.club_id WHERE usuarios.usuario = ? AND usuarios.password = ?");
$stmt->bind_param("ss", $usuario, $password);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado && $resultado->num_rows > 0) {
    $datos = $resultado->fetch_assoc();

    if ($datos["rol"] === "club" && (int) ($datos["activo"] ?? 0) !== 1) {
        $_SESSION = [];
        $_SESSION['error_login'] = 'El club ha sido suspendido.';
        header('Location: login.php');
        exit();
    }

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