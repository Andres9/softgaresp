<?php

if (isset($_SERVER['SCRIPT_FILENAME']) && basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    http_response_code(404);
    exit;
}

mysqli_report(MYSQLI_REPORT_OFF);
require_once __DIR__ . '/conexion.php';

if (!$conn) {
    error_log('No fue posible conectar con la base de datos para cotizaciones.');
    http_response_code(500);
    exit('No se pudo acceder al módulo de cotizaciones.');
}

if (!mysqli_set_charset($conn, 'utf8mb4')) {
    error_log('No fue posible establecer utf8mb4 para cotizaciones: ' . mysqli_error($conn));
    http_response_code(500);
    exit('No se pudo preparar el módulo de cotizaciones.');
}

$tablasCotizaciones = array(
    "CREATE TABLE IF NOT EXISTS cotizaciones (
        id_cotizacion INT UNSIGNED NOT NULL AUTO_INCREMENT,
        folio VARCHAR(32) NOT NULL,
        cliente_nombre VARCHAR(180) NOT NULL,
        cliente_empresa VARCHAR(180) NOT NULL DEFAULT '',
        cliente_email VARCHAR(180) NOT NULL DEFAULT '',
        cliente_telefono VARCHAR(50) NOT NULL DEFAULT '',
        cliente_rfc VARCHAR(30) NOT NULL DEFAULT '',
        cliente_direccion TEXT NOT NULL,
        fecha_cotizacion DATE NOT NULL,
        vigencia_hasta DATE NOT NULL,
        moneda CHAR(3) NOT NULL DEFAULT 'MXN',
        condiciones_pago VARCHAR(255) NOT NULL DEFAULT '',
        tiempo_entrega VARCHAR(180) NOT NULL DEFAULT '',
        garantia VARCHAR(180) NOT NULL DEFAULT '',
        notas TEXT NOT NULL,
        estado VARCHAR(30) NOT NULL DEFAULT 'Emitida',
        subtotal DECIMAL(13,2) NOT NULL DEFAULT 0,
        descuento_total DECIMAL(13,2) NOT NULL DEFAULT 0,
        impuestos_total DECIMAL(13,2) NOT NULL DEFAULT 0,
        total DECIMAL(13,2) NOT NULL DEFAULT 0,
        creado_por VARCHAR(150) NOT NULL,
        creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id_cotizacion),
        UNIQUE KEY uq_cotizaciones_folio (folio),
        KEY idx_cotizaciones_fecha (fecha_cotizacion),
        KEY idx_cotizaciones_cliente (cliente_nombre)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS cotizacion_detalles (
        id_detalle INT UNSIGNED NOT NULL AUTO_INCREMENT,
        id_cotizacion INT UNSIGNED NOT NULL,
        orden SMALLINT UNSIGNED NOT NULL,
        tipo_producto VARCHAR(100) NOT NULL DEFAULT '',
        marca VARCHAR(100) NOT NULL DEFAULT '',
        modelo VARCHAR(120) NOT NULL DEFAULT '',
        sku VARCHAR(100) NOT NULL DEFAULT '',
        descripcion VARCHAR(255) NOT NULL,
        especificaciones TEXT NOT NULL,
        cantidad DECIMAL(10,2) NOT NULL,
        precio_unitario DECIMAL(13,2) NOT NULL,
        descuento_porcentaje DECIMAL(5,2) NOT NULL DEFAULT 0,
        impuesto_porcentaje DECIMAL(5,2) NOT NULL DEFAULT 0,
        subtotal DECIMAL(13,2) NOT NULL,
        descuento DECIMAL(13,2) NOT NULL DEFAULT 0,
        impuesto DECIMAL(13,2) NOT NULL DEFAULT 0,
        total DECIMAL(13,2) NOT NULL,
        garantia VARCHAR(180) NOT NULL DEFAULT '',
        imagen VARCHAR(80) NOT NULL DEFAULT '',
        PRIMARY KEY (id_detalle),
        KEY idx_cotizacion_detalles_cotizacion (id_cotizacion),
        CONSTRAINT fk_cotizacion_detalles_cotizacion
            FOREIGN KEY (id_cotizacion) REFERENCES cotizaciones (id_cotizacion)
            ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS cotizacion_ventas (
        id_venta INT UNSIGNED NOT NULL AUTO_INCREMENT,
        id_cotizacion INT UNSIGNED NOT NULL,
        folio VARCHAR(32) NOT NULL,
        fecha_venta DATE NOT NULL,
        total DECIMAL(13,2) NOT NULL,
        total_abonado DECIMAL(13,2) NOT NULL DEFAULT 0,
        saldo DECIMAL(13,2) NOT NULL,
        estado VARCHAR(20) NOT NULL DEFAULT 'Pendiente',
        creado_por VARCHAR(150) NOT NULL,
        creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id_venta),
        UNIQUE KEY uq_cotizacion_ventas_cotizacion (id_cotizacion),
        UNIQUE KEY uq_cotizacion_ventas_folio (folio),
        CONSTRAINT fk_cotizacion_ventas_cotizacion
            FOREIGN KEY (id_cotizacion) REFERENCES cotizaciones (id_cotizacion)
            ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS cotizacion_venta_abonos (
        id_abono INT UNSIGNED NOT NULL AUTO_INCREMENT,
        id_venta INT UNSIGNED NOT NULL,
        fecha_abono DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        monto DECIMAL(13,2) NOT NULL,
        recibido_por VARCHAR(150) NOT NULL,
        notas VARCHAR(255) NOT NULL DEFAULT '',
        PRIMARY KEY (id_abono),
        KEY idx_cotizacion_venta_abonos_venta (id_venta),
        CONSTRAINT fk_cotizacion_venta_abonos_venta
            FOREIGN KEY (id_venta) REFERENCES cotizacion_ventas (id_venta)
            ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

foreach ($tablasCotizaciones as $sqlTabla) {
    if (!mysqli_query($conn, $sqlTabla)) {
        error_log('No fue posible crear las tablas de cotizaciones: ' . mysqli_error($conn));
        http_response_code(500);
        exit('No se pudo preparar el almacenamiento de cotizaciones.');
    }
}
