<?php
session_start();
include("restringir_club.php");

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

require_once("conexion.php");

$rol = $_SESSION["rol"] ?? "";
if ($rol !== "club") {
    http_response_code(403);
    die("Solo los clubes pueden subir fichas médicas.");
}
$mensaje = $_GET["error"] ?? "";
$exito = $_GET["ok"] ?? "";
$clubId = (int) ($_SESSION["club_id"] ?? 0);

$jugadoresStmt = mysqli_prepare($conn, "SELECT id, nombre, ci FROM jugadores WHERE club_id = ? ORDER BY nombre");
mysqli_stmt_bind_param($jugadoresStmt, "i", $clubId);
mysqli_stmt_execute($jugadoresStmt);
$jugadores = mysqli_stmt_get_result($jugadoresStmt);
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

<h2>Actualizar documentación de un jugador</h2>

<p class="ayuda">Elige un jugador de tu club y carga su ficha médica. Si aún no aparece en la lista, solicita al administrador que lo registre.</p>

<?php if ($mensaje !== "") { ?>
    <p class="mensaje-error"><?php echo htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8"); ?></p>
<?php } ?>

<?php if ($exito !== "") { ?>
    <p class="mensaje-exito"><?php echo htmlspecialchars($exito, ENT_QUOTES, "UTF-8"); ?></p>
<?php } ?>

<?php if ($jugadores && mysqli_num_rows($jugadores) > 0) { ?>
    <form action="guardar_documentos.php" method="POST" enctype="multipart/form-data">
        <label for="jugador_id">Jugador</label>
        <select id="jugador_id" name="jugador_id" required>
            <option value="">Selecciona un jugador</option>
            <?php while ($jugador = mysqli_fetch_assoc($jugadores)) { ?>
                <option value="<?php echo (int) $jugador["id"]; ?>">
                    <?php echo htmlspecialchars($jugador["nombre"] . " — CI " . $jugador["ci"], ENT_QUOTES, "UTF-8"); ?>
                </option>
            <?php } ?>
        </select>

        <label for="ficha_medica">Imagen de la ficha médica</label>
        <input id="ficha_medica" type="file" name="ficha_medica" accept="image/jpeg,image/png,image/webp" required>
        <p class="ayuda">Formatos permitidos: JPG, PNG o WEBP. Tamaño máximo: 10 MB.</p>
        <button type="submit">Guardar ficha médica</button>
    </form>
<?php } else { ?>
    <p class="aviso">Tu club todavía no tiene jugadores registrados.</p>
<?php } ?>

</body>
</html>
