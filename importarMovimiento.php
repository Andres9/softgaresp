<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
mysqli_report(MYSQLI_REPORT_OFF);
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/movimientos_db.php';
require_once __DIR__ . '/movimientos_excel.php';
require_once __DIR__ . '/movimientos_importar.php';

function informarImportacionMovimiento($tipo, $texto, $desde = null, $hasta = null)
{
    $_SESSION['movimiento_mensaje'] = array('tipo' => $tipo, 'texto' => $texto);
    $rango = $desde !== null && $hasta !== null
        ? '?desde=' . rawurlencode($desde) . '&hasta=' . rawurlencode($hasta)
        : '';
    header('Location: movimiento.php' . $rango);
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
    error_log('No fue posible conectar con la base de datos para importar movimientos.');
    http_response_code(500);
    exit('No se pudo importar el libro.');
}

if (!isset($_FILES['archivo_excel']) || !is_array($_FILES['archivo_excel']) ||
    !isset($_FILES['archivo_excel']['error'], $_FILES['archivo_excel']['name'], $_FILES['archivo_excel']['size'], $_FILES['archivo_excel']['tmp_name']) ||
    $_FILES['archivo_excel']['error'] !== UPLOAD_ERR_OK) {
    informarImportacionMovimiento('error', 'Selecciona un libro XLSX válido para importar.');
}

$archivoSubido = $_FILES['archivo_excel'];
$extension = strtolower(pathinfo($archivoSubido['name'], PATHINFO_EXTENSION));
if ($extension !== 'xlsx' || (int) $archivoSubido['size'] < 1 || (int) $archivoSubido['size'] > 10485760 ||
    !is_uploaded_file($archivoSubido['tmp_name'])) {
    informarImportacionMovimiento('error', 'El archivo debe ser un XLSX de hasta 10 MB.');
}

$rutaTemporal = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'movimientos_' . bin2hex(random_bytes(16)) . '.xlsx';
if (!move_uploaded_file($archivoSubido['tmp_name'], $rutaTemporal)) {
    error_log('No fue posible mover el libro XLSX al directorio temporal para leerlo.');
    informarImportacionMovimiento('error', 'No se pudo procesar el archivo seleccionado.');
}

try {
    $libro = analizarLibroMovimientos($rutaTemporal);
} catch (Exception $error) {
    error_log('No fue posible analizar el libro de movimientos: ' . $error->getMessage());
    unlink($rutaTemporal);
    informarImportacionMovimiento('error', $error->getMessage());
}
unlink($rutaTemporal);

$usuario = isset($_SESSION['usuario']) ? (string) $_SESSION['usuario'] : '';
try {
    $resultadoImportacion = importarRegistrosMovimientosExcel($conn, $libro['registros'], $usuario);
} catch (RuntimeException $error) {
    error_log('Falló la importación de movimientos: ' . $error->getMessage());
    informarImportacionMovimiento('error', 'No se pudo completar la importación. No se guardaron cambios.');
}

$fechas = array_column($libro['registros'], 'fecha');
sort($fechas);
$desde = reset($fechas);
$hasta = end($fechas);
$mensaje = $resultadoImportacion['importados'] === 0
    ? 'No hubo registros nuevos; se omitieron ' . $resultadoImportacion['omitidos'] . ' movimientos duplicados.'
    : 'Importación completa: ' . $resultadoImportacion['importados'] . ' movimientos agregados y ' .
        $resultadoImportacion['omitidos'] . ' duplicados omitidos.';
$mensaje .= ' ' . $resultadoImportacion['fechas_estimadas'] . ' registros conservan fecha estimada (primer día del mes).';

informarImportacionMovimiento('exito', $mensaje, $desde, $hasta);
