<?php

session_start();

if(!isset($_SESSION["usuario"])){
    header("Location: login.php");
    exit();
}

if($_SESSION["rol"]!="admin"){
    die("Acceso denegado.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: comunicados.php');
    exit();
}

include('conexion.php');

$titulo = trim($_POST['titulo'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');
$id = (int) ($_POST['id'] ?? 0);

// Validación sencilla
if ($titulo !== "" && $mensaje !== "") {
    if ($id > 0) {
        $sentencia = mysqli_prepare($conn, 'UPDATE comunicados SET titulo = ?, mensaje = ? WHERE id = ?');
        mysqli_stmt_bind_param($sentencia, 'ssi', $titulo, $mensaje, $id);
    } else {
        $sentencia = mysqli_prepare($conn, 'INSERT INTO comunicados (titulo, mensaje) VALUES (?, ?)');
        mysqli_stmt_bind_param($sentencia, 'ss', $titulo, $mensaje);
    }
    mysqli_stmt_execute($sentencia);
    mysqli_stmt_close($sentencia);

    header("Location: comunicados.php?" . ($id > 0 ? "editado=1" : "creado=1"));
    exit();

} else {
    header("Location: " . ($id > 0 ? "editar_comunicado.php?id=" . $id . "&error=1" : "agregar_comunicado.php?error=1"));
    exit();
}

?>
