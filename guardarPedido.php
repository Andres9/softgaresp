<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
require_once __DIR__ . '/pedidos_db.php';

function pedidoFallido($mensaje)
{
    global $idPedidoEditar;
    $_SESSION['pedido_error'] = $mensaje;
    header('Location: pedido.php' . ($idPedidoEditar ? '?editar=' . $idPedidoEditar . '#pedido-form' : ''));
    exit;
}

function decimalPedido($item, $campo)
{
    if (!isset($item[$campo]) || (!is_string($item[$campo]) && !is_numeric($item[$campo]))) {
        return null;
    }

    $valor = str_replace(',', '.', trim((string) $item[$campo]));
    if ($valor === '' || !is_numeric($valor)) {
        return null;
    }
    $numero = (float) $valor;
    return is_finite($numero) ? $numero : null;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}

$idPedidoEditar = null;
if (isset($_POST['id_pedido'])) {
    $idPedidoEditar = filter_var($_POST['id_pedido'], FILTER_VALIDATE_INT);
    if ($idPedidoEditar === false || $idPedidoEditar < 1) {
        $idPedidoEditar = null;
        pedidoFallido('El pedido seleccionado no es válido.');
    }
}

if (
    !isset($_POST['token_csrf'], $_SESSION['token_csrf']) ||
    !hash_equals($_SESSION['token_csrf'], (string) $_POST['token_csrf'])
) {
    http_response_code(403);
    pedidoFallido('La sesión de seguridad expiró. Recarga la página e inténtalo nuevamente.');
}

if (isset($_POST['eliminar'])) {
    $idEliminar = filter_var($_POST['eliminar'], FILTER_VALIDATE_INT);
    if ($idEliminar === false || $idEliminar < 1) {
        pedidoFallido('El pedido seleccionado no es válido.');
    }
    $consulta = mysqli_prepare($conn, 'DELETE FROM pedidos WHERE id_pedido = ?');
    if (!$consulta) {
        pedidoFallido('No fue posible eliminar el pedido.');
    }
    $consulta->bind_param('i', $idEliminar);
    if (!$consulta->execute()) {
        $consulta->close();
        pedidoFallido('No fue posible eliminar el pedido.');
    }
    $consulta->close();
    header('Location: pedido.php?eliminado=1');
    exit;
}

$fechaPedido = isset($_POST['fecha_pedido']) && is_string($_POST['fecha_pedido']) ? trim($_POST['fecha_pedido']) : '';
$proveedor = isset($_POST['proveedor']) && is_string($_POST['proveedor']) ? trim($_POST['proveedor']) : '';
$notas = isset($_POST['notas']) && is_string($_POST['notas']) ? trim($_POST['notas']) : '';
$fechaValidada = DateTimeImmutable::createFromFormat('!Y-m-d', $fechaPedido);
if (!$fechaValidada || $fechaValidada->format('Y-m-d') !== $fechaPedido) {
    pedidoFallido('La fecha del pedido no es válida.');
}
$fechaPedido = $fechaValidada->format('Y-m-d');
$proveedoresPermitidos = proveedoresPedido();
if ($proveedor !== '' && !in_array($proveedor, $proveedoresPermitidos, true)) {
    pedidoFallido('Selecciona un proveedor de la lista.');
}
$envio = isset($_POST['envio'])
    ? decimalPedido(array('envio' => $_POST['envio']), 'envio')
    : 0.0;
if ($envio === null || $envio < 0 || $envio > 99999999999.99) {
    pedidoFallido('El costo de envío no es válido.');
}

$itemsEntrada = isset($_POST['items']) && is_array($_POST['items']) ? $_POST['items'] : array();
$itemsValidos = array();
$catalogoProveedor = catalogoPedidoPorProveedor($proveedor);

foreach ($itemsEntrada as $item) {
    if (
        !is_array($item) ||
        (isset($item['modelo']) && !is_string($item['modelo'])) ||
        (isset($item['descripcion']) && !is_string($item['descripcion'])) ||
        (isset($item['imagen']) && !is_string($item['imagen']))
    ) {
        pedidoFallido('Los datos de uno de los productos no tienen un formato válido.');
    }
    $modelo = isset($item['modelo']) ? trim((string) $item['modelo']) : '';
    $descripcion = isset($item['descripcion']) ? trim((string) $item['descripcion']) : '';
    $cantidad = decimalPedido($item, 'cantidad');
    $precioCompra = decimalPedido($item, 'precio_compra');
    $precioFinal = decimalPedido($item, 'precio_final');
    $imagen = isset($item['imagen']) ? trim((string) $item['imagen']) : '';

    if (
        $modelo === '' || $descripcion === '' || $cantidad === null || $cantidad <= 0 ||
        $precioCompra === null || $precioCompra < 0 || $precioFinal === null ||
        !is_finite($precioFinal) || $precioFinal < 0 ||
        $cantidad > 99999999.99 || $precioCompra > 99999999999.99 ||
        $precioFinal > 99999999999.99 || strlen($modelo) > 120 || strlen($descripcion) > 255 ||
        ($imagen !== '' && nombreImagenCatalogoPedido($imagen) !== $imagen)
    ) {
        if ($idPedidoEditar) {
            pedidoFallido('Revisa los datos de todos los productos antes de actualizar el pedido.');
        }
        continue;
    }

    if ($catalogoProveedor !== null && !$idPedidoEditar) {
        $consultaCatalogo = mysqli_prepare(
            $conn,
            'SELECT descripcion, imagen FROM `' . $catalogoProveedor['tabla'] . '` WHERE codigo = ? LIMIT 1'
        );
        if (!$consultaCatalogo) {
            error_log('No fue posible preparar la consulta del catálogo ' . $proveedor . ': ' . mysqli_error($conn));
            pedidoFallido('No se pudo validar el producto en el catálogo seleccionado.');
        }
        $consultaCatalogo->bind_param('s', $modelo);
        if (!$consultaCatalogo->execute()) {
            error_log('No fue posible consultar el catálogo ' . $proveedor . ': ' . $consultaCatalogo->error);
            $consultaCatalogo->close();
            pedidoFallido('No se pudo validar el producto en el catálogo seleccionado.');
        }
        $productoCatalogo = $consultaCatalogo->get_result()->fetch_assoc();
        $consultaCatalogo->close();
        if ($productoCatalogo) {
            $descripcion = (string) $productoCatalogo['descripcion'];
            $imagen = imagenCatalogoPedidoDisponible($productoCatalogo['imagen'], $proveedor);
        }
    }

    $totalCompra = round($cantidad * $precioCompra, 2);
    $precioFinal = round($precioFinal, 2);
    $ivaPorcentaje = 0.0;
    $ivaMonto = 0.0;
    $margen = $precioFinal > 0 ? round(($precioFinal - $precioCompra) / $precioFinal * 100, 2) : 0.0;
    if ($margen < -999.99) {
        pedidoFallido('El margen de pérdida no puede ser inferior a -999.99%.');
    }
    $precioVenta = $proveedor === 'Pixal Center' ? 0.0 : $precioFinal;
    if ($totalCompra > 99999999999.99 || $precioVenta > 99999999999.99 || $precioFinal > 99999999999.99) {
        pedidoFallido('Un importe del pedido excede el máximo permitido.');
    }
    $gananciaIndividual = round($precioFinal - $precioCompra, 2);
    $gananciaTotal = round($gananciaIndividual * $cantidad, 2);
    $totalVenta = round($precioFinal * $cantidad, 2);

    $itemsValidos[] = array(
        'modelo' => $modelo,
        'cantidad' => $cantidad,
        'descripcion' => $descripcion,
        'imagen' => $imagen,
        'precio_compra' => $precioCompra,
        'total_compra' => $totalCompra,
        'margen' => $margen,
        'precio_venta' => $precioVenta,
        'precio_final' => $precioFinal,
        'ganancia_individual' => $gananciaIndividual,
        'ganancia_total' => $gananciaTotal,
        'total_venta' => $totalVenta,
        'iva_porcentaje' => $ivaPorcentaje,
        'iva_monto' => $ivaMonto
    );
}

if (count($itemsValidos) === 0) {
    pedidoFallido('Agrega al menos un producto válido.');
}

$totalCompraGeneral = 0;
$totalVentaGeneral = 0;
foreach ($itemsValidos as $item) {
    $totalCompraGeneral += $item['total_compra'];
    $totalVentaGeneral += $item['total_venta'];
}
$totalCompraGeneral += $envio;
$totalCompraGeneral = round($totalCompraGeneral, 2);
$totalVentaGeneral = round($totalVentaGeneral, 2);
if ($totalCompraGeneral > 99999999999.99 || $totalVentaGeneral > 99999999999.99) {
    pedidoFallido('El importe total del pedido excede el máximo permitido.');
}

$notas = strlen($notas) > 3000 ? substr($notas, 0, 3000) : $notas;

if (!mysqli_begin_transaction($conn)) {
    pedidoFallido('No se pudo iniciar el guardado del pedido.');
}

if ($idPedidoEditar) {
    $bloqueo = mysqli_prepare($conn, 'SELECT id_pedido FROM pedidos WHERE id_pedido = ? FOR UPDATE');
    if (!$bloqueo) {
        error_log('No fue posible preparar la edición del pedido: ' . mysqli_error($conn));
        mysqli_rollback($conn);
        pedidoFallido('No fue posible actualizar el pedido.');
    }
    $bloqueo->bind_param('i', $idPedidoEditar);
    if (!$bloqueo->execute()) {
        error_log('No fue posible consultar el pedido para edición: ' . $bloqueo->error);
        $bloqueo->close();
        mysqli_rollback($conn);
        pedidoFallido('No fue posible actualizar el pedido.');
    }
    $existePedido = $bloqueo->get_result()->fetch_assoc();
    $bloqueo->close();
    if (!$existePedido) {
        mysqli_rollback($conn);
        $idPedidoEditar = null;
        pedidoFallido('El pedido ya no existe.');
    }
    $consultaPedido = mysqli_prepare($conn, 'UPDATE pedidos SET fecha_pedido = ?, proveedor = ?, notas = ?, envio = ?, total_compra = ?, total_venta = ? WHERE id_pedido = ?');
} else {
    $folioTemporal = 'PEND-' . bin2hex(random_bytes(8));
    $creadoPor = (string) $_SESSION['usuario'];
    $consultaPedido = mysqli_prepare(
        $conn,
        'INSERT INTO pedidos (folio, fecha_pedido, proveedor, notas, envio, total_compra, total_venta, creado_por)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
}
if (!$consultaPedido) {
    error_log('No fue posible preparar el guardado del pedido: ' . mysqli_error($conn));
    mysqli_rollback($conn);
    pedidoFallido('No fue posible guardar el pedido.');
}
if ($idPedidoEditar) {
    $consultaPedido->bind_param('sssdddi', $fechaPedido, $proveedor, $notas, $envio, $totalCompraGeneral, $totalVentaGeneral, $idPedidoEditar);
} else {
    $consultaPedido->bind_param('ssssddds', $folioTemporal, $fechaPedido, $proveedor, $notas, $envio, $totalCompraGeneral, $totalVentaGeneral, $creadoPor);
}
if (!$consultaPedido->execute()) {
    error_log('No fue posible guardar el pedido: ' . $consultaPedido->error);
    $consultaPedido->close();
    mysqli_rollback($conn);
    pedidoFallido('No fue posible guardar el pedido.');
}
$idPedido = $idPedidoEditar ?: (int) $conn->insert_id;
$consultaPedido->close();

if ($idPedidoEditar) {
    $eliminarDetalles = mysqli_prepare($conn, 'DELETE FROM pedido_detalles WHERE id_pedido = ?');
    if (!$eliminarDetalles) {
        error_log('No fue posible preparar la actualización de productos: ' . mysqli_error($conn));
        mysqli_rollback($conn);
        pedidoFallido('No fue posible actualizar los productos del pedido.');
    }
    $eliminarDetalles->bind_param('i', $idPedido);
    if (!$eliminarDetalles->execute()) {
        error_log('No fue posible actualizar los productos: ' . $eliminarDetalles->error);
        $eliminarDetalles->close();
        mysqli_rollback($conn);
        pedidoFallido('No fue posible actualizar los productos del pedido.');
    }
    $eliminarDetalles->close();
} else {
    $folio = 'PED-' . date('Ym') . '-' . str_pad((string) $idPedido, 6, '0', STR_PAD_LEFT);
    $consultaFolio = mysqli_prepare($conn, 'UPDATE pedidos SET folio = ? WHERE id_pedido = ?');
    if (!$consultaFolio) {
        mysqli_rollback($conn);
        pedidoFallido('No fue posible completar el folio del pedido.');
    }
    $consultaFolio->bind_param('si', $folio, $idPedido);
    if (!$consultaFolio->execute()) {
        $consultaFolio->close();
        mysqli_rollback($conn);
        pedidoFallido('No fue posible completar el pedido.');
    }
    $consultaFolio->close();
}

$consultaDetalle = mysqli_prepare(
    $conn,
    'INSERT INTO pedido_detalles (
        id_pedido, orden, modelo, cantidad, descripcion, imagen,
        precio_compra, total_compra, margen_porcentaje, precio_venta,
        precio_final, ganancia_individual, ganancia_total, total_venta,
        iva_porcentaje, iva_monto
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
if (!$consultaDetalle) {
    mysqli_rollback($conn);
    pedidoFallido('No fue posible guardar los productos del pedido.');
}

foreach ($itemsValidos as $indice => $item) {
    $orden = $indice + 1;
    $consultaDetalle->bind_param(
        'iisdssdddddddddd',
        $idPedido,
        $orden,
        $item['modelo'],
        $item['cantidad'],
        $item['descripcion'],
        $item['imagen'],
        $item['precio_compra'],
        $item['total_compra'],
        $item['margen'],
        $item['precio_venta'],
        $item['precio_final'],
        $item['ganancia_individual'],
        $item['ganancia_total'],
        $item['total_venta'],
        $item['iva_porcentaje'],
        $item['iva_monto']
    );
    if (!$consultaDetalle->execute()) {
        error_log('No fue posible guardar un detalle de pedido: ' . $consultaDetalle->error);
        $consultaDetalle->close();
        mysqli_rollback($conn);
        pedidoFallido('No fue posible guardar los productos del pedido.');
    }
}
$consultaDetalle->close();

if (!mysqli_commit($conn)) {
    mysqli_rollback($conn);
    pedidoFallido('No fue posible finalizar el pedido.');
}

header('Location: pedido.php?' . ($idPedidoEditar ? 'actualizado=' : 'guardado=') . $idPedido);
exit;
