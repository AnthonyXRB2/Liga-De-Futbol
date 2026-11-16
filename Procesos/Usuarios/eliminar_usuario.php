<?php
require_once __DIR__ . '/../../Includes/rutas.php';
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ' . app_url('Paginas/Ingreso-y-Usuarios/login.php'));
    exit();
}

if (($_SESSION['rol'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Acceso denegado.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
if ($id === false || $id === null) {
    http_response_code(400);
    exit('Usuario no válido.');
}

if ($id === (int) ($_SESSION['id'] ?? 0)) {
    http_response_code(403);
    exit('No podés eliminar tu propio usuario.');
}

require_once __DIR__ . '/../../Includes/conexion.php';

$eliminar = $conn->prepare("DELETE FROM usuarios WHERE id = ? AND rol <> 'admin'");
if (!$eliminar) {
    http_response_code(500);
    exit('No se pudo eliminar el usuario.');
}
$eliminar->bind_param('i', $id);
if (!$eliminar->execute()) {
    $eliminar->close();
    http_response_code(500);
    exit('No se pudo eliminar el usuario.');
}
if ($eliminar->affected_rows !== 1) {
    $eliminar->close();
    http_response_code(404);
    exit('Usuario no encontrado.');
}
$eliminar->close();

header('Location: ' . app_url('Paginas/Ingreso-y-Usuarios/usuarios.php'));
exit();
