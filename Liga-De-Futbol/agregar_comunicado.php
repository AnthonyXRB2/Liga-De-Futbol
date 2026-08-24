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
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agregar Comunicado</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Agregar Comunicado</h2>

<?php if (isset($_GET['error'])) { ?>
    <p class="mensaje-error">Complet&aacute; el t&iacute;tulo y el mensaje.</p>
<?php } ?>

<form action="guardar_comunicado.php" method="POST">

    <label>Título</label><br>
    <input type="text" name="titulo" required>

    <br><br>

    <label>Mensaje</label><br>
    <textarea name="mensaje" rows="5" cols="50" required></textarea>

    <br><br>

    <button type="submit">Guardar Comunicado</button>

</form>

</body>
</html>
