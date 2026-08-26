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
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrar Usuario</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Registrar Usuario</h2>

<form action="guardar_usuario.php" method="POST">

<label>Usuario</label><br>
<input type="text" name="usuario" required>

<br><br>

<label>Contraseña</label><br>
<input type="password" name="password" required>

<br><br>

<label>Club</label><br>

<select name="club_id">

<?php

$clubes = mysqli_query($conn,"
SELECT *
FROM clubes
WHERE activo=1
ORDER BY nombre
");

while($club=mysqli_fetch_assoc($clubes)){
?>

<option value="<?php echo $club["id"]; ?>">
<?php echo $club["nombre"]; ?>
</option>

<?php } ?>

</select>

<br><br>

<button type="submit">
Registrar Usuario
</button>

</form>

</body>
</html>
