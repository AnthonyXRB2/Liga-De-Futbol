<?php
// Comprueba permisos y recupera el comunicado que se editará.
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}
if (($_SESSION['rol'] ?? '') !== 'admin') {
    http_response_code(403);
    die('Acceso denegado.');
}

require_once('conexion.php');
$id = (int) ($_GET['id'] ?? 0);
$consulta = mysqli_prepare($conn, 'SELECT titulo, mensaje FROM comunicados WHERE id = ? LIMIT 1');
// Si el identificador no corresponde a un aviso, vuelve a la lista.
mysqli_stmt_bind_param($consulta, 'i', $id);
mysqli_stmt_execute($consulta);
$resultado = mysqli_stmt_get_result($consulta);
$comunicado = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($consulta);
if (!$comunicado) {
    header('Location: comunicados.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar comunicado</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
<?php include('menu.php'); ?>
<main class="pagina-contenido">
    <!-- Rellena el formulario con contenido escapado del comunicado actual. -->
    <h2>Editar comunicado</h2>
    <?php if (isset($_GET['error'])) { ?><p class="mensaje-error">Completa el título y el mensaje.</p><?php } ?>
    <form action="guardar_comunicado.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <label for="titulo">Título</label>
        <input id="titulo" type="text" name="titulo" maxlength="100" value="<?php echo htmlspecialchars($comunicado['titulo'], ENT_QUOTES, 'UTF-8'); ?>" required>
        <label for="mensaje">Mensaje</label>
        <textarea id="mensaje" name="mensaje" rows="6" required><?php echo htmlspecialchars($comunicado['mensaje'], ENT_QUOTES, 'UTF-8'); ?></textarea>
        <button type="submit">Guardar cambios</button>
    </form>
</main>
</body>
</html>
