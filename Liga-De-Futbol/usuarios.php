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
    <title>Usuarios</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Usuarios Registrados</h2>

<div class="contenedor">

<a href="registrar_usuario.php">
    <button>➕ Registrar Usuario</button>
</a>

<br><br>

<table>

<tr>
    <th>Usuario</th>
    <th>Rol</th>
    <th>Club</th>
    <th>Acciones</th>
</tr>

<?php

$sql="

SELECT
usuarios.*,
clubes.nombre AS club

FROM usuarios

LEFT JOIN clubes
ON usuarios.club_id=clubes.id

ORDER BY usuarios.usuario

";

$consulta=mysqli_query($conn,$sql);

while($fila=mysqli_fetch_assoc($consulta)){
?>

<tr>

<td><?php echo $fila["usuario"]; ?></td>

<td><?php echo ucfirst($fila["rol"]); ?></td>

<td>

<?php

if($fila["club"]!=""){
    echo $fila["club"];
}else{
    echo "-";
}

?>

<td>

<a href="cambiar_password_usuario.php?id=<?php echo $fila["id"]; ?>">

<button>
🔒 Contraseña
</button>

</a>

<?php if($fila["id"] != $_SESSION["id"]){ ?>

<a href="eliminar_usuario.php?id=<?php echo $fila["id"]; ?>"
onclick="return confirm('¿Eliminar este usuario?');">

<button>
🗑 Eliminar
</button>

</a>

<?php } ?>

</td>

</tr>

<?php } ?>

</table>

</div>

</body>
</html>
