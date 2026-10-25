<?php
session_start();
include('restringir_club.php');

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

$esAdmin = ($_SESSION['rol'] ?? '') === 'admin';
require_once("conexion.php");
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clubes</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Lista de Clubes</h2>

<div class="contenedor">

<?php if($esAdmin){ ?>

<a href="agregar_club.php">
    <button>➕ Agregar Club</button>
</a>

<br><br>

<?php } ?>

<table>

<tr>
    <th>Nombre</th>
    <th>Director técnico</th>
    <th>Departamento</th>
    <th>Ciudad</th>
    <th>Estado</th>

<?php if($esAdmin){ ?>
    <th>Acciones</th>
<?php } ?>

</tr>

<?php

$consulta = mysqli_query($conn, "SELECT * FROM clubes ORDER BY nombre");

while($club = mysqli_fetch_assoc($consulta)){
?>

<tr>

    <td><?php echo htmlspecialchars($club["nombre"], ENT_QUOTES, "UTF-8"); ?></td>

    <td><?php echo htmlspecialchars($club["director_tecnico"] ?? "", ENT_QUOTES, "UTF-8"); ?></td>

    <td><?php echo htmlspecialchars($club["departamento"] ?? "", ENT_QUOTES, "UTF-8"); ?></td>

    <td><?php echo htmlspecialchars($club["ciudad"], ENT_QUOTES, "UTF-8"); ?></td>

    <td>
        <?php
        if($club["activo"] == 1){
            echo "🟢 Activo";
        }else{
            echo "🔴 Inactivo";
        }
        ?>
    </td>

<?php if($esAdmin){ ?>

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
