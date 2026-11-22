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
    exit('La sesión del formulario no es válida.');
}
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
$cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 2147483647)));
$seccion = filter_input(INPUT_POST, 'opcionnegocio', FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 2147483647)));
$descripcion = isset($_POST['descripcion']) && is_string($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
$costo = centavosVenta(isset($_POST['costo']) ? $_POST['costo'] : null);
$compraTexto = isset($_POST['pc']) && is_string($_POST['pc']) ? $_POST['pc'] : null;
$gananciaTexto = isset($_POST['g']) && is_string($_POST['g']) ? $_POST['g'] : null;
$compra = $compraTexto === '' ? null : centavosVenta($compraTexto);
$ganancia = $gananciaTexto === '' ? null : centavosVenta($gananciaTexto);
$factura = isset($_POST['factura']) && is_string($_POST['factura']) ? trim($_POST['factura']) : '';
$fecha = isset($_POST['fechaVenta']) && is_string($_POST['fechaVenta']) ? $_POST['fechaVenta'] : '';
$fechaValidada = DateTime::createFromFormat('!Y-m-d H:i:s', $fecha);
if (!$id || !$cantidad || !$seccion || $descripcion === '' || !mb_check_encoding($descripcion, 'UTF-8') ||
    mb_strlen($descripcion, 'UTF-8') > 50 || $costo === null ||
    ($compra === null && $compraTexto !== '') || ($ganancia === null && $gananciaTexto !== '') ||
    strlen($factura) > 65535 || !$fechaValidada || $fechaValidada->format('Y-m-d H:i:s') !== $fecha) {
    http_response_code(400);
    exit('Los datos de la venta no son válidos. Regresa al formulario y revísalos.');
}
$costo = importeVenta($costo);
$compra = $compra === null ? null : importeVenta($compra);
$ganancia = $ganancia === null ? null : importeVenta($ganancia);

$transaccion = false;
try {
    if (!mysqli_begin_transaction($conn)) throw new RuntimeException(mysqli_error($conn));
    $transaccion = true;
    $consulta = mysqli_prepare($conn, 'SELECT id_grupo FROM ventas WHERE id_venta = ? FOR UPDATE');
    if (!$consulta) throw new RuntimeException(mysqli_error($conn));
    $consulta->bind_param('i', $id);
    if (!$consulta->execute()) throw new RuntimeException($consulta->error);
    $resultado = $consulta->get_result();
    if (!$resultado) throw new RuntimeException($consulta->error);
    $venta = $resultado->fetch_assoc();
    $consulta->close();
    if (!$venta) {
        mysqli_rollback($conn);
        http_response_code(404);
        exit('No se encontró la venta.');
    }

    $consulta = mysqli_prepare($conn, 'UPDATE ventas SET cantidad = ?, descripcion = ?, costo = ?, preciocosto = ?, ganancia = ?, seccion = ?, factura = ?, fecha_venta = ? WHERE id_venta = ?');
    if (!$consulta) throw new RuntimeException(mysqli_error($conn));
    $consulta->bind_param('issssissi', $cantidad, $descripcion, $costo, $compra, $ganancia, $seccion, $factura, $fecha, $id);
    if (!$consulta->execute()) throw new RuntimeException($consulta->error);
    $consulta->close();
    if ($venta['id_grupo'] !== null) {
        $grupo = (int) $venta['id_grupo'];
        $consulta = mysqli_prepare($conn, 'UPDATE ventas SET factura = ?, fecha_venta = ? WHERE id_grupo = ?');
        if (!$consulta) throw new RuntimeException(mysqli_error($conn));
        $consulta->bind_param('ssi', $factura, $fecha, $grupo);
        if (!$consulta->execute()) throw new RuntimeException($consulta->error);
        $consulta->close();
    }
    if (!mysqli_commit($conn)) throw new RuntimeException(mysqli_error($conn));
    $transaccion = false;
} catch (RuntimeException $error) {
    if ($transaccion && !mysqli_rollback($conn)) error_log('No se pudo revertir la edición de la venta: ' . mysqli_error($conn));
    error_log('No se pudo actualizar la venta: ' . $error->getMessage());
    http_response_code(500);
    exit('No se pudo actualizar la venta. Regresa al formulario e inténtalo nuevamente.');
}
$_SESSION['venta_exito'] = 'Producto de la venta actualizado correctamente.';
header('Location: ventas.php');
exit;
