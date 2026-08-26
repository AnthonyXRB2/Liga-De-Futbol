<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

include("conexion.php");

$where="";

if($_SESSION["rol"]=="club"){

    $club=$_SESSION["club_id"];

    $where="WHERE jugadores.club_id='$club'";

}

$sql="

SELECT

documentos.*,
jugadores.nombre

FROM documentos

INNER JOIN jugadores
ON documentos.jugador_id=jugadores.id

$where

ORDER BY jugadores.nombre

";

$consulta=mysqli_query($conn,$sql);

?>

<!DOCTYPE html>

<html>

<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Documentos</title>

<link rel="stylesheet" href="estilo.css">

</head>

<body>

<?php include("menu.php"); ?>

<h2>Documentos de los Jugadores</h2>

<div class="contenedor">

<table>

<tr>

<th>Jugador</th>

<th>Ficha Médica</th>

<th>Fecha Subida</th>

<th>Carnet</th>

<th>Vencimiento</th>

<th>Fecha Subida</th>

</tr>

<?php while($fila=mysqli_fetch_assoc($consulta)){ ?>

<tr>

<td><?php echo $fila["nombre"]; ?></td>

<td>

<a href="<?php echo $fila["ficha_medica"]; ?>" target="_blank">

Ver Ficha

</a>

</td>

<td><?php echo $fila["fecha_subida_ficha"]; ?></td>

<td>

<a href="<?php echo $fila["carnet_salud"]; ?>" target="_blank">

Ver Carnet

</a>

</td>

<td><?php echo $fila["vencimiento_carnet"]; ?></td>

<td><?php echo $fila["fecha_subida_carnet"]; ?></td>

</tr>

<?php } ?>

</table>

</div>

</body>

</html>
