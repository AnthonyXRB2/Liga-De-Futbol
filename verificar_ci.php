<?php
// Prepara una respuesta JSON para validar la cédula desde los formularios.
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['disponible' => false, 'error' => 'Debes iniciar sesión.']);
    exit();
}
if (($_SESSION['rol'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['disponible' => false, 'error' => 'Acceso denegado.']);
    exit();
}

$ci = $_GET['ci'] ?? '';
// Acepta solo cédulas numéricas con la longitud prevista.
if (!is_string($ci) || !preg_match('/^[0-9]{1,8}$/D', $ci)) {
    http_response_code(400);
    echo json_encode(['disponible' => false, 'error' => 'La cédula debe contener entre 1 y 8 dígitos.']);
    exit();
}

require_once('conexion.php');
// Comprueba si ya existe un jugador con la cédula indicada.
$consulta = mysqli_prepare($conn, 'SELECT id FROM jugadores WHERE ci = ? LIMIT 1');
if (!$consulta) {
    http_response_code(500);
    echo json_encode(['disponible' => false, 'error' => 'No se pudo verificar la cédula.']);
    exit();
}
mysqli_stmt_bind_param($consulta, 's', $ci);
if (!mysqli_stmt_execute($consulta)) {
    http_response_code(500);
    echo json_encode(['disponible' => false, 'error' => 'No se pudo verificar la cédula.']);
    exit();
}
mysqli_stmt_store_result($consulta);
$disponible = mysqli_stmt_num_rows($consulta) === 0;
mysqli_stmt_close($consulta);
echo json_encode(['disponible' => $disponible]);
