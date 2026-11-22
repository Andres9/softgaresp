<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
if (!usuario_es_administrador()) {
    http_response_code(403);
    exit('No tienes permiso para importar el catálogo.');
}
require_once __DIR__ . '/pedidos_db.php';

function importarPixalFallido($mensaje)
{
    $_SESSION['catalogo_pixal_error'] = $mensaje;
    header('Location: catalogoPixal.php');
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
    importarPixalFallido('La sesión de seguridad expiró. Recarga la página e inténtalo nuevamente.');
}
if (
    !isset($_FILES['catalogo_csv']['error'], $_FILES['catalogo_csv']['tmp_name'], $_FILES['catalogo_csv']['name'], $_FILES['catalogo_csv']['size']) ||
    !is_string($_FILES['catalogo_csv']['name']) ||
    !is_string($_FILES['catalogo_csv']['tmp_name']) ||
    !is_int($_FILES['catalogo_csv']['error']) ||
    $_FILES['catalogo_csv']['error'] !== UPLOAD_ERR_OK ||
    !is_uploaded_file($_FILES['catalogo_csv']['tmp_name']) ||
    (int) $_FILES['catalogo_csv']['size'] < 1 ||
    (int) $_FILES['catalogo_csv']['size'] > 5 * 1024 * 1024 ||
    strtolower(pathinfo($_FILES['catalogo_csv']['name'], PATHINFO_EXTENSION)) !== 'csv'
) {
    importarPixalFallido('Selecciona un archivo CSV válido de hasta 5 MB.');
}

$archivoCsv = fopen($_FILES['catalogo_csv']['tmp_name'], 'rb');
if ($archivoCsv === false) {
    importarPixalFallido('No se pudo leer el archivo CSV.');
}
$primeraLinea = fgets($archivoCsv);
if ($primeraLinea === false) {
    fclose($archivoCsv);
    importarPixalFallido('El CSV está vacío.');
}
$delimitador = substr_count($primeraLinea, ';') > substr_count($primeraLinea, ',') ? ';' : ',';
rewind($archivoCsv);
$encabezados = fgetcsv($archivoCsv, 0, $delimitador, '"', '\\');
if (!is_array($encabezados)) {
    fclose($archivoCsv);
    importarPixalFallido('No se pudo leer la fila de encabezados del CSV.');
}
if (isset($encabezados[0])) {
    $encabezados[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $encabezados[0]);
}
$encabezados = array_map(function ($valor) {
    return strtolower(trim((string) $valor));
}, $encabezados);
$columnasRequeridas = array('codigo', 'descripcion', 'imagen', 'marca', 'precio');
$indicesColumnas = array();
foreach ($columnasRequeridas as $columna) {
    $indice = array_search($columna, $encabezados, true);
    if ($indice === false) {
        fclose($archivoCsv);
        importarPixalFallido('El CSV debe incluir los encabezados codigo, descripcion, imagen, marca y precio.');
    }
    $indicesColumnas[$columna] = $indice;
}

$productos = array();
$codigos = array();
$numeroFila = 1;
while (($fila = fgetcsv($archivoCsv, 0, $delimitador, '"', '\\')) !== false) {
    $numeroFila++;
    if ($fila === array(null) || count(array_filter($fila, function ($valor) { return trim((string) $valor) !== ''; })) === 0) {
        continue;
    }
    $producto = array();
    foreach ($columnasRequeridas as $columna) {
        $indice = $indicesColumnas[$columna];
        $producto[$columna] = isset($fila[$indice]) ? trim((string) $fila[$indice]) : '';
    }
    $producto['precio'] = str_replace(array('$', ',', ' '), '', $producto['precio']);
    if (
        $producto['codigo'] === '' || strlen($producto['codigo']) > 80 ||
        $producto['descripcion'] === '' || strlen($producto['descripcion']) > 65535 ||
        strlen($producto['marca']) > 120 || !is_numeric($producto['precio']) ||
        !is_finite((float) $producto['precio']) || (float) $producto['precio'] < 0 ||
        (float) $producto['precio'] > 99999999999.99
    ) {
        fclose($archivoCsv);
        importarPixalFallido('Revisa el código, la descripción, la marca y el precio de la fila ' . $numeroFila . '.');
    }
    $codigoNormalizado = strtolower($producto['codigo']);
    if (isset($codigos[$codigoNormalizado])) {
        fclose($archivoCsv);
        importarPixalFallido('El código ' . $producto['codigo'] . ' está duplicado en el CSV.');
    }
    $codigos[$codigoNormalizado] = true;
    $productos[] = $producto;
    if (count($productos) > 5000) {
        fclose($archivoCsv);
        importarPixalFallido('El archivo no puede contener más de 5,000 productos.');
    }
}
fclose($archivoCsv);
if (count($productos) === 0) {
    importarPixalFallido('El CSV no contiene productos para importar.');
}

$archivosImagenes = array();
if (isset($_FILES['imagenes'])) {
    $carga = $_FILES['imagenes'];
    if (
        !isset($carga['name'], $carga['tmp_name'], $carga['error'], $carga['size']) ||
        !is_array($carga['name']) || !is_array($carga['tmp_name']) ||
        !is_array($carga['error']) || !is_array($carga['size'])
    ) {
        importarPixalFallido('La lista de imágenes no tiene un formato válido.');
    }
    if (count($carga['name']) > 300) {
        importarPixalFallido('Adjunta un máximo de 300 imágenes por importación.');
    }
    foreach ($carga['name'] as $indice => $nombreOriginal) {
        if (!isset($carga['error'][$indice], $carga['size'][$indice], $carga['tmp_name'][$indice])) {
            importarPixalFallido('La lista de imágenes no tiene un formato válido.');
        }
        if ($carga['error'][$indice] === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if (
            $carga['error'][$indice] !== UPLOAD_ERR_OK ||
            !is_string($nombreOriginal) ||
            !is_string($carga['tmp_name'][$indice]) ||
            !is_uploaded_file($carga['tmp_name'][$indice]) ||
            (int) $carga['size'][$indice] < 1 ||
            (int) $carga['size'][$indice] > 5 * 1024 * 1024
        ) {
            importarPixalFallido('Una imagen no se recibió correctamente o excede 5 MB.');
        }
        $nombreArchivo = basename(str_replace('\\', '/', $nombreOriginal));
        if ($nombreArchivo === '' || isset($archivosImagenes[$nombreArchivo])) {
            importarPixalFallido('Hay nombres de imagen vacíos o duplicados.');
        }
        $informacionImagen = @getimagesize($carga['tmp_name'][$indice]);
        if (
            $informacionImagen === false ||
            !isset($informacionImagen['mime']) ||
            !in_array($informacionImagen['mime'], array('image/jpeg', 'image/png'), true)
        ) {
            importarPixalFallido('Solo se permiten imágenes JPG o PNG válidas.');
        }
        $extensionImagen = $informacionImagen['mime'] === 'image/png' ? 'png' : 'jpg';
        $archivosImagenes[$nombreArchivo] = array(
            'temporal' => $carga['tmp_name'][$indice],
            'nombre' => bin2hex(random_bytes(16)) . '.' . $extensionImagen,
            'destino' => ''
        );
        $archivosImagenes[$nombreArchivo]['destino'] = __DIR__ . '/uploads/catalogo_pixal/' . $archivosImagenes[$nombreArchivo]['nombre'];
    }
}

$directorioImagenes = __DIR__ . '/uploads/catalogo_pixal';
if (!is_dir($directorioImagenes) && !mkdir($directorioImagenes, 0750, true) && !is_dir($directorioImagenes)) {
    error_log('No fue posible crear el directorio de imágenes del catálogo Pixal Center.');
    importarPixalFallido('No se pudo preparar el almacenamiento de imágenes.');
}
if (!is_writable($directorioImagenes)) {
    error_log('El directorio de imágenes del catálogo Pixal Center no permite escritura.');
    importarPixalFallido('No se pueden guardar las imágenes del catálogo.');
}

$imagenesGuardadas = array();
foreach ($productos as $producto) {
    if ($producto['imagen'] !== '') {
        $nombreImagen = basename(str_replace('\\', '/', $producto['imagen']));
        if (!isset($archivosImagenes[$nombreImagen])) {
            importarPixalFallido('No se adjuntó la imagen indicada en el CSV: ' . $nombreImagen . '.');
        }
    }
}
foreach ($productos as &$producto) {
    $imagenCsv = $producto['imagen'];
    if ($imagenCsv === '') {
        $producto['imagen_guardada'] = '';
        continue;
    }
    $nombreImagen = basename(str_replace('\\', '/', $imagenCsv));
    $archivoImagen = $archivosImagenes[$nombreImagen];
    if (!isset($archivoImagen['guardada'])) {
        if (!move_uploaded_file($archivoImagen['temporal'], $archivoImagen['destino'])) {
            error_log('No fue posible guardar una imagen del catálogo Pixal Center.');
            foreach ($imagenesGuardadas as $rutaImagen) {
                if (is_file($rutaImagen) && !unlink($rutaImagen)) {
                    error_log('No fue posible retirar la imagen parcial: ' . $rutaImagen);
                }
            }
            unset($producto);
            importarPixalFallido('No se pudo guardar una imagen del catálogo.');
        }
        $imagenesGuardadas[] = $archivoImagen['destino'];
        $archivosImagenes[$nombreImagen]['guardada'] = true;
    }
    $producto['imagen_guardada'] = $archivoImagen['nombre'];
}
unset($producto);

if (!mysqli_begin_transaction($conn)) {
    foreach ($imagenesGuardadas as $rutaImagen) {
        if (is_file($rutaImagen) && !unlink($rutaImagen)) {
            error_log('No fue posible retirar una imagen del catálogo sin guardar.');
        }
    }
    importarPixalFallido('No se pudo iniciar la importación.');
}
$consultaProducto = mysqli_prepare(
    $conn,
    "INSERT INTO productos_pixal_center (codigo, descripcion, imagen, marca, precio)
     VALUES (?, ?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE
        descripcion = VALUES(descripcion),
        imagen = IF(VALUES(imagen) = '', imagen, VALUES(imagen)),
        marca = VALUES(marca),
        precio = VALUES(precio)"
);
if (!$consultaProducto) {
    mysqli_rollback($conn);
    foreach ($imagenesGuardadas as $rutaImagen) {
        if (is_file($rutaImagen) && !unlink($rutaImagen)) {
            error_log('No fue posible retirar una imagen del catálogo sin guardar.');
        }
    }
    importarPixalFallido('No se pudo preparar la importación del catálogo.');
}
foreach ($productos as $producto) {
    $codigo = $producto['codigo'];
    $descripcion = $producto['descripcion'];
    $imagen = $producto['imagen_guardada'];
    $marca = $producto['marca'];
    $precio = (float) $producto['precio'];
    if (!$consultaProducto->bind_param('ssssd', $codigo, $descripcion, $imagen, $marca, $precio) || !$consultaProducto->execute()) {
        error_log('No fue posible importar un producto Pixal: ' . $consultaProducto->error);
        $consultaProducto->close();
        mysqli_rollback($conn);
        foreach ($imagenesGuardadas as $rutaImagen) {
            if (is_file($rutaImagen) && !unlink($rutaImagen)) {
                error_log('No fue posible retirar una imagen del catálogo sin guardar.');
            }
        }
        importarPixalFallido('No se pudo importar el catálogo. Verifica los datos e inténtalo nuevamente.');
    }
}
$consultaProducto->close();
if (!mysqli_commit($conn)) {
    mysqli_rollback($conn);
    foreach ($imagenesGuardadas as $rutaImagen) {
        if (is_file($rutaImagen) && !unlink($rutaImagen)) {
            error_log('No fue posible retirar una imagen del catálogo sin guardar.');
        }
    }
    importarPixalFallido('No se pudo finalizar la importación.');
}

header('Location: catalogoPixal.php?importados=' . count($productos));
exit;
