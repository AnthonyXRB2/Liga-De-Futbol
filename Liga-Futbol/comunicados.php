<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

include("conexion.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Comunicados</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Comunicados de la Liga</h2>

<?php if ($_SESSION["rol"] == "admin") { ?>

<a href="agregar_comunicado.php">
    <button>➕ Agregar Comunicado</button>
</a>

<br><br>

<?php } ?>

<?php

$consulta = mysqli_query($conn, "
SELECT *
FROM comunicados
ORDER BY id DESC
");

while($fila = mysqli_fetch_assoc($consulta)){
?>

<div style="border:1px solid gray; padding:15px; margin-bottom:15px; border-radius:8px; background:#f8f8f8;">

    <h3><?php echo $fila["titulo"]; ?></h3>

    <p><?php echo nl2br($fila["mensaje"]); ?></p>

</div>

<?php
}
?>

</body>
</html>