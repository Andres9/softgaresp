<?php

function prepararCatalogoServicios(mysqli $conn)
{
    $columnasNecesarias = array(
        'categoria' => 'VARCHAR(50) NULL',
        'descripcion' => 'VARCHAR(500) NULL',
        'duracion_minutos' => 'INT NULL'
    );
    $columnas = array();
    foreach ($conn->query('SHOW COLUMNS FROM servicio')->fetch_all(MYSQLI_ASSOC) as $columna) {
        $columnas[$columna['Field']] = true;
    }
    if (!array_diff_key($columnasNecesarias, $columnas)) {
        return;
    }
    $bloqueo = $conn->query("SELECT GET_LOCK('softgaresp_catalogo_servicios_esquema', 5) AS obtenido")->fetch_assoc();
    if ((int) $bloqueo['obtenido'] !== 1) {
        throw new RuntimeException('No se pudo reservar el esquema del catálogo.');
    }
    try {
        $columnas = array();
        foreach ($conn->query('SHOW COLUMNS FROM servicio')->fetch_all(MYSQLI_ASSOC) as $columna) {
            $columnas[$columna['Field']] = true;
        }
        foreach (array_diff_key($columnasNecesarias, $columnas) as $nombre => $definicion) {
            $conn->query('ALTER TABLE servicio ADD COLUMN `' . $nombre . '` ' . $definicion);
        }
    } finally {
        $conn->query("SELECT RELEASE_LOCK('softgaresp_catalogo_servicios_esquema')");
    }
}

function registrarServicioCatalogo(mysqli $conn, $nombre, $precioEntrada, $categoria, $descripcion, $duracionEntrada, $idServicio = null)
{
    if (!is_string($nombre) || !is_string($precioEntrada) || !is_string($categoria) ||
        !is_string($descripcion) || !is_string($duracionEntrada)) {
        throw new InvalidArgumentException('Revisa los datos del servicio.');
    }
    $nombre = trim($nombre);
    $precioEntrada = trim($precioEntrada);
    if ($nombre === '' || !mb_check_encoding($nombre, 'UTF-8') ||
        mb_strlen($nombre, 'UTF-8') > 50 || preg_match('/[\p{C}]/u', $nombre)) {
        throw new InvalidArgumentException('El nombre debe tener entre 1 y 50 caracteres, sin saltos de línea.');
    }
    if ($precioEntrada === '' || !ctype_digit($precioEntrada) ||
        (float) $precioEntrada > 2147483647) {
        throw new InvalidArgumentException('El precio debe ser un número entero entre 0 y 2147483647.');
    }
    $categoria = trim($categoria);
    $descripcion = trim($descripcion);
    $duracionEntrada = trim($duracionEntrada);
    if (!mb_check_encoding($categoria, 'UTF-8') || mb_strlen($categoria, 'UTF-8') > 50 ||
        preg_match('/[\p{C}]/u', $categoria)) {
        throw new InvalidArgumentException('La categoría debe tener hasta 50 caracteres, sin saltos de línea.');
    }
    if (!mb_check_encoding($descripcion, 'UTF-8') || mb_strlen($descripcion, 'UTF-8') > 500 ||
        preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $descripcion)) {
        throw new InvalidArgumentException('La descripción debe tener hasta 500 caracteres.');
    }
    if ($duracionEntrada !== '' && (!ctype_digit($duracionEntrada) ||
        (float) $duracionEntrada < 1 || (float) $duracionEntrada > 2147483647)) {
        throw new InvalidArgumentException('La duración debe ser un número entero de minutos mayor que cero.');
    }
    $precio = (int) $precioEntrada;
    $categoria = $categoria !== '' ? $categoria : null;
    $descripcion = $descripcion !== '' ? $descripcion : null;
    $duracion = $duracionEntrada !== '' ? (int) $duracionEntrada : null;
    $bloqueo = $conn->query("SELECT GET_LOCK('softgaresp_catalogo_servicios', 5) AS obtenido")->fetch_assoc();
    if ((int) $bloqueo['obtenido'] !== 1) {
        throw new RuntimeException('No se pudo reservar el catálogo de servicios.');
    }
    try {
        $idExcluido = $idServicio === null ? 0 : $idServicio;
        $consulta = $conn->prepare('SELECT id_servicio FROM servicio WHERE LOWER(TRIM(tipoServicio)) = LOWER(?) AND id_servicio <> ? LIMIT 1');
        $consulta->bind_param('si', $nombre, $idExcluido);
        $consulta->execute();
        $existe = $consulta->get_result()->fetch_assoc();
        $consulta->close();
        if ($existe) {
            throw new InvalidArgumentException('Ya existe un servicio con ese nombre.');
        }

        if ($idServicio === null) {
            $consulta = $conn->prepare('INSERT INTO servicio (tipoServicio, precio, categoria, descripcion, duracion_minutos) VALUES (?, ?, ?, ?, ?)');
            $consulta->bind_param('sissi', $nombre, $precio, $categoria, $descripcion, $duracion);
        } else {
            $consulta = $conn->prepare('SELECT id_servicio FROM servicio WHERE id_servicio = ?');
            $consulta->bind_param('i', $idServicio);
            $consulta->execute();
            $existe = $consulta->get_result()->fetch_assoc();
            $consulta->close();
            if (!$existe) {
                throw new InvalidArgumentException('El servicio ya no existe.');
            }
            $consulta = $conn->prepare('UPDATE servicio SET tipoServicio = ?, precio = ?, categoria = ?, descripcion = ?, duracion_minutos = ? WHERE id_servicio = ?');
            $consulta->bind_param('sissii', $nombre, $precio, $categoria, $descripcion, $duracion, $idServicio);
        }
        $consulta->execute();
        $consulta->close();
    } finally {
        $conn->query("SELECT RELEASE_LOCK('softgaresp_catalogo_servicios')");
    }
}

function eliminarServicioCatalogo(mysqli $conn, int $idServicio)
{
    $bloqueo = $conn->query("SELECT GET_LOCK('softgaresp_catalogo_servicios', 5) AS obtenido")->fetch_assoc();
    if ((int) $bloqueo['obtenido'] !== 1) {
        throw new RuntimeException('No se pudo reservar el catálogo de servicios.');
    }
    try {
        $consulta = $conn->prepare('DELETE FROM servicio WHERE id_servicio = ? AND NOT EXISTS (SELECT 1 FROM notas WHERE servicio = ?) AND NOT EXISTS (SELECT 1 FROM nota_servicio WHERE id_servicio = ?)');
        $consulta->bind_param('iii', $idServicio, $idServicio, $idServicio);
        $consulta->execute();
        $eliminado = $consulta->affected_rows === 1;
        $consulta->close();
        if (!$eliminado) {
            $consulta = $conn->prepare('SELECT id_servicio FROM servicio WHERE id_servicio = ?');
            $consulta->bind_param('i', $idServicio);
            $consulta->execute();
            $existe = $consulta->get_result()->fetch_assoc();
            $consulta->close();
            throw new InvalidArgumentException($existe
                ? 'No se puede eliminar el servicio porque está vinculado a notas.'
                : 'El servicio ya no existe.');
        }
    } finally {
        $conn->query("SELECT RELEASE_LOCK('softgaresp_catalogo_servicios')");
    }
}
