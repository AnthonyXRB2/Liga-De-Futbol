<?php
require_once __DIR__ . '/../../Includes/rutas.php';
// Cierra la sesión activa y vuelve al formulario de acceso.
session_start();
session_destroy();

header('Location: ' . app_url('Paginas/Ingreso-y-Usuarios/login.php'));
exit();
?>