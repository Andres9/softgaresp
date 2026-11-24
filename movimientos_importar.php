<?php

function importarRegistrosMovimientosExcel($conn, $registros, $usuario)
{
    prepararTablaMovimientos($conn);
    if (!mysqli_begin_transaction($conn)) {
        throw new RuntimeException('No fue posible iniciar la importación: ' . mysqli_error($conn));
    }

    $transaccionActiva = true;
    try {
        $secciones = array();
        $resultadoSecciones = mysqli_query($conn, 'SELECT id_seccion, descripcionSeccion FROM seccion');
        if (!$resultadoSecciones) {
            throw new RuntimeException('No fue posible consultar las secciones para importar: ' . mysqli_error($conn));
        }
        while ($seccion = mysqli_fetch_assoc($resultadoSecciones)) {
            $secciones[strtolower(trim($seccion['descripcionSeccion']))] = (int) $seccion['id_seccion'];
        }

        $consultaVenta = mysqli_prepare(
            $conn,
            'SELECT id_venta FROM ventas WHERE DATE(fecha_venta) = ? AND descripcion = ? AND costo = ? AND seccion = ?'
        );
        $consultaImpresion = mysqli_prepare(
            $conn,
            'SELECT idImpre FROM impresion WHERE DATE(fecha_impresion) = ? AND concepto = ? AND precio = ? AND cant = ?'
        );
        $consultaImpresionesDia = mysqli_prepare(
            $conn,
            'SELECT COALESCE(SUM(cant), 0) FROM impresion WHERE fecha_impresion >= ? AND fecha_impresion < ?'
        );
        $consultaHistorico = mysqli_prepare(
            $conn,
            "SELECT id_movimiento FROM movimientos
             WHERE fuente = 'excel' AND fecha_movimiento = ? AND categoria = ? AND concepto = ?
               AND cantidad = ? AND importe = ? AND ganancia = ? AND fecha_estimada = ?"
        );
        $insertarMovimiento = mysqli_prepare(
            $conn,
            'INSERT INTO movimientos (fecha_movimiento, fecha_estimada, tipo, categoria, concepto, cantidad, importe, ganancia, fuente, hoja_origen, fila_origen, clave_origen, creado_por)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, \'excel\', ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE id_movimiento = id_movimiento'
        );
        if (!$consultaVenta || !$consultaImpresion || !$consultaImpresionesDia || !$consultaHistorico || !$insertarMovimiento) {
            throw new RuntimeException('No fue posible preparar la importación: ' . mysqli_error($conn));
        }

        $importados = 0;
        $omitidos = 0;
        $usosVentas = array();
        $usosImpresiones = array();
        $usosHistoricos = array();
        $conteosImpresionesRevisados = array();

        foreach ($registros as $registro) {
            $fecha = $registro['fecha'];
            $categoria = $registro['categoria'];
            $concepto = $registro['concepto'];
            $cantidad = $registro['cantidad'];
            $importe = $registro['importe'];
            $ganancia = $registro['ganancia'];
            $fechaEstimada = (int) $registro['fecha_estimada'];
            $tipo = $registro['tipo'];
            $duplicado = false;

            $categoriaVenta = strtolower($categoria);
            if (isset($secciones[$categoriaVenta])) {
                $idSeccion = $secciones[$categoriaVenta];
                $importeVenta = $tipo === 'gasto' ? '-' . $importe : $importe;
                $firmaVenta = hash('sha256', implode('|', array($fecha, $concepto, $importeVenta, $idSeccion)));
                $usosVentas[$firmaVenta] = isset($usosVentas[$firmaVenta]) ? $usosVentas[$firmaVenta] + 1 : 1;
                if (!mysqli_stmt_bind_param($consultaVenta, 'sssi', $fecha, $concepto, $importeVenta, $idSeccion) ||
                    !mysqli_stmt_execute($consultaVenta) || !mysqli_stmt_store_result($consultaVenta)) {
                    throw new RuntimeException('No fue posible comparar las ventas existentes: ' . mysqli_stmt_error($consultaVenta));
                }
                $duplicado = $usosVentas[$firmaVenta] <= mysqli_stmt_num_rows($consultaVenta);
                mysqli_stmt_free_result($consultaVenta);
            }

            if (!$duplicado && ($categoria === 'Impresiones' || $categoria === 'Servicios') && $concepto !== 'Impresiones del día') {
                $importeImpresion = $tipo === 'gasto' ? '-' . $importe : $importe;
                $cantidadImpresion = 1;
                $firmaImpresion = hash('sha256', implode('|', array($fecha, $concepto, $importeImpresion, $cantidadImpresion)));
                $usosImpresiones[$firmaImpresion] = isset($usosImpresiones[$firmaImpresion]) ? $usosImpresiones[$firmaImpresion] + 1 : 1;
                if (!mysqli_stmt_bind_param($consultaImpresion, 'sssi', $fecha, $concepto, $importeImpresion, $cantidadImpresion) ||
                    !mysqli_stmt_execute($consultaImpresion) || !mysqli_stmt_store_result($consultaImpresion)) {
                    throw new RuntimeException('No fue posible comparar las impresiones existentes: ' . mysqli_stmt_error($consultaImpresion));
                }
                $duplicado = $usosImpresiones[$firmaImpresion] <= mysqli_stmt_num_rows($consultaImpresion);
                mysqli_stmt_free_result($consultaImpresion);
            }

            if (!$duplicado && $concepto === 'Impresiones del día' && !isset($conteosImpresionesRevisados[$fecha])) {
                $conteosImpresionesRevisados[$fecha] = true;
                $inicioDia = $fecha . ' 00:00:00';
                $finDia = (new DateTimeImmutable($fecha))->modify('+1 day')->format('Y-m-d') . ' 00:00:00';
                if (!mysqli_stmt_bind_param($consultaImpresionesDia, 'ss', $inicioDia, $finDia) ||
                    !mysqli_stmt_execute($consultaImpresionesDia) ||
                    !mysqli_stmt_bind_result($consultaImpresionesDia, $cantidadRegistrada) ||
                    mysqli_stmt_fetch($consultaImpresionesDia) !== true) {
                    throw new RuntimeException('No fue posible comparar el conteo diario de impresiones: ' . mysqli_stmt_error($consultaImpresionesDia));
                }
                mysqli_stmt_free_result($consultaImpresionesDia);
                $duplicado = abs((float) $cantidadRegistrada - (float) $cantidad) < 0.005;
            }

            if (!$duplicado) {
                $firmaHistorico = hash('sha256', implode('|', array($fecha, $categoria, $concepto, $cantidad, $importe, $ganancia, $fechaEstimada)));
                $usosHistoricos[$firmaHistorico] = isset($usosHistoricos[$firmaHistorico]) ? $usosHistoricos[$firmaHistorico] + 1 : 1;
                if (!mysqli_stmt_bind_param($consultaHistorico, 'ssssssi', $fecha, $categoria, $concepto, $cantidad, $importe, $ganancia, $fechaEstimada) ||
                    !mysqli_stmt_execute($consultaHistorico) || !mysqli_stmt_store_result($consultaHistorico)) {
                    throw new RuntimeException('No fue posible comprobar una importación anterior: ' . mysqli_stmt_error($consultaHistorico));
                }
                $duplicado = $usosHistoricos[$firmaHistorico] <= mysqli_stmt_num_rows($consultaHistorico);
                mysqli_stmt_free_result($consultaHistorico);
            }

            if ($duplicado) {
                $omitidos++;
                continue;
            }

            $hoja = $registro['hoja'];
            $fila = (int) $registro['fila'];
            $claveOrigen = hash('sha256', implode('|', array($hoja, $fila, $fecha, $fechaEstimada, $categoria, $concepto, $cantidad, $importe, $ganancia)));
            if (!mysqli_stmt_bind_param(
                $insertarMovimiento,
                'sisssssssiss',
                $fecha,
                $fechaEstimada,
                $tipo,
                $categoria,
                $concepto,
                $cantidad,
                $importe,
                $ganancia,
                $hoja,
                $fila,
                $claveOrigen,
                $usuario
            ) || !mysqli_stmt_execute($insertarMovimiento)) {
                throw new RuntimeException('No fue posible guardar un registro importado: ' . mysqli_stmt_error($insertarMovimiento));
            }

            if (mysqli_stmt_affected_rows($insertarMovimiento) === 1) {
                $importados++;
            } else {
                $omitidos++;
            }
        }

        mysqli_stmt_close($consultaVenta);
        mysqli_stmt_close($consultaImpresion);
        mysqli_stmt_close($consultaImpresionesDia);
        mysqli_stmt_close($consultaHistorico);
        mysqli_stmt_close($insertarMovimiento);

        if (!mysqli_commit($conn)) {
            throw new RuntimeException('No fue posible confirmar la importación: ' . mysqli_error($conn));
        }
        $transaccionActiva = false;
    } catch (RuntimeException $error) {
        if ($transaccionActiva) {
            mysqli_rollback($conn);
        }
        throw $error;
    }

    return array(
        'importados' => $importados,
        'omitidos' => $omitidos,
        'fechas_estimadas' => count(array_filter($registros, function ($registro) {
            return $registro['fecha_estimada'] === 1;
        }))
    );
}
