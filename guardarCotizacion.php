<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
require_once __DIR__ . '/cotizaciones_db.php';

date_default_timezone_set('America/Mexico_City');

$idCotizacionEdicion = null;

function cotizacionFallida($mensaje, $archivos = array())
{
    global $conn, $transaccionIniciada, $idCotizacionEdicion;

    if ($transaccionIniciada) {
        mysqli_rollback($conn);
        $transaccionIniciada = false;
    }

    foreach ($archivos as $archivo) {
        if (is_file($archivo) && !unlink($archivo)) {
            error_log('No fue posible eliminar un archivo temporal de cotización: ' . $archivo);
        }
    }

    $_SESSION['cotizacion_error'] = $mensaje;
    header('Location: cotizacion.php' . ($idCotizacionEdicion !== null ? '?editar=' . $idCotizacionEdicion : ''));
    exit;
}

$transaccionIniciada = false;

function textoCotizacion($datos, $campo, $longitud)
{
    if (isset($datos[$campo]) && !is_string($datos[$campo])) {
        cotizacionFallida('Uno de los campos enviados no tiene un formato válido.');
    }

    $valor = isset($datos[$campo]) ? trim($datos[$campo]) : '';
    if (strlen($valor) > $longitud) {
        cotizacionFallida('Uno de los campos excede la longitud permitida.');
    }

    return $valor;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método no permitido.');
}

if (
    !isset($_POST['token_csrf']) ||
    !is_string($_POST['token_csrf']) ||
    !hash_equals(token_csrf(), $_POST['token_csrf'])
) {
    http_response_code(403);
    exit('La solicitud expiró. Recarga la página e inténtalo nuevamente.');
}

if (isset($_POST['id_cotizacion'])) {
    if (!is_string($_POST['id_cotizacion'])) {
        cotizacionFallida('La cotización seleccionada no es válida.');
    }
    $idCotizacionEdicion = filter_var($_POST['id_cotizacion'], FILTER_VALIDATE_INT);
    if ($idCotizacionEdicion === false || $idCotizacionEdicion < 1) {
        $idCotizacionEdicion = null;
        cotizacionFallida('La cotización seleccionada no es válida.');
    }

    $consultaExistente = mysqli_prepare($conn, 'SELECT id_cotizacion FROM cotizaciones WHERE id_cotizacion = ? LIMIT 1');
    if ($consultaExistente === false) {
        error_log('No fue posible preparar la validación de cotización existente: ' . mysqli_error($conn));
        cotizacionFallida('No se pudo cargar la cotización para editarla.');
    }
    $consultaExistente->bind_param('i', $idCotizacionEdicion);
    if (!$consultaExistente->execute()) {
        error_log('No fue posible validar la cotización existente: ' . $consultaExistente->error);
        $consultaExistente->close();
        cotizacionFallida('No se pudo cargar la cotización para editarla.');
    }
    $cotizacionExistente = $consultaExistente->get_result()->fetch_assoc();
    $consultaExistente->close();
    if (!$cotizacionExistente) {
        $idCotizacionEdicion = null;
        cotizacionFallida('La cotización que intentas editar ya no existe.');
    }
}

$imagenesAnteriores = array();
if ($idCotizacionEdicion !== null) {
    $consultaImagenesAnteriores = mysqli_prepare(
        $conn,
        "SELECT imagen FROM cotizacion_detalles WHERE id_cotizacion = ? AND imagen <> ''"
    );
    if ($consultaImagenesAnteriores === false) {
        error_log('No fue posible preparar la consulta de imágenes anteriores: ' . mysqli_error($conn));
        cotizacionFallida('No se pudo cargar la cotización para editarla.');
    }
    $consultaImagenesAnteriores->bind_param('i', $idCotizacionEdicion);
    if (!$consultaImagenesAnteriores->execute()) {
        error_log('No fue posible cargar las imágenes anteriores: ' . $consultaImagenesAnteriores->error);
        $consultaImagenesAnteriores->close();
        cotizacionFallida('No se pudo cargar la cotización para editarla.');
    }
    $resultadoImagenesAnteriores = $consultaImagenesAnteriores->get_result();
    while ($filaImagen = $resultadoImagenesAnteriores->fetch_assoc()) {
        if (preg_match('/^[a-f0-9]{32}\.(jpg|png)$/', $filaImagen['imagen'])) {
            $imagenesAnteriores[$filaImagen['imagen']] = true;
        }
    }
    $consultaImagenesAnteriores->close();
}

$clienteNombre = textoCotizacion($_POST, 'cliente_nombre', 180);
$clienteEmpresa = textoCotizacion($_POST, 'cliente_empresa', 180);
$clienteEmail = textoCotizacion($_POST, 'cliente_email', 180);
$clienteTelefono = textoCotizacion($_POST, 'cliente_telefono', 50);
$clienteRfc = textoCotizacion($_POST, 'cliente_rfc', 30);
$clienteDireccion = textoCotizacion($_POST, 'cliente_direccion', 1000);
$vigencia = textoCotizacion($_POST, 'vigencia_hasta', 10);
$moneda = textoCotizacion($_POST, 'moneda', 3);
$condicionesPago = textoCotizacion($_POST, 'condiciones_pago', 255);
$tiempoEntrega = textoCotizacion($_POST, 'tiempo_entrega', 180);
$garantia = textoCotizacion($_POST, 'garantia', 180);
$notas = textoCotizacion($_POST, 'notas', 3000);

if ($clienteNombre === '') {
    cotizacionFallida('Escribe el nombre o la razón social del cliente.');
}
if ($clienteEmail !== '' && !filter_var($clienteEmail, FILTER_VALIDATE_EMAIL)) {
    cotizacionFallida('El correo electrónico del cliente no es válido.');
}
$fechaActual = date('Y-m-d');
$fechaVigencia = DateTimeImmutable::createFromFormat('!Y-m-d', $vigencia);
if (
    !$fechaVigencia ||
    $fechaVigencia->format('Y-m-d') !== $vigencia ||
    $vigencia < $fechaActual
) {
    cotizacionFallida('La fecha de vigencia debe ser válida y no puede estar en el pasado.');
}
if (!in_array($moneda, array('MXN', 'USD'), true)) {
    cotizacionFallida('Selecciona una moneda válida.');
}

$items = isset($_POST['items']) && is_array($_POST['items']) ? $_POST['items'] : array();
if (count($items) < 1 || count($items) > 10) {
    cotizacionFallida('La cotización debe incluir entre 1 y 10 conceptos.');
}

$directorioImagenes = __DIR__ . '/uploads/cotizaciones';
if (!is_dir($directorioImagenes) && !mkdir($directorioImagenes, 0750, true) && !is_dir($directorioImagenes)) {
    error_log('No fue posible crear el directorio de imágenes de cotizaciones.');
    cotizacionFallida('No se pudieron guardar imágenes. Inténtalo nuevamente sin imágenes o contacta al administrador.');
}
if (!is_writable($directorioImagenes)) {
    error_log('El directorio de imágenes de cotizaciones no permite escritura.');
    cotizacionFallida('No se pudieron guardar imágenes. Contacta al administrador.');
}

$camposTextoItem = array(
    'tipo_producto' => 100,
    'marca' => 100,
    'modelo' => 120,
    'sku' => 100,
    'descripcion' => 255,
    'especificaciones' => 3000,
    'garantia' => 180
);
$itemsValidos = array();
$imagenesTemporales = array();
$archivosSubidos = null;
if (isset($_FILES['imagenes'])) {
    if (
        !is_array($_FILES['imagenes']) ||
        !isset($_FILES['imagenes']['error'], $_FILES['imagenes']['name'], $_FILES['imagenes']['tmp_name'], $_FILES['imagenes']['size']) ||
        !is_array($_FILES['imagenes']['error']) ||
        !is_array($_FILES['imagenes']['name']) ||
        !is_array($_FILES['imagenes']['tmp_name']) ||
        !is_array($_FILES['imagenes']['size'])
    ) {
        cotizacionFallida('La información de las imágenes no tiene un formato válido.');
    }
    $archivosSubidos = $_FILES['imagenes'];
}
$subtotalGeneral = 0.0;
$descuentoGeneral = 0.0;
$impuestoGeneral = 0.0;
$totalGeneral = 0.0;

foreach ($items as $indice => $item) {
    if (!is_array($item)) {
        cotizacionFallida('La información de un concepto no es válida.');
    }

    $itemLimpio = array();
    foreach ($camposTextoItem as $campo => $limite) {
        if (isset($item[$campo]) && !is_string($item[$campo])) {
            cotizacionFallida('Uno de los campos del producto no tiene un formato válido.');
        }
        $valor = isset($item[$campo]) ? trim($item[$campo]) : '';
        if (strlen($valor) > $limite) {
            cotizacionFallida('Uno de los campos del producto excede la longitud permitida.');
        }
        $itemLimpio[$campo] = $valor;
    }

    $cantidad = isset($item['cantidad']) && is_numeric($item['cantidad']) ? (float) $item['cantidad'] : 0;
    $precio = isset($item['precio_unitario']) && is_numeric($item['precio_unitario']) ? (float) $item['precio_unitario'] : -1;
    $descuentoPorcentaje = isset($item['descuento_porcentaje']) && is_numeric($item['descuento_porcentaje']) ? (float) $item['descuento_porcentaje'] : -1;
    $impuestoPorcentaje = isset($item['impuesto_porcentaje']) && is_numeric($item['impuesto_porcentaje']) ? (float) $item['impuesto_porcentaje'] : -1;

    if (
        $itemLimpio['descripcion'] === '' ||
        !is_finite($cantidad) || $cantidad <= 0 ||
        !is_finite($precio) || $precio < 0 ||
        !is_finite($descuentoPorcentaje) || $descuentoPorcentaje < 0 || $descuentoPorcentaje > 100 ||
        !is_finite($impuestoPorcentaje) || $impuestoPorcentaje < 0 || $impuestoPorcentaje > 100
    ) {
        cotizacionFallida('Revisa la descripción, cantidad, precio, descuento e impuesto de cada concepto.');
    }

    $subtotal = round($cantidad * $precio, 2);
    $descuento = round($subtotal * $descuentoPorcentaje / 100, 2);
    $impuesto = round(($subtotal - $descuento) * $impuestoPorcentaje / 100, 2);
    $total = round($subtotal - $descuento + $impuesto, 2);
    if (
        !is_finite($subtotal) || $subtotal > 99999999999.99 ||
        !is_finite($descuento) || $descuento > 99999999999.99 ||
        !is_finite($impuesto) || $impuesto > 99999999999.99 ||
        !is_finite($total) || $total > 99999999999.99
    ) {
        cotizacionFallida('El importe de un concepto excede el máximo permitido.');
    }

    $itemLimpio['cantidad'] = $cantidad;
    $itemLimpio['precio_unitario'] = $precio;
    $itemLimpio['descuento_porcentaje'] = $descuentoPorcentaje;
    $itemLimpio['impuesto_porcentaje'] = $impuestoPorcentaje;
    $itemLimpio['subtotal'] = $subtotal;
    $itemLimpio['descuento'] = $descuento;
    $itemLimpio['impuesto'] = $impuesto;
    $itemLimpio['total'] = $total;
    $itemLimpio['imagen'] = '';
    $itemLimpio['_indice_original'] = $indice;
    if (isset($item['imagen_actual']) && !is_string($item['imagen_actual'])) {
        cotizacionFallida('La imagen guardada del producto no es válida.');
    }
    $imagenActual = isset($item['imagen_actual']) ? trim($item['imagen_actual']) : '';
    if (
        ($imagenActual !== '' && !isset($imagenesAnteriores[$imagenActual])) ||
        ($idCotizacionEdicion === null && $imagenActual !== '')
    ) {
        cotizacionFallida('Una de las imágenes guardadas no corresponde a esta cotización.');
    }
    $itemLimpio['imagen_actual'] = $imagenActual;
    $itemLimpio['imagen'] = $imagenActual;

    if (
        $archivosSubidos !== null &&
        isset($archivosSubidos['error'][$indice]) &&
        $archivosSubidos['error'][$indice] !== UPLOAD_ERR_NO_FILE
    ) {
        if (
            !isset($archivosSubidos['name'][$indice], $archivosSubidos['tmp_name'][$indice], $archivosSubidos['size'][$indice]) ||
            !is_string($archivosSubidos['tmp_name'][$indice])
        ) {
            cotizacionFallida('La imagen seleccionada no tiene un formato de carga válido.');
        }
        $errorSubida = $archivosSubidos['error'][$indice];
        if ($errorSubida !== UPLOAD_ERR_OK) {
            cotizacionFallida('No se pudo recibir una de las imágenes. Verifica su tamaño e inténtalo nuevamente.');
        }

        $temporal = $archivosSubidos['tmp_name'][$indice];
        $tamano = (int) $archivosSubidos['size'][$indice];
        if (!is_uploaded_file($temporal) || $tamano < 1 || $tamano > 5 * 1024 * 1024) {
            cotizacionFallida('Cada imagen debe pesar como máximo 5 MB.');
        }

        $informacionImagen = @getimagesize($temporal);
        if (
            $informacionImagen === false ||
            !isset($informacionImagen['mime']) ||
            !in_array($informacionImagen['mime'], array('image/jpeg', 'image/png'), true)
        ) {
            cotizacionFallida('Solo se permiten imágenes JPG o PNG válidas.');
        }

        $extensionImagen = $informacionImagen['mime'] === 'image/png' ? 'png' : 'jpg';
        $nombreImagen = bin2hex(random_bytes(16)) . '.' . $extensionImagen;
        $itemLimpio['imagen'] = $nombreImagen;
        $imagenesTemporales[$indice] = array(
            'temporal' => $temporal,
            'destino' => $directorioImagenes . '/' . $nombreImagen
        );
    }

    $itemsValidos[] = $itemLimpio;
    $subtotalGeneral += $subtotal;
    $descuentoGeneral += $descuento;
    $impuestoGeneral += $impuesto;
    $totalGeneral += $total;
}

$subtotalGeneral = round($subtotalGeneral, 2);
$descuentoGeneral = round($descuentoGeneral, 2);
$impuestoGeneral = round($impuestoGeneral, 2);
$totalGeneral = round($totalGeneral, 2);
if (!is_finite($totalGeneral) || $totalGeneral > 99999999999.99) {
    cotizacionFallida('El total excede el máximo permitido.');
}
if (
    !is_finite($subtotalGeneral) || $subtotalGeneral > 99999999999.99 ||
    !is_finite($descuentoGeneral) || $descuentoGeneral > 99999999999.99 ||
    !is_finite($impuestoGeneral) || $impuestoGeneral > 99999999999.99
) {
    cotizacionFallida('El resumen de la cotización excede el máximo permitido.');
}

if (!$conn->begin_transaction()) {
    error_log('No fue posible iniciar la transacción de cotización: ' . mysqli_error($conn));
    cotizacionFallida('No se pudo guardar la cotización. Inténtalo nuevamente.');
}
$transaccionIniciada = true;
$archivosGuardados = array();
if ($idCotizacionEdicion === null) {
    $folioTemporal = 'PEND-' . bin2hex(random_bytes(8));
    $estado = 'Emitida';
    $creadoPor = (string) $_SESSION['usuario'];
    $consultaCotizacion = mysqli_prepare(
        $conn,
        'INSERT INTO cotizaciones (
            folio, cliente_nombre, cliente_empresa, cliente_email, cliente_telefono, cliente_rfc,
            cliente_direccion, fecha_cotizacion, vigencia_hasta, moneda, condiciones_pago,
            tiempo_entrega, garantia, notas, estado, subtotal, descuento_total, impuestos_total,
            total, creado_por
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    if ($consultaCotizacion === false) {
        error_log('No fue posible preparar el alta de cotización: ' . mysqli_error($conn));
        cotizacionFallida('No se pudo guardar la cotización. Inténtalo nuevamente.');
    }

    $consultaCotizacion->bind_param(
        'sssssssssssssssdddds',
        $folioTemporal,
        $clienteNombre,
        $clienteEmpresa,
        $clienteEmail,
        $clienteTelefono,
        $clienteRfc,
        $clienteDireccion,
        $fechaActual,
        $vigencia,
        $moneda,
        $condicionesPago,
        $tiempoEntrega,
        $garantia,
        $notas,
        $estado,
        $subtotalGeneral,
        $descuentoGeneral,
        $impuestoGeneral,
        $totalGeneral,
        $creadoPor
    );

    if (!$consultaCotizacion->execute()) {
        error_log('No fue posible guardar la cotización: ' . $consultaCotizacion->error);
        $consultaCotizacion->close();
        cotizacionFallida('No se pudo guardar la cotización. Inténtalo nuevamente.');
    }

    $idCotizacion = (int) $conn->insert_id;
    $folio = 'COT-' . date('Ym') . '-' . str_pad((string) $idCotizacion, 6, '0', STR_PAD_LEFT);
    $actualizarFolio = mysqli_prepare($conn, 'UPDATE cotizaciones SET folio = ? WHERE id_cotizacion = ?');
    if ($actualizarFolio === false) {
        error_log('No fue posible preparar la actualización del folio: ' . mysqli_error($conn));
        $consultaCotizacion->close();
        cotizacionFallida('No se pudo completar la cotización. Inténtalo nuevamente.');
    }
    $actualizarFolio->bind_param('si', $folio, $idCotizacion);
    if (!$actualizarFolio->execute()) {
        error_log('No fue posible actualizar el folio de cotización: ' . $actualizarFolio->error);
        $actualizarFolio->close();
        $consultaCotizacion->close();
        cotizacionFallida('No se pudo completar la cotización. Inténtalo nuevamente.');
    }
    $actualizarFolio->close();
    $consultaCotizacion->close();
} else {
    $idCotizacion = $idCotizacionEdicion;
    $actualizarCotizacion = mysqli_prepare(
        $conn,
        'UPDATE cotizaciones SET
            cliente_nombre = ?, cliente_empresa = ?, cliente_email = ?, cliente_telefono = ?,
            cliente_rfc = ?, cliente_direccion = ?, vigencia_hasta = ?, moneda = ?,
            condiciones_pago = ?, tiempo_entrega = ?, garantia = ?, notas = ?,
            subtotal = ?, descuento_total = ?, impuestos_total = ?, total = ?
         WHERE id_cotizacion = ?'
    );
    if ($actualizarCotizacion === false) {
        error_log('No fue posible preparar la actualización de cotización: ' . mysqli_error($conn));
        cotizacionFallida('No se pudo actualizar la cotización.');
    }
    $actualizarCotizacion->bind_param(
        'ssssssssssssddddi',
        $clienteNombre,
        $clienteEmpresa,
        $clienteEmail,
        $clienteTelefono,
        $clienteRfc,
        $clienteDireccion,
        $vigencia,
        $moneda,
        $condicionesPago,
        $tiempoEntrega,
        $garantia,
        $notas,
        $subtotalGeneral,
        $descuentoGeneral,
        $impuestoGeneral,
        $totalGeneral,
        $idCotizacion
    );
    if (!$actualizarCotizacion->execute()) {
        error_log('No fue posible actualizar la cotización: ' . $actualizarCotizacion->error);
        $actualizarCotizacion->close();
        cotizacionFallida('No se pudo actualizar la cotización.');
    }
    $actualizarCotizacion->close();

    $eliminarDetalles = mysqli_prepare($conn, 'DELETE FROM cotizacion_detalles WHERE id_cotizacion = ?');
    if ($eliminarDetalles === false) {
        error_log('No fue posible preparar la sustitución de conceptos: ' . mysqli_error($conn));
        cotizacionFallida('No se pudieron actualizar los conceptos.');
    }
    $eliminarDetalles->bind_param('i', $idCotizacion);
    if (!$eliminarDetalles->execute()) {
        error_log('No fue posible reemplazar los conceptos: ' . $eliminarDetalles->error);
        $eliminarDetalles->close();
        cotizacionFallida('No se pudieron actualizar los conceptos.');
    }
    $eliminarDetalles->close();
}

$consultaDetalle = mysqli_prepare(
    $conn,
    'INSERT INTO cotizacion_detalles (
        id_cotizacion, orden, tipo_producto, marca, modelo, sku, descripcion, especificaciones,
        cantidad, precio_unitario, descuento_porcentaje, impuesto_porcentaje, subtotal,
        descuento, impuesto, total, garantia, imagen
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
if ($consultaDetalle === false) {
    error_log('No fue posible preparar el detalle de cotización: ' . mysqli_error($conn));
    cotizacionFallida('No se pudo completar la cotización. Inténtalo nuevamente.', $archivosGuardados);
}

foreach ($itemsValidos as $posicion => $item) {
    $numeroOrden = $posicion + 1;
    $imagen = $item['imagen'];
    if ($imagen !== '') {
        $indiceOriginal = $item['_indice_original'];
        $archivo = isset($imagenesTemporales[$indiceOriginal]) ? $imagenesTemporales[$indiceOriginal] : null;
        if (
            ($archivo !== null && !move_uploaded_file($archivo['temporal'], $archivo['destino'])) ||
            ($archivo === null && $item['imagen_actual'] !== $imagen)
        ) {
            error_log('No fue posible mover una imagen de cotización al almacenamiento.');
            $consultaDetalle->close();
            cotizacionFallida('No se pudo guardar una imagen de producto.', $archivosGuardados);
        }
        if ($archivo !== null) {
            $archivosGuardados[] = $archivo['destino'];
        }
    }

    $tipoProducto = $item['tipo_producto'];
    $marca = $item['marca'];
    $modelo = $item['modelo'];
    $sku = $item['sku'];
    $descripcion = $item['descripcion'];
    $especificaciones = $item['especificaciones'];
    $cantidad = $item['cantidad'];
    $precio = $item['precio_unitario'];
    $descuentoPorcentaje = $item['descuento_porcentaje'];
    $impuestoPorcentaje = $item['impuesto_porcentaje'];
    $subtotal = $item['subtotal'];
    $descuento = $item['descuento'];
    $impuesto = $item['impuesto'];
    $total = $item['total'];
    $garantiaItem = $item['garantia'];

    $consultaDetalle->bind_param(
        'iissssssddddddddss',
        $idCotizacion,
        $numeroOrden,
        $tipoProducto,
        $marca,
        $modelo,
        $sku,
        $descripcion,
        $especificaciones,
        $cantidad,
        $precio,
        $descuentoPorcentaje,
        $impuestoPorcentaje,
        $subtotal,
        $descuento,
        $impuesto,
        $total,
        $garantiaItem,
        $imagen
    );
    if (!$consultaDetalle->execute()) {
        error_log('No fue posible guardar un concepto de cotización: ' . $consultaDetalle->error);
        $consultaDetalle->close();
        cotizacionFallida('No se pudo guardar el detalle de la cotización.', $archivosGuardados);
    }
}

$consultaDetalle->close();
if (!$conn->commit()) {
    error_log('No fue posible confirmar la transacción de cotización: ' . mysqli_error($conn));
    cotizacionFallida('No se pudo completar la cotización. Inténtalo nuevamente.', $archivosGuardados);
}
$transaccionIniciada = false;

if ($idCotizacionEdicion !== null) {
    $imagenesConservadas = array();
    foreach ($itemsValidos as $itemGuardado) {
        if ($itemGuardado['imagen'] !== '') {
            $imagenesConservadas[$itemGuardado['imagen']] = true;
        }
    }
    foreach ($imagenesAnteriores as $imagenAnterior => $existe) {
        if (!isset($imagenesConservadas[$imagenAnterior])) {
            $rutaImagenAnterior = $directorioImagenes . '/' . $imagenAnterior;
            if (is_file($rutaImagenAnterior) && !unlink($rutaImagenAnterior)) {
                error_log('No fue posible eliminar una imagen reemplazada de cotización: ' . $rutaImagenAnterior);
            }
        }
    }
    header('Location: cotizacion.php?actualizada=' . $idCotizacion);
} else {
    header('Location: cotizacion.php?creada=' . $idCotizacion);
}
exit;
