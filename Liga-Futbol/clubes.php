<?php
session_start();
include("conexion.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Clubes</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Lista de Clubes</h2>

<div class="contenedor">

<?php if($_SESSION["rol"]=="admin"){ ?>

<a href="agregar_club.php">
    <button>➕ Agregar Club</button>
</a>

<br><br>

<?php } ?>

<table>

<tr>
    <th>Nombre</th>
    <th>Ciudad</th>
    <th>Estado</th>

<?php if($_SESSION["rol"]=="admin"){ ?>
    <th>Acciones</th>
<?php } ?>

</tr>

<?php

$consulta = mysqli_query($conn, "SELECT * FROM clubes ORDER BY nombre");

while($club = mysqli_fetch_assoc($consulta)){
?>

<tr>

    <td><?php echo $club["nombre"]; ?></td>

    <td><?php echo $club["ciudad"]; ?></td>

    <td>
        <?php
        if($club["activo"] == 1){
            echo "🟢 Activo";
        }else{
            echo "🔴 Inactivo";
        }
        ?>
    </td>

<?php if($_SESSION["rol"]=="admin"){ ?>

    <td>

        <a href="desactivar_club.php?id=<?php echo $club["id"]; ?>">
            <button>

            <?php
            if($club["activo"] == 1){
                echo "Desactivar";
            }else{
                echo "Activar";
            }
            ?>

            </button>
        </a>

    </td>

<?php } ?>

</tr>

<?php
}
?>

</table>

</div>

</body>
</html>