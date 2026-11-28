<?php

if (isset($_SERVER['SCRIPT_FILENAME']) && basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    http_response_code(404);
    exit;
}

function prepararEquiposNota(mysqli $conn)
{
    try {
        $comprobar = $conn->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND BINARY TABLE_NAME = 'nota_equipo'");
        if ($comprobar && $comprobar->num_rows > 0) {
            return;
        }
        $sql = "CREATE TABLE IF NOT EXISTS nota_equipo (
            id_detalle INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_nota INT NOT NULL,
            id_equipo INT NOT NULL,
            descripcion VARCHAR(255) NOT NULL,
            marca VARCHAR(25) NOT NULL DEFAULT '',
            modelo VARCHAR(25) NOT NULL DEFAULT '',
            numero_serie VARCHAR(100) NOT NULL DEFAULT '',
            KEY (id_nota),
            KEY (id_equipo),
            CONSTRAINT fk_nota_equipo_nota FOREIGN KEY (id_nota) REFERENCES notas (id_nota) ON DELETE CASCADE,
            CONSTRAINT fk_nota_equipo_equipo FOREIGN KEY (id_equipo) REFERENCES equipo (id_equipo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        if (!$conn->query($sql)) {
            throw new RuntimeException($conn->error);
        }
    } catch (mysqli_sql_exception $error) {
        error_log('No se pudo preparar el detalle de equipos: ' . $error->getMessage());
        http_response_code(500);
        exit('No se pudo preparar el detalle de equipos de las notas.');
    } catch (RuntimeException $error) {
        error_log('No se pudo preparar el detalle de equipos: ' . $error->getMessage());
        http_response_code(500);
        exit('No se pudo preparar el detalle de equipos de las notas.');
    }
}

function equiposDeNota(mysqli $conn, array $nota)
{
    $consulta = $conn->prepare('SELECT id_equipo, descripcion, marca, modelo, numero_serie FROM nota_equipo WHERE id_nota = ? ORDER BY id_detalle');
    $consulta->bind_param('i', $nota['id_nota']);
    $consulta->execute();
    $equipos = $consulta->get_result()->fetch_all(MYSQLI_ASSOC);
    $consulta->close();
    if ($equipos) {
        return $equipos;
    }
    // Notas anteriores: un solo equipo guardado en la propia nota.
    $serie = $nota['numero_serie'] ?? null;
    if ($serie === null) {
        $consulta = $conn->prepare('SELECT numero_serie FROM nota_recepcion WHERE id_nota = ?');
        $consulta->bind_param('i', $nota['id_nota']);
        $consulta->execute();
        $fila = $consulta->get_result()->fetch_assoc();
        $consulta->close();
        $serie = $fila ? $fila['numero_serie'] : '';
    }
    $consulta = $conn->prepare('SELECT descripcionEquipo FROM equipo WHERE id_equipo = ?');
    $consulta->bind_param('i', $nota['equipo']);
    $consulta->execute();
    $fila = $consulta->get_result()->fetch_assoc();
    $consulta->close();
    return array(array(
        'id_equipo' => $nota['equipo'],
        'descripcion' => $fila ? $fila['descripcionEquipo'] : '',
        'marca' => $nota['marca'],
        'modelo' => $nota['modelo'],
        'numero_serie' => (string) $serie,
    ));
}

function prepararServiciosNota(mysqli $conn)
{
    prepararEquiposNota($conn);
    try {
        $comprobar = $conn->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND BINARY TABLE_NAME = 'nota_servicio'");
        if (!$comprobar) {
            throw new RuntimeException($conn->error);
        }
        if ($comprobar->num_rows > 0) {
            return;
        }
    } catch (mysqli_sql_exception $error) {
        error_log('No se pudo comprobar el detalle de servicios: ' . $error->getMessage());
        http_response_code(500);
        exit('No se pudo preparar el detalle de servicios de las notas.');
    } catch (RuntimeException $error) {
        error_log('No se pudo comprobar el detalle de servicios: ' . $error->getMessage());
        http_response_code(500);
        exit('No se pudo preparar el detalle de servicios de las notas.');
    }
    $sql = "CREATE TABLE IF NOT EXISTS nota_servicio (
        id_detalle INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        id_nota INT NOT NULL,
        id_servicio INT NOT NULL,
        nombre VARCHAR(50) NOT NULL,
        precio INT NOT NULL,
        KEY (id_nota),
        KEY (id_servicio),
        CONSTRAINT fk_nota_servicio_nota FOREIGN KEY (id_nota) REFERENCES notas (id_nota) ON DELETE CASCADE,
        CONSTRAINT fk_nota_servicio_servicio FOREIGN KEY (id_servicio) REFERENCES servicio (id_servicio)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    try {
        if (!$conn->query($sql)) {
            throw new RuntimeException($conn->error);
        }
    } catch (mysqli_sql_exception $error) {
        error_log('No se pudo preparar el detalle de servicios: ' . $error->getMessage());
        http_response_code(500);
        exit('No se pudo preparar el detalle de servicios de las notas.');
    } catch (RuntimeException $error) {
        error_log('No se pudo preparar el detalle de servicios: ' . $error->getMessage());
        http_response_code(500);
        exit('No se pudo preparar el detalle de servicios de las notas.');
    }
}

function serviciosDeNota(mysqli $conn, array $nota)
{
    $consulta = $conn->prepare('SELECT id_servicio, nombre, precio FROM nota_servicio WHERE id_nota = ? ORDER BY id_detalle');
    if (!$consulta) {
        error_log('No se pudo preparar la consulta de servicios: ' . $conn->error);
        http_response_code(500);
        exit('No se pudieron consultar los servicios de la nota.');
    }
    $consulta->bind_param('i', $nota['id_nota']);
    if (!$consulta->execute()) {
        error_log('No se pudieron consultar los servicios: ' . $consulta->error);
        http_response_code(500);
        exit('No se pudieron consultar los servicios de la nota.');
    }
    $detalles = $consulta->get_result()->fetch_all(MYSQLI_ASSOC);
    $consulta->close();
    if (!$detalles) {
        $consulta = $conn->prepare('SELECT id_servicio, tipoServicio AS nombre FROM servicio WHERE id_servicio = ?');
        if (!$consulta) {
            error_log('No se pudo preparar la consulta del servicio anterior: ' . $conn->error);
            http_response_code(500);
            exit('No se pudo consultar el servicio de la nota.');
        }
        $consulta->bind_param('i', $nota['servicio']);
        if (!$consulta->execute()) {
            error_log('No se pudo consultar el servicio anterior: ' . $consulta->error);
            http_response_code(500);
            exit('No se pudo consultar el servicio de la nota.');
        }
        $servicio = $consulta->get_result()->fetch_assoc();
        $consulta->close();
        if (!$servicio) {
            error_log('La nota no tiene un servicio válido: ' . $nota['id_nota']);
            http_response_code(500);
            exit('No se pudo consultar el servicio de la nota.');
        }
        $servicio['precio'] = $nota['costoServicio'];
        $detalles[] = $servicio;
    }
    return $detalles;
}

function prepararResumenServiciosNota(mysqli $conn)
{
    prepararServiciosNota($conn);
    if (!$conn->query('SET SESSION group_concat_max_len = 65535')) {
        error_log('No se pudo preparar el resumen de servicios: ' . $conn->error);
        http_response_code(500);
        exit('No se pudo preparar el resumen de servicios.');
    }
}
