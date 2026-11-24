<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
require_once __DIR__ . '/pedidos_db.php';

header('Content-Type: application/json; charset=utf-8');
$proveedor = isset($_GET['proveedor']) && is_string($_GET['proveedor']) ? trim($_GET['proveedor']) : '';
$catalogo = catalogoPedidoPorProveedor($proveedor);
if ($catalogo === null) {
    echo json_encode(array());
    exit;
}

$resultado = mysqli_query(
    $conn,
    'SELECT codigo, descripcion, imagen, marca, precio
     FROM `' . $catalogo['tabla'] . '` ORDER BY codigo LIMIT 5000'
);
if ($resultado === false) {
    error_log('No fue posible consultar el catálogo de ' . $proveedor . ': ' . mysqli_error($conn));
    http_response_code(500);
    echo json_encode(array('error' => 'No se pudo cargar el catálogo.'));
    exit;
}

$productos = array();
while ($producto = mysqli_fetch_assoc($resultado)) {
    $imagen = imagenCatalogoPedidoDisponible($producto['imagen'], $proveedor);
    $productos[] = array(
        'codigo' => (string) $producto['codigo'],
        'descripcion' => (string) $producto['descripcion'],
        'imagen' => $imagen === '' ? '' : $catalogo['directorio'] . '/' . $imagen,
        'marca' => (string) $producto['marca'],
        'precio' => (float) $producto['precio']
    );
}

echo json_encode($productos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
