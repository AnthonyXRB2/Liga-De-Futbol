<?php
// Carga los documentos visibles para el rol y club de la sesión.
session_start();
include("restringir_club.php");

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

require_once("conexion.php");
$rol = $_SESSION["rol"] ?? "";
if ($rol !== "club" && $rol !== "admin") {
    http_response_code(403);
    die("No tienes permiso para consultar documentos.");
}

$esAdmin = $rol === "admin";
$clubId = 0;
$categoria = $_GET["categoria"] ?? "";
$categoriasPermitidas = array_merge(array_map("strval", range(2013, 2019)), ["Femenina"]);

if (!is_string($categoria) || ($categoria !== "" && !in_array($categoria, $categoriasPermitidas, true))) {
    http_response_code(400);
    die("El filtro de categoría no es válido.");
}

if ($esAdmin) {
    $filtroRecibido = $_GET["club_id"] ?? "";
    if (!is_string($filtroRecibido) || ($filtroRecibido !== "" && !ctype_digit($filtroRecibido))) {
        http_response_code(400);
        die("El filtro de club no es válido.");
    }
    $clubId = $filtroRecibido === "" ? 0 : (int) $filtroRecibido;
} else {
    $clubId = (int) ($_SESSION["club_id"] ?? 0);
    if ($clubId <= 0) {
        http_response_code(403);
        die("No se pudo determinar el club de la sesión.");
    }
}

$sql = "
    SELECT documentos.*, jugadores.nombre, jugadores.categoria, clubes.id AS club_id, clubes.nombre AS nombre_club
    FROM documentos
    INNER JOIN jugadores ON documentos.jugador_id = jugadores.id
    INNER JOIN clubes ON jugadores.club_id = clubes.id
";

$condiciones = [];
if ($clubId > 0) {
    $condiciones[] = "jugadores.club_id = ?";
}
if ($categoria !== "") {
    $condiciones[] = "jugadores.categoria = ?";
}
if ($condiciones) {
    $sql .= " WHERE " . implode(" AND ", $condiciones);
}

$sql .= " ORDER BY clubes.nombre, jugadores.nombre";

$consultaStmt = mysqli_prepare($conn, $sql);
if (!$consultaStmt) {
    http_response_code(500);
    die("No se pudieron consultar los documentos.");
}

if ($clubId > 0 && $categoria !== "") {
    mysqli_stmt_bind_param($consultaStmt, "is", $clubId, $categoria);
} elseif ($clubId > 0) {
    mysqli_stmt_bind_param($consultaStmt, "i", $clubId);
} elseif ($categoria !== "") {
    mysqli_stmt_bind_param($consultaStmt, "s", $categoria);
}

if (!mysqli_stmt_execute($consultaStmt)) {
    http_response_code(500);
    die("No se pudieron consultar los documentos.");
}

$consulta = mysqli_stmt_get_result($consultaStmt);

$clubes = null;
if ($esAdmin) {
    $clubes = mysqli_query($conn, "SELECT id, nombre FROM clubes ORDER BY nombre");
    if (!$clubes) {
        http_response_code(500);
        die("No se pudieron consultar los clubes.");
    }
}

?>

<!DOCTYPE html>

<html>

<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Documentos De Los Jugadores</title>

<link rel="stylesheet" href="estilo.css">

</head>

<body>

<?php include("menu.php"); ?>

<h2>Documentos De Los Jugadores</h2>

<!-- Presenta la ficha médica y sus fechas de expedición y vencimiento. -->
<div class="contenedor">

<form method="GET" action="ver_documentos.php">
<?php if ($esAdmin) { ?>
    <label for="club_id">Filtrar por club</label>
    <select id="club_id" name="club_id">
        <option value="0" <?php echo $clubId === 0 ? "selected" : ""; ?>>Todos los clubes</option>
        <?php while ($club = mysqli_fetch_assoc($clubes)) { ?>
            <option value="<?php echo (int) $club["id"]; ?>" <?php echo $clubId === (int) $club["id"] ? "selected" : ""; ?>>
                <?php echo htmlspecialchars($club["nombre"], ENT_QUOTES, "UTF-8"); ?>
            </option>
        <?php } ?>
    </select>
<?php } ?>
    <label for="categoria">Filtrar por categoría</label>
    <select id="categoria" name="categoria">
        <option value="" <?php echo $categoria === "" ? "selected" : ""; ?>>Todas las categorías</option>
        <?php foreach ($categoriasPermitidas as $opcionCategoria) { ?>
            <option value="<?php echo htmlspecialchars((string) $opcionCategoria, ENT_QUOTES, "UTF-8"); ?>" <?php echo $categoria === (string) $opcionCategoria ? "selected" : ""; ?>>
                <?php echo htmlspecialchars((string) $opcionCategoria, ENT_QUOTES, "UTF-8"); ?>
            </option>
        <?php } ?>
    </select>
    <button type="submit">Filtrar</button>
</form>

<table>

<tr>

<?php if ($esAdmin) { ?>
<th>Club</th>
<?php } ?>

<th>Jugador</th>

<th>Categoría</th>

<th>Ficha Médica</th>

<th>Fecha de expedición</th>

<th>Fecha de vencimiento</th>

</tr>

<?php while ($fila = mysqli_fetch_assoc($consulta)) { ?>

<tr>

<?php if ($esAdmin) { ?>
<td><?php echo htmlspecialchars($fila["nombre_club"], ENT_QUOTES, "UTF-8"); ?></td>
<?php } ?>

<td><?php echo htmlspecialchars($fila["nombre"], ENT_QUOTES, "UTF-8"); ?></td>

<td><?php echo htmlspecialchars((string) $fila["categoria"], ENT_QUOTES, "UTF-8"); ?></td>

<td>

<?php if (!empty($fila["ficha_medica"])) { ?>
<a href="<?php echo htmlspecialchars($fila["ficha_medica"], ENT_QUOTES, "UTF-8"); ?>" target="_blank" rel="noopener">
Ver Ficha
</a>
<?php } else { ?>
Sin ficha
<?php } ?>
</td>

<?php
$valorFechaExpedicion = $fila["fecha_subida_ficha"] ?? "";
$fechaExpedicion = DateTimeImmutable::createFromFormat("!Y-m-d H:i:s", $valorFechaExpedicion);
if ($fechaExpedicion !== false && $fechaExpedicion->format("Y-m-d H:i:s") === $valorFechaExpedicion) {
    $fechaVencimiento = $fechaExpedicion->modify("+2 years");
    $formatoFecha = "d/m/Y h:i ";
    $fechaExpedicionFormateada = $fechaExpedicion->format($formatoFecha) . ucfirst($fechaExpedicion->format("a"));
    $fechaVencimientoFormateada = $fechaVencimiento->format($formatoFecha) . ucfirst($fechaVencimiento->format("a"));
    ?>
    <td><?php echo $fechaExpedicionFormateada; ?></td>
    <td><?php echo $fechaVencimientoFormateada; ?></td>
<?php } else { ?>
    <td>No disponible</td>
    <td>No disponible</td>
<?php } ?>

</tr>

<?php } ?>

</table>

</div>

<?php
mysqli_stmt_close($consultaStmt);
?>

</body>

</html>
