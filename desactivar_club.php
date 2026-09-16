<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

if (($_SESSION['rol'] ?? '') !== 'admin') {
    die('Acceso denegado.');
}

include('conexion.php');

$id = $_GET['id'] ?? null;

if ($id === null || !is_numeric($id)) {
    header('Location: clubes.php');
    exit();
}

$resultado = mysqli_query($conn, "SELECT activo FROM clubes WHERE id = $id LIMIT 1");

if ($resultado && mysqli_num_rows($resultado) > 0) {
    $club = mysqli_fetch_assoc($resultado);
    $nuevoEstado = ($club['activo'] == 1) ? 0 : 1;
    mysqli_query($conn, "UPDATE clubes SET activo = $nuevoEstado WHERE id = $id");
}

header('Location: clubes.php');
exit();
