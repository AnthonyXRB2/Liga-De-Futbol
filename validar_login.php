<?php
// Inicializa la sesión y carga la conexión y las utilidades de contraseñas.
session_start();

include("conexion.php");
require_once(__DIR__ . "/password_storage.php");

$usuario = $_POST["usuario"] ?? '';
$password = $_POST["password"] ?? '';

// Busca la cuenta y el estado del club asociado para validar el acceso.
$stmt = $conn->prepare("SELECT usuarios.id, usuarios.usuario, usuarios.password, usuarios.rol, usuarios.club_id, clubes.activo FROM usuarios LEFT JOIN clubes ON clubes.id = usuarios.club_id WHERE usuarios.usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado && $resultado->num_rows > 0) {
    $datos = $resultado->fetch_assoc();

    // Verifica la clave y actualiza el formato almacenado si hace falta.
    if (verify_user_password($password, $datos["password"])) {
        if (user_password_needs_rehash($datos["password"])) {
            $passwordHash = hash_user_password($password);
            $actualizar = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
            $actualizar->bind_param("si", $passwordHash, $datos["id"]);
            $actualizar->execute();
        }

        // Impide que las cuentas de clubes suspendidos inicien sesión.
        if ($datos["rol"] === "club" && (int) ($datos["activo"] ?? 0) !== 1) {
            $_SESSION = [];
            $_SESSION['error_login'] = 'El club ha sido suspendido.';
            header('Location: login.php');
            exit();
        }

        // Guarda la identidad y el rol para las páginas protegidas.
        $_SESSION["id"] = $datos["id"];
        $_SESSION["usuario"] = $datos["usuario"];
        $_SESSION["rol"] = $datos["rol"];
        $_SESSION["club_id"] = $datos["club_id"];

        header("Location: index.php");
        exit();
    }
}

// Devuelve un mensaje genérico cuando el usuario o la clave no coinciden.
$_SESSION['error_login'] = 'Usuario o contraseña incorrectos.';
header('Location: login.php');
exit();
?>