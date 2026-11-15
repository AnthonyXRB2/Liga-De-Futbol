<?php

$appDocumentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$appRoot = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..');

if ($appDocumentRoot === false || $appRoot === false) {
    throw new RuntimeException('No se pudo determinar la ruta pública del proyecto.');
}

$appDocumentRoot = rtrim(str_replace('\\', '/', $appDocumentRoot), '/');
$appRoot = str_replace('\\', '/', $appRoot);

if (stripos($appRoot, $appDocumentRoot . '/') !== 0) {
    throw new RuntimeException('La carpeta del proyecto debe estar dentro de la raíz pública del servidor.');
}

if (!defined('APP_BASE_URL')) {
    define('APP_BASE_URL', substr($appRoot, strlen($appDocumentRoot)));
}

function app_url($path)
{
    return rtrim(APP_BASE_URL, '/') . '/' . ltrim(str_replace('\\', '/', $path), '/');
}
