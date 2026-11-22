<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
require_once __DIR__ . '/pedidos_db.php';

$errorPedido = isset($_SESSION['pedido_error']) ? $_SESSION['pedido_error'] : '';
unset($_SESSION['pedido_error']);
$guardado = isset($_GET['guardado']) ? (int) $_GET['guardado'] : 0;
$actualizado = isset($_GET['actualizado']) ? (int) $_GET['actualizado'] : 0;
$eliminado = isset($_GET['eliminado']) ? (int) $_GET['eliminado'] : 0;

$pedidos = array();
$resultadoPedidos = mysqli_query(
    $conn,
    'SELECT id_pedido, folio, fecha_pedido, proveedor, total_compra, total_venta
     FROM pedidos ORDER BY id_pedido DESC LIMIT 100'
);
if ($resultadoPedidos !== false) {
    while ($pedido = mysqli_fetch_assoc($resultadoPedidos)) {
        $pedidos[] = $pedido;
    }
} else {
    error_log('No fue posible consultar los pedidos: ' . mysqli_error($conn));
    http_response_code(500);
    exit('No se pudieron cargar los pedidos.');
}

$pedidoActual = null;
$editando = isset($_GET['editar']);
if ($editando || isset($_GET['ver'])) {
    $idVer = filter_var($editando ? $_GET['editar'] : $_GET['ver'], FILTER_VALIDATE_INT);
    if ($idVer === false || $idVer < 1) {
        http_response_code(400);
        exit('El pedido seleccionado no es válido.');
    }
    $consulta = mysqli_prepare($conn, 'SELECT id_pedido, folio, fecha_pedido, proveedor, notas, envio, total_compra, total_venta FROM pedidos WHERE id_pedido = ?');
    if (!$consulta) {
        error_log('No fue posible preparar la consulta de pedido: ' . mysqli_error($conn));
        http_response_code(500);
        exit('No se pudo cargar el pedido.');
    }
    $consulta->bind_param('i', $idVer);
    if (!$consulta->execute()) {
        error_log('No fue posible cargar el pedido: ' . $consulta->error);
        $consulta->close();
        http_response_code(500);
        exit('No se pudo cargar el pedido.');
    }
    $pedidoActual = $consulta->get_result()->fetch_assoc();
    $consulta->close();
    if (!$pedidoActual) {
        http_response_code(404);
        exit('No se encontró el pedido seleccionado.');
    }
}

$detalles = array();
if ($pedidoActual) {
    $consultaDetalles = mysqli_prepare(
        $conn,
        'SELECT modelo, cantidad, descripcion, imagen, precio_compra, total_compra,
                margen_porcentaje, precio_venta, precio_final, ganancia_individual,
                ganancia_total, total_venta, iva_porcentaje, iva_monto
         FROM pedido_detalles WHERE id_pedido = ? ORDER BY orden, id_detalle'
    );
    if (!$consultaDetalles) {
        error_log('No fue posible preparar los productos del pedido: ' . mysqli_error($conn));
        http_response_code(500);
        exit('No se pudieron cargar los productos del pedido.');
    }
    $consultaDetalles->bind_param('i', $pedidoActual['id_pedido']);
    if (!$consultaDetalles->execute()) {
        error_log('No fue posible consultar los productos del pedido: ' . $consultaDetalles->error);
        $consultaDetalles->close();
        http_response_code(500);
        exit('No se pudieron cargar los productos del pedido.');
    }
    $detalles = $consultaDetalles->get_result()->fetch_all(MYSQLI_ASSOC);
    $consultaDetalles->close();
}

function escaparPedido($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function imagenDetallePedido($nombre)
{
    $nombre = imagenCatalogoPedidoDisponible($nombre);
    if ($nombre === '') return '';
    $ruta = __DIR__ . '/uploads/catalogo_pixal/' . $nombre;
    $contenido = @file_get_contents($ruta);
    if ($contenido === false) {
        return '';
    }
    $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
    $tipo = $extension === 'png' ? 'image/png' : 'image/jpeg';
    return 'data:' . $tipo . ';base64,' . base64_encode($contenido);
}

include __DIR__ . '/encabezado.php';
?>
<main class="pedido-page">
  <section class="pedido-hero">
    <div>
      <span class="pedido-eyebrow"><i class="fa-solid fa-basket-shopping"></i> PEDIDOS · COMPRA Y VENTA</span>
      <h1>Pedidos</h1>
      <p>Incluye los productos y calcula automáticamente compra, margen y venta.</p>
    </div>
    <div class="pedido-hero-actions">
      <?php if (usuario_es_administrador()) { ?>
        <a class="btn btn-outline-light" href="catalogoPixal.php"><i class="fa-solid fa-database"></i> Catálogo Pixal</a>
      <?php } ?>
      <i class="fa-solid fa-box-open pedido-hero-icon" aria-hidden="true"></i>
    </div>
  </section>

  <?php if ($errorPedido !== '') { ?>
    <div class="alert alert-danger"><?= escaparPedido($errorPedido) ?></div>
  <?php } ?>
  <?php if ($guardado > 0) { ?>
    <div class="alert alert-success">Pedido guardado correctamente.</div>
  <?php } ?>
  <?php if ($actualizado > 0) { ?>
    <div class="alert alert-success">Pedido actualizado correctamente.</div>
  <?php } ?>
  <?php if ($eliminado > 0) { ?>
    <div class="alert alert-success">Pedido eliminado correctamente.</div>
  <?php } ?>

  <section class="pedido-panel">
    <div class="pedido-panel-heading pedido-panel-heading-dark">
      <div><span class="pedido-step">01</span><h2><?= $editando ? 'Editar pedido ' . escaparPedido($pedidoActual['folio']) : 'Nuevo pedido' ?></h2></div>
      <p>Los totales y el margen se calculan automáticamente.</p>
    </div>

    <form action="guardarPedido.php" method="post" class="pedido-form" id="pedido-form" data-user="<?= escaparPedido($_SESSION['usuario']) ?>" data-editar="<?= $editando ? (int) $pedidoActual['id_pedido'] : 0 ?>">
      <input type="hidden" name="token_csrf" value="<?= escaparPedido(token_csrf()) ?>">
      <?php if ($editando) { ?><input type="hidden" name="id_pedido" value="<?= (int) $pedidoActual['id_pedido'] ?>"><?php } ?>
      <div class="pedido-data-grid">
        <label class="pedido-field">
          <span>Fecha <b>*</b></span>
          <input type="date" name="fecha_pedido" value="<?= escaparPedido($editando ? $pedidoActual['fecha_pedido'] : date('Y-m-d')) ?>" required>
        </label>
        <label class="pedido-field">
          <span>Proveedor</span>
          <select name="proveedor" id="pedido-proveedor">
            <option value="">Selecciona proveedor</option>
            <?php foreach (array('Pixal Center', 'Dipath', 'Tony Superpapelerías', 'CT Internacional', 'CVA', 'Grupo Loma') as $proveedorOpcion) { ?>
              <option value="<?= escaparPedido($proveedorOpcion) ?>" <?= $editando && $pedidoActual['proveedor'] === $proveedorOpcion ? 'selected' : '' ?>><?= escaparPedido($proveedorOpcion) ?></option>
            <?php } ?>
          </select>
        </label>
        <label class="pedido-field">
          <span>Envío (costo)</span>
          <input type="number" name="envio" min="0" max="99999999999.99" step="0.01" value="<?= escaparPedido($editando ? $pedidoActual['envio'] : '0') ?>" required>
        </label>
        <label class="pedido-field pedido-field-wide">
          <span>Notas <small>El envío se suma al costo de compra y reduce la ganancia.</small></span>
          <textarea name="notas" rows="2" maxlength="3000" placeholder="Observaciones del pedido"><?= escaparPedido($editando ? $pedidoActual['notas'] : '') ?></textarea>
        </label>
      </div>
      <p class="pedido-catalog-message" id="pedido-catalog-message" role="status" aria-live="polite"></p>

      <div class="pedido-items-heading">
        <div>
          <h3>Productos del pedido</h3>
          <p>Ingresa el modelo y completa los precios. Los importes se calculan automáticamente.</p>
        </div>
        <span class="pedido-items-hint"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> Pixal Center completa los datos al elegir un código.</span>
      </div>
      <div class="pedido-product-list" id="pedido-items" role="list">
        <article class="pedido-row" role="listitem">
          <input type="hidden" name="items[0][imagen]" value="">

          <div class="pedido-item-top">
            <div class="pedido-field pedido-model">
              <label for="pedido-modelo-0">Modelo o código <b>*</b></label>
              <div class="pedido-model-search">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input id="pedido-modelo-0" type="text" name="items[0][modelo]" maxlength="120" required autocomplete="off" placeholder="Busca por código" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="pedido-sugerencias-0">
                <div class="pedido-suggestions" id="pedido-sugerencias-0" role="listbox" aria-label="Sugerencias de productos" hidden></div>
              </div>
            </div>
            <label class="pedido-field pedido-quantity">
              <span>Cantidad <b>*</b></span>
              <input type="number" name="items[0][cantidad]" min="<?= $editando ? '0.01' : '1' ?>" step="<?= $editando ? '0.01' : '1' ?>" value="1" required>
            </label>
            <button type="button" class="pedido-remove" disabled aria-label="Eliminar producto"><i class="fa-solid fa-trash" aria-hidden="true"></i><span>Eliminar</span></button>
          </div>

          <div class="pedido-item-description">
            <div class="pedido-product-preview">
              <img class="pedido-product-image" alt="" hidden>
              <span>Imagen del producto</span>
            </div>
            <label class="pedido-field pedido-description">
              <span>Descripción <b>*</b></span>
              <input type="text" name="items[0][descripcion]" maxlength="255" required placeholder="La descripción se completa al elegir el código">
            </label>
          </div>

          <div class="pedido-item-values">
            <label class="pedido-field">
              <span>Precio de compra</span>
              <input type="number" name="items[0][precio_compra]" class="calculo" min="0" step="0.01" value="0" required>
              <small class="pedido-purchase-tax-hint" hidden>Al seleccionar, se suma 8% al precio del catálogo.</small>
            </label>
            <label class="pedido-field">
              <span>Total de compra</span>
              <input type="text" name="items[0][total_compra]" class="pedido-output total-compra" readonly value="$0.00">
            </label>
            <label class="pedido-field pedido-field-highlight">
              <span>Precio final unitario</span>
              <input type="number" name="items[0][precio_final]" class="pedido-final precio-final" min="0" max="99999999999.99" step="0.01" value="0" required>
            </label>
            <label class="pedido-field">
              <span>Margen de ganancia (%)</span>
              <input type="text" name="items[0][margen_porcentaje]" class="pedido-output" readonly value="No definido">
            </label>
            <label class="pedido-field">
              <span>Ganancia por unidad</span>
              <input type="text" name="items[0][ganancia_individual]" class="pedido-output" readonly value="$0.00">
            </label>
            <label class="pedido-field">
              <span>Ganancia total</span>
              <input type="text" name="items[0][ganancia_total]" class="pedido-output" readonly value="$0.00">
            </label>
            <label class="pedido-field">
              <span>Total de venta</span>
              <input type="text" name="items[0][total_venta]" class="pedido-output pedido-output-total" readonly value="$0.00">
            </label>
          </div>
        </article>
      </div>
      <div class="pedido-totals" aria-label="Resumen de totales" aria-live="polite">
        <div class="pedido-total-card">
          <span><i class="fa-solid fa-cart-shopping" aria-hidden="true"></i> Compra con envío</span>
          <strong id="pedido-total-compra">$0.00</strong>
        </div>
        <div class="pedido-total-card pedido-total-profit">
          <span><i class="fa-solid fa-arrow-trend-up" aria-hidden="true"></i> Ganancia estimada</span>
          <strong id="pedido-total-ganancia">$0.00</strong>
        </div>
        <div class="pedido-total-card pedido-total-sales">
          <span><i class="fa-solid fa-sack-dollar" aria-hidden="true"></i> Venta total</span>
          <strong id="pedido-total-venta">$0.00</strong>
        </div>
      </div>

      <div class="pedido-actions">
        <button type="button" id="pedido-add" class="btn btn-outline-primary"><i class="fa-solid fa-plus" aria-hidden="true"></i> Añadir otro producto</button>
        <?php if ($editando) { ?><a href="pedido.php" class="btn btn-outline-secondary">Cancelar edición</a><?php } ?>
        <button type="submit" class="btn btn-primary btn-lg" <?= $editando ? 'disabled' : '' ?>><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> <?= $editando ? 'Actualizar pedido' : 'Guardar pedido' ?></button>
      </div>
      <?php if ($editando) { ?><noscript><p class="alert alert-warning">Activa JavaScript para cargar y editar los productos del pedido.</p></noscript><?php } ?>
    </form>
  </section>

  <section class="pedido-panel pedido-history">
    <div class="pedido-panel-heading pedido-panel-heading-dark">
      <div><span class="pedido-step">02</span><h2>Pedidos registrados</h2></div>
      <p>Últimos pedidos guardados.</p>
    </div>
    <div class="pedido-table-wrap">
      <table class="pedido-history-table">
        <thead><tr><th>Folio</th><th>Fecha</th><th>Proveedor</th><th>Total compra</th><th>Total venta</th><th>Ganancia estimada</th><th>Acciones</th></tr></thead>
        <tbody>
          <?php if (count($pedidos) === 0) { ?>
            <tr><td colspan="7" class="pedido-empty">Aún no hay pedidos registrados.</td></tr>
          <?php } ?>
          <?php foreach ($pedidos as $pedido) { ?>
            <tr>
              <td><strong><?= escaparPedido($pedido['folio']) ?></strong></td>
              <td><?= escaparPedido(date('d/m/Y', strtotime($pedido['fecha_pedido']))) ?></td>
              <td><?= escaparPedido($pedido['proveedor'] ?: 'Sin proveedor') ?></td>
              <td>$<?= number_format((float) $pedido['total_compra'], 2) ?></td>
              <td>$<?= number_format((float) $pedido['total_venta'], 2) ?></td>
              <td>$<?= number_format((float) $pedido['total_venta'] - (float) $pedido['total_compra'], 2) ?></td>
              <td class="pedido-history-actions">
                <a href="pedido.php?ver=<?= (int) $pedido['id_pedido'] ?>" title="Ver pedido"><i class="fa-solid fa-eye"></i></a>
                <a href="pedido.php?editar=<?= (int) $pedido['id_pedido'] ?>#pedido-form" title="Editar pedido" aria-label="Editar pedido <?= escaparPedido($pedido['folio']) ?>"><i class="fa-solid fa-pen"></i></a>
                <a href="pedidoPDF.php?id=<?= (int) $pedido['id_pedido'] ?>" title="PDF del pedido" target="_blank" rel="noopener"><i class="fa-solid fa-file-pdf"></i></a>
                <form action="guardarPedido.php" method="post" onsubmit="return confirm('¿Eliminar este pedido?');">
                  <input type="hidden" name="token_csrf" value="<?= escaparPedido(token_csrf()) ?>">
                  <input type="hidden" name="eliminar" value="<?= (int) $pedido['id_pedido'] ?>">
                  <button type="submit" title="Eliminar pedido"><i class="fa-solid fa-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </section>

  <?php if ($pedidoActual && !$editando) { ?>
  <section class="pedido-panel pedido-history">
    <div class="pedido-panel-heading">
      <div><span class="pedido-step">03</span><h2><?= escaparPedido($pedidoActual['folio']) ?></h2></div>
      <p><?= escaparPedido(date('d/m/Y', strtotime($pedidoActual['fecha_pedido']))) ?> · <?= escaparPedido($pedidoActual['proveedor']) ?></p>
    </div>
    <p><a class="btn btn-outline-primary btn-sm" href="pedidoPDF.php?id=<?= (int) $pedidoActual['id_pedido'] ?>" target="_blank" rel="noopener"><i class="fa-solid fa-file-pdf"></i> Descargar PDF del pedido</a></p>
    <div class="pedido-table-wrap">
      <table class="pedido-detail-table">
        <thead><tr><th>Modelo</th><th>Cantidad</th><th>Imagen</th><th>Descripción</th><th>Precio compra</th><th>Margen</th><th>Precio venta</th><th>Precio final</th><th>Total venta</th></tr></thead>
        <tbody>
          <?php foreach ($detalles as $detalle) { ?>
          <tr>
            <td><?= escaparPedido($detalle['modelo']) ?></td>
            <td><?= escaparPedido(number_format((float) $detalle['cantidad'], 2)) ?></td>
            <td>
              <?php $imagenDetalle = imagenDetallePedido($detalle['imagen']); ?>
              <?php if ($imagenDetalle !== '') { ?>
                <img class="pedido-detail-image" src="<?= escaparPedido($imagenDetalle) ?>" alt="<?= escaparPedido($detalle['descripcion']) ?>">
              <?php } ?>
            </td>
            <td class="pedido-detail-description"><?= escaparPedido($detalle['descripcion']) ?></td>
            <td>$<?= number_format((float) $detalle['precio_compra'], 2) ?></td>
            <td><?= (float) $detalle['precio_final'] > 0 ? number_format((float) $detalle['margen_porcentaje'], 2) . '%' : 'No definido' ?></td>
            <td>$<?= number_format((float) $detalle['precio_venta'], 2) ?></td>
            <td>$<?= number_format((float) $detalle['precio_final'], 2) ?></td>
            <td>$<?= number_format((float) $detalle['total_venta'], 2) ?></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </section>
  <?php } ?>
</main>
<script>
  window.pedidoClearDraft = <?= $guardado > 0 ? 'true' : 'false' ?>;
  window.pedidoUpdated = <?= $actualizado > 0 ? $actualizado : 0 ?>;
  window.pedidoEditItems = <?= $editando ? json_encode($detalles, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) : 'null' ?>;
</script>
<script src="js/pedido.js"></script>
<?php include __DIR__ . '/footer.php'; ?>
