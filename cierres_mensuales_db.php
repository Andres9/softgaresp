<?php

function prepararTablasCierreMensual($conn)
{
    static $preparada = false;
    if ($preparada) {
        return;
    }
    if (!mysqli_set_charset($conn, 'utf8mb4')) {
        throw new RuntimeException('No fue posible configurar la codificación de cierres mensuales: ' . mysqli_error($conn));
    }

    $bloqueo = mysqli_query($conn, "SELECT GET_LOCK('softgaresp_cierre_mensual_schema', 10)");
    $filaBloqueo = $bloqueo ? mysqli_fetch_row($bloqueo) : false;
    if (!$filaBloqueo || (int) $filaBloqueo[0] !== 1) {
        throw new RuntimeException('No fue posible asegurar la estructura de cierres mensuales.');
    }

    try {
        $sqlCierres = "CREATE TABLE IF NOT EXISTS cierres_mensuales (
            id_cierre INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            periodo DATE NOT NULL,
            local_adeudo_ganancia DECIMAL(13,2) NOT NULL DEFAULT 0,
            local_ganancia DECIMAL(13,2) NOT NULL DEFAULT 0,
            gastos_base DECIMAL(13,2) NOT NULL DEFAULT 0,
            inventario_actual DECIMAL(13,2) NOT NULL DEFAULT 0,
            inventario_deuda DECIMAL(13,2) NOT NULL DEFAULT 0,
            creado_por VARCHAR(100) NOT NULL,
            actualizado_por VARCHAR(100) NOT NULL,
            creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_cierres_mensuales_periodo (periodo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        if (!mysqli_query($conn, $sqlCierres)) {
            throw new RuntimeException('No fue posible preparar los cierres mensuales: ' . mysqli_error($conn));
        }

        $sqlDetalles = "CREATE TABLE IF NOT EXISTS cierres_mensuales_detalle (
            id_detalle INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_cierre INT UNSIGNED NOT NULL,
            seccion VARCHAR(20) NOT NULL,
            concepto VARCHAR(80) NOT NULL,
            importe DECIMAL(13,2) NOT NULL DEFAULT 0,
            posicion SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            KEY idx_cierre_detalle_seccion (id_cierre, seccion, posicion),
            CONSTRAINT fk_cierre_mensual_detalle
                FOREIGN KEY (id_cierre) REFERENCES cierres_mensuales (id_cierre)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        if (!mysqli_query($conn, $sqlDetalles)) {
            throw new RuntimeException('No fue posible preparar los detalles de cierres: ' . mysqli_error($conn));
        }

        $sqlCuentas = "CREATE TABLE IF NOT EXISTS cierres_mensuales_cuentas (
            id_cierre INT UNSIGNED NOT NULL,
            cuenta VARCHAR(20) NOT NULL,
            saldo DECIMAL(13,2) NOT NULL,
            fecha_saldo DATE NOT NULL,
            PRIMARY KEY (id_cierre, cuenta),
            CONSTRAINT fk_cierre_mensual_cuenta
                FOREIGN KEY (id_cierre) REFERENCES cierres_mensuales (id_cierre)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        if (!mysqli_query($conn, $sqlCuentas)) {
            throw new RuntimeException('No fue posible preparar las cuentas bancarias de los cierres: ' . mysqli_error($conn));
        }

        $preparada = true;
    } finally {
        if (!mysqli_query($conn, "SELECT RELEASE_LOCK('softgaresp_cierre_mensual_schema')")) {
            error_log('No se pudo liberar el bloqueo de la estructura de cierres: ' . mysqli_error($conn));
        }
    }
}

function cuentasCierreMensual()
{
    return array('bbva' => 'BBVA', 'azteca' => 'Azteca', 'mercado' => 'Mercado', 'toka' => 'Toka');
}

function seccionesCierreMensual()
{
    return array(
        'local' => 'Local del mes',
        'fondo' => 'Fondo actual',
        'deuda' => 'Deuda actual'
    );
}

function validarImporteCierreMensual($valor)
{
    if (!is_string($valor) || !preg_match('/^-?[0-9]{1,11}(?:\.([0-9]{1,2}))?$/D', $valor, $partes)) {
        return null;
    }

    $negativo = isset($valor[0]) && $valor[0] === '-';
    $entero = $negativo ? substr($valor, 1) : $valor;
    $entero = explode('.', $entero, 2)[0];
    $decimales = isset($partes[1]) ? $partes[1] : '';

    return ($negativo ? '-' : '') . $entero . '.' . str_pad($decimales, 2, '0');
}

function consultarCierreMensual($conn, $periodo)
{
    $consulta = mysqli_prepare(
        $conn,
        'SELECT id_cierre, local_adeudo_ganancia, local_ganancia, gastos_base, inventario_actual, inventario_deuda
         FROM cierres_mensuales WHERE periodo = ?'
    );
    if (!$consulta || !mysqli_stmt_bind_param($consulta, 's', $periodo) ||
        !mysqli_stmt_execute($consulta) ||
        !mysqli_stmt_bind_result($consulta, $idCierre, $adeudoLocal, $gananciaLocal, $gastosBase, $inventarioActual, $inventarioDeuda)) {
        $error = $consulta ? mysqli_stmt_error($consulta) : mysqli_error($conn);
        error_log('No fue posible consultar el cierre mensual: ' . $error);
        if ($consulta) {
            mysqli_stmt_close($consulta);
        }
        throw new RuntimeException('No se pudo cargar el cierre mensual.');
    }

    $cierre = null;
    $filaEncontrada = mysqli_stmt_fetch($consulta);
    if ($filaEncontrada === false) {
        error_log('Ocurrió un error al leer el encabezado del cierre: ' . mysqli_stmt_error($consulta));
        mysqli_stmt_close($consulta);
        throw new RuntimeException('No se pudo leer el cierre mensual.');
    }
    if ($filaEncontrada) {
        $cierre = array(
            'id_cierre' => (int) $idCierre,
            'local_adeudo_ganancia' => $adeudoLocal,
            'local_ganancia' => $gananciaLocal,
            'gastos_base' => $gastosBase,
            'inventario_actual' => $inventarioActual,
            'inventario_deuda' => $inventarioDeuda,
            'detalles' => array(),
            'cuentas' => array()
        );
    }
    mysqli_stmt_close($consulta);

    if ($cierre === null) {
        return null;
    }

    $consultaDetalles = mysqli_prepare(
        $conn,
        'SELECT seccion, concepto, importe FROM cierres_mensuales_detalle
         WHERE id_cierre = ? ORDER BY seccion, posicion, id_detalle'
    );
    if (!$consultaDetalles || !mysqli_stmt_bind_param($consultaDetalles, 'i', $idCierre) ||
        !mysqli_stmt_execute($consultaDetalles) ||
        !mysqli_stmt_bind_result($consultaDetalles, $seccion, $concepto, $importe)) {
        $error = $consultaDetalles ? mysqli_stmt_error($consultaDetalles) : mysqli_error($conn);
        error_log('No fue posible consultar el detalle del cierre mensual: ' . $error);
        if ($consultaDetalles) {
            mysqli_stmt_close($consultaDetalles);
        }
        throw new RuntimeException('No se pudo cargar el detalle del cierre mensual.');
    }

    while (mysqli_stmt_fetch($consultaDetalles)) {
        if (!isset($cierre['detalles'][$seccion])) {
            $cierre['detalles'][$seccion] = array();
        }
        $cierre['detalles'][$seccion][] = array('concepto' => $concepto, 'importe' => $importe);
    }
    if (mysqli_stmt_errno($consultaDetalles) !== 0) {
        error_log('Ocurrió un error al leer el cierre mensual: ' . mysqli_stmt_error($consultaDetalles));
        mysqli_stmt_close($consultaDetalles);
        throw new RuntimeException('No se pudo leer el detalle del cierre mensual.');
    }
    mysqli_stmt_close($consultaDetalles);

    $consultaCuentas = mysqli_prepare(
        $conn,
        'SELECT cuenta, saldo, fecha_saldo FROM cierres_mensuales_cuentas WHERE id_cierre = ?'
    );
    if (!$consultaCuentas || !mysqli_stmt_bind_param($consultaCuentas, 'i', $idCierre) ||
        !mysqli_stmt_execute($consultaCuentas) ||
        !mysqli_stmt_bind_result($consultaCuentas, $cuenta, $saldo, $fechaSaldo)) {
        $error = $consultaCuentas ? mysqli_stmt_error($consultaCuentas) : mysqli_error($conn);
        error_log('No fue posible consultar las cuentas del cierre: ' . $error);
        if ($consultaCuentas) {
            mysqli_stmt_close($consultaCuentas);
        }
        throw new RuntimeException('No se pudieron cargar las cuentas del cierre.');
    }
    while (mysqli_stmt_fetch($consultaCuentas)) {
        $cierre['cuentas'][$cuenta] = array('saldo' => $saldo, 'fecha' => $fechaSaldo);
    }
    if (mysqli_stmt_errno($consultaCuentas) !== 0) {
        error_log('No fue posible leer las cuentas del cierre: ' . mysqli_stmt_error($consultaCuentas));
        mysqli_stmt_close($consultaCuentas);
        throw new RuntimeException('No se pudieron leer las cuentas del cierre.');
    }
    mysqli_stmt_close($consultaCuentas);

    return $cierre;
}

function listarCierresMensuales($conn)
{
    $sql = "SELECT c.id_cierre, c.periodo, c.local_adeudo_ganancia, c.local_ganancia,
                   c.gastos_base, c.inventario_actual, c.inventario_deuda,
                   COALESCE(SUM(CASE WHEN d.seccion = 'movimientos' THEN d.importe ELSE 0 END), 0) AS total_movimientos,
                   COALESCE(SUM(CASE WHEN d.seccion = 'fondo' THEN d.importe ELSE 0 END), 0) AS total_fondo,
                   COALESCE(SUM(CASE WHEN d.seccion = 'gastos' THEN d.importe ELSE 0 END), 0) AS total_gastos,
                   COALESCE(SUM(CASE WHEN d.seccion = 'deuda' THEN d.importe ELSE 0 END), 0) AS total_deuda
            FROM cierres_mensuales c
            LEFT JOIN cierres_mensuales_detalle d ON d.id_cierre = c.id_cierre
            GROUP BY c.id_cierre, c.periodo, c.local_adeudo_ganancia, c.local_ganancia,
                     c.gastos_base, c.inventario_actual, c.inventario_deuda
            ORDER BY c.periodo DESC LIMIT 24";
    $resultado = mysqli_query($conn, $sql);
    if (!$resultado) {
        error_log('No fue posible listar los cierres mensuales: ' . mysqli_error($conn));
        throw new RuntimeException('No se pudo cargar el historial de cierres.');
    }

    $cierres = array();
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $cierres[] = $fila;
    }
    if (mysqli_errno($conn) !== 0) {
        error_log('Ocurrió un error al leer el historial de cierres: ' . mysqli_error($conn));
        mysqli_free_result($resultado);
        throw new RuntimeException('No se pudo leer el historial de cierres.');
    }
    mysqli_free_result($resultado);
    return $cierres;
}
