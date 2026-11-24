<?php

function prepararTablaMovimientos($conn)
{
    static $preparada = false;
    if ($preparada) {
        return;
    }

    $bloqueo = mysqli_query($conn, "SELECT GET_LOCK('softgaresp_movimientos_schema', 10)");
    $filaBloqueo = $bloqueo ? mysqli_fetch_row($bloqueo) : false;
    if (!$filaBloqueo || (int) $filaBloqueo[0] !== 1) {
        throw new RuntimeException('No fue posible asegurar la estructura de movimientos.');
    }

    $sql = "CREATE TABLE IF NOT EXISTS movimientos (
        id_movimiento INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        fecha_movimiento DATE NOT NULL,
        fecha_estimada TINYINT(1) NOT NULL DEFAULT 0,
        tipo ENUM('ingreso', 'gasto') NOT NULL,
        categoria VARCHAR(40) NOT NULL,
        concepto VARCHAR(150) NOT NULL,
        cantidad DECIMAL(12,2) NOT NULL DEFAULT 0,
        importe DECIMAL(13,2) NOT NULL,
        ganancia DECIMAL(13,2) NOT NULL DEFAULT 0,
        fuente ENUM('manual', 'excel') NOT NULL DEFAULT 'manual',
        hoja_origen VARCHAR(64) NULL,
        fila_origen INT NULL,
        clave_origen CHAR(64) NULL,
        creado_por VARCHAR(100) NOT NULL,
        creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_movimientos_clave_origen (clave_origen),
        KEY idx_movimientos_fecha (fecha_movimiento),
        KEY idx_movimientos_categoria_fecha (categoria, fecha_movimiento)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    try {
        if (!mysqli_query($conn, $sql)) {
            throw new RuntimeException('No fue posible preparar el registro de movimientos: ' . mysqli_error($conn));
        }

        $columnaFechaEstimada = mysqli_query($conn, "SHOW COLUMNS FROM movimientos LIKE 'fecha_estimada'");
        if (!$columnaFechaEstimada) {
            throw new RuntimeException('No fue posible revisar las columnas de movimientos: ' . mysqli_error($conn));
        }
        if (mysqli_num_rows($columnaFechaEstimada) === 0 &&
            !mysqli_query($conn, 'ALTER TABLE movimientos ADD COLUMN fecha_estimada TINYINT(1) NOT NULL DEFAULT 0 AFTER fecha_movimiento')) {
            throw new RuntimeException('No fue posible agregar la marca de fecha estimada: ' . mysqli_error($conn));
        }
        $preparada = true;
    } finally {
        if (!mysqli_query($conn, "SELECT RELEASE_LOCK('softgaresp_movimientos_schema')")) {
            error_log('No se pudo liberar el bloqueo de la estructura de movimientos: ' . mysqli_error($conn));
        }
    }
}

function validarDecimalMovimiento($valor, $maximoEntero = 11)
{
    if (!is_string($valor) || !preg_match('/^([0-9]{1,' . $maximoEntero . '})(?:\.([0-9]{1,2}))?$/D', $valor, $partes)) {
        return null;
    }

    return $partes[1] . '.' . str_pad(isset($partes[2]) ? $partes[2] : '', 2, '0');
}
