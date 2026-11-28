<?php

if (isset($_SERVER['SCRIPT_FILENAME']) && basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    http_response_code(404);
    exit;
}

function imagenComercialPdf($nombre)
{
    if ($nombre === '') {
        return '';
    }

    if (!is_string($nombre) || !preg_match('/^[a-f0-9]{32}\.(jpg|png)$/', $nombre)) {
        error_log('Nombre de imagen de cotización no válido.');
        return '';
    }

    $contenido = @file_get_contents(__DIR__ . '/uploads/cotizaciones/' . $nombre);
    if ($contenido === false) {
        error_log('No fue posible leer la imagen de cotización: ' . $nombre);
        return '';
    }

    $tipo = substr($nombre, -3) === 'png' ? 'image/png' : 'image/jpeg';
    return 'data:' . $tipo . ';base64,' . base64_encode($contenido);
}

