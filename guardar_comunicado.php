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

// Validación sencilla
if ($titulo != "" && $mensaje != "") {

    $sentencia = mysqli_prepare($conn, 'INSERT INTO comunicados (titulo, mensaje) VALUES (?, ?)');
    mysqli_stmt_bind_param($sentencia, 'ss', $titulo, $mensaje);
    mysqli_stmt_execute($sentencia);
    mysqli_stmt_close($sentencia);

    header("Location: comunicados.php?creado=1");
    exit();

} else {

    echo "Tenés que completar todos los campos.";

}

?>
