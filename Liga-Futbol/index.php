<?php
session_start();

if(!isset($_SESSION["usuario"])){
    header("Location: login.php");
    exit();
}

include("conexion.php");

if($_SESSION["rol"]=="admin"){

    $clubes = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM clubes WHERE activo=1"))["total"];
    $jugadores = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM jugadores"))["total"];
    $partidos = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM partidos"))["total"];
    $usuarios = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM usuarios"))["total"];
    $tarjetas = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM tarjetas"))["total"];
    $documentos = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM documentos"))["total"];
    $comunicados = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM comunicados"))["total"];

}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Inicio</title>
    <link rel="stylesheet" href="estilo.css">
</head>

<body>

<?php include("menu.php"); ?>

<h2>Bienvenido, <?php echo $_SESSION["usuario"]; ?></h2>

<p>Has iniciado sesión como <b><?php echo ucfirst($_SESSION["rol"]); ?></b>.</p>

<hr>

<?php if($_SESSION["rol"]=="admin"){ ?>

<div class="dashboard">

    <div class="card">
        <h3>⚽ Clubes</h3>
        <p><?php echo $clubes; ?></p>
    </div>

    <div class="card">
        <h3>👥 Jugadores</h3>
        <p><?php echo $jugadores; ?></p>
    </div>

    <div class="card">
        <h3>📅 Partidos</h3>
        <p><?php echo $partidos; ?></p>
    </div>

    <div class="card">
        <h3>👤 Usuarios</h3>
        <p><?php echo $usuarios; ?></p>
    </div>

    <div class="card">
        <h3>🟨 Tarjetas</h3>
        <p><?php echo $tarjetas; ?></p>
    </div>

    <div class="card">
        <h3>📄 Documentos</h3>
        <p><?php echo $documentos; ?></p>
    </div>

    <div class="card">
        <h3>📢 Comunicados</h3>
        <p><?php echo $comunicados; ?></p>
    </div>

</div>

<?php } ?>

</body>
</html>