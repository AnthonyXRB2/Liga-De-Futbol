<?php
// Valida la fecha de nacimiento y deriva edad y categoría del jugador.
function calcular_datos_nacimiento($fechaNacimiento)
{
    // Rechaza fechas mal formadas, fuera de rango o posteriores a hoy.
    if (!is_string($fechaNacimiento) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaNacimiento)) {
        return false;
    }

    $fecha = DateTime::createFromFormat('!Y-m-d', $fechaNacimiento);
    $errores = DateTime::getLastErrors();
    if (!$fecha || ($errores !== false && ($errores['warning_count'] > 0 || $errores['error_count'] > 0))) {
        return false;
    }

    if ((int) $fecha->format('Y') < 2013 || (int) $fecha->format('Y') > 2019) {
        return false;
    }

    $hoy = new DateTime('today');
    if ($fecha > $hoy) {
        return false;
    }

    return [
        'edad' => $hoy->diff($fecha)->y,
        'categoria' => $fecha->format('Y')
    ];
}
