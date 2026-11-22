<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
require_once __DIR__ . '/cotizaciones_db.php';

function abonoVentaFallido($mensaje)
{
    $_SESSION['venta_error'] = $mensaje;
    header('Location: cotizacion.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método no permitido.');
}

if (
    !isset($_POST['token_csrf']) || !is_string($_POST['token_csrf']) ||
    !hash_equals(token_csrf(), $_POST['token_csrf'])
) {
    http_response_code(403);
    abonoVentaFallido('La solicitud expiró. Recarga la página e inténtalo nuevamente.');
}

$idVenta = isset($_POST['id_venta']) ? filter_var($_POST['id_venta'], FILTER_VALIDATE_INT) : false;
$abono = isset($_POST['abono']) && is_numeric($_POST['abono']) ? round((float) $_POST['abono'], 2) : 0;
if ($idVenta === false || $idVenta < 1 || !is_finite($abono) || $abono <= 0) {
    abonoVentaFallido('Escribe un abono válido.');
}

if (!mysqli_begin_transaction($conn)) {
    abonoVentaFallido('No se pudo iniciar el registro del abono.');
}
$consultaVenta = mysqli_prepare(
    $conn,
    'SELECT saldo FROM cotizacion_ventas WHERE id_venta = ? FOR UPDATE'
);
if (!$consultaVenta) {
    mysqli_rollback($conn);
    abonoVentaFallido('No se pudo cargar la venta.');
}
$consultaVenta->bind_param('i', $idVenta);
if (!$consultaVenta->execute()) {
    error_log('No fue posible consultar el saldo de venta: ' . $consultaVenta->error);
    $consultaVenta->close();
    mysqli_rollback($conn);
    abonoVentaFallido('No se pudo cargar la venta.');
}
$venta = $consultaVenta->get_result()->fetch_assoc();
$consultaVenta->close();
if (!$venta || $abono > (float) $venta['saldo']) {
    mysqli_rollback($conn);
    abonoVentaFallido('El abono debe ser menor o igual al saldo pendiente.');
}

$recibidoPor = (string) $_SESSION['usuario'];
$notasAbono = '';
$consultaAbono = mysqli_prepare(
    $conn,
    'INSERT INTO cotizacion_venta_abonos (id_venta, monto, recibido_por, notas) VALUES (?, ?, ?, ?)'
);
if (!$consultaAbono) {
    mysqli_rollback($conn);
    abonoVentaFallido('No se pudo registrar el abono.');
}
$consultaAbono->bind_param('idss', $idVenta, $abono, $recibidoPor, $notasAbono);
if (!$consultaAbono->execute()) {
    error_log('No fue posible insertar el abono de venta: ' . $consultaAbono->error);
    $consultaAbono->close();
    mysqli_rollback($conn);
    abonoVentaFallido('No se pudo registrar el abono.');
}
$consultaAbono->close();

$saldoNuevo = round((float) $venta['saldo'] - $abono, 2);
$estadoNuevo = $saldoNuevo <= 0 ? 'Pagada' : 'Parcial';
$actualizarVenta = mysqli_prepare(
    $conn,
    'UPDATE cotizacion_ventas
     SET total_abonado = total_abonado + ?, saldo = ?, estado = ?
     WHERE id_venta = ?'
);
if (!$actualizarVenta) {
    mysqli_rollback($conn);
    abonoVentaFallido('No se pudo recalcular el saldo de venta.');
}
$actualizarVenta->bind_param('ddsi', $abono, $saldoNuevo, $estadoNuevo, $idVenta);
if (!$actualizarVenta->execute()) {
    error_log('No fue posible actualizar el saldo de venta: ' . $actualizarVenta->error);
    $actualizarVenta->close();
    mysqli_rollback($conn);
    abonoVentaFallido('No se pudo recalcular el saldo de venta.');
}
$actualizarVenta->close();

if (!mysqli_commit($conn)) {
    mysqli_rollback($conn);
    abonoVentaFallido('No se pudo finalizar el registro del abono.');
}

header('Location: cotizacion.php');
exit;
