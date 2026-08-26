<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

include('conexion.php');

$consulta = mysqli_query($conn, 'SELECT id, titulo, mensaje FROM comunicados ORDER BY id DESC');
$esAdmin = ($_SESSION['rol'] ?? '') === 'admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comunicados</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
<?php include('menu.php'); ?>

<section class="seccion-comunicados" aria-labelledby="titulo-comunicados">
    <h2 id="titulo-comunicados">Comunicados</h2>

    <?php if (isset($_GET['creado'])) { ?>
        <p class="mensaje-exito">El comunicado se public&oacute; correctamente.</p>
    <?php } ?>

    <?php if ($esAdmin) { ?>
        <p class="acciones-comunicados"><a href="agregar_comunicado.php"><button type="button">Publicar comunicado</button></a></p>
    <?php } ?>

    <?php if ($consulta && mysqli_num_rows($consulta) > 0) { ?>
        <div class="lista-comunicados">
            <?php while ($comunicado = mysqli_fetch_assoc($consulta)) { ?>
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
