<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

include("conexion.php");

$rol = $_SESSION["rol"] ?? "";
$mensaje = $_GET["error"] ?? "";
$exito = $_GET["ok"] ?? "";
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subir ficha médica</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Subir ficha médica</h2>

<?php if ($mensaje !== "") { ?>
    <p class="mensaje-error"><?php echo htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8"); ?></p>
<?php } ?>

<?php if ($exito !== "") { ?>
    <p class="mensaje-exito"><?php echo htmlspecialchars($exito, ENT_QUOTES, "UTF-8"); ?></p>
<?php } ?>

<form action="guardar_documentos.php" method="POST" enctype="multipart/form-data">

<?php if ($rol === "admin") { ?>
<label>Club</label><br>

<select name="club_id" required>
    <option value="">Seleccionar club</option>

<?php
$clubes = mysqli_query($conn, "SELECT id, nombre FROM clubes WHERE activo = 1 ORDER BY nombre");

while ($club = mysqli_fetch_assoc($clubes)) {
?>

<option value="<?php echo (int) $club["id"]; ?>">
    <?php echo htmlspecialchars($club["nombre"], ENT_QUOTES, "UTF-8"); ?>
</option>

<?php } ?>

</select>

<br><br>
<?php } ?>

<label>Imagen de la ficha médica</label><br>
<input type="file" name="ficha_medica" accept="image/jpeg,image/png,image/webp" required>

<br><br>

<button type="submit">Analizar y crear jugador</button>

</form>

</body>
</html>
