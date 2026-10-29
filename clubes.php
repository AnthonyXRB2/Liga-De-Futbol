<?php
// Carga los clubes y muestra herramientas de edición solo a administradores.
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
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clubes</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<?php include("menu.php"); ?>

<h2>Lista de Clubes</h2>

<!-- Incluye búsqueda, estado actual y acciones administrativas. -->
<div class="contenedor">

<?php if($esAdmin){ ?>

<a href="agregar_club.php">
    <button>➕ Agregar Club</button>
</a>

<br><br>

<?php } ?>

<label for="buscar-club">Buscar club por nombre</label><br>
<input type="search" id="buscar-club" placeholder="Escribe el nombre del club" autocomplete="off">
<p id="resultado-busqueda" aria-live="polite"></p>

<table>

<tr>
    <th>Nombre</th>
    <th>Director técnico</th>
    <th>Departamento</th>
    <th>Fecha de fundación</th>
    <th>Estado</th>

<?php if($esAdmin){ ?>
    <th>Acciones</th>
<?php } ?>

</tr>

<?php

$consulta = mysqli_query($conn, "SELECT * FROM clubes ORDER BY nombre");

// Presenta cada club y sus controles según el rol activo.
while($club = mysqli_fetch_assoc($consulta)){
?>

<tr class="fila-club">

    <td><?php echo htmlspecialchars($club["nombre"], ENT_QUOTES, "UTF-8"); ?></td>

    <td><?php echo htmlspecialchars($club["director_tecnico"] ?? "", ENT_QUOTES, "UTF-8"); ?></td>

    <td><?php echo htmlspecialchars($club["departamento"] ?? "", ENT_QUOTES, "UTF-8"); ?></td>

    <td><?php echo $club["fecha_fundacion"] ? date("d/m/Y", strtotime($club["fecha_fundacion"])) : "—"; ?></td>

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

        <a href="agregar_club.php?id=<?php echo (int) $club["id"]; ?>">
            <button type="button" aria-label="Editar club" title="Editar club">✏️</button>
        </a>

        <a href="desactivar_club.php?id=<?php echo $club["id"]; ?>">
            <button type="button" aria-label="<?php echo $club["activo"] == 1 ? "Desactivar club" : "Activar club"; ?>" title="<?php echo $club["activo"] == 1 ? "Desactivar club" : "Activar club"; ?>">

            <?php
            if($club["activo"] == 1){
                echo "⛔";
            }else{
                echo "✅";
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

<script>
// Filtra las filas por nombre sin recargar la lista.
const buscadorClub = document.getElementById("buscar-club");
const filasClubes = Array.from(document.querySelectorAll(".fila-club"));
const resultadoBusqueda = document.getElementById("resultado-busqueda");

function filtrarClubes() {
    const termino = buscadorClub.value.trim().toLocaleLowerCase("es");
    let visibles = 0;

    filasClubes.forEach((fila) => {
        const coincide = fila.cells[0].textContent.trim().toLocaleLowerCase("es").includes(termino);
        fila.hidden = !coincide;
        if (coincide) {
            visibles += 1;
        }
    });

    resultadoBusqueda.textContent = termino !== "" && visibles === 0
        ? "No se encontraron clubes con ese nombre."
        : "";
}

buscadorClub.addEventListener("input", filtrarClubes);
</script>

</body>
</html>
