<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
if (!usuario_es_administrador()) {
    http_response_code(403);
    exit('No tienes permiso para administrar el catálogo.');
}
require_once __DIR__ . '/pedidos_db.php';

$errorCatalogo = isset($_SESSION['catalogo_pixal_error']) ? $_SESSION['catalogo_pixal_error'] : '';
unset($_SESSION['catalogo_pixal_error']);
$importados = isset($_GET['importados']) ? filter_var($_GET['importados'], FILTER_VALIDATE_INT) : false;
$resultado = mysqli_query($conn, 'SELECT COUNT(*) AS cantidad FROM productos_pixal_center');
if ($resultado === false) {
    error_log('No fue posible consultar el tamaño del catálogo Pixal: ' . mysqli_error($conn));
    http_response_code(500);
    exit('No se pudo consultar el catálogo.');
}
$cantidadProductos = (int) mysqli_fetch_assoc($resultado)['cantidad'];

function escaparCatalogoPixal($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

include __DIR__ . '/encabezado.php';
?>
<main class="pedido-page">
  <section class="pedido-hero">
    <div>
      <span class="pedido-eyebrow"><i class="fa-solid fa-database"></i> CATÁLOGO DE PROVEEDOR</span>
      <h1>Pixal Center</h1>
      <p><?= number_format($cantidadProductos) ?> productos registrados.</p>
    </div>
    <a class="btn btn-outline-light" href="pedido.php"><i class="fa-solid fa-arrow-left"></i> Volver a pedidos</a>
  </section>

  <?php if ($errorCatalogo !== '') { ?><div class="alert alert-danger" role="alert"><?= escaparCatalogoPixal($errorCatalogo) ?></div><?php } ?>
  <?php if ($importados !== false && $importados > 0) { ?><div class="alert alert-success" role="status">Se importaron <?= (int) $importados ?> productos correctamente.</div><?php } ?>

  <section class="pedido-panel">
    <div class="pedido-panel-heading">
      <div><span class="pedido-step">01</span><h2>Importar productos</h2></div>
      <p>Los códigos existentes se actualizan sin duplicarse.</p>
    </div>
    <p>Guarda la hoja de cálculo como CSV UTF-8. El archivo debe incluir estos encabezados: <code>codigo, descripcion, imagen, marca, precio</code>. El precio debe ser numérico con punto decimal. En <code>imagen</code> indica el nombre del archivo y adjunta las fotos en el mismo formulario.</p>
    <form action="importarPixalCSV.php" method="post" enctype="multipart/form-data" class="pedido-data-grid">
      <input type="hidden" name="token_csrf" value="<?= escaparCatalogoPixal(token_csrf()) ?>">
      <label class="pedido-field">
        <span>Archivo CSV UTF-8 <b>*</b></span>
        <input type="file" name="catalogo_csv" accept=".csv,text/csv" required>
      </label>
      <label class="pedido-field">
        <span>Imágenes de productos (JPG o PNG; máximo 5 MB c/u)</span>
        <input type="file" name="imagenes[]" accept="image/jpeg,image/png" multiple>
      </label>
      <div class="pedido-field-wide">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-file-import"></i> Importar catálogo</button>
      </div>
    </form>
  </section>
</main>
<?php include __DIR__ . '/footer.php'; ?>
