<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
mysqli_report(MYSQLI_REPORT_OFF);
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/movimientos_db.php';

$esRecarga = isset($_POST['origen_formulario']) && $_POST['origen_formulario'] === 'recargas';

function rechazarMovimiento($mensaje)
{
    global $esRecarga;
    $_SESSION[$esRecarga ? 'recarga_mensaje' : 'movimiento_mensaje'] = array('tipo' => 'error', 'texto' => $mensaje);
    header('Location: ' . ($esRecarga ? 'recargas.php' : 'movimiento.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}
if (!isset($_POST['token_csrf'], $_SESSION['token_csrf']) || !is_string($_POST['token_csrf']) ||
    !hash_equals($_SESSION['token_csrf'], $_POST['token_csrf'])) {
    http_response_code(403);
    exit('La sesión del formulario no es válida. Recarga la página e inténtalo nuevamente.');
}
if (!$conn) {
    error_log('No fue posible conectar con la base de datos para guardar un movimiento.');
    http_response_code(500);
    exit('No se pudo guardar el movimiento.');
}

$fecha = isset($_POST['fecha']) && is_string($_POST['fecha']) ? $_POST['fecha'] : '';
$tipo = isset($_POST['tipo']) && is_string($_POST['tipo']) ? $_POST['tipo'] : '';
$categoria = isset($_POST['categoria']) && is_string($_POST['categoria']) ? $_POST['categoria'] : '';
$concepto = isset($_POST['concepto']) && is_string($_POST['concepto']) ? trim($_POST['concepto']) : '';
$cantidadCapturada = isset($_POST['cantidad']) && is_string($_POST['cantidad']) ? trim($_POST['cantidad']) : '';
$cantidad = $cantidadCapturada === '' ? '0.00' : validarDecimalMovimiento($cantidadCapturada, 10);
$importe = validarDecimalMovimiento(isset($_POST['importe']) && is_string($_POST['importe']) ? $_POST['importe'] : '', 11);
$fechaValidada = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
$erroresFecha = DateTimeImmutable::getLastErrors();

if ($fechaValidada === false || ($erroresFecha !== false && ($erroresFecha['warning_count'] || $erroresFecha['error_count'])) ||
    $fechaValidada->format('Y-m-d') !== $fecha || ($tipo !== 'ingreso' && $tipo !== 'gasto') ||
    ($esRecarga && $categoria !== 'Recargas') ||
    (!$esRecarga && (($tipo === 'ingreso' && $categoria !== 'Otros ingresos') ||
        ($tipo === 'gasto' && $categoria !== 'Gastos'))) || $concepto === '' ||
    !mb_check_encoding($concepto, 'UTF-8') || mb_strlen($concepto, 'UTF-8') > 150 ||
    $importe === null || (float) $importe <= 0 || $cantidad === null) {
    rechazarMovimiento('Revisa la fecha, categoría, concepto, cantidad y el importe del movimiento.');
}

try {
    prepararTablaMovimientos($conn);
} catch (RuntimeException $error) {
    error_log($error->getMessage());
    http_response_code(500);
    exit('No se pudo preparar el registro de movimientos.');
}

$ganancia = '0.00';
$usuario = isset($_SESSION['usuario']) ? (string) $_SESSION['usuario'] : '';
$consulta = mysqli_prepare(
    $conn,
    "INSERT INTO movimientos (fecha_movimiento, tipo, categoria, concepto, cantidad, importe, ganancia, fuente, creado_por)
     VALUES (?, ?, ?, ?, ?, ?, ?, 'manual', ?)"
);
if (!$consulta || !mysqli_stmt_bind_param($consulta, 'ssssssss', $fecha, $tipo, $categoria, $concepto, $cantidad, $importe, $ganancia, $usuario) ||
    !mysqli_stmt_execute($consulta)) {
    $error = $consulta ? mysqli_stmt_error($consulta) : mysqli_error($conn);
    error_log('No fue posible guardar el movimiento: ' . $error);
    if ($consulta) {
        mysqli_stmt_close($consulta);
    }
    rechazarMovimiento('No se pudo guardar el movimiento. Inténtalo nuevamente.');
}
mysqli_stmt_close($consulta);

$_SESSION[$esRecarga ? 'recarga_mensaje' : 'movimiento_mensaje'] = array(
    'tipo' => 'exito',
    'texto' => $esRecarga ? 'Operación de recarga registrada correctamente.' : 'Movimiento registrado correctamente.'
);
header('Location: ' . ($esRecarga ? 'recargas.php' : 'movimiento.php') . '?desde=' . rawurlencode($fecha) . '&hasta=' . rawurlencode($fecha));
exit;
