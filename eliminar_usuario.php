<?php
// Autoriza la eliminación y evita que el administrador se quite a sí mismo.
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION["rol"] != "admin") {
    die("Acceso denegado.");
}

include("conexion.php");

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

header("Location: usuarios.php");
exit();
?>