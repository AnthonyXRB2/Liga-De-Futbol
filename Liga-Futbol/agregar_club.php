<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION["rol"] != "admin") {
    echo "<h2>Acceso denegado</h2>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Agregar Club</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Agregar Club</h2>

<form action="guardar_club.php" method="POST">

    <label>Nombre del club</label><br>
    <input type="text" name="nombre" required>

    <br><br>

    <label>Ciudad</label><br>
    <input type="text" name="ciudad" required>

    <br><br>

    <button type="submit">Guardar</button>

</form>

</body>
</html>