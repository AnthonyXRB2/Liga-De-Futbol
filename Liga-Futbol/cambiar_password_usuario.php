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

include("conexion.php");

$id = $_GET["id"];

$consulta = mysqli_query($conn,"
SELECT *
FROM usuarios
WHERE id='$id'
");

$usuario = mysqli_fetch_assoc($consulta);

if(isset($_POST["guardar"])){

    $password = $_POST["password"];

    mysqli_query($conn,"
    UPDATE usuarios
    SET password='$password'
    WHERE id='$id'
    ");

    header("Location: usuarios.php");
    exit();
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

<p><b>Usuario:</b> <?php echo $usuario["usuario"]; ?></p>

<form method="POST">

<label>Nueva contraseña</label><br>

<input type="password" name="password" required>

<br><br>

<button type="submit" name="guardar">

Guardar

</button>

</form>

</body>
</html>