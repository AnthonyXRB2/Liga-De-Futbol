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
    <title>Agregar Comunicado</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Agregar Comunicado</h2>

<form action="guardar_comunicado.php" method="POST">

    <label>Título</label><br>
    <input type="text" name="titulo" required>

    <br><br>

    <label>Mensaje</label><br>
    <textarea name="mensaje" rows="5" cols="50" required></textarea>

    <br><br>

    <button type="submit">Guardar Comunicado</button>

</form>

</body>
</html>