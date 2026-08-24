<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario = htmlspecialchars($_SESSION['usuario'] ?? '', ENT_QUOTES, 'UTF-8');
$rol = $_SESSION['rol'] ?? '';
$rolVisible = htmlspecialchars($rol, ENT_QUOTES, 'UTF-8');
?>

<button class="boton-menu" type="button" aria-controls="menu-lateral" aria-expanded="false" aria-label="Abrir menú de navegación">
    <span aria-hidden="true">☰</span>
</button>
<div class="fondo-sidebar" aria-hidden="true"></div>

<aside class="sidebar" id="menu-lateral">
    <header class="encabezado-principal">
        <div class="marca-liga">Sistema Liga de Fútbol</div>
        <div class="barra-usuario">
            <span class="datos-usuario"><?php echo $usuario; ?> (<?php echo $rolVisible; ?>)</span>
        </div>
    </header>

    <nav class="navegacion-principal" aria-label="Navegación principal">
        <a href="index.php">Inicio</a>

        <details class="menu-desplegable">
        <summary>Gestión</summary>
        <div class="opciones-menu">
            <a href="clubes.php">Clubes</a>
            <a href="jugadores.php">Jugadores</a>
            <a href="partidos.php">Partidos</a>
            <a href="tabla_posiciones.php">Tabla</a>
        </div>
        </details>

        <details class="menu-desplegable">
        <summary>Administración</summary>
        <div class="opciones-menu">
            <?php if ($rol === 'admin') { ?>
                <a href="usuarios.php">Usuarios</a>
            <?php } ?>
            <a href="cambiar_password.php">Contraseña</a>
            <a href="ver_tarjetas.php">Sanciones</a>
            <?php if ($rol === 'admin') { ?>
                <a href="agregar_tarjeta.php">Tarjetas</a>
            <?php } ?>
        </div>
        </details>

        <details class="menu-desplegable">
        <summary>Publicaciones</summary>
        <div class="opciones-menu">
            <a href="comunicados.php">Comunicados</a>
            <a href="subir_documentos.php">Documentos</a>
            <a href="ver_documentos.php">Ver Docs</a>
        </div>
        </details>
    </nav>
    <a class="cerrar-sesion" href="logout.php">Cerrar sesión</a>
</aside>

<script>
    (function () {
        var boton = document.querySelector('.boton-menu');
        var fondo = document.querySelector('.fondo-sidebar');

        function alternarMenu(abierto) {
            document.body.classList.toggle('sidebar-abierta', abierto);
            boton.setAttribute('aria-expanded', abierto ? 'true' : 'false');
            boton.setAttribute('aria-label', abierto ? 'Cerrar menú de navegación' : 'Abrir menú de navegación');
        }

        boton.addEventListener('click', function () {
            alternarMenu(!document.body.classList.contains('sidebar-abierta'));
        });

        fondo.addEventListener('click', function () {
            alternarMenu(false);
        });

        document.addEventListener('keydown', function (evento) {
            if (evento.key === 'Escape') {
                alternarMenu(false);
            }
        });
    }());
</script>
