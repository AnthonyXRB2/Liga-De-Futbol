<?php
// Valida la sesión y elimina el comunicado seleccionado por administración.
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
    header('Location: comunicados.php');
    exit();
}

$id = (int)$id;
// Ejecuta la eliminación y vuelve a la lista de comunicados.
mysqli_query($conn, "DELETE FROM comunicados WHERE id = $id");

header('Location: comunicados.php');
exit();
