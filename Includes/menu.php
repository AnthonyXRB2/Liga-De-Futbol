<?php
require_once __DIR__ . '/rutas.php';

// Prepara los datos de sesión que se muestran en la navegación.
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

    <!-- Las opciones visibles dependen de si la cuenta es club o administrador. -->
    <nav class="navegacion-principal" aria-label="Navegación principal">
        <?php if ($rol === 'club') { ?>
            <a href="<?php echo app_url('Paginas/Inicio/index.php'); ?>">Inicio</a>
            <a href="<?php echo app_url('Paginas/Jugadores/jugadores.php'); ?>">Jugadores</a>
            <a href="<?php echo app_url('Paginas/Competencia/tabla_posiciones.php'); ?>">Competencias y Sanciones</a>
            <a href="<?php echo app_url('Paginas/Documentos/ver_documentos.php'); ?>">Documentos Del Club</a>
            <a href="<?php echo app_url('Paginas/Comunicados/comunicados.php'); ?>">Comunicados</a>
            <a href="<?php echo app_url('Paginas/Documentos/subir_documentos.php'); ?>">Subir Ficha Médica</a>
            <a href="<?php echo app_url('Paginas/Ingreso-y-Usuarios/cambiar_password.php'); ?>">Contraseña</a>
        <?php } else { ?>
            <a href="<?php echo app_url('Paginas/Inicio/index.php'); ?>">Inicio</a>

            <details class="menu-desplegable">
            <summary>Gestión</summary>
            <div class="opciones-menu">
                <a href="<?php echo app_url('Paginas/Clubes/clubes.php'); ?>">Clubes</a>
                <a href="<?php echo app_url('Paginas/Jugadores/jugadores.php'); ?>">Jugadores</a>
                <a href="<?php echo app_url('Paginas/Competencia/tabla_posiciones.php'); ?>">Competencias y Sanciones</a>
            </div>
            </details>

            <details class="menu-desplegable">
            <summary>Administración</summary>
            <div class="opciones-menu">
                <?php if ($rol === 'admin') { ?>
                    <a href="<?php echo app_url('Paginas/Ingreso-y-Usuarios/usuarios.php'); ?>">Usuarios</a>
                    <a href="<?php echo app_url('Paginas/Documentos/ver_documentos.php'); ?>">Documentos De Los Jugadores</a>
                <?php } ?>
            </div>
            </details>

            <details class="menu-desplegable">
            <summary>Publicaciones</summary>
            <div class="opciones-menu">
                <a href="<?php echo app_url('Paginas/Comunicados/comunicados.php'); ?>">Comunicados</a>
            </div>
            </details>
        <?php } ?>
    </nav>
    <a class="cerrar-sesion" href="<?php echo app_url('Procesos/Acceso/logout.php'); ?>">Cerrar Sesión</a>
</aside>

<script>
    // Abre y cierra el panel lateral con el botón, el fondo o Escape.
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
