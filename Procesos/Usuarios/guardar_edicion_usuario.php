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
    header('Location: ' . app_url('Paginas/Ingreso-y-Usuarios/usuarios.php'));
    exit();
}

require_once __DIR__ . '/../../Includes/conexion.php';
require_once __DIR__ . '/../../Includes/password_storage.php';

$usuarioId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
$nombreUsuario = $_POST['usuario'] ?? null;
$password = $_POST['password'] ?? '';

if ($usuarioId === false || $usuarioId === null) {
    http_response_code(400);
    exit('Usuario no válido.');
}
if (!is_string($password)) {
    http_response_code(400);
    exit('Contraseña no válida.');
}

$actual = $conn->prepare('SELECT id, rol FROM usuarios WHERE id = ?');
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
    if ($password === '') {
        header('Location: ' . app_url('Paginas/Ingreso-y-Usuarios/usuarios.php') . '?edit=' . $usuarioId . '&error=contrasena');
        exit();
    }

    $passwordHash = hash_user_password($password);
    $actualizar = $conn->prepare('UPDATE usuarios SET password = ? WHERE id = ?');
    if (!$actualizar) {
        http_response_code(500);
        exit('No se pudo actualizar la contraseña.');
    }
    $actualizar->bind_param('si', $passwordHash, $usuarioId);
    if (!$actualizar->execute()) {
        $actualizar->close();
        http_response_code(500);
        exit('No se pudo actualizar la contraseña.');
    }
    $actualizar->close();

    header('Location: ' . app_url('Paginas/Ingreso-y-Usuarios/usuarios.php') . '?ok=1');
    exit();
}

$cantidadCaracteres = is_string($nombreUsuario)
    ? preg_match_all('/./us', $nombreUsuario)
    : false;
$nombreValido = is_string($nombreUsuario)
    && $cantidadCaracteres !== false
    && $cantidadCaracteres <= 43
    && preg_match('/\A\p{Lu}[\p{Ll}\p{M}]*(?: \p{Lu}[\p{Ll}\p{M}]*){0,3}\z/u', $nombreUsuario) === 1;
if ($nombreValido) {
    $palabras = explode(' ', $nombreUsuario);
    foreach ($palabras as $palabra) {
        $cantidadLetras = preg_match_all('/\p{L}/u', $palabra);
        if ($cantidadLetras === false || $cantidadLetras < 4 || $cantidadLetras > 10) {
            $nombreValido = false;
            break;
        }
    }
}
if (!$nombreValido) {
    header('Location: ' . app_url('Paginas/Ingreso-y-Usuarios/usuarios.php') . '?edit=' . $usuarioId . '&error=nombre');
    exit();
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
    header('Location: ' . app_url('Paginas/Ingreso-y-Usuarios/usuarios.php') . '?edit=' . $usuarioId . '&error=duplicado');
    exit();
}

$passwordHash = $password === '' ? null : hash_user_password($password);
$actualizar = $passwordHash === null
    ? $conn->prepare('UPDATE usuarios SET usuario = ? WHERE id = ?')
    : $conn->prepare('UPDATE usuarios SET usuario = ?, password = ? WHERE id = ?');
if (!$actualizar) {
    http_response_code(500);
    exit('No se pudo actualizar el usuario.');
}
if ($passwordHash === null) {
    $actualizar->bind_param('si', $nombreUsuario, $usuarioId);
} else {
    $actualizar->bind_param('ssi', $nombreUsuario, $passwordHash, $usuarioId);
}
if (!$actualizar->execute()) {
    $actualizar->close();
    http_response_code(500);
    exit('No se pudo actualizar el usuario.');
}
$actualizar->close();

if ((int) ($_SESSION['id'] ?? 0) === $usuarioId) {
    $_SESSION['usuario'] = $nombreUsuario;
}

header('Location: ' . app_url('Paginas/Ingreso-y-Usuarios/usuarios.php') . '?ok=1');
exit();
