<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

if (($_SESSION['rol'] ?? '') === 'club') {
    if (!isset($conn)) {
        include('conexion.php');
    }

    $clubId = (int) ($_SESSION['club_id'] ?? 0);
    $consultaClub = mysqli_prepare($conn, 'SELECT activo FROM clubes WHERE id = ? LIMIT 1');
    if (!$consultaClub) {
        http_response_code(500);
        die('No se pudo verificar el estado del club.');
    }
    mysqli_stmt_bind_param($consultaClub, 'i', $clubId);
    if (!mysqli_stmt_execute($consultaClub)) {
        http_response_code(500);
        die('No se pudo verificar el estado del club.');
    }
    $club = mysqli_stmt_get_result($consultaClub);
    $datosClub = mysqli_fetch_assoc($club);
    mysqli_stmt_close($consultaClub);

    if (!$datosClub || (int) $datosClub['activo'] !== 1) {
        $_SESSION = [];
        $_SESSION['error_login'] = 'El club ha sido suspendido.';
        header('Location: login.php');
        exit();
    }
}
