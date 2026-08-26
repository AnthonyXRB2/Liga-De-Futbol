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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subir Documentos</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Subir Ficha Médica y Carnet de Salud</h2>

<form action="guardar_documentos.php" method="POST" enctype="multipart/form-data">

<label>Jugador</label><br>

<select name="jugador_id">

<?php

if($_SESSION["rol"]=="admin"){

    $consulta=mysqli_query($conn,"
        SELECT *
        FROM jugadores
        ORDER BY nombre
    ");

}else{

    $club=$_SESSION["club_id"];

    $consulta=mysqli_query($conn,"
        SELECT *
        FROM jugadores
        WHERE club_id='$club'
        ORDER BY nombre
    ");

}

while($fila=mysqli_fetch_assoc($consulta)){
?>

<option value="<?php echo $fila["id"]; ?>">
    <?php echo $fila["nombre"]; ?>
</option>

<?php } ?>

</select>

<br><br>

<label>Foto de la ficha médica</label><br>
<input type="file" name="ficha_medica">

<br><br>

<label>Foto del carnet de salud</label><br>
<input type="file" name="carnet_salud">

<br><br>

<label>Fecha de vencimiento del carnet</label><br>
<input type="date" name="vencimiento_carnet">

<br><br>

<button type="submit">Guardar Documentos</button>

</form>

</body>
</html>
