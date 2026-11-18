<?php

require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    header('Content-Type: text/plain; charset=utf-8');
    exit('Método no permitido.');
}

$dump = null;

try {
    require_once 'conexion.php';
    mysqli_set_charset($conn, 'utf8mb4');

    $databaseResult = mysqli_query($conn, 'SELECT DATABASE()');
    $databaseName = mysqli_fetch_row($databaseResult)[0] ?? null;
    if (!$databaseName) {
        throw new RuntimeException('No hay una base de datos seleccionada.');
    }

    $dump = fopen('php://temp/maxmemory:5242880', 'w+b');
    if ($dump === false) {
        throw new RuntimeException('No se pudo preparar el archivo de respaldo.');
    }

    $write = static function ($content) use ($dump) {
        if (fwrite($dump, $content) === false) {
            throw new RuntimeException('No se pudo escribir el archivo de respaldo.');
        }
    };

    $quoteIdentifier = static function ($identifier) {
        return '`' . str_replace('`', '``', $identifier) . '`';
    };

    $databaseIdentifier = $quoteIdentifier($databaseName);
    $write("-- Respaldo de la base de datos {$databaseName}\n");
    $write("-- Generado: " . date('Y-m-d H:i:s') . "\n\n");
    $write("SET NAMES utf8mb4;\n");
    $write("SET FOREIGN_KEY_CHECKS=0;\n");
    $write("CREATE DATABASE IF NOT EXISTS {$databaseIdentifier};\n");
    $write("USE {$databaseIdentifier};\n\n");

    $tablesResult = mysqli_query($conn, 'SHOW FULL TABLES');
    while ($tableInfo = mysqli_fetch_row($tablesResult)) {
        $tableName = $tableInfo[0];
        $tableType = strtoupper($tableInfo[1] ?? '');
        if ($tableType !== 'BASE TABLE') {
            continue;
        }

        $tableIdentifier = $quoteIdentifier($tableName);
        $createResult = mysqli_query($conn, "SHOW CREATE TABLE {$tableIdentifier}");
        $createRow = mysqli_fetch_row($createResult);
        if (!isset($createRow[1])) {
            throw new RuntimeException("No se pudo leer la definición de {$tableName}.");
        }

        $write("-- Estructura de {$tableName}\n");
        $write("DROP TABLE IF EXISTS {$tableIdentifier};\n");
        $write($createRow[1] . ";\n\n");

        $columnsResult = mysqli_query($conn, "SHOW FULL COLUMNS FROM {$tableIdentifier}");
        $binaryColumns = [];
        while ($column = mysqli_fetch_assoc($columnsResult)) {
            if (preg_match('/\\b(binary|varbinary|tinyblob|blob|mediumblob|longblob|geometry|point|linestring|polygon|multipoint|multilinestring|multipolygon|geometrycollection|bit)\\b/i', $column['Type'])) {
                $binaryColumns[$column['Field']] = true;
            }
        }

        $rowsResult = mysqli_query($conn, "SELECT * FROM {$tableIdentifier}");
        while ($row = mysqli_fetch_assoc($rowsResult)) {
            $columns = [];
            $values = [];
            foreach ($row as $columnName => $value) {
                $columns[] = $quoteIdentifier($columnName);
                if ($value === null) {
                    $values[] = 'NULL';
                } elseif (isset($binaryColumns[$columnName])) {
                    $values[] = '0x' . bin2hex($value);
                } else {
                    $values[] = "'" . mysqli_real_escape_string($conn, $value) . "'";
                }
            }

            $write(
                "INSERT INTO {$tableIdentifier} (" . implode(', ', $columns) . ') VALUES (' .
                implode(', ', $values) . ");\n"
            );
        }

        $write("\n");
    }

    $write("SET FOREIGN_KEY_CHECKS=1;\n");
    rewind($dump);

    $filename = 'respaldo_garesp_' . date('Y-m-d_H-i-s') . '.sql';
    header('Content-Type: application/sql; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    fpassthru($dump);
} catch (Throwable $exception) {
    error_log('Error al generar respaldo SQL: ' . $exception->getMessage());
    if (is_resource($dump)) {
        fclose($dump);
    }

    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'No se pudo generar el respaldo de la base de datos.';
    exit;
}

if (is_resource($dump)) {
    fclose($dump);
}
