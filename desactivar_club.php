<?php
// Autoriza el cambio de estado y valida el identificador recibido.
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

if (($_SESSION['rol'] ?? '') !== 'admin') {
    die('Acceso denegado.');
}

include('conexion.php');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: clubes.php');
    exit();
}

$consulta = mysqli_prepare($conn, 'SELECT activo FROM clubes WHERE id = ? LIMIT 1');
// Invierte el estado entre activo e inactivo cuando el club existe.
mysqli_stmt_bind_param($consulta, 'i', $id);
mysqli_stmt_execute($consulta);
$resultado = mysqli_stmt_get_result($consulta);
$club = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($consulta);

if ($club) {
    $nuevoEstado = (int) $club['activo'] === 1 ? 0 : 1;
    $actualizar = mysqli_prepare($conn, 'UPDATE clubes SET activo = ? WHERE id = ?');
    mysqli_stmt_bind_param($actualizar, 'ii', $nuevoEstado, $id);
    mysqli_stmt_execute($actualizar);
    mysqli_stmt_close($actualizar);
}

header('Location: clubes.php');
exit();
