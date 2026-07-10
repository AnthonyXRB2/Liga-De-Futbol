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
    <title>Registrar Tarjeta</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Registrar Tarjeta</h2>

<form action="guardar_tarjeta.php" method="POST">

<label>Jugador</label><br>
<select name="jugador_id">

<?php
$jugadores = mysqli_query($conn, "SELECT * FROM jugadores");

while($j = mysqli_fetch_assoc($jugadores)){
?>

<option value="<?php echo $j["id"]; ?>">
    <?php echo $j["nombre"]; ?>
</option>

<?php
}
?>

</select>

<br><br>

<label>Partido</label><br>
<select name="partido_id">

<?php
$partidos = mysqli_query($conn, "SELECT * FROM partidos");

while($p = mysqli_fetch_assoc($partidos)){
?>

<option value="<?php echo $p["id"]; ?>">
    Partido <?php echo $p["id"]; ?>
</option>

<?php
}
?>

</select>

<br><br>

<label>Tipo de tarjeta</label><br>

<select name="tipo">
    <option value="Amarilla">Amarilla</option>
    <option value="Roja">Roja</option>
</select>

<br><br>

<label>Motivo</label><br>
<input type="text" name="motivo">

<br><br>

<button type="submit">Guardar Tarjeta</button>

</form>

</body>
</html>