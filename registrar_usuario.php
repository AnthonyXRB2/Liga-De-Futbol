<?php
// Restringe el alta de cuentas al administrador autenticado.
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
    <title>Registrar Usuario</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Registrar Usuario</h2>

<?php if (($_GET["error"] ?? "") === "club_ocupado") { ?>
<p class="mensaje-error">Este club ya tiene un usuario asociado.</p>
<?php } elseif (($_GET["error"] ?? "") === "club_invalido") { ?>
<p class="mensaje-error">Selecciona un club activo.</p>
<?php } ?>

<!-- Formulario para crear una cuenta vinculada a un club activo. -->
<form action="guardar_usuario.php" method="POST">

<label>Usuario</label><br>
<input type="text" name="usuario" required>

<br><br>

<label>Contraseña</label><br>
<input type="password" name="password" required>

<br><br>

<label>Club</label><br>

<select name="club_id" required>
<option value="" selected disabled>Selecciona un club</option>

<?php

$clubes = mysqli_query($conn,"
SELECT
    clubes.id,
    clubes.nombre,
    EXISTS(
        SELECT 1
        FROM usuarios
        WHERE usuarios.club_id = clubes.id
    ) AS tiene_usuario
FROM clubes
WHERE clubes.activo = 1
ORDER BY clubes.nombre
");

// Muestra los clubes activos e indica cuales ya tienen una cuenta asociada.
while($club=mysqli_fetch_assoc($clubes)){
?>

<option value="<?php echo (int) $club["id"]; ?>" <?php echo $club["tiene_usuario"] ? "disabled" : ""; ?>>
<?php echo htmlspecialchars($club["nombre"], ENT_QUOTES, "UTF-8"); ?><?php echo $club["tiene_usuario"] ? " (este club ya tiene un usuario asociado)" : ""; ?>
</option>

<?php } ?>

</select>

<br><br>

<button type="submit">
Registrar Usuario
</button>

</form>

</body>
</html>
