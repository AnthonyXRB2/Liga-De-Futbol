<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

if (($_SESSION['rol'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Acceso denegado.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: usuarios.php');
    exit();
}

require_once('conexion.php');

$usuarioId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
$nombreUsuario = $_POST['usuario'] ?? null;
if (!is_string($nombreUsuario)) {
    http_response_code(400);
    exit('Ingresa un nombre de usuario válido.');
}
$nombreUsuario = trim($nombreUsuario);

if ($usuarioId === false || $usuarioId === null ||
    $nombreUsuario === '' || strlen($nombreUsuario) > 50 ||
    preg_match('/[\x00-\x1F\x7F]/', $nombreUsuario)) {
    http_response_code(400);
    exit('Ingresa un nombre de usuario válido.');
}

$actual = $conn->prepare('SELECT rol FROM usuarios WHERE id = ?');
if (!$actual) {
    http_response_code(500);
    exit('No se pudo comprobar el usuario.');
}
$actual->bind_param('i', $usuarioId);
if (!$actual->execute()) {
    $actual->close();
    http_response_code(500);
    exit('No se pudo comprobar el usuario.');
}
$usuarioActual = $actual->get_result()->fetch_assoc();
$actual->close();
if (!$usuarioActual) {
    http_response_code(404);
    exit('Usuario no encontrado.');
}
if ($usuarioActual['rol'] === 'admin') {
    http_response_code(403);
    exit('Las cuentas administrador solo permiten cambiar la contraseña.');
}

$duplicado = $conn->prepare('SELECT id FROM usuarios WHERE usuario = ? AND id <> ? LIMIT 1');
if (!$duplicado) {
    http_response_code(500);
    exit('No se pudo validar el nombre de usuario.');
}
$duplicado->bind_param('si', $nombreUsuario, $usuarioId);
if (!$duplicado->execute()) {
    $duplicado->close();
    http_response_code(500);
    exit('No se pudo validar el nombre de usuario.');
}
$nombreDuplicado = $duplicado->get_result()->num_rows > 0;
$duplicado->close();
if ($nombreDuplicado) {
    header('Location: editar_usuario.php?id=' . $usuarioId . '&error=duplicado');
    exit();
}

if ($usuarioActual['rol'] === 'club') {
    $clubId = filter_input(INPUT_POST, 'club_id', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);
    if ($clubId === false || $clubId === null) {
        header('Location: editar_usuario.php?id=' . $usuarioId . '&error=club');
        exit();
    }

    $conn->begin_transaction();
    $club = $conn->prepare('SELECT id FROM clubes WHERE id = ? AND activo = 1 FOR UPDATE');
    if (!$club) {
        $conn->rollback();
        http_response_code(500);
        exit('No se pudo validar el club.');
    }
    $club->bind_param('i', $clubId);
    if (!$club->execute()) {
        $club->close();
        $conn->rollback();
        http_response_code(500);
        exit('No se pudo validar el club.');
    }
    $clubValido = $club->get_result()->num_rows === 1;
    $club->close();

    $ocupacion = $conn->prepare('SELECT id FROM usuarios WHERE club_id = ? AND id <> ? LIMIT 1');
    if (!$ocupacion) {
        $conn->rollback();
        http_response_code(500);
        exit('No se pudo validar la asignación del club.');
    }
    $ocupacion->bind_param('ii', $clubId, $usuarioId);
    if (!$ocupacion->execute()) {
        $ocupacion->close();
        $conn->rollback();
        http_response_code(500);
        exit('No se pudo validar la asignación del club.');
    }
    $clubOcupado = $ocupacion->get_result()->num_rows > 0;
    $ocupacion->close();

    if (!$clubValido || $clubOcupado) {
        $conn->rollback();
        header('Location: editar_usuario.php?id=' . $usuarioId . '&error=club');
        exit();
    }

    $actualizar = $conn->prepare('UPDATE usuarios SET usuario = ?, club_id = ? WHERE id = ?');
    if (!$actualizar) {
        $conn->rollback();
        http_response_code(500);
        exit('No se pudo actualizar el usuario.');
    }
    $actualizar->bind_param('sii', $nombreUsuario, $clubId, $usuarioId);
} else {
    $actualizar = $conn->prepare('UPDATE usuarios SET usuario = ? WHERE id = ?');
    if (!$actualizar) {
        http_response_code(500);
        exit('No se pudo actualizar el usuario.');
    }
    $actualizar->bind_param('si', $nombreUsuario, $usuarioId);
}

if (!$actualizar->execute()) {
    $actualizar->close();
    if ($usuarioActual['rol'] === 'club') {
        $conn->rollback();
    }
    http_response_code(500);
    exit('No se pudo actualizar el usuario.');
}
$actualizar->close();

if ($usuarioActual['rol'] === 'club') {
    $conn->commit();
}
if ((int) ($_SESSION['id'] ?? 0) === $usuarioId) {
    $_SESSION['usuario'] = $nombreUsuario;
}

header('Location: usuarios.php');
exit();
