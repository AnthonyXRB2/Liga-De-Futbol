<?php
session_start();
include("restringir_club.php");
require_once("conexion.php");

$rol = $_SESSION["rol"] ?? "";
if (!in_array($rol, ["admin", "club"], true)) {
    http_response_code(403);
    die("Acceso denegado.");
}

$busqueda = trim($_GET["buscar"] ?? "");
$categoria = trim($_GET["categoria"] ?? "");
$alcance = $_GET["alcance"] ?? "propios";
$clubSeleccionado = (int) ($_GET["club_id"] ?? 0);
$condiciones = [];

if ($rol === "club") {
    if ($alcance === "ajenos") {
        if ($clubSeleccionado > 0 && $clubSeleccionado !== (int) $_SESSION["club_id"]) {
            $condiciones[] = "jugadores.club_id = " . $clubSeleccionado;
        } else {
            $condiciones[] = "1 = 0";
        }
    } else {
        $alcance = "propios";
        $condiciones[] = "jugadores.club_id = " . (int) $_SESSION["club_id"];
    }
} elseif ($clubSeleccionado > 0) {
    $condiciones[] = "jugadores.club_id = " . $clubSeleccionado;
}

if ($busqueda !== "") {
    $busquedaSql = mysqli_real_escape_string($conn, $busqueda);
    $condiciones[] = "(jugadores.nombre LIKE '%" . $busquedaSql . "%' OR jugadores.ci LIKE '%" . $busquedaSql . "%')";
}
if ($categoria !== "") {
    $categoriaSql = mysqli_real_escape_string($conn, $categoria);
    $condiciones[] = "jugadores.categoria = '" . $categoriaSql . "'";
}

$where = $condiciones ? "WHERE " . implode(" AND ", $condiciones) : "";
$sql = "
    SELECT jugadores.*,
           clubes.nombre AS club,
           (SELECT documentos.ficha_medica
            FROM documentos
            WHERE documentos.jugador_id = jugadores.id
            ORDER BY documentos.id DESC
            LIMIT 1) AS ficha_medica
    FROM jugadores
    LEFT JOIN clubes ON jugadores.club_id = clubes.id
    $where
    ORDER BY jugadores.nombre
";
$consulta = mysqli_query($conn, $sql);
if (!$consulta) {
    http_response_code(500);
    die("No se pudo cargar la lista de jugadores.");
}
$clubes = mysqli_query($conn, "SELECT id, nombre FROM clubes ORDER BY nombre");

function h($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jugadores</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
<?php include("menu.php"); ?>

<main class="pagina-contenido">
    <h2>Plantel de jugadores</h2>
    <p class="ayuda">
        <?php if ($rol === "admin") { ?>
            Filtra por club para consultar el plantel y abrir la ficha médica disponible.
        <?php } else { ?>
            Consulta tu plantel o selecciona otro club para revisar sus jugadores y fichas médicas.
        <?php } ?>
    </p>

    <?php if ($rol === "admin") { ?>
        <p class="acciones-pagina"><a class="boton" href="agregar_jugador.php">＋ Agregar jugador</a></p>
    <?php } ?>

    <form class="filtros-jugadores" method="GET">
        <div class="campos-filtros">
            <?php if ($rol === "club") { ?>
                <div class="campo-filtro">
                    <label for="alcance">Plantel que quieres consultar</label>
                    <select id="alcance" name="alcance" onchange="this.form.submit()">
                        <option value="propios" <?php echo $alcance === "propios" ? "selected" : ""; ?>>Mi club</option>
                        <option value="ajenos" <?php echo $alcance === "ajenos" ? "selected" : ""; ?>>Otros clubes</option>
                    </select>
                </div>
            <?php } ?>

            <?php if ($rol === "admin" || $alcance === "ajenos") { ?>
                <div class="campo-filtro">
                    <label for="club_id"><?php echo $rol === "admin" ? "Seleccionar club" : "Seleccionar otro club"; ?></label>
                    <select id="club_id" name="club_id">
                        <option value="0"><?php echo $rol === "admin" ? "Todos los clubes" : "Elige un club"; ?></option>
                        <?php while ($club = mysqli_fetch_assoc($clubes)) {
                            if ($rol === "club" && (int) $club["id"] === (int) $_SESSION["club_id"]) {
                                continue;
                            }
                        ?>
                            <option value="<?php echo (int) $club["id"]; ?>" <?php echo $clubSeleccionado === (int) $club["id"] ? "selected" : ""; ?>>
                                <?php echo h($club["nombre"]); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            <?php } ?>

            <div class="campo-filtro campo-busqueda">
                <label for="buscar">Buscar por nombre o cédula</label>
                <input id="buscar" type="search" name="buscar" value="<?php echo h($busqueda); ?>" placeholder="Ej.: nombre o CI">
            </div>

            <div class="campo-filtro">
                <label for="categoria">Categoría</label>
                <select id="categoria" name="categoria">
                    <option value="">Todas las categorías</option>
                    <?php for ($opcionCategoria = 2013; $opcionCategoria <= 2019; $opcionCategoria++) { ?>
                        <option value="<?php echo $opcionCategoria; ?>" <?php echo $categoria === (string) $opcionCategoria ? "selected" : ""; ?>>
                            <?php echo $opcionCategoria; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="acciones-filtros">
            <button type="submit">Aplicar filtros</button>
            <a href="jugadores.php">Limpiar filtros</a>
        </div>
    </form>

    <?php if ($rol === "club" && $alcance === "ajenos" && $clubSeleccionado === 0) { ?>
        <p class="aviso">Selecciona un club en la lista para ver sus jugadores.</p>
    <?php } ?>

    <div class="contenedor">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th><th>Edad</th><th>Posición</th><th>CI</th><th>Categoría</th>
                    <th>Club</th><th>Altura</th><th>Masa</th><th>Fuerza</th><th>Velocidad</th>
                    <th>Ficha médica</th>
                    <?php if ($rol === "admin") { ?><th>Acciones</th><?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($consulta) > 0) {
                    while ($fila = mysqli_fetch_assoc($consulta)) { ?>
                        <tr>
                            <td><?php echo h($fila["nombre"]); ?></td>
                            <td><?php echo h($fila["edad"]); ?></td>
                            <td><?php echo h($fila["posicion"]); ?></td>
                            <td><?php echo h($fila["ci"]); ?></td>
                            <td><?php echo h($fila["categoria"]); ?></td>
                            <td><?php echo h($fila["club"]); ?></td>
                            <td><?php echo h($fila["altura"]); ?> m</td>
                            <td><?php echo h($fila["masa"]); ?> kg</td>
                            <td><?php echo h($fila["fuerza_peso"]); ?> N</td>
                            <td><?php echo h($fila["velocidad"]); ?> m/s</td>
                            <td>
                                <?php if (!empty($fila["ficha_medica"])) { ?>
                                    <a href="<?php echo h($fila["ficha_medica"]); ?>" target="_blank" rel="noopener">Ver ficha</a>
                                <?php } else { ?>
                                    <span class="estado-pendiente">Pendiente</span>
                                <?php } ?>
                            </td>
                            <?php if ($rol === "admin") { ?>
                                <td class="acciones-jugador">
                                    <a class="boton boton-editar" href="editar_jugador.php?id=<?php echo (int) $fila["id"]; ?>">Editar</a>
                                    <details class="menu-acciones-jugador">
                                        <summary>Más</summary>
                                        <form class="form-accion" action="eliminar_jugador.php" method="POST" onsubmit="return confirm('¿Eliminar este jugador y sus tarjetas y documentos asociados?');">
                                            <input type="hidden" name="id" value="<?php echo (int) $fila["id"]; ?>">
                                            <button class="boton-peligro" type="submit">Eliminar</button>
                                        </form>
                                    </details>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php }
                } else { ?>
                    <tr><td colspan="<?php echo $rol === "admin" ? 12 : 11; ?>">No se encontraron jugadores con estos filtros.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
