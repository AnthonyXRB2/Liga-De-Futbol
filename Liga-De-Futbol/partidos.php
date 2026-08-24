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
    <title>Partidos</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Lista de Partidos</h2>

<?php if ($_SESSION["rol"] == "admin") { ?>

<a href="agregar_partido.php">
    <button>Agregar Partido</button>
</a>

<?php } ?>

<br><br>

<div class="contenedor">
<table border="1" cellpadding="5">

<tr>
    <th>Local</th>
    <th>Visitante</th>
    <th>Fecha</th>
    <th>Estado</th>
    <th>Motivo Suspensión</th>
    <th>Resultado</th>
</tr>

<?php

$sql = "
SELECT
    p.*,
    c1.nombre AS nombre_local,
    c2.nombre AS nombre_visitante
FROM partidos p
INNER JOIN clubes c1 ON p.local_id = c1.id
INNER JOIN clubes c2 ON p.visitante_id = c2.id
ORDER BY p.fecha DESC
";

$consulta = mysqli_query($conn, $sql);

while($fila = mysqli_fetch_assoc($consulta)){
?>

<tr>

    <td><?php echo $fila["nombre_local"]; ?></td>

    <td><?php echo $fila["nombre_visitante"]; ?></td>

    <td><?php echo $fila["fecha"]; ?></td>

    <td><?php echo $fila["estado"]; ?></td>

    <td><?php echo $fila["motivo_suspension"]; ?></td>

    <td>
        <?php echo $fila["goles_local"] . " - " . $fila["goles_visitante"]; ?>
    </td>

</tr>

<?php
}
?>

</table>
</div>

</body>
</html>
