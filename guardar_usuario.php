<?php
// Valida permisos y carga funciones para proteger la contraseña nueva.
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
$club_id = (int) ($_POST["club_id"] ?? 0);

// Evita registrar nombres de usuario duplicados o campos incompletos.
if ($usuario === "" || $password === "" || $club_id <= 0) {
    echo "<h2>Completa todos los campos.</h2>";
    echo "<br>";
    echo "<a href='registrar_usuario.php'>Volver</a>";
    exit();
}

$conn->begin_transaction();

$club = $conn->prepare("SELECT id FROM clubes WHERE id = ? AND activo = 1 FOR UPDATE");
$club->bind_param("i", $club_id);
$club->execute();
$clubValido = $club->get_result()->num_rows === 1;
$club->close();

if (!$clubValido) {
    $conn->rollback();
    header("Location: registrar_usuario.php?error=club_invalido");
    exit();
}

$clubOcupado = $conn->prepare("SELECT id FROM usuarios WHERE club_id = ? LIMIT 1");
$clubOcupado->bind_param("i", $club_id);
$clubOcupado->execute();
$yaTieneUsuario = $clubOcupado->get_result()->num_rows > 0;
$clubOcupado->close();

if ($yaTieneUsuario) {
    $conn->rollback();
    header("Location: registrar_usuario.php?error=club_ocupado");
    exit();
}

$existe = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ?");
$existe->bind_param("s", $usuario);
$existe->execute();
$usuarioDuplicado = $existe->get_result()->num_rows > 0;
$existe->close();

if ($usuarioDuplicado) {
    $conn->rollback();
    echo "<h2>Ese usuario ya existe.</h2>";
    echo "<br>";
    echo "<a href='registrar_usuario.php'>Volver</a>";
    exit();
}

// Guarda la cuenta asociada al club con su clave almacenada como hash.
$passwordHash = hash_user_password($password);
$stmt = $conn->prepare("INSERT INTO usuarios (usuario, password, rol, club_id) VALUES (?, ?, 'club', ?)");
$stmt->bind_param("ssi", $usuario, $passwordHash, $club_id);
$stmt->execute();
$conn->commit();

header("Location: registrar_usuario.php?ok=1");
exit();
?>