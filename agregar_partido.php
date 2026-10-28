<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION["rol"] !== "admin") {
    echo "<h2>Acceso denegado</h2>";
    exit();
}

include("conexion.php");

$torneos = [
    "estandar" => "Estándar",
    "plata" => "Copa Plata",
    "oro" => "Copa Oro"
];
$claveSolicitada = $_GET["torneo"] ?? "estandar";
$claveTorneo = is_string($claveSolicitada) && isset($torneos[$claveSolicitada])
    ? $claveSolicitada
    : "estandar";
$categoriaSeleccionada = $torneos[$claveTorneo];
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agregar Partido</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Agregar Partido</h2>

<form action="guardar_partido.php" method="POST">
<?php if (($_GET["origen"] ?? "") === "tabla") { ?>
<input type="hidden" name="volver_tabla" value="1">
<?php } ?>

<label>Club Local</label><br>
<select name="local">
<?php
$clubes = mysqli_query($conn, "SELECT * FROM clubes WHERE activo = 1");
while($club = mysqli_fetch_assoc($clubes)){
?>
<option value="<?php echo $club["id"]; ?>">
    <?php echo $club["nombre"]; ?>
</option>
<?php } ?>
</select>

<br><br>

<label>Club Visitante</label><br>
<select name="visitante">
<?php
$clubes2 = mysqli_query($conn, "SELECT * FROM clubes WHERE activo = 1");
while($club2 = mysqli_fetch_assoc($clubes2)){
?>
<option value="<?php echo $club2["id"]; ?>">
    <?php echo $club2["nombre"]; ?>
</option>
<?php } ?>
</select>

<br><br>

<label>Fecha</label><br>
<input type="date" name="fecha" required>

<br><br>

<label>Competencia</label><br>
<select name="categoria" required>
    <?php foreach ($torneos as $categoria) { ?>
        <option value="<?php echo htmlspecialchars($categoria, ENT_QUOTES, "UTF-8"); ?>"
            <?php echo $categoria === $categoriaSeleccionada ? "selected" : ""; ?>>
            <?php echo htmlspecialchars($categoria, ENT_QUOTES, "UTF-8"); ?>
        </option>
    <?php } ?>
</select>

<br><br>

<label>Estado</label><br>
<select name="estado">
    <option>Programado</option>
    <option>Sin fecha</option>
    <option>Pendiente</option>
    <option>Suspendido</option>
    <option>Jugado</option>
</select>

<br><br>

<label>Motivo de suspensión (si corresponde)</label><br>
<input type="text" name="motivo_suspension">

<br><br>

<label>Goles del Local</label><br>
<input type="number" name="goles_local" value="0">

<br><br>

<label>Goles del Visitante</label><br>
<input type="number" name="goles_visitante" value="0">

<br><br>

<button type="submit">Guardar Partido</button>

</form>

</body>
</html>
