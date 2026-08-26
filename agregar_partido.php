<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION["rol"] !== "admin") {
    echo "<h2>Acceso denegado</h2>";
    exit();
}

include("conexion.php");
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agregar Partido</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Agregar Partido</h2>

<form action="guardar_partido.php" method="POST">

<label>Club Local</label><br>
<select name="local">
<?php
$clubes = mysqli_query($conn, "SELECT * FROM clubes WHERE activo = 1");
while($club = mysqli_fetch_assoc($clubes)){
?>
<option value="<?php echo $club["id"]; ?>">
    <?php echo $club["nombre"]; ?>
</option>
<?php } ?>
</select>

<br><br>

<label>Club Visitante</label><br>
<select name="visitante">
<?php
$clubes2 = mysqli_query($conn, "SELECT * FROM clubes WHERE activo = 1");
while($club2 = mysqli_fetch_assoc($clubes2)){
?>
<option value="<?php echo $club2["id"]; ?>">
    <?php echo $club2["nombre"]; ?>
</option>
<?php } ?>
</select>

<br><br>

<label>Fecha</label><br>
<input type="date" name="fecha">

<br><br>

<label>Estado</label><br>
<select name="estado">
    <option>Programado</option>
    <option>Sin fecha</option>
    <option>Pendiente</option>
    <option>Suspendido</option>
    <option>Jugado</option>
</select>

<br><br>

<label>Motivo de suspensión (si corresponde)</label><br>
<input type="text" name="motivo_suspension">

<br><br>

<label>Goles del Local</label><br>
<input type="number" name="goles_local" value="0">

<br><br>

<label>Goles del Visitante</label><br>
<input type="number" name="goles_visitante" value="0">

<br><br>

<button type="submit">Guardar Partido</button>

</form>

</body>
</html>
