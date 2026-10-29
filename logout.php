<?php
// Cierra la sesión activa y vuelve al formulario de acceso.
session_start();
session_destroy();

header("Location: login.php");
exit();
?>