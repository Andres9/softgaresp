<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
mysqli_report(MYSQLI_REPORT_OFF);
require_once __DIR__ . '/ventas_db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}
if (!isset($_POST['token_csrf'], $_SESSION['token_csrf']) || !is_string($_POST['token_csrf']) ||
    !hash_equals($_SESSION['token_csrf'], $_POST['token_csrf'])) {
    http_response_code(403);
    exit('La sesión del formulario no es válida. Recarga la página e inténtalo nuevamente.');
}

function rechazarVenta($mensaje)
{
    $_SESSION['venta_borrador'] = array(
        'productos' => isset($_POST['productos']) && is_array($_POST['productos']) ? array_slice($_POST['productos'], 0, 100) : array(),
        'factura' => isset($_POST['factura']) && is_string($_POST['factura']) ? $_POST['factura'] : 'no'
    );
    $_SESSION['venta_error'] = $mensaje;
    header('Location: ventas.php');
    exit;
}

$productos = isset($_POST['productos']) ? $_POST['productos'] : null;
$factura = isset($_POST['factura']) ? $_POST['factura'] : null;
if (!is_array($productos) || count($productos) < 1 || count($productos) > 100 ||
    !is_string($factura) || !in_array($factura, array('no', 'factura'), true)) {
    rechazarVenta('La venta debe contener entre 1 y 100 productos y una opción de factura válida.');
}

$lineas = array();
foreach ($productos as $producto) {
    if (!is_array($producto)) {
        rechazarVenta('Revisa los datos de todos los productos.');
    }
    $cantidad = isset($producto['cantidad']) && is_scalar($producto['cantidad'])
        ? filter_var($producto['cantidad'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 2147483647))) : false;
    $seccion = isset($producto['seccion']) && is_scalar($producto['seccion'])
        ? filter_var($producto['seccion'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 2147483647))) : false;
    $descripcion = isset($producto['descripcion']) && is_string($producto['descripcion']) ? trim($producto['descripcion']) : '';
    $precio = centavosVenta(isset($producto['precio']) ? $producto['precio'] : null);
    $compra = centavosVenta(isset($producto['pc']) ? $producto['pc'] : null);
    if ($cantidad === false || $seccion === false || $descripcion === '' || !mb_check_encoding($descripcion, 'UTF-8') ||
        mb_strlen($descripcion, 'UTF-8') > 50 || $precio === null || $compra === null ||
        abs($precio) > intdiv(9999999999999, $cantidad)) {
        rechazarVenta('Revisa cada producto: cantidad y sección válidas, descripción de hasta 50 caracteres e importes con hasta dos decimales dentro del límite permitido.');
    }
    $ganancia = $compra !== 0 ? $precio * $cantidad - $compra : 0;
    if (abs($ganancia) > 9999999999999) {
        rechazarVenta('La ganancia del producto supera el límite permitido.');
    }
    $lineas[] = array($cantidad, $descripcion, importeVenta($precio * $cantidad), importeVenta($compra), importeVenta($ganancia), $seccion);
}

date_default_timezone_set('America/Mexico_City');
$fecha = date('Y-m-d H:i:s');
$transaccion = false;
try {
    if (!mysqli_begin_transaction($conn)) {
        throw new RuntimeException(mysqli_error($conn));
    }
    $transaccion = true;
    if (!mysqli_query($conn, 'INSERT INTO ventas_grupos () VALUES ()')) {
        throw new RuntimeException(mysqli_error($conn));
    }
    $grupo = mysqli_insert_id($conn);
    $consulta = mysqli_prepare($conn, 'INSERT INTO ventas (cantidad, descripcion, costo, preciocosto, ganancia, seccion, factura, fecha_venta, id_grupo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    if (!$consulta) {
        throw new RuntimeException(mysqli_error($conn));
    }
    foreach ($lineas as $linea) {
        list($cantidad, $descripcion, $costo, $compra, $ganancia, $seccion) = $linea;
        if (!mysqli_stmt_bind_param($consulta, 'issssissi', $cantidad, $descripcion, $costo, $compra, $ganancia, $seccion, $factura, $fecha, $grupo) ||
            !mysqli_stmt_execute($consulta)) {
            throw new RuntimeException(mysqli_stmt_error($consulta));
        }
    }
    mysqli_stmt_close($consulta);
    if (!mysqli_commit($conn)) {
        throw new RuntimeException(mysqli_error($conn));
    }
    $transaccion = false;
} catch (RuntimeException $error) {
    if ($transaccion && !mysqli_rollback($conn)) {
        error_log('No se pudo revertir la venta: ' . mysqli_error($conn));
    }
    error_log('No se pudo registrar la venta: ' . $error->getMessage());
    rechazarVenta('No se pudo registrar la venta. Verifica las secciones e inténtalo nuevamente.');
}
$_SESSION['venta_exito'] = 'Venta registrada correctamente.';
header('Location: ventas.php');
exit;
