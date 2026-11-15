<?php
require_once __DIR__ . '/Includes/rutas.php';

session_start();

$destino = isset($_SESSION['usuario'])
    ? 'Paginas/Inicio/index.php'
    : 'Paginas/Ingreso-y-Usuarios/login.php';

header('Location: ' . app_url($destino));
exit();
