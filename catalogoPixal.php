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
$guardado = isset($_GET['guardado']) && $_GET['guardado'] === '1';
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

  <?php include __DIR__ . '/app/Views/comercial_subnav.php'; ?>

  <?php if ($errorCatalogo !== '') { ?><div class="alert alert-danger" role="alert"><?= escaparCatalogoPixal($errorCatalogo) ?></div><?php } ?>
  <?php if ($guardado) { ?><div class="alert alert-success" role="status">El producto se guardó correctamente.</div><?php } ?>

  <section class="pedido-panel">
    <div class="pedido-panel-heading">
      <div><span class="pedido-step">01</span><h2>Agregar producto</h2></div>
      <p>Si el código ya existe, se actualizarán sus datos.</p>
    </div>
    <form action="guardarProductoPixal.php" method="post" enctype="multipart/form-data" class="pedido-data-grid">
      <input type="hidden" name="token_csrf" value="<?= escaparCatalogoPixal(token_csrf()) ?>">
      <label class="pedido-field">
        <span>Código <b>*</b></span>
        <input type="text" name="codigo" maxlength="80" required>
      </label>
      <label class="pedido-field">
        <span>Descripción <b>*</b></span>
        <textarea name="descripcion" maxlength="65535" required></textarea>
      </label>
      <label class="pedido-field">
        <span>Marca</span>
        <input type="text" name="marca" maxlength="120">
      </label>
      <label class="pedido-field">
        <span>Precio <b>*</b></span>
        <input type="number" name="precio" min="0" max="99999999999.99" step="0.01" required>
      </label>
      <label class="pedido-field">
        <span>Imagen del producto (JPG o PNG; máximo 5 MB)</span>
        <input type="file" name="imagen" accept="image/jpeg,image/png">
      </label>
      <div class="pedido-field-wide">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Guardar producto</button>
      </div>
    </form>
  </section>
</main>
<?php include __DIR__ . '/footer.php'; ?>
