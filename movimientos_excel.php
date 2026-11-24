<?php

function movimientoExcelLeerXml($archivo, $ruta, $maximoBytes = 5242880)
{
    if (!$archivo->offsetExists($ruta)) {
        throw new RuntimeException('El libro no contiene una parte necesaria: ' . $ruta);
    }

    $elemento = $archivo[$ruta];
    if ($elemento->getSize() > $maximoBytes) {
        throw new RuntimeException('Una hoja del libro excede el tamaño permitido.');
    }

    $contenido = $elemento->getContent();
    $usoAnteriorDeErrores = libxml_use_internal_errors(true);
    $xml = simplexml_load_string($contenido, 'SimpleXMLElement', LIBXML_NONET | LIBXML_COMPACT);
    libxml_clear_errors();
    libxml_use_internal_errors($usoAnteriorDeErrores);

    if ($xml === false) {
        throw new RuntimeException('No fue posible leer la estructura XML del libro.');
    }

    return $xml;
}

function movimientoExcelColumna($referencia)
{
    if (!preg_match('/^([A-Z]+)/i', $referencia, $partes)) {
        return 0;
    }

    $columna = 0;
    foreach (str_split(strtoupper($partes[1])) as $letra) {
        $columna = ($columna * 26) + ord($letra) - 64;
    }

    return $columna;
}

function movimientoExcelNormalizarTexto($valor)
{
    return trim(preg_replace('/\s+/', ' ', (string) $valor));
}

function movimientoExcelValorCelda($celda, $cadenasCompartidas, $espacioNombres)
{
    $celda->registerXPathNamespace('m', $espacioNombres);
    $atributos = $celda->attributes();
    $tipo = isset($atributos['t']) ? (string) $atributos['t'] : '';

    if ($tipo === 'inlineStr') {
        $textos = $celda->xpath('./m:is//m:t');
        $valor = '';
        foreach ($textos as $texto) {
            $valor .= (string) $texto;
        }
        return $valor;
    }

    $nodosValor = $celda->xpath('./m:v');
    if (!$nodosValor || !isset($nodosValor[0])) {
        return '';
    }

    $valor = (string) $nodosValor[0];
    if ($tipo === 's') {
        $indice = filter_var($valor, FILTER_VALIDATE_INT);
        return $indice !== false && isset($cadenasCompartidas[$indice]) ? $cadenasCompartidas[$indice] : '';
    }

    return $valor;
}

function movimientoExcelFecha($valor)
{
    if (!is_numeric($valor)) {
        $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $valor);
        $errores = DateTimeImmutable::getLastErrors();
        if ($fecha === false || ($errores !== false && ($errores['warning_count'] || $errores['error_count']))) {
            return null;
        }
        return $fecha->format('Y-m-d');
    }

    $dias = (int) floor((float) $valor);
    if ($dias < 1 || $dias > 73050) {
        return null;
    }

    return (new DateTimeImmutable('1899-12-30'))->modify('+' . $dias . ' days')->format('Y-m-d');
}

function movimientoExcelAgregarRegistro(&$registros, $fecha, $categoria, $concepto, $cantidad, $importe, $ganancia, $hoja, $fila, $fechaEstimada = false)
{
    if (!is_numeric($importe) || !is_numeric($cantidad) || !is_numeric($ganancia)) {
        return;
    }

    $importe = (float) $importe;
    $cantidad = (float) $cantidad;
    $ganancia = (float) $ganancia;
    if (abs($importe) < 0.005 && abs($cantidad) < 0.005) {
        return;
    }

    $concepto = movimientoExcelNormalizarTexto($concepto);
    if ($concepto === '') {
        $concepto = $categoria;
    }

    $registros[] = array(
        'fecha' => $fecha,
        'tipo' => $importe < 0 ? 'gasto' : 'ingreso',
        'categoria' => $categoria,
        'concepto' => $concepto,
        'cantidad' => number_format(abs($cantidad), 2, '.', ''),
        'importe' => number_format(abs($importe), 2, '.', ''),
        'ganancia' => number_format($ganancia, 2, '.', ''),
        'hoja' => $hoja,
        'fila' => $fila,
        'fecha_estimada' => $fechaEstimada ? 1 : 0
    );
}

function analizarLibroMovimientos($rutaLibro)
{
    if (!class_exists('PharData')) {
        throw new RuntimeException('El servidor no cuenta con soporte para leer libros XLSX.');
    }

    try {
        $archivo = new PharData($rutaLibro);
    } catch (Exception $error) {
        throw new RuntimeException('El archivo no es un libro XLSX válido.');
    }

    $espacioNombres = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
    $espacioRelaciones = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
    $espacioPaquete = 'http://schemas.openxmlformats.org/package/2006/relationships';

    $libro = movimientoExcelLeerXml($archivo, 'xl/workbook.xml', 1048576);
    $relaciones = movimientoExcelLeerXml($archivo, 'xl/_rels/workbook.xml.rels', 1048576);
    $libro->registerXPathNamespace('m', $espacioNombres);
    $relaciones->registerXPathNamespace('p', $espacioPaquete);

    $rutasRelacion = array();
    foreach ($relaciones->xpath('/p:Relationships/p:Relationship') as $relacion) {
        $atributos = $relacion->attributes();
        $rutasRelacion[(string) $atributos['Id']] = (string) $atributos['Target'];
    }

    $cadenasCompartidas = array();
    if ($archivo->offsetExists('xl/sharedStrings.xml')) {
        $compartidas = movimientoExcelLeerXml($archivo, 'xl/sharedStrings.xml');
        $compartidas->registerXPathNamespace('m', $espacioNombres);
        foreach ($compartidas->xpath('/m:sst/m:si') as $elementoCadena) {
            $elementoCadena->registerXPathNamespace('m', $espacioNombres);
            $textos = $elementoCadena->xpath('.//m:t');
            $cadena = '';
            foreach ($textos as $texto) {
                $cadena .= (string) $texto;
            }
            $cadenasCompartidas[] = $cadena;
        }
    }

    $hojasPermitidas = array(
        'ene', 'enero', 'jan', 'january', 'feb', 'febrero', 'february',
        'mar', 'marzo', 'march', 'abr', 'abril', 'apr', 'april',
        'may', 'mayo', 'jun', 'junio', 'june', 'jul', 'julio', 'july',
        'ago', 'agosto', 'aug', 'august', 'sep', 'sept', 'set', 'septiembre', 'setiembre',
        'oct', 'octubre', 'october', 'nov', 'noviembre', 'november',
        'dic', 'diciembre', 'dec', 'december'
    );
    $registros = array();
    $hojasAnalizadas = array();

    foreach ($libro->xpath('/m:workbook/m:sheets/m:sheet') as $hoja) {
        $atributos = $hoja->attributes();
        $nombreHoja = movimientoExcelNormalizarTexto((string) $atributos['name']);
        $claveHoja = strtolower($nombreHoja);
        if (!in_array($claveHoja, $hojasPermitidas, true)) {
            continue;
        }

        $atributosRelacion = $hoja->attributes($espacioRelaciones);
        $idRelacion = (string) $atributosRelacion['id'];
        if (!isset($rutasRelacion[$idRelacion])) {
            throw new RuntimeException('El libro contiene una hoja mensual sin ruta válida.');
        }

        $destino = str_replace('\\', '/', $rutasRelacion[$idRelacion]);
        $rutaHoja = strpos($destino, '/') === 0 ? ltrim($destino, '/') : 'xl/' . $destino;
        $segmentos = array();
        foreach (explode('/', $rutaHoja) as $segmento) {
            if ($segmento === '' || $segmento === '.') {
                continue;
            }
            if ($segmento === '..') {
                array_pop($segmentos);
            } else {
                $segmentos[] = $segmento;
            }
        }
        $rutaHoja = implode('/', $segmentos);
        if (!preg_match('/^xl\/worksheets\/[A-Za-z0-9._-]+\.xml$/D', $rutaHoja)) {
            throw new RuntimeException('El libro contiene una ruta de hoja mensual no permitida.');
        }

        $documentoHoja = movimientoExcelLeerXml($archivo, $rutaHoja);
        $documentoHoja->registerXPathNamespace('m', $espacioNombres);
        $filasHoja = array();
        $filaTieneFormula = array();
        $filaEncabezado = 0;
        $columnaFecha = 0;

        foreach ($documentoHoja->xpath('/m:worksheet/m:sheetData/m:row') as $filaXml) {
            $filaXml->registerXPathNamespace('m', $espacioNombres);
            $atributosFila = $filaXml->attributes();
            $numeroFila = (int) $atributosFila['r'];
            $filasHoja[$numeroFila] = array();
            foreach ($filaXml->xpath('./m:c') as $celda) {
                $atributosCelda = $celda->attributes();
                $columna = movimientoExcelColumna((string) $atributosCelda['r']);
                $filasHoja[$numeroFila][$columna] = movimientoExcelValorCelda($celda, $cadenasCompartidas, $espacioNombres);
                $formulas = $celda->xpath('./m:f');
                if ($formulas) {
                    $filaTieneFormula[$numeroFila] = true;
                }
            }
            if ($numeroFila <= 10 && $filaEncabezado === 0) {
                foreach ($filasHoja[$numeroFila] as $columna => $valor) {
                    if (strtolower(movimientoExcelNormalizarTexto($valor)) === 'fecha') {
                        $filaEncabezado = $numeroFila;
                        $columnaFecha = $columna;
                        break;
                    }
                }
            }
        }

        if ($filaEncabezado === 0 || !isset($filasHoja[$filaEncabezado])) {
            continue;
        }

        $encabezados = $filasHoja[$filaEncabezado];
        $grupos = array();
        $columnaImpresionesDia = 0;
        if ($columnaFecha === 3 && isset($encabezados[4]) && strtolower(trim((string) $encabezados[4])) === 'impresiones') {
            $grupos = array(
                array('categoria' => 'Negocio', 'descripcion' => 5, 'importe' => 6, 'ganancia' => 7),
                array('categoria' => 'Recargas', 'descripcion' => 8, 'importe' => 9, 'ganancia' => 0)
            );
            $columnaImpresionesDia = 4;
        } elseif ($columnaFecha === 3 && isset($encabezados[4]) && strtolower(trim((string) $encabezados[4])) === 'servicio') {
            $grupos = array(
                array('categoria' => 'Servicios', 'descripcion' => 4, 'importe' => 5, 'ganancia' => 0),
                array('categoria' => 'Patricia', 'descripcion' => 6, 'importe' => 7, 'ganancia' => 0),
                array('categoria' => 'Negocio', 'descripcion' => 8, 'importe' => 9, 'ganancia' => 10),
                array('categoria' => 'Recargas', 'descripcion' => 11, 'importe' => 12, 'ganancia' => 0)
            );
        } elseif ($columnaFecha === 2 && isset($encabezados[3]) && strtolower(trim((string) $encabezados[3])) === 'impresiones') {
            $grupos = array(
                array('categoria' => 'Impresiones', 'descripcion' => 3, 'importe' => 4, 'ganancia' => 0),
                array('categoria' => 'Servicios', 'descripcion' => 5, 'importe' => 6, 'ganancia' => 0),
                array('categoria' => 'Patricia', 'descripcion' => 7, 'importe' => 8, 'ganancia' => 0),
                array('categoria' => 'Negocio', 'descripcion' => 9, 'importe' => 10, 'ganancia' => 11),
                array('categoria' => 'Recargas', 'descripcion' => 12, 'importe' => 13, 'ganancia' => 0)
            );
        } else {
            continue;
        }

        $fechaActual = null;
        $fechaPrimerRegistro = null;
        foreach ($filasHoja as $numeroFila => $celdas) {
            if ($numeroFila > $filaEncabezado && isset($celdas[$columnaFecha])) {
                $fechaPrimerRegistro = movimientoExcelFecha($celdas[$columnaFecha]);
                if ($fechaPrimerRegistro !== null) {
                    break;
                }
            }
        }
        $fechaEstimacion = $fechaPrimerRegistro !== null
            ? substr($fechaPrimerRegistro, 0, 7) . '-01'
            : null;
        $fechaActualEsEstimada = false;
        foreach ($filasHoja as $numeroFila => $celdas) {
            if ($numeroFila <= $filaEncabezado || isset($filaTieneFormula[$numeroFila])) {
                continue;
            }

            if (isset($celdas[$columnaFecha]) && $celdas[$columnaFecha] !== '') {
                $fechaActual = movimientoExcelFecha($celdas[$columnaFecha]);
                $fechaActualEsEstimada = false;
            } elseif ($fechaActual === null && $claveHoja === 'ago' && $fechaEstimacion !== null) {
                $fechaActual = $fechaEstimacion;
                $fechaActualEsEstimada = true;
            }
            if ($fechaActual === null) {
                continue;
            }

            if ($columnaImpresionesDia > 0 && isset($celdas[$columnaImpresionesDia]) && is_numeric($celdas[$columnaImpresionesDia])) {
                movimientoExcelAgregarRegistro(
                    $registros,
                    $fechaActual,
                    'Impresiones',
                    'Impresiones del día',
                    $celdas[$columnaImpresionesDia],
                    0,
                    0,
                    $nombreHoja,
                    $numeroFila,
                    $fechaActualEsEstimada
                );
            }

            foreach ($grupos as $grupo) {
                $concepto = isset($celdas[$grupo['descripcion']]) ? $celdas[$grupo['descripcion']] : '';
                $importe = isset($celdas[$grupo['importe']]) ? $celdas[$grupo['importe']] : '';
                if (!is_numeric($importe)) {
                    continue;
                }
                $ganancia = $grupo['ganancia'] > 0 && isset($celdas[$grupo['ganancia']]) && is_numeric($celdas[$grupo['ganancia']])
                    ? $celdas[$grupo['ganancia']]
                    : 0;
                movimientoExcelAgregarRegistro(
                    $registros,
                    $fechaActual,
                    $grupo['categoria'],
                    $concepto,
                    1,
                    $importe,
                    $ganancia,
                    $nombreHoja,
                    $numeroFila,
                    $fechaActualEsEstimada
                );
            }
        }

        $hojasAnalizadas[] = $nombreHoja;
        if (count($registros) > 10000) {
            throw new RuntimeException('El libro contiene demasiados movimientos para una sola importación.');
        }
    }

    if (count($hojasAnalizadas) === 0 || count($registros) === 0) {
        throw new RuntimeException('No se encontraron movimientos en hojas mensuales compatibles del libro.');
    }

    return array('registros' => $registros, 'hojas' => $hojasAnalizadas);
}
