<?php

function ejecutar_ocr_ficha($rutaImagen)
{
    $rutasTesseract = array_filter([
        getenv('TESSERACT_PATH'),
        getenv('TESSERACT'),
        'C:\\Program Files\\Tesseract-OCR\\tesseract.exe',
        'C:\\Program Files (x86)\\Tesseract-OCR\\tesseract.exe',
        'C:\\Users\\' . getenv('USERNAME') . '\\AppData\\Local\\Programs\\Tesseract-OCR\\tesseract.exe',
        'tesseract'
    ], function ($ruta) {
        return is_string($ruta) && trim($ruta) !== '';
    });

    $tesseract = '';
    foreach ($rutasTesseract as $ruta) {
        $ruta = trim((string) $ruta);
        if ($ruta === 'tesseract' || file_exists($ruta)) {
            $tesseract = $ruta;
            break;
        }
    }

    if ($tesseract === '' && PHP_OS_FAMILY === 'Windows') {
        $resultadoWhere = shell_exec('where.exe tesseract 2>nul');
        if (is_string($resultadoWhere)) {
            $lineas = preg_split('/\r\n|\r|\n/', trim($resultadoWhere));
            foreach ($lineas as $linea) {
                $linea = trim((string) $linea);
                if ($linea !== '' && file_exists($linea)) {
                    $tesseract = $linea;
                    break;
                }
            }
        }
    }

    if ($tesseract === '') {
        throw new RuntimeException('No se encontró Tesseract OCR. Instálalo y asegúrate de que exista en PATH o define TESSERACT_PATH.');
    }

    $baseSalida = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ficha_' . bin2hex(random_bytes(8));
    $comando = escapeshellarg($tesseract) . ' ' . escapeshellarg($rutaImagen) . ' ' . escapeshellarg($baseSalida) . ' -l spa+eng --psm 6 2>&1';
    $salida = shell_exec($comando);
    $archivoTexto = $baseSalida . '.txt';

    if (!is_file($archivoTexto)) {
        throw new RuntimeException('Tesseract no pudo procesar la imagen. ' . trim((string) $salida));
    }

    $texto = file_get_contents($archivoTexto);
    unlink($archivoTexto);

    if ($texto === false || trim($texto) === '') {
        throw new RuntimeException('No se pudo leer texto en la ficha médica.');
    }

    return $texto;
}

function extraer_datos_ficha($texto)
{
    $texto = preg_replace('/[ \t]+/', ' ', str_replace(["\r\n", "\r"], "\n", $texto));
    $textoPlano = trim($texto);
    $datos = [
        'nombre' => '',
        'ci' => '',
        'edad' => 0,
        'posicion' => 'Sin especificar',
        'categoria' => 'Sin especificar'
    ];

    $patrones = [
        'nombre' => '/(?:apellido\s*y\s*nombre|nombre\s*completo|nombre|jugador)\s*[:\-]?\s*([^\n]+)/iu',
        'ci' => '/(?:c[ée]dula|documento|dni|\bci\b)\s*(?:de\s*identidad)?\s*[:\-#]?\s*([0-9][0-9.\- ]{5,})/iu',
        'categoria' => '/categor[ií]a\s*[:\-]?\s*([^\n]+)/iu',
        'posicion' => '/posici[oó]n\s*[:\-]?\s*([^\n]+)/iu'
    ];

    foreach ($patrones as $campo => $patron) {
        if (preg_match($patron, $textoPlano, $coincidencia)) {
            $datos[$campo] = trim(preg_replace('/\s+/', ' ', $coincidencia[1]));
        }
    }

    if ($datos['nombre'] === '') {
        $lineas = array_values(array_filter(array_map('trim', explode("\n", $textoPlano))));
        foreach ($lineas as $linea) {
            if (preg_match('/^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ ]{5,}$/u', $linea)) {
                $datos['nombre'] = $linea;
                break;
            }
        }
    }

    if ($datos['ci'] !== '') {
        $datos['ci'] = preg_replace('/[^0-9]/', '', $datos['ci']);
    }

    $fechaNacimiento = '';
    if (preg_match('/(?:nacimiento|nac\.?|fecha\s*de\s*nac\.?)\D{0,15}(\d{1,2}[\/-]\d{1,2}[\/-]\d{2,4})/iu', $textoPlano, $coincidencia)) {
        $fechaNacimiento = $coincidencia[1];
    }

    if ($fechaNacimiento !== '') {
        $partes = preg_split('/[\/-]/', $fechaNacimiento);
        $anio = (int) $partes[2];
        if ($anio < 100) {
            $anio += $anio < 30 ? 2000 : 1900;
        }
        $fecha = DateTime::createFromFormat('!d-m-Y', $partes[0] . '-' . $partes[1] . '-' . $anio);
        if ($fecha instanceof DateTime) {
            $hoy = new DateTime('today');
            $datos['edad'] = $hoy->diff($fecha)->y;
        }
    }

    if ($datos['categoria'] === '') {
        $datos['categoria'] = 'Sin especificar';
    }
    if ($datos['posicion'] === '') {
        $datos['posicion'] = 'Sin especificar';
    }

    $datos['nombre'] = trim($datos['nombre'], " \t\n\r\0\x0B:.-");
    return $datos;
}
