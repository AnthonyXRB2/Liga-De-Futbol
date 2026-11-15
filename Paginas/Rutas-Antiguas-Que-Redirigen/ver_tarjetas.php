<?php
require_once __DIR__ . '/../../Includes/rutas.php';
// Redirige a la sección de sanciones manteniendo el torneo elegido.
session_start();
require_once __DIR__ . '/../../Includes/restringir_club.php';

$torneos = ['estandar', 'plata', 'oro'];
$torneo = $_GET['torneo'] ?? 'estandar';
if (!is_string($torneo) || !in_array($torneo, $torneos, true)) {
    $torneo = 'estandar';
}

header('Location: ' . app_url('Paginas/Competencia/tabla_posiciones.php') . '?torneo=' . $torneo . '#sanciones');
exit();
