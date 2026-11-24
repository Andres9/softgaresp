<?php

if (isset($_SERVER['SCRIPT_FILENAME']) && basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    http_response_code(404);
    exit;
}

mysqli_report(MYSQLI_REPORT_OFF);
require_once __DIR__ . '/conexion.php';

function nombreImagenCatalogoPedido($imagen)
{
    if (!is_string($imagen)) {
        return '';
    }
    $nombre = str_replace('\\', '/', trim($imagen));
    if (strpos($nombre, 'imagenes_productos/') === 0) {
        $nombre = substr($nombre, strlen('imagenes_productos/'));
    }
    if (!preg_match('/\A[A-Za-z0-9][A-Za-z0-9._-]{0,249}\.(?:jpg|jpeg|png)\z/i', $nombre)) {
        return '';
    }
    return $nombre;
}

function proveedoresPedido()
{
    return array('Pixal Center', 'Seven Accesorios', 'Dipath', 'Tony Superpapelerías', 'CT Internacional', 'CVA', 'Grupo Loma');
}

function catalogoPedidoPorProveedor($proveedor)
{
    $catalogos = array(
        'Pixal Center' => array('tabla' => 'productos_pixal_center', 'directorio' => 'uploads/catalogo_pixal'),
        'Seven Accesorios' => array('tabla' => 'productos_seven_accesorios', 'directorio' => 'uploads/catalogo_seven')
    );
    return isset($catalogos[$proveedor]) ? $catalogos[$proveedor] : null;
}

function directorioImagenesPedido($proveedor)
{
    $catalogo = catalogoPedidoPorProveedor($proveedor);
    return $catalogo ? $catalogo['directorio'] : 'uploads/catalogo_pixal';
}

function imagenCatalogoPedidoDisponible($imagen, $proveedor = 'Pixal Center')
{
    if ($proveedor === 'Seven Accesorios' && is_string($imagen)) {
        $ruta = str_replace('\\', '/', trim($imagen));
        foreach (array('img/productos/', 'uploads/catalogo_seven/') as $prefijo) {
            if (strpos($ruta, $prefijo) === 0) {
                $imagen = substr($ruta, strlen($prefijo));
                break;
            }
        }
    }
    $nombre = nombreImagenCatalogoPedido($imagen);
    if ($nombre === '' || !is_file(__DIR__ . '/' . directorioImagenesPedido($proveedor) . '/' . $nombre)) {
        return '';
    }
    return $nombre;
}

if (!$conn) {
    error_log('No fue posible conectar con la base de datos para pedidos.');
    http_response_code(500);
    exit('No se pudo acceder al módulo de pedidos.');
}

if (!mysqli_set_charset($conn, 'utf8mb4')) {
    error_log('No fue posible establecer utf8mb4 para pedidos: ' . mysqli_error($conn));
    http_response_code(500);
    exit('No se pudo preparar el módulo de pedidos.');
}

$tablasPedidos = array(
    "CREATE TABLE IF NOT EXISTS pedidos (
        id_pedido INT UNSIGNED NOT NULL AUTO_INCREMENT,
        folio VARCHAR(32) NOT NULL,
        fecha_pedido DATE NOT NULL,
        proveedor VARCHAR(180) NOT NULL DEFAULT '',
        notas TEXT NOT NULL,
        envio DECIMAL(13,2) NOT NULL DEFAULT 0,
        total_compra DECIMAL(13,2) NOT NULL DEFAULT 0,
        total_venta DECIMAL(13,2) NOT NULL DEFAULT 0,
        creado_por VARCHAR(150) NOT NULL,
        creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id_pedido),
        UNIQUE KEY uq_pedidos_folio (folio),
        KEY idx_pedidos_fecha (fecha_pedido)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS pedido_detalles (
        id_detalle INT UNSIGNED NOT NULL AUTO_INCREMENT,
        id_pedido INT UNSIGNED NOT NULL,
        orden SMALLINT UNSIGNED NOT NULL,
        modelo VARCHAR(120) NOT NULL,
        cantidad DECIMAL(10,2) NOT NULL,
        descripcion VARCHAR(255) NOT NULL DEFAULT '',
        imagen VARCHAR(255) NOT NULL DEFAULT '',
        precio_compra DECIMAL(13,2) NOT NULL,
        total_compra DECIMAL(13,2) NOT NULL,
        margen_porcentaje DECIMAL(5,2) NOT NULL DEFAULT 0,
        precio_venta DECIMAL(13,2) NOT NULL DEFAULT 0,
        precio_final DECIMAL(13,2) NOT NULL DEFAULT 0,
        iva_porcentaje DECIMAL(5,2) NOT NULL DEFAULT 0,
        iva_monto DECIMAL(13,2) NOT NULL DEFAULT 0,
        ganancia_individual DECIMAL(13,2) NOT NULL DEFAULT 0,
        ganancia_total DECIMAL(13,2) NOT NULL DEFAULT 0,
        total_venta DECIMAL(13,2) NOT NULL DEFAULT 0,
        PRIMARY KEY (id_detalle),
        KEY idx_pedido_detalles_pedido (id_pedido),
        CONSTRAINT fk_pedido_detalles_pedido
            FOREIGN KEY (id_pedido) REFERENCES pedidos (id_pedido)
            ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS productos_pixal_center (
        id_producto INT UNSIGNED NOT NULL AUTO_INCREMENT,
        codigo VARCHAR(80) NOT NULL,
        descripcion TEXT NOT NULL,
        imagen VARCHAR(255) NOT NULL DEFAULT '',
        marca VARCHAR(120) NOT NULL DEFAULT '',
        precio DECIMAL(13,2) NOT NULL DEFAULT 0,
        PRIMARY KEY (id_producto),
        UNIQUE KEY uq_productos_pixal_codigo (codigo),
        KEY idx_productos_pixal_descripcion (descripcion(120))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS productos_seven_accesorios LIKE productos_pixal_center"
);

foreach ($tablasPedidos as $sqlTabla) {
    if (!mysqli_query($conn, $sqlTabla)) {
        error_log('No fue posible crear las tablas de pedidos: ' . mysqli_error($conn));
        http_response_code(500);
        exit('No se pudo preparar el almacenamiento de pedidos.');
    }
}

$columnaEnvio = mysqli_query($conn, "SHOW COLUMNS FROM pedidos LIKE 'envio'");
if ($columnaEnvio === false) {
    error_log('No fue posible revisar la columna de envío en pedidos: ' . mysqli_error($conn));
    http_response_code(500);
    exit('No se pudo preparar el almacenamiento de pedidos.');
}
if (mysqli_num_rows($columnaEnvio) === 0 && !mysqli_query(
    $conn,
    'ALTER TABLE pedidos ADD COLUMN envio DECIMAL(13,2) NOT NULL DEFAULT 0 AFTER notas'
)) {
    error_log('No fue posible agregar la columna de envío a pedidos: ' . mysqli_error($conn));
    http_response_code(500);
    exit('No se pudo preparar el almacenamiento de pedidos.');
}

$columnasIva = array(
    'iva_porcentaje' => 'DECIMAL(5,2) NOT NULL DEFAULT 0',
    'iva_monto' => 'DECIMAL(13,2) NOT NULL DEFAULT 0'
);
foreach ($columnasIva as $nombreColumna => $definicionColumna) {
    $resultadoColumna = mysqli_query($conn, "SHOW COLUMNS FROM pedido_detalles LIKE '" . $nombreColumna . "'");
    if ($resultadoColumna === false) {
        error_log('No fue posible revisar la columna ' . $nombreColumna . ' en pedido_detalles: ' . mysqli_error($conn));
        http_response_code(500);
        exit('No se pudo preparar el almacenamiento de pedidos.');
    }
    if (mysqli_num_rows($resultadoColumna) === 0 && !mysqli_query(
        $conn,
        'ALTER TABLE pedido_detalles ADD COLUMN ' . $nombreColumna . ' ' . $definicionColumna
    )) {
        error_log('No fue posible agregar la columna ' . $nombreColumna . ' a pedido_detalles: ' . mysqli_error($conn));
        http_response_code(500);
        exit('No se pudo preparar el almacenamiento de pedidos.');
    }
}
