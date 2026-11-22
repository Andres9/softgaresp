<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}
if (!isset($_POST['token_csrf']) || !is_string($_POST['token_csrf']) || !hash_equals(token_csrf(), $_POST['token_csrf'])) {
    http_response_code(403);
    exit('La sesión de seguridad expiró. Recarga la página e inténtalo de nuevo.');
}
$idNota = isset($_POST['id_nota']) ? filter_var($_POST['id_nota'], FILTER_VALIDATE_INT) : false;
if ($idNota === false || $idNota < 1) {
    http_response_code(400);
    exit('La nota seleccionada no es válida.');
}
$destino = ($_POST['origen'] ?? '') === 'reportesnotas' ? 'reportesnotas.php' : 'notaCliente.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once __DIR__ . '/conexion.php';
try {
    $conn->begin_transaction();
    $consulta = $conn->prepare('SELECT id_cliente FROM notas WHERE id_nota = ? FOR UPDATE');
    $consulta->bind_param('i', $idNota);
    $consulta->execute();
    $nota = $consulta->get_result()->fetch_assoc();
    $consulta->close();
    if (!$nota) {
        $conn->rollback();
        http_response_code(404);
        exit('No se encontró la nota.');
    }

    $eliminar = $conn->prepare('DELETE FROM notas WHERE id_nota = ?');
    $eliminar->bind_param('i', $idNota);
    $eliminar->execute();
    $eliminar->close();

    $idCliente = (int) $nota['id_cliente'];
    $cliente = $conn->prepare('UPDATE clientes SET numServicio = GREATEST(COALESCE(numServicio, 0) - 1, 0) WHERE id_cliente = ?');
    $cliente->bind_param('i', $idCliente);
    $cliente->execute();
    $cliente->close();
    $conn->commit();
} catch (mysqli_sql_exception $error) {
    $conn->rollback();
    error_log('No fue posible eliminar la nota: ' . $error->getMessage());
    http_response_code(500);
    exit('No se pudo eliminar la nota. Inténtalo nuevamente.');
}

$_SESSION['nota_mensaje'] = 'Nota eliminada correctamente.';
header('Location: ' . $destino, true, 303);
exit;
