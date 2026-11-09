<?php
// Carga los avisos y determina si se muestran controles de administración.
session_start();
include('restringir_club.php');

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

require_once('conexion.php');

$fechaMinima = '2000-01-01';
$fechaMaxima = date('Y-m-d');
$fechaPublicacion = is_string($_GET['fecha_publicacion'] ?? null) ? trim($_GET['fecha_publicacion']) : '';
$errorFechas = false;
$validarFecha = static function ($fecha) use ($fechaMinima, $fechaMaxima) {
    if ($fecha === '') {
        return true;
    }

    $fechaValida = DateTime::createFromFormat('!Y-m-d', $fecha);
    return $fechaValida !== false
        && $fechaValida->format('Y-m-d') === $fecha
        && $fecha >= $fechaMinima
        && $fecha <= $fechaMaxima;
};

if (!$validarFecha($fechaPublicacion)) {
    $errorFechas = true;
    $consulta = false;
} else {
    $sentenciaComunicados = mysqli_prepare(
        $conn,
        "SELECT id, titulo, mensaje, fecha_publicacion
         FROM comunicados
         WHERE (? = '' OR (
             fecha_publicacion >= CONCAT(?, ' 00:00:00')
             AND fecha_publicacion < DATE_ADD(?, INTERVAL 1 DAY)
         ))
         ORDER BY fecha_publicacion DESC, id DESC"
    );
    mysqli_stmt_bind_param($sentenciaComunicados, 'sss', $fechaPublicacion, $fechaPublicacion, $fechaPublicacion);
    mysqli_stmt_execute($sentenciaComunicados);
    $consulta = mysqli_stmt_get_result($sentenciaComunicados);
    mysqli_stmt_close($sentenciaComunicados);
}
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
    <!-- Lista las publicaciones y sus mensajes de resultado. -->
    <h2 id="titulo-comunicados">Comunicados</h2>

    <form class="filtro-comunicados" method="GET" action="comunicados.php" novalidate>
        <div>
            <label for="fecha-publicacion">Fecha de publicaci&oacute;n</label>
            <input id="fecha-publicacion" type="date" name="fecha_publicacion" min="<?php echo $fechaMinima; ?>" max="<?php echo $fechaMaxima; ?>" value="<?php echo htmlspecialchars($fechaPublicacion, ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <button type="submit">Filtrar</button>
        <a class="boton-limpiar-filtro" href="comunicados.php">Limpiar filtro</a>
    </form>

    <?php if ($errorFechas) { ?>
        <p class="mensaje-error">Solo Se Puede Desde el 01/01/2000 Hasta El Dia Actual</p>
    <?php } ?>

    <?php if (isset($_GET['creado'])) { ?>
        <p class="mensaje-exito">El comunicado se public&oacute; correctamente.</p>
    <?php } ?>
    <?php if (isset($_GET['editado'])) { ?>
        <p class="mensaje-exito">El comunicado se actualiz&oacute; correctamente.</p>
    <?php } ?>

    <?php if ($esAdmin) { ?>
        <p class="acciones-comunicados"><a href="agregar_comunicado.php"><button type="button">Publicar comunicado</button></a></p>
    <?php } ?>

    <?php if ($consulta && mysqli_num_rows($consulta) > 0) { ?>
        <div class="lista-comunicados">
            <?php while ($comunicado = mysqli_fetch_assoc($consulta)) { ?>
                <article class="comunicado">
                    <h3><?php echo htmlspecialchars($comunicado['titulo'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <?php if ($comunicado['fecha_publicacion'] !== null) { ?>
                        <p class="fecha-comunicado">Publicado el <time datetime="<?php echo htmlspecialchars($comunicado['fecha_publicacion'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo date('d/m/Y H:i', strtotime($comunicado['fecha_publicacion'])); ?></time></p>
                    <?php } else { ?>
                        <p class="fecha-comunicado">Fecha de publicaci&oacute;n no registrada.</p>
                    <?php } ?>
                    <p><?php echo nl2br(htmlspecialchars($comunicado['mensaje'], ENT_QUOTES, 'UTF-8')); ?></p>

                    <?php if ($esAdmin) { ?>
                        <p class="acciones-comunicados">
                            <a href="editar_comunicado.php?id=<?php echo (int)$comunicado['id']; ?>">
                                <button type="button">Editar comunicado</button>
                            </a>
                            <a href="eliminar_comunicado.php?id=<?php echo (int)$comunicado['id']; ?>" onclick="return confirm('¿Eliminar este comunicado?');">
                                <button type="button">Eliminar comunicado</button>
                            </a>
                        </p>
                    <?php } ?>
                </article>
            <?php } ?>
        </div>
    <?php } elseif (!$errorFechas) { ?>
        <?php if ($fechaPublicacion !== '') { ?>
            <p class="sin-comunicados">No hay comunicados para la fecha seleccionada.</p>
        <?php } else { ?>
            <p class="sin-comunicados">No hay comunicados publicados por el momento.</p>
        <?php } ?>
    <?php } ?>
</section>
<script>
const formularioFechas = document.querySelector('.filtro-comunicados');
const fechaPublicacionInput = document.getElementById('fecha-publicacion');
const mensajeFechaInvalida = 'Solo Se Puede Desde el 01/01/2000 Hasta El Dia Actual';

function validarFechaPublicacion() {
    fechaPublicacionInput.setCustomValidity('');
    if (fechaPublicacionInput.value
        && (fechaPublicacionInput.value < fechaPublicacionInput.min
            || fechaPublicacionInput.value > fechaPublicacionInput.max)) {
        fechaPublicacionInput.setCustomValidity(mensajeFechaInvalida);
    }
}

function mostrarErrorAlSalir(evento) {
    validarFechaPublicacion();
    if (!evento.currentTarget.validity.valid) {
        evento.currentTarget.reportValidity();
    }
}

fechaPublicacionInput.addEventListener('input', validarFechaPublicacion);
fechaPublicacionInput.addEventListener('blur', mostrarErrorAlSalir);
formularioFechas.addEventListener('submit', function (evento) {
    validarFechaPublicacion();
    if (!formularioFechas.checkValidity()) {
        evento.preventDefault();
        formularioFechas.reportValidity();
    }
});
</script>
</body>
</html>
