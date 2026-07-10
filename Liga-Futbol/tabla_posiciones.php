<?php
session_start();

if(!isset($_SESSION["usuario"])){
    header("Location: login.php");
    exit();
}

include("conexion.php");

$clubes = mysqli_query($conn, "SELECT * FROM clubes WHERE activo = 1");

$tabla = [];

while($club = mysqli_fetch_assoc($clubes)){

    $id = $club["id"];

    $tabla[$id] = [
        "nombre" => $club["nombre"],
        "pj" => 0,
        "pg" => 0,
        "pe" => 0,
        "pp" => 0,
        "gf" => 0,
        "gc" => 0,
        "pts" => 0
    ];
}

$partidos = mysqli_query($conn, "SELECT * FROM partidos WHERE estado='Jugado'");

while($p = mysqli_fetch_assoc($partidos)){

    $local = $p["local_id"];
    $visitante = $p["visitante_id"];

    $gl = $p["goles_local"];
    $gv = $p["goles_visitante"];

    $tabla[$local]["pj"]++;
    $tabla[$visitante]["pj"]++;

    $tabla[$local]["gf"] += $gl;
    $tabla[$local]["gc"] += $gv;

    $tabla[$visitante]["gf"] += $gv;
    $tabla[$visitante]["gc"] += $gl;

    if($gl > $gv){

        $tabla[$local]["pg"]++;
        $tabla[$local]["pts"] += 3;

        $tabla[$visitante]["pp"]++;

    }elseif($gl < $gv){

        $tabla[$visitante]["pg"]++;
        $tabla[$visitante]["pts"] += 3;

        $tabla[$local]["pp"]++;

    }else{

        $tabla[$local]["pe"]++;
        $tabla[$visitante]["pe"]++;

        $tabla[$local]["pts"]++;
        $tabla[$visitante]["pts"]++;

    }
}

usort($tabla, function($a, $b){

    if($a["pts"] == $b["pts"]){
        return ($b["gf"] - $b["gc"]) - ($a["gf"] - $a["gc"]);
    }

    return $b["pts"] - $a["pts"];
});

?>

<!DOCTYPE html>
<html>
<head>
    <title>Tabla de Posiciones</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Tabla de Posiciones</h2>

<table border="1" cellpadding="5">

<th>Club</th>
<th>Jugados</th>
<th>Ganados</th>
<th>Empates</th>
<th>Perdidos</th>
<th>Goles a Favor</th>
<th>Goles en Contra</th>
<th>Dif. Gol</th>
<th>Puntos</th>

<?php foreach($tabla as $fila){ ?>

<tr>

<td><?php echo $fila["nombre"]; ?></td>
<td><?php echo $fila["pj"]; ?></td>
<td><?php echo $fila["pg"]; ?></td>
<td><?php echo $fila["pe"]; ?></td>
<td><?php echo $fila["pp"]; ?></td>
<td><?php echo $fila["gf"]; ?></td>
<td><?php echo $fila["gc"]; ?></td>
<td><?php echo $fila["gf"] - $fila["gc"]; ?></td>
<td><?php echo $fila["pts"]; ?></td>

</tr>

<?php } ?>

</table>

</body>
</html>