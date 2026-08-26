<?php
session_start();
include("conexion.php");

$busqueda = "";
$categoria = "";

if (isset($_GET["buscar"])) {
    $busqueda = $_GET["buscar"];
}

if (isset($_GET["categoria"])) {
    $categoria = $_GET["categoria"];
}

$condiciones = [];

if (isset($_SESSION["rol"]) && $_SESSION["rol"] == "club") {
    $club = $_SESSION["club_id"];
    $condiciones[] = "jugadores.club_id = '$club'";
}

if ($busqueda != "") {
    $condiciones[] = "(jugadores.nombre LIKE '%$busqueda%' OR jugadores.ci LIKE '%$busqueda%')";
}

if ($categoria != "") {
    $condiciones[] = "jugadores.categoria = '$categoria'";
}

$where = "";

if (count($condiciones) > 0) {
    $where = "WHERE " . implode(" AND ", $condiciones);
}

$sql = "
SELECT
jugadores.*,
clubes.nombre AS club,
documentos.id AS documento

FROM jugadores

LEFT JOIN clubes
ON jugadores.club_id = clubes.id

LEFT JOIN documentos
ON jugadores.id = documentos.jugador_id

$where

ORDER BY jugadores.nombre
";

$consulta = mysqli_query($conn,$sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jugadores</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Lista de Jugadores</h2>

<div class="contenedor">

<?php if($_SESSION["rol"]=="admin"){ ?>

<a href="agregar_jugador.php">
<button>➕ Agregar Jugador</button>
</a>

<?php } ?>

<br><br>

<form method="GET">

<label>Buscar por Nombre o CI</label><br>

<input type="text" name="buscar" value="<?php echo $busqueda; ?>">

<br><br>

<label>Categoría</label><br>

<select name="categoria">

<option value="">Todas</option>

<option value="2013" <?php if($categoria=="2013") echo "selected"; ?>>2013</option>

<option value="2014" <?php if($categoria=="2014") echo "selected"; ?>>2014</option>

<option value="2015" <?php if($categoria=="2015") echo "selected"; ?>>2015</option>

<option value="Femenino" <?php if($categoria=="Femenino") echo "selected"; ?>>Femenino</option>

</select>

<br><br>

<button type="submit">Buscar / Filtrar</button>

</form>

<br>

<table>

<tr>

<th>Nombre</th>
<th>Edad</th>
<th>Posición</th>
<th>CI</th>
<th>Categoría</th>
<th>Club</th>

<?php if($_SESSION["rol"]=="admin"){ ?>

<th>Documentación</th>

<?php } ?>

</tr>

<?php while($fila=mysqli_fetch_assoc($consulta)){ ?>

<tr>

<td><?php echo $fila["nombre"]; ?></td>

<td><?php echo $fila["edad"]; ?></td>

<td><?php echo $fila["posicion"]; ?></td>

<td><?php echo $fila["ci"]; ?></td>

<td><?php echo $fila["categoria"]; ?></td>

<td><?php echo $fila["club"]; ?></td>

<?php if($_SESSION["rol"]=="admin"){ ?>

<td>

<?php

if($fila["documento"]){

echo "🟢";

}else{

echo "🔴";

}

?>

</td>

<?php } ?>

</tr>

<?php } ?>

</table>

</div>

</body>
</html>
