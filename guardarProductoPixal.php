<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
if (!usuario_es_administrador()) {
    http_response_code(403);
    exit('No tienes permiso para administrar el catálogo.');
}
require_once __DIR__ . '/pedidos_db.php';

function guardarProductoPixalFallido($mensaje)
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
    guardarProductoPixalFallido('La sesión de seguridad expiró. Recarga la página e inténtalo nuevamente.');
}

$codigo = isset($_POST['codigo']) && is_string($_POST['codigo']) ? trim($_POST['codigo']) : '';
$descripcion = isset($_POST['descripcion']) && is_string($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
$marca = isset($_POST['marca']) && is_string($_POST['marca']) ? trim($_POST['marca']) : '';
$precioEntrada = isset($_POST['precio']) && is_string($_POST['precio']) ? trim($_POST['precio']) : '';
if (
    $codigo === '' || strlen($codigo) > 80 ||
    $descripcion === '' || strlen($descripcion) > 65535 ||
    strlen($marca) > 120 || !is_numeric($precioEntrada) ||
    !is_finite((float) $precioEntrada) || (float) $precioEntrada < 0 ||
    (float) $precioEntrada > 99999999999.99
) {
    guardarProductoPixalFallido('Revisa el código, la descripción, la marca y el precio del producto.');
}

$imagenGuardada = '';
$imagenMovida = '';
if (isset($_FILES['imagen']['error']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
    if (
        !isset($_FILES['imagen']['tmp_name'], $_FILES['imagen']['size']) ||
        !is_string($_FILES['imagen']['tmp_name']) ||
        !is_int($_FILES['imagen']['error']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK ||
        !is_uploaded_file($_FILES['imagen']['tmp_name']) ||
        (int) $_FILES['imagen']['size'] < 1 || (int) $_FILES['imagen']['size'] > 5 * 1024 * 1024
    ) {
        guardarProductoPixalFallido('La imagen no se recibió correctamente o excede 5 MB.');
    }
    $informacionImagen = @getimagesize($_FILES['imagen']['tmp_name']);
    if (
        $informacionImagen === false || !isset($informacionImagen['mime']) ||
        !in_array($informacionImagen['mime'], array('image/jpeg', 'image/png'), true)
    ) {
        guardarProductoPixalFallido('Solo se permiten imágenes JPG o PNG válidas.');
    }
    $directorioImagenes = __DIR__ . '/uploads/catalogo_pixal';
    if (!is_dir($directorioImagenes) && !mkdir($directorioImagenes, 0750, true) && !is_dir($directorioImagenes)) {
        error_log('No fue posible crear el directorio de imágenes del catálogo Pixal Center.');
        guardarProductoPixalFallido('No se pudo preparar el almacenamiento de imágenes.');
    }
    if (!is_writable($directorioImagenes)) {
        error_log('El directorio de imágenes del catálogo Pixal Center no permite escritura.');
        guardarProductoPixalFallido('No se pueden guardar las imágenes del catálogo.');
    }
    $extensionImagen = $informacionImagen['mime'] === 'image/png' ? 'png' : 'jpg';
    $imagenGuardada = bin2hex(random_bytes(16)) . '.' . $extensionImagen;
    $imagenMovida = $directorioImagenes . '/' . $imagenGuardada;
    if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $imagenMovida)) {
        error_log('No fue posible guardar una imagen del catálogo Pixal Center.');
        guardarProductoPixalFallido('No se pudo guardar la imagen del producto.');
    }
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
    error_log('No fue posible preparar el guardado de un producto Pixal: ' . mysqli_error($conn));
    if ($imagenMovida !== '' && is_file($imagenMovida) && !unlink($imagenMovida)) {
        error_log('No fue posible retirar la imagen de un producto Pixal sin guardar.');
    }
    guardarProductoPixalFallido('No se pudo preparar el guardado del producto.');
}
$precio = (float) $precioEntrada;
if (
    !$consultaProducto->bind_param('ssssd', $codigo, $descripcion, $imagenGuardada, $marca, $precio) ||
    !$consultaProducto->execute()
) {
    error_log('No fue posible guardar un producto Pixal: ' . $consultaProducto->error);
    $consultaProducto->close();
    if ($imagenMovida !== '' && is_file($imagenMovida) && !unlink($imagenMovida)) {
        error_log('No fue posible retirar la imagen de un producto Pixal sin guardar.');
    }
    guardarProductoPixalFallido('No se pudo guardar el producto. Verifica los datos e inténtalo nuevamente.');
}
$consultaProducto->close();

header('Location: catalogoPixal.php?guardado=1');
exit;