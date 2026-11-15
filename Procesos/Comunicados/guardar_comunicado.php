<?php
require_once __DIR__ . '/../../Includes/rutas.php';

// Autoriza la edición o publicación y limita el procesamiento a POST.
session_start();

if(!isset($_SESSION["usuario"])){
    header('Location: ' . app_url('Paginas/Ingreso-y-Usuarios/login.php'));
    exit();
}

if($_SESSION["rol"]!="admin"){
    die("Acceso denegado.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . app_url('Paginas/Comunicados/comunicados.php'));
    exit();
}

require_once __DIR__ . '/../../Includes/conexion.php';

$titulo = trim($_POST['titulo'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');
$id = (int) ($_POST['id'] ?? 0);

// Validación sencilla
if ($titulo !== "" && $mensaje !== "") {
    // Un identificador existente actualiza; sin él se crea un comunicado.
    if ($id > 0) {
        $sentencia = mysqli_prepare($conn, 'UPDATE comunicados SET titulo = ?, mensaje = ? WHERE id = ?');
        mysqli_stmt_bind_param($sentencia, 'ssi', $titulo, $mensaje, $id);
    } else {
        $sentencia = mysqli_prepare($conn, 'INSERT INTO comunicados (titulo, mensaje, fecha_publicacion) VALUES (?, ?, CURRENT_TIMESTAMP)');
        mysqli_stmt_bind_param($sentencia, 'ss', $titulo, $mensaje);
    }
    mysqli_stmt_execute($sentencia);
    mysqli_stmt_close($sentencia);

    header('Location: ' . app_url('Paginas/Comunicados/comunicados.php') . '?' . ($id > 0 ? 'editado=1' : 'creado=1'));
    exit();

} else {
    $destino = $id > 0
        ? app_url('Paginas/Comunicados/editar_comunicado.php') . '?id=' . $id . '&error=1'
        : app_url('Paginas/Comunicados/agregar_comunicado.php') . '?error=1';
    header('Location: ' . $destino);
    exit();
}

?>
