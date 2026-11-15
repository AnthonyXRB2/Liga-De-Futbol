<?php
require_once __DIR__ . '/../../Includes/rutas.php';
// Autoriza la eliminación y evita que el administrador se quite a sí mismo.
session_start();

if (!isset($_SESSION["usuario"])) {
    header('Location: ' . app_url('Paginas/Ingreso-y-Usuarios/login.php'));
    exit();
}

if ($_SESSION["rol"] != "admin") {
    die("Acceso denegado.");
}

require_once __DIR__ . '/../../Includes/conexion.php';

$id = $_GET["id"];

// Evitar que el admin se elimine a sí mismo
if($id == $_SESSION["id"]){
    die("No podés eliminar tu propio usuario.");
}

// Elimina la cuenta seleccionada y vuelve al listado de usuarios.
mysqli_query($conn,"
DELETE FROM usuarios
WHERE id='$id'
");

header('Location: ' . app_url('Paginas/Ingreso-y-Usuarios/usuarios.php'));
exit();
?>