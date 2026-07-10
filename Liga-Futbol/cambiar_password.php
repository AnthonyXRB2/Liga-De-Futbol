<?php
session_start();
if(!isset($_SESSION["usuario"])){
    header("Location: login.php");
    exit();
}

include("conexion.php");

if (isset($_POST["guardar"])) {

    $nueva = $_POST["nueva"];

    $id = $_SESSION["id"];

    $sql = "UPDATE usuarios
            SET password='$nueva'
            WHERE id='$id'";

    mysqli_query($conn, $sql);

    echo "<p>Contraseña actualizada correctamente.</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cambiar Contraseña</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Cambiar Contraseña</h2>

<form method="POST">

    <label>Nueva contraseña</label><br>
    <input type="password" name="nueva" required>

    <br><br>

    <button type="submit" name="guardar">
        Guardar
    </button>

</form>

</body>
</html>