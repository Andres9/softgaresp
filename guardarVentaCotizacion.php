<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
require_once __DIR__ . '/cotizaciones_db.php';

function ventaCotizacionFallida($mensaje)
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
    ventaCotizacionFallida('La solicitud expiró. Recarga la página e inténtalo nuevamente.');
}

$idCotizacion = isset($_POST['id_cotizacion']) ? filter_var($_POST['id_cotizacion'], FILTER_VALIDATE_INT) : false;
$anticipo = isset($_POST['anticipo']) && is_numeric($_POST['anticipo']) ? (float) $_POST['anticipo'] : -1;
if ($idCotizacion === false || $idCotizacion < 1 || !is_finite($anticipo) || $anticipo < 0) {
    ventaCotizacionFallida('Revisa la cotización seleccionada y el anticipo.');
}

if (!mysqli_begin_transaction($conn)) {
    ventaCotizacionFallida('No se pudo iniciar el registro de la venta.');
}

$consultaCotizacion = mysqli_prepare(
    $conn,
    'SELECT id_cotizacion, folio, total, estado FROM cotizaciones WHERE id_cotizacion = ? FOR UPDATE'
);
if (!$consultaCotizacion) {
    mysqli_rollback($conn);
    ventaCotizacionFallida('No se pudo cargar la cotización.');
}
$consultaCotizacion->bind_param('i', $idCotizacion);
if (!$consultaCotizacion->execute()) {
    error_log('No fue posible consultar la cotización para convertirla: ' . $consultaCotizacion->error);
    $consultaCotizacion->close();
    mysqli_rollback($conn);
    ventaCotizacionFallida('No se pudo cargar la cotización.');
}
$cotizacion = $consultaCotizacion->get_result()->fetch_assoc();
$consultaCotizacion->close();
if (!$cotizacion || $cotizacion['estado'] === 'Vendida') {
    mysqli_rollback($conn);
    ventaCotizacionFallida('La cotización no existe o ya fue convertida en venta.');
}

$totalVenta = round((float) $cotizacion['total'], 2);
$anticipo = round($anticipo, 2);
if ($totalVenta <= 0 || $anticipo > $totalVenta) {
    mysqli_rollback($conn);
    ventaCotizacionFallida('El anticipo no puede ser mayor que el total de la cotización.');
}

$saldo = round($totalVenta - $anticipo, 2);
$estadoVenta = $saldo <= 0 ? 'Pagada' : ($anticipo > 0 ? 'Parcial' : 'Pendiente');
$folioTemporal = 'PEND-' . bin2hex(random_bytes(8));
$fechaVenta = date('Y-m-d');
$creadoPor = (string) $_SESSION['usuario'];
$consultaVenta = mysqli_prepare(
    $conn,
    'INSERT INTO cotizacion_ventas
        (id_cotizacion, folio, fecha_venta, total, total_abonado, saldo, estado, creado_por)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
);
if (!$consultaVenta) {
    mysqli_rollback($conn);
    ventaCotizacionFallida('No se pudo crear el registro de venta.');
}
$consultaVenta->bind_param(
    'issdddss',
    $idCotizacion,
    $folioTemporal,
    $fechaVenta,
    $totalVenta,
    $anticipo,
    $saldo,
    $estadoVenta,
    $creadoPor
);
if (!$consultaVenta->execute()) {
    error_log('No fue posible crear la venta de cotización: ' . $consultaVenta->error);
    $consultaVenta->close();
    mysqli_rollback($conn);
    ventaCotizacionFallida('No se pudo crear la venta. Verifica si ya fue convertida.');
}
$idVenta = (int) $conn->insert_id;
$consultaVenta->close();

$folioVenta = 'VTA-' . date('Ym') . '-' . str_pad((string) $idVenta, 6, '0', STR_PAD_LEFT);
$actualizarFolio = mysqli_prepare($conn, 'UPDATE cotizacion_ventas SET folio = ? WHERE id_venta = ?');
if (!$actualizarFolio) {
    mysqli_rollback($conn);
    ventaCotizacionFallida('No se pudo completar el folio de venta.');
}
$actualizarFolio->bind_param('si', $folioVenta, $idVenta);
if (!$actualizarFolio->execute()) {
    $actualizarFolio->close();
    mysqli_rollback($conn);
    ventaCotizacionFallida('No se pudo completar la venta.');
}
$actualizarFolio->close();

if ($anticipo > 0) {
    $notasAbono = 'Anticipo inicial';
    $consultaAbono = mysqli_prepare(
        $conn,
        'INSERT INTO cotizacion_venta_abonos (id_venta, monto, recibido_por, notas) VALUES (?, ?, ?, ?)'
    );
    if (!$consultaAbono) {
        mysqli_rollback($conn);
        ventaCotizacionFallida('No se pudo registrar el anticipo.');
    }
    $consultaAbono->bind_param('idss', $idVenta, $anticipo, $creadoPor, $notasAbono);
    if (!$consultaAbono->execute()) {
        error_log('No fue posible registrar el anticipo de venta: ' . $consultaAbono->error);
        $consultaAbono->close();
        mysqli_rollback($conn);
        ventaCotizacionFallida('No se pudo registrar el anticipo.');
    }
    $consultaAbono->close();
}

$actualizarCotizacion = mysqli_prepare($conn, "UPDATE cotizaciones SET estado = 'Vendida' WHERE id_cotizacion = ?");
if (!$actualizarCotizacion) {
    mysqli_rollback($conn);
    ventaCotizacionFallida('No se pudo actualizar el estado de la cotización.');
}
$actualizarCotizacion->bind_param('i', $idCotizacion);
if (!$actualizarCotizacion->execute()) {
    error_log('No fue posible marcar la cotización como vendida: ' . $actualizarCotizacion->error);
    $actualizarCotizacion->close();
    mysqli_rollback($conn);
    ventaCotizacionFallida('No se pudo actualizar el estado de la cotización.');
}
$actualizarCotizacion->close();

if (!mysqli_commit($conn)) {
    mysqli_rollback($conn);
    ventaCotizacionFallida('No se pudo finalizar el registro de venta.');
}

header('Location: cotizacion.php?venta_creada=' . $idVenta);
exit;
