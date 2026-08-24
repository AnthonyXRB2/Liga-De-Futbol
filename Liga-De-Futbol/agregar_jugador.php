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
    <title>Agregar Jugador</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Agregar Jugador</h2>

<form action="guardar_jugador.php" method="POST">

<label>Nombre</label><br>
<input type="text" name="nombre"><br><br>

<label>Edad</label><br>
<input type="number" name="edad"><br><br>

<label>Posición</label><br>
<select name="posicion">
    <option>Arquero</option>
    <option>Defensa</option>
    <option>Mediocampista</option>
    <option>Delantero</option>
</select>

<br><br>

<label>Club</label><br>

<select name="club_id">

<?php

$clubes = mysqli_query($conn, "SELECT * FROM clubes");

while($club = mysqli_fetch_assoc($clubes)){
?>

<option value="<?php echo $club["id"]; ?>">
    <?php echo $club["nombre"]; ?>
</option>

<?php
}
?>

</select>

<br><br>

<label>CI</label><br>
<input type="text" name="ci"><br><br>

<label>Categoría</label><br>
<input type="text" name="categoria" placeholder="Ej: 2013, 2015, Femenino"><br><br>

<button type="submit">Guardar</button>

</form>

</body>
</html>
