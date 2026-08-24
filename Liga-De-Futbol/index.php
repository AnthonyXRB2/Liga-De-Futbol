<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

include('conexion.php');

$rol = $_SESSION['rol'] ?? '';
$consultaComunicados = mysqli_query(
    $conn,
    'SELECT titulo, mensaje FROM comunicados ORDER BY id DESC LIMIT 5'
);

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
<body>

<?php include('menu.php'); ?>

<h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario'], ENT_QUOTES, 'UTF-8'); ?></h2>

<p>Has iniciado sesión como <b><?php echo htmlspecialchars(ucfirst($rol), ENT_QUOTES, 'UTF-8'); ?></b>.</p>

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

</body>
</html>
