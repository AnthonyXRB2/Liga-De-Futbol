<?php
// Carga los comunicados recientes y el resumen visible para administración.
session_start();
include('restringir_club.php');

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

require_once('conexion.php');

$rol = $_SESSION['rol'] ?? '';
$consultaComunicados = mysqli_query(
    $conn,
    'SELECT titulo, mensaje FROM comunicados ORDER BY id DESC LIMIT 5'
);

// Cuenta los registros principales únicamente para el panel administrativo.
if ($rol === 'admin') {
    $clubes = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) AS total FROM clubes WHERE activo=1'))['total'];
    $jugadores = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) AS total FROM jugadores'))['total'];
    $partidos = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) AS total FROM partidos'))['total'];
    $usuarios = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) AS total FROM usuarios'))['total'];
    $tarjetas = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) AS total FROM tarjetas'))['total'];
    $documentos = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) AS total FROM documentos'))['total'];
    $comunicados = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) AS total FROM comunicados'))['total'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inicio</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body class="pagina-inicio">

<?php include('menu.php'); ?>

<main class="pagina-contenido inicio">
    <!-- Presentación de la sesión y enlaces a las áreas más consultadas. -->
    <section class="portada-inicio">
        <div>
            <span class="etiqueta-seccion">SISTEMA DE LIGA</span>
            <h2>¡Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario'], ENT_QUOTES, 'UTF-8'); ?>!</h2>
            <p>Has iniciado sesión como <strong><?php echo htmlspecialchars(ucfirst($rol), ENT_QUOTES, 'UTF-8'); ?></strong>. Consulta la actividad de la liga y accede rápidamente a sus secciones.</p>
        </div>
        <nav class="atajos-inicio" aria-label="Accesos rápidos">
            <?php if ($rol !== 'club') { ?>
                <a class="boton" href="clubes.php">Ver clubes</a>
            <?php } ?>
            <a class="boton boton-secundario" href="jugadores.php">Ver jugadores</a>
            <a class="boton boton-secundario" href="tabla_posiciones.php">Competencia</a>
        </nav>
    </section>

    <!-- Resume las cifras básicas del sistema para el administrador. -->
    <?php if ($rol === 'admin') { ?>
        <section class="resumen-inicio" aria-label="Resumen del sistema">
            <article class="dato-inicio"><span>Clubes activos</span><strong><?php echo (int) $clubes; ?></strong></article>
            <article class="dato-inicio"><span>Jugadores</span><strong><?php echo (int) $jugadores; ?></strong></article>
            <article class="dato-inicio"><span>Partidos</span><strong><?php echo (int) $partidos; ?></strong></article>
            <article class="dato-inicio"><span>Comunicados</span><strong><?php echo (int) $comunicados; ?></strong></article>
        </section>
    <?php } ?>

    <!-- Muestra los últimos avisos disponibles para todos los usuarios. -->
    <section class="seccion-comunicados" aria-labelledby="titulo-comunicados">
        <h2 id="titulo-comunicados">Comunicados recientes</h2>

        <?php if ($consultaComunicados && mysqli_num_rows($consultaComunicados) > 0) { ?>
            <div class="lista-comunicados">
                <?php while ($comunicado = mysqli_fetch_assoc($consultaComunicados)) { ?>
                    <article class="comunicado">
                        <h3><?php echo htmlspecialchars($comunicado['titulo'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($comunicado['mensaje'], ENT_QUOTES, 'UTF-8')); ?></p>
                    </article>
                <?php } ?>
            </div>
        <?php } else { ?>
            <p class="sin-comunicados">No hay comunicados publicados por el momento.</p>
        <?php } ?>
    </section>
</main>

</body>
</html>
