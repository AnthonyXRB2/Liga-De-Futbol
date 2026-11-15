<?php
require_once __DIR__ . '/../../Includes/rutas.php';
// Limita la eliminación a administradores y valida el jugador seleccionado.
session_start();

if (!isset($_SESSION["usuario"])) {
    header('Location: ' . app_url('Paginas/Ingreso-y-Usuarios/login.php'));
    exit();
}

if (($_SESSION["rol"] ?? "") !== "admin") {
    http_response_code(403);
    exit("Acceso denegado.");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Location: ' . app_url('Paginas/Jugadores/jugadores.php'));
    exit();
}

$jugadorId = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT, [
    "options" => ["min_range" => 1],
]);

if ($jugadorId === false || $jugadorId === null) {
    http_response_code(400);
    exit("Jugador no válido.");
}

require_once __DIR__ . '/../../Includes/conexion.php';

// Elimina dependencias y jugador en una sola transacción.
mysqli_begin_transaction($conn);

try {
    foreach ([
        "DELETE FROM documentos WHERE jugador_id = ?",
        "DELETE FROM tarjetas WHERE jugador_id = ?",
        "DELETE FROM jugadores WHERE id = ?",
    ] as $sql) {
        $consulta = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($consulta, "i", $jugadorId);
        mysqli_stmt_execute($consulta);

        if (str_starts_with($sql, "DELETE FROM jugadores") && mysqli_stmt_affected_rows($consulta) !== 1) {
            throw new RuntimeException("Jugador no encontrado.");
        }

        mysqli_stmt_close($consulta);
    }

    mysqli_commit($conn);
} catch (Throwable $error) {
    mysqli_rollback($conn);
    http_response_code(500);
    exit("No se pudo eliminar el jugador.");
}

header('Location: ' . app_url('Paginas/Jugadores/jugadores.php'));
exit();
?>