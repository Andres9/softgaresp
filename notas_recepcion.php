<?php

if (isset($_SERVER['SCRIPT_FILENAME']) && basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    http_response_code(404);
    exit;
}

function prepararRecepcionNotas($conn)
{
    try {
        $comprobar = mysqli_query($conn, "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND BINARY TABLE_NAME = 'nota_recepcion'");
    } catch (mysqli_sql_exception $error) {
        error_log('No fue posible comprobar los datos de recepción: ' . $error->getMessage());
        http_response_code(500);
        exit('No se pudo preparar el almacenamiento de las notas.');
    }
    if ($comprobar === false) {
        error_log('No fue posible comprobar los datos de recepción: ' . mysqli_error($conn));
        http_response_code(500);
        exit('No se pudo preparar el almacenamiento de las notas.');
    }
    if (mysqli_num_rows($comprobar) > 0) {
        mysqli_free_result($comprobar);
        return;
    }
    mysqli_free_result($comprobar);
    $sql = 'CREATE TABLE IF NOT EXISTS nota_recepcion (
        id_nota INT NOT NULL PRIMARY KEY,
        cargador TINYINT NULL,
        accesorios VARCHAR(255) NOT NULL DEFAULT \'\',
        numero_serie VARCHAR(100) NOT NULL DEFAULT \'\',
        estado_fisico VARCHAR(255) NOT NULL DEFAULT \'\',
        contrasena_cifrada TEXT NULL,
        patron_cifrado TEXT NULL,
        CONSTRAINT fk_nota_recepcion_nota FOREIGN KEY (id_nota)
            REFERENCES notas (id_nota) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci';
    try {
        $resultado = mysqli_query($conn, $sql);
    } catch (mysqli_sql_exception $error) {
        error_log('No fue posible preparar los datos de recepción: ' . $error->getMessage());
        http_response_code(500);
        exit('No se pudo preparar el almacenamiento de las notas.');
    }
    if (!$resultado) {
        error_log('No fue posible preparar los datos de recepción: ' . mysqli_error($conn));
        http_response_code(500);
        exit('No se pudo preparar el almacenamiento de las notas.');
    }
}

function escaparRecepcionHtml($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function canalSeguroNota()
{
    $https = isset($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off'
        && (string) $_SERVER['HTTPS'] !== '';
    $remoto = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    return $https || $remoto === '127.0.0.1' || $remoto === '::1';
}

function claveRecepcionNota()
{
    $ruta = getenv('SOFTGARESP_NOTAS_KEY_FILE');
    if ($ruta === false || $ruta === '') {
        $ruta = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'softgaresp-secrets' . DIRECTORY_SEPARATOR . 'notas.key';
    }
    if (!is_file($ruta) || !is_readable($ruta)) {
        throw new RuntimeException('No se puede leer la clave de cifrado de notas.');
    }
    $clave = file_get_contents($ruta);
    if ($clave === false || strlen($clave) !== 32) {
        throw new RuntimeException('La clave de cifrado de notas no es válida.');
    }
    return $clave;
}

function cifrarRecepcionNota($valor)
{
    if ($valor === '') {
        return null;
    }
    $nonce = random_bytes(12);
    $tag = '';
    $cifrado = openssl_encrypt($valor, 'aes-256-gcm', claveRecepcionNota(), OPENSSL_RAW_DATA, $nonce, $tag, 'softgaresp-notas-v1', 16);
    if ($cifrado === false || strlen($tag) !== 16) {
        throw new RuntimeException('No se pudo cifrar la información de acceso.');
    }
    return base64_encode("\x01" . $nonce . $tag . $cifrado);
}

function descifrarRecepcionNota($valor)
{
    if ($valor === null || $valor === '') {
        return '';
    }
    $datos = base64_decode($valor, true);
    if ($datos === false || strlen($datos) < 30 || $datos[0] !== "\x01") {
        throw new RuntimeException('La información cifrada de la nota no es válida.');
    }
    $claro = openssl_decrypt(substr($datos, 29), 'aes-256-gcm', claveRecepcionNota(), OPENSSL_RAW_DATA, substr($datos, 1, 12), substr($datos, 13, 16), 'softgaresp-notas-v1');
    if ($claro === false) {
        throw new RuntimeException('No se pudo descifrar la información de acceso.');
    }
    return $claro;
}

function equipoAdmitePatron($descripcion)
{
    return preg_match('/\b(celular|tel[eé]fono|smartphone|tablet|tableta)\b/iu', (string) $descripcion) === 1;
}

function patronNotaValido($patron)
{
    if (!preg_match('/^[1-9]{4,9}$/D', $patron)) {
        return false;
    }
    $vistos = array();
    $anterior = null;
    foreach (str_split($patron) as $punto) {
        $numero = (int) $punto;
        if (isset($vistos[$numero])) {
            return false;
        }
        if ($anterior !== null) {
            $x1 = ($anterior - 1) % 3;
            $y1 = intdiv($anterior - 1, 3);
            $x2 = ($numero - 1) % 3;
            $y2 = intdiv($numero - 1, 3);
            if (($x1 + $x2) % 2 === 0 && ($y1 + $y2) % 2 === 0 && max(abs($x2 - $x1), abs($y2 - $y1)) === 2) {
                $intermedio = intdiv($y1 + $y2, 2) * 3 + intdiv($x1 + $x2, 2) + 1;
                if (!isset($vistos[$intermedio])) {
                    return false;
                }
            }
        }
        $vistos[$numero] = true;
        $anterior = $numero;
    }
    return true;
}
