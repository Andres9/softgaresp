<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
mysqli_report(MYSQLI_REPORT_OFF);
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/cierres_mensuales_db.php';

function rechazarCierreMensual($mensaje)
{
    $_SESSION['cierre_mensual_mensaje'] = array('tipo' => 'error', 'texto' => $mensaje);
    $mes = isset($_POST['mes']) && is_string($_POST['mes']) &&
        preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])$/D', $_POST['mes'])
        ? $_POST['mes']
        : date('Y-m');
    header('Location: cierre_mensual.php?mes=' . rawurlencode($mes));
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
    error_log('No fue posible conectar con la base de datos para guardar un cierre mensual.');
    http_response_code(500);
    exit('No se pudo guardar el cierre mensual.');
}

$mes = isset($_POST['mes']) && is_string($_POST['mes']) ? $_POST['mes'] : '';
$mesValidado = DateTimeImmutable::createFromFormat('!Y-m', $mes);
$erroresMes = DateTimeImmutable::getLastErrors();
if ($mesValidado === false ||
    ($erroresMes !== false && ($erroresMes['warning_count'] || $erroresMes['error_count'])) ||
    $mesValidado->format('Y-m') !== $mes) {
    rechazarCierreMensual('Selecciona un periodo válido.');
}
$periodo = $mesValidado->format('Y-m-01');

$camposImporte = array(
    'local_adeudo_ganancia' => 'Adeudo o ganancia del local',
    'local_ganancia' => 'Ganancia del local',
    'inventario_actual' => 'Inventario actual',
    'inventario_deuda' => 'Deuda del inventario'
);
$valoresImporte = array();
foreach ($camposImporte as $campo => $etiqueta) {
    $capturado = isset($_POST[$campo]) && is_string($_POST[$campo]) ? trim($_POST[$campo]) : '';
    $importeValidado = validarImporteCierreMensual($capturado);
    if ($importeValidado === null ||
        (in_array($campo, array('inventario_actual', 'inventario_deuda'), true) && (float) $importeValidado < 0)) {
        rechazarCierreMensual('Revisa el importe de "' . $etiqueta . '".');
    }
    $valoresImporte[$campo] = $importeValidado;
}

$secciones = seccionesCierreMensual();
$itemsCapturados = isset($_POST['items']) && is_array($_POST['items']) ? $_POST['items'] : array();
$itemsValidados = array();
foreach ($secciones as $seccion => $nombreSeccion) {
    $datosSeccion = isset($itemsCapturados[$seccion]) && is_array($itemsCapturados[$seccion])
        ? $itemsCapturados[$seccion]
        : array();
    $conceptos = isset($datosSeccion['concepto']) && is_array($datosSeccion['concepto'])
        ? $datosSeccion['concepto']
        : array();
    $importes = isset($datosSeccion['importe']) && is_array($datosSeccion['importe'])
        ? $datosSeccion['importe']
        : array();
    if (count($conceptos) > 30 || count($conceptos) !== count($importes)) {
        rechazarCierreMensual('Revisa los conceptos de la sección "' . $nombreSeccion . '".');
    }

    $itemsValidados[$seccion] = array();
    foreach ($conceptos as $indice => $conceptoCapturado) {
        if (!is_string($conceptoCapturado) || !isset($importes[$indice]) || !is_string($importes[$indice])) {
            rechazarCierreMensual('Revisa los conceptos de la sección "' . $nombreSeccion . '".');
        }
        $concepto = trim($conceptoCapturado);
        $importeCapturado = trim($importes[$indice]);
        if ($concepto === '' && $importeCapturado === '') {
            continue;
        }
        if ($concepto === '' || !mb_check_encoding($concepto, 'UTF-8') ||
            mb_strlen($concepto, 'UTF-8') > 80) {
            rechazarCierreMensual('Cada importe debe tener un concepto válido en "' . $nombreSeccion . '".');
        }

        $importe = $importeCapturado === '' ? '0.00' : validarImporteCierreMensual($importeCapturado);
        if ($importe === null) {
            rechazarCierreMensual('Revisa los importes de la sección "' . $nombreSeccion . '".');
        }
        $itemsValidados[$seccion][] = array('concepto' => $concepto, 'importe' => $importe);
    }
}

$cuentasCapturadas = isset($_POST['cuentas']) && is_array($_POST['cuentas']) ? $_POST['cuentas'] : array();
$cuentasValidadas = array();
foreach (cuentasCierreMensual() as $clave => $nombre) {
    if (!isset($cuentasCapturadas[$clave]) || !is_array($cuentasCapturadas[$clave]) ||
        !isset($cuentasCapturadas[$clave]['saldo'], $cuentasCapturadas[$clave]['fecha']) ||
        !is_string($cuentasCapturadas[$clave]['saldo']) || !is_string($cuentasCapturadas[$clave]['fecha'])) {
        rechazarCierreMensual('Revisa los datos de la cuenta ' . $nombre . '. Recarga el formulario si es necesario.');
    }
    $saldo = trim($cuentasCapturadas[$clave]['saldo']);
    $fecha = trim($cuentasCapturadas[$clave]['fecha']);
    if ($saldo === '' && $fecha === '') {
        continue;
    }
    $saldoValidado = validarImporteCierreMensual($saldo);
    $fechaValidada = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
    $erroresFecha = DateTimeImmutable::getLastErrors();
    if ($saldoValidado === null || $fechaValidada === false ||
        ($erroresFecha !== false && ($erroresFecha['warning_count'] || $erroresFecha['error_count'])) ||
        $fechaValidada->format('Y-m-d') !== $fecha) {
        rechazarCierreMensual('Captura un saldo y una fecha válidos para la cuenta ' . $nombre . '.');
    }
    $cuentasValidadas[$clave] = array('saldo' => $saldoValidado, 'fecha' => $fecha);
}

try {
    prepararTablasCierreMensual($conn);
} catch (RuntimeException $error) {
    error_log($error->getMessage());
    http_response_code(500);
    exit('No se pudo preparar el cierre mensual.');
}

$usuario = isset($_SESSION['usuario']) ? (string) $_SESSION['usuario'] : '';
if (!mysqli_begin_transaction($conn)) {
    error_log('No fue posible iniciar la transacción del cierre: ' . mysqli_error($conn));
    rechazarCierreMensual('No se pudo guardar el cierre. Inténtalo nuevamente.');
}

$consultaCierre = mysqli_prepare(
    $conn,
    "INSERT INTO cierres_mensuales
        (periodo, local_adeudo_ganancia, local_ganancia, inventario_actual, inventario_deuda, creado_por, actualizado_por)
     VALUES (?, ?, ?, ?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE
        id_cierre = LAST_INSERT_ID(id_cierre),
        local_adeudo_ganancia = VALUES(local_adeudo_ganancia),
        local_ganancia = VALUES(local_ganancia),
        inventario_actual = VALUES(inventario_actual),
        inventario_deuda = VALUES(inventario_deuda),
        actualizado_por = VALUES(actualizado_por)"
);
$guardado = $consultaCierre &&
    mysqli_stmt_bind_param(
        $consultaCierre,
        'sssssss',
        $periodo,
        $valoresImporte['local_adeudo_ganancia'],
        $valoresImporte['local_ganancia'],
        $valoresImporte['inventario_actual'],
        $valoresImporte['inventario_deuda'],
        $usuario,
        $usuario
    ) &&
    mysqli_stmt_execute($consultaCierre);
if (!$guardado) {
    $error = $consultaCierre ? mysqli_stmt_error($consultaCierre) : mysqli_error($conn);
    error_log('No fue posible guardar el encabezado del cierre: ' . $error);
    if ($consultaCierre) {
        mysqli_stmt_close($consultaCierre);
    }
    mysqli_rollback($conn);
    rechazarCierreMensual('No se pudo guardar el cierre. Inténtalo nuevamente.');
}
$idCierre = (int) mysqli_insert_id($conn);
mysqli_stmt_close($consultaCierre);

$eliminarDetalles = mysqli_prepare($conn, "DELETE FROM cierres_mensuales_detalle WHERE id_cierre = ? AND seccion IN ('local', 'fondo', 'deuda')");
if (!$eliminarDetalles || !mysqli_stmt_bind_param($eliminarDetalles, 'i', $idCierre) ||
    !mysqli_stmt_execute($eliminarDetalles)) {
    $error = $eliminarDetalles ? mysqli_stmt_error($eliminarDetalles) : mysqli_error($conn);
    error_log('No fue posible reemplazar el detalle del cierre mensual: ' . $error);
    if ($eliminarDetalles) {
        mysqli_stmt_close($eliminarDetalles);
    }
    mysqli_rollback($conn);
    rechazarCierreMensual('No se pudo actualizar el detalle del cierre.');
}
mysqli_stmt_close($eliminarDetalles);

$insertarDetalle = mysqli_prepare(
    $conn,
    'INSERT INTO cierres_mensuales_detalle (id_cierre, seccion, concepto, importe, posicion)
     VALUES (?, ?, ?, ?, ?)'
);
if (!$insertarDetalle) {
    error_log('No fue posible preparar los conceptos del cierre mensual: ' . mysqli_error($conn));
    mysqli_rollback($conn);
    rechazarCierreMensual('No se pudieron guardar los conceptos del cierre.');
}

foreach ($itemsValidados as $seccion => $items) {
    foreach ($items as $posicion => $item) {
        $posicion = (int) $posicion;
        $concepto = $item['concepto'];
        $importe = $item['importe'];
        if (!mysqli_stmt_bind_param($insertarDetalle, 'isssi', $idCierre, $seccion, $concepto, $importe, $posicion) ||
            !mysqli_stmt_execute($insertarDetalle)) {
            error_log('No fue posible guardar un concepto del cierre: ' . mysqli_stmt_error($insertarDetalle));
            mysqli_stmt_close($insertarDetalle);
            mysqli_rollback($conn);
            rechazarCierreMensual('No se pudieron guardar los conceptos del cierre.');
        }
    }
}
mysqli_stmt_close($insertarDetalle);

$eliminarCuentas = mysqli_prepare($conn, 'DELETE FROM cierres_mensuales_cuentas WHERE id_cierre = ?');
if (!$eliminarCuentas || !mysqli_stmt_bind_param($eliminarCuentas, 'i', $idCierre) ||
    !mysqli_stmt_execute($eliminarCuentas)) {
    $error = $eliminarCuentas ? mysqli_stmt_error($eliminarCuentas) : mysqli_error($conn);
    error_log('No fue posible reemplazar las cuentas del cierre: ' . $error);
    if ($eliminarCuentas) {
        mysqli_stmt_close($eliminarCuentas);
    }
    mysqli_rollback($conn);
    rechazarCierreMensual('No se pudieron actualizar las cuentas bancarias.');
}
mysqli_stmt_close($eliminarCuentas);

$insertarCuenta = mysqli_prepare(
    $conn,
    'INSERT INTO cierres_mensuales_cuentas (id_cierre, cuenta, saldo, fecha_saldo) VALUES (?, ?, ?, ?)'
);
if (!$insertarCuenta) {
    error_log('No fue posible preparar las cuentas del cierre: ' . mysqli_error($conn));
    mysqli_rollback($conn);
    rechazarCierreMensual('No se pudieron guardar las cuentas bancarias.');
}
foreach ($cuentasValidadas as $clave => $cuenta) {
    $saldo = $cuenta['saldo'];
    $fecha = $cuenta['fecha'];
    if (!mysqli_stmt_bind_param($insertarCuenta, 'isss', $idCierre, $clave, $saldo, $fecha) ||
        !mysqli_stmt_execute($insertarCuenta)) {
        error_log('No fue posible guardar una cuenta del cierre: ' . mysqli_stmt_error($insertarCuenta));
        mysqli_stmt_close($insertarCuenta);
        mysqli_rollback($conn);
        rechazarCierreMensual('No se pudieron guardar las cuentas bancarias.');
    }
}
mysqli_stmt_close($insertarCuenta);

if (!mysqli_commit($conn)) {
    error_log('No fue posible confirmar la transacción del cierre mensual: ' . mysqli_error($conn));
    mysqli_rollback($conn);
    rechazarCierreMensual('No se pudo confirmar el cierre. Inténtalo nuevamente.');
}

$revisionBorrador = isset($_POST['borrador_revision']) && is_string($_POST['borrador_revision']) &&
    preg_match('/^[a-z0-9-]{1,80}$/D', $_POST['borrador_revision']) ? $_POST['borrador_revision'] : '';
$_SESSION['cierre_mensual_mensaje'] = array(
    'tipo' => 'exito',
    'texto' => 'Cierre mensual guardado correctamente.',
    'borrador_guardado' => $revisionBorrador
);
header('Location: cierre_mensual.php?mes=' . rawurlencode($mes));
exit;
