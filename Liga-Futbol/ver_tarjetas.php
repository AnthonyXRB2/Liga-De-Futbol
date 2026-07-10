<?php
session_start();

if(!isset($_SESSION["usuario"])){
    header("Location: login.php");
    exit();
}

include("conexion.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tarjetas</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Listado de Tarjetas</h2>

<table border="1" cellpadding="5">

<tr>
    <th>Jugador</th>
    <th>Partido</th>
    <th>Tipo</th>
    <th>Motivo</th>
    <th>Fecha</th>
</tr>

<?php

$sql = "
SELECT
    t.*,
    j.nombre AS jugador,
    cl.nombre AS local_nombre,
    cv.nombre AS visitante_nombre
FROM tarjetas t
INNER JOIN jugadores j ON t.jugador_id = j.id
INNER JOIN partidos p ON t.partido_id = p.id
INNER JOIN clubes cl ON p.local_id = cl.id
INNER JOIN clubes cv ON p.visitante_id = cv.id
ORDER BY t.fecha_registro DESC
";

$consulta = mysqli_query($conn, $sql);

while($fila = mysqli_fetch_assoc($consulta)){
?>

<tr>

<td><?php echo $fila["jugador"]; ?></td>

<td><?php echo $fila["local_nombre"] . " vs " . $fila["visitante_nombre"]; ?>

<td><?php echo $fila["tipo"]; ?></td>

<td><?php echo $fila["motivo"]; ?></td>

<td><?php echo $fila["fecha_registro"]; ?></td>

</tr>

<?php
}
?>

</table>

</body>
</html>