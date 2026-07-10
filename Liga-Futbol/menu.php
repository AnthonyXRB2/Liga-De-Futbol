<?php
if(session_status()===PHP_SESSION_NONE){
    session_start();
}
?>

<header>
    Sistema Liga de Fútbol
</header>

<nav>

<a href="index.php">🏠 Inicio</a>

<a href="clubes.php">⚽ Clubes</a>

<a href="jugadores.php">👥 Jugadores</a>

<a href="partidos.php">📅 Partidos</a>

<?php if($_SESSION["rol"]=="admin"){ ?>

<a href="agregar_tarjeta.php">🟨 Tarjetas</a>

<?php } ?>

<a href="comunicados.php">📢 Comunicados</a>

<a href="subir_documentos.php">📄 Documentos</a>

<a href="ver_documentos.php">📂 Ver Docs</a>

<a href="ver_tarjetas.php">📋 Sanciones</a>

<a href="tabla_posiciones.php">🏆 Tabla</a>

<?php if($_SESSION["rol"]=="admin"){ ?>

<a href="usuarios.php">👤 Usuarios</a>

<?php } ?>

<a href="cambiar_password.php">🔒 Contraseña</a>

<a href="logout.php">🚪 Salir</a>

</nav>

<div class="usuario">

<?php
echo $_SESSION["usuario"] . " (" . $_SESSION["rol"] . ")";
?>

</div>