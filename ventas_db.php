<?php

require_once __DIR__ . '/conexion.php';

function prepararVentasAgrupadas($conn)
{
    $sql = "CREATE TABLE IF NOT EXISTS ventas_grupos (
        id_grupo INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    if (!mysqli_query($conn, $sql)) {
        throw new RuntimeException('No fue posible preparar los grupos de ventas: ' . mysqli_error($conn));
    }

    $columnas = array();
    $resultado = mysqli_query($conn, 'SHOW COLUMNS FROM ventas');
    if (!$resultado) {
        throw new RuntimeException('No fue posible revisar las columnas de ventas: ' . mysqli_error($conn));
    }
    while ($columna = mysqli_fetch_assoc($resultado)) {
        $columnas[$columna['Field']] = strtolower($columna['Type']);
    }
    if (!isset($columnas['id_grupo']) && !mysqli_query($conn, 'ALTER TABLE ventas ADD COLUMN id_grupo INT NULL, ADD INDEX idx_ventas_grupo (id_grupo), ADD CONSTRAINT fk_ventas_grupo FOREIGN KEY (id_grupo) REFERENCES ventas_grupos (id_grupo)')) {
        throw new RuntimeException('No fue posible agregar la agrupación de ventas: ' . mysqli_error($conn));
    }
    foreach (array('costo', 'preciocosto', 'ganancia') as $nombre) {
        if (!isset($columnas[$nombre])) {
            throw new RuntimeException('Falta una columna de importes en ventas: ' . $nombre);
        }
        if ($columnas[$nombre] !== 'decimal(13,2)') {
            if (!in_array($columnas[$nombre], array('decimal(6,2)', 'int', 'int(11)'), true)) {
                throw new RuntimeException('Tipo de importe inesperado en ventas: ' . $nombre . ' ' . $columnas[$nombre]);
            }
            if (!mysqli_query($conn, 'ALTER TABLE ventas MODIFY COLUMN ' . $nombre . ' DECIMAL(13,2) DEFAULT NULL')) {
                throw new RuntimeException('No fue posible ampliar la precisión de ventas: ' . mysqli_error($conn));
            }
        }
    }
}

function centavosVenta($valor)
{
    if (!is_string($valor) || !preg_match('/^(-?)([0-9]{1,11})(?:\.([0-9]{1,2}))?$/D', $valor, $partes)) {
        return null;
    }
    $centavos = (int) $partes[2] * 100 + (int) str_pad(isset($partes[3]) ? $partes[3] : '', 2, '0');
    return $partes[1] === '-' ? -$centavos : $centavos;
}

function importeVenta($centavos)
{
    $absoluto = abs($centavos);
    return ($centavos < 0 ? '-' : '') . intdiv($absoluto, 100) . '.' . str_pad((string) ($absoluto % 100), 2, '0', STR_PAD_LEFT);
}

if (!$conn) {
    error_log('No fue posible conectar con la base de datos de ventas.');
    http_response_code(500);
    exit('No se pudo acceder al módulo de ventas.');
}
try {
    $bloqueo = mysqli_query($conn, "SELECT GET_LOCK('softgaresp_ventas_schema', 10)");
    $filaBloqueo = $bloqueo ? mysqli_fetch_row($bloqueo) : false;
    if (!$filaBloqueo || (int) $filaBloqueo[0] !== 1) {
        throw new RuntimeException('No fue posible obtener el bloqueo de la estructura de ventas.');
    }
    try {
        prepararVentasAgrupadas($conn);
    } finally {
        if (!mysqli_query($conn, "SELECT RELEASE_LOCK('softgaresp_ventas_schema')")) {
            error_log('No se pudo liberar el bloqueo de la estructura de ventas: ' . mysqli_error($conn));
        }
    }
} catch (RuntimeException $error) {
    error_log($error->getMessage());
    http_response_code(500);
    exit('No se pudo preparar el almacenamiento de ventas.');
}
