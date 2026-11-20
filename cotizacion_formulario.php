<?php
if (
    !isset($editarCotizacion, $datosCotizacion, $itemsFormulario) ||
    !function_exists('escaparCotizacion')
) {
    http_response_code(404);
    exit;
}
?>
<form class="cotizacion-form" action="guardarCotizacion.php" method="post" enctype="multipart/form-data">
  <input type="hidden" name="token_csrf" value="<?= escaparCotizacion(token_csrf()) ?>">
  <?php if ($editarCotizacion) { ?>
    <input type="hidden" name="id_cotizacion" value="<?= (int) $idEditar ?>">
  <?php } ?>

  <section class="cotizacion-panel">
    <div class="cotizacion-panel-heading">
      <div><span class="cotizacion-step">01</span><h2>Datos del cliente</h2></div>
      <p>Información que aparecerá en la propuesta.</p>
    </div>
    <div class="cotizacion-grid">
      <label class="cotizacion-field">
        <span>Nombre o razón social <b>*</b></span>
        <input type="text" name="cliente_nombre" maxlength="180" required autocomplete="name" value="<?= escaparCotizacion($datosCotizacion['cliente_nombre']) ?>">
      </label>
      <label class="cotizacion-field">
        <span>Empresa</span>
        <input type="text" name="cliente_empresa" maxlength="180" autocomplete="organization" value="<?= escaparCotizacion($datosCotizacion['cliente_empresa']) ?>">
      </label>
      <label class="cotizacion-field">
        <span>Correo electrónico</span>
        <input type="email" name="cliente_email" maxlength="180" autocomplete="email" value="<?= escaparCotizacion($datosCotizacion['cliente_email']) ?>">
      </label>
      <label class="cotizacion-field">
        <span>Teléfono</span>
        <input type="tel" name="cliente_telefono" maxlength="50" autocomplete="tel" value="<?= escaparCotizacion($datosCotizacion['cliente_telefono']) ?>">
      </label>
      <label class="cotizacion-field">
        <span>RFC</span>
        <input type="text" name="cliente_rfc" maxlength="30" autocomplete="off" value="<?= escaparCotizacion($datosCotizacion['cliente_rfc']) ?>">
      </label>
      <label class="cotizacion-field">
        <span>Vigencia hasta <b>*</b></span>
        <input type="date" name="vigencia_hasta" min="<?= date('Y-m-d') ?>" value="<?= escaparCotizacion($datosCotizacion['vigencia_hasta']) ?>" required>
      </label>
      <label class="cotizacion-field cotizacion-field-wide">
        <span>Domicilio</span>
        <textarea name="cliente_direccion" rows="2" maxlength="1000" autocomplete="street-address"><?= escaparCotizacion($datosCotizacion['cliente_direccion']) ?></textarea>
      </label>
    </div>
  </section>

  <section class="cotizacion-panel">
    <div class="cotizacion-panel-heading">
      <div><span class="cotizacion-step">02</span><h2>Productos y servicios</h2></div>
      <p>Agrega descripción, especificaciones, precio e imagen por concepto.</p>
    </div>
    <div id="cotizacion-items" class="cotizacion-items" data-next-index="<?= count($itemsFormulario) ?>">
      <?php foreach ($itemsFormulario as $indice => $itemFormulario) {
          $indice = (int) $indice;
          $itemImagen = isset($itemFormulario['imagen']) ? (string) $itemFormulario['imagen'] : '';
          ?>
        <article class="cotizacion-item" data-item-index="<?= $indice ?>">
          <div class="cotizacion-item-heading">
            <strong><span class="cotizacion-item-number"><?= str_pad((string) ($indice + 1), 2, '0', STR_PAD_LEFT) ?></span> Concepto</strong>
            <button type="button" class="cotizacion-remove-item" aria-label="Eliminar concepto" <?= count($itemsFormulario) === 1 ? 'disabled' : '' ?>><i class="fa-solid fa-trash"></i></button>
          </div>
          <input type="hidden" name="items[<?= $indice ?>][imagen_actual]" value="<?= escaparCotizacion($itemImagen) ?>">
          <div class="cotizacion-grid">
            <label class="cotizacion-field">
              <span>Tipo de producto o servicio</span>
              <input type="text" name="items[<?= $indice ?>][tipo_producto]" maxlength="100" placeholder="Ej. Impresora, computadora, instalación" value="<?= escaparCotizacion($itemFormulario['tipo_producto'] ?? '') ?>">
            </label>
            <label class="cotizacion-field">
              <span>Marca</span>
              <input type="text" name="items[<?= $indice ?>][marca]" maxlength="100" placeholder="Ej. HP" value="<?= escaparCotizacion($itemFormulario['marca'] ?? '') ?>">
            </label>
            <label class="cotizacion-field">
              <span>Modelo</span>
              <input type="text" name="items[<?= $indice ?>][modelo]" maxlength="120" value="<?= escaparCotizacion($itemFormulario['modelo'] ?? '') ?>">
            </label>
            <label class="cotizacion-field">
              <span>SKU / número de parte</span>
              <input type="text" name="items[<?= $indice ?>][sku]" maxlength="100" value="<?= escaparCotizacion($itemFormulario['sku'] ?? '') ?>">
            </label>
            <label class="cotizacion-field cotizacion-field-wide">
              <span>Descripción <b>*</b></span>
              <input type="text" name="items[<?= $indice ?>][descripcion]" maxlength="255" required placeholder="Ej. Impresora multifuncional a color" value="<?= escaparCotizacion($itemFormulario['descripcion'] ?? '') ?>">
            </label>
            <label class="cotizacion-field cotizacion-field-wide">
              <span>Especificaciones y alcance</span>
              <textarea name="items[<?= $indice ?>][especificaciones]" rows="2" maxlength="3000" placeholder="Características, compatibilidad, entregables o alcance del servicio"><?= escaparCotizacion($itemFormulario['especificaciones'] ?? '') ?></textarea>
            </label>
            <label class="cotizacion-field">
              <span>Cantidad <b>*</b></span>
              <input type="number" name="items[<?= $indice ?>][cantidad]" class="cotizacion-number" min="0.01" step="0.01" value="<?= escaparCotizacion($itemFormulario['cantidad'] ?? 1) ?>" required>
            </label>
            <label class="cotizacion-field">
              <span>Precio unitario <b>*</b></span>
              <input type="number" name="items[<?= $indice ?>][precio_unitario]" class="cotizacion-number" min="0" step="0.01" value="<?= escaparCotizacion($itemFormulario['precio_unitario'] ?? 0) ?>" required>
            </label>
            <label class="cotizacion-field">
              <span>Descuento (%)</span>
              <input type="number" name="items[<?= $indice ?>][descuento_porcentaje]" class="cotizacion-number" min="0" max="100" step="0.01" value="<?= escaparCotizacion($itemFormulario['descuento_porcentaje'] ?? 0) ?>">
            </label>
            <label class="cotizacion-field">
              <span>Impuesto (%)</span>
              <input type="number" name="items[<?= $indice ?>][impuesto_porcentaje]" class="cotizacion-number" min="0" max="100" step="0.01" value="<?= escaparCotizacion($itemFormulario['impuesto_porcentaje'] ?? 16) ?>">
            </label>
            <label class="cotizacion-field">
              <span>Garantía del concepto</span>
              <input type="text" name="items[<?= $indice ?>][garantia]" maxlength="180" placeholder="Ej. 1 año con fabricante" value="<?= escaparCotizacion($itemFormulario['garantia'] ?? '') ?>">
            </label>
            <label class="cotizacion-field">
              <span>Imagen del producto (JPG o PNG, máximo 5 MB)</span>
              <input type="file" name="imagenes[<?= $indice ?>]" accept="image/jpeg,image/png">
              <?php if ($itemImagen !== '' && preg_match('/^[a-f0-9]{32}\.(jpg|png)$/', $itemImagen)) { ?>
                <small class="cotizacion-image-current"><i class="fa-solid fa-image"></i> Imagen guardada; se conserva si no seleccionas otra.</small>
              <?php } ?>
            </label>
          </div>
        </article>
      <?php } ?>
    </div>
    <button type="button" id="cotizacion-add-item" class="btn btn-outline-primary cotizacion-add-button">
      <i class="fa-solid fa-plus" aria-hidden="true"></i> Añadir otro concepto
    </button>
  </section>

  <section class="cotizacion-panel">
    <div class="cotizacion-panel-heading">
      <div><span class="cotizacion-step">03</span><h2>Condiciones comerciales</h2></div>
      <p>Define moneda, entrega y condiciones de la oferta.</p>
    </div>
    <div class="cotizacion-grid">
      <label class="cotizacion-field">
        <span>Moneda</span>
        <select name="moneda">
          <option value="MXN" <?= $datosCotizacion['moneda'] === 'MXN' ? 'selected' : '' ?>>MXN — Peso mexicano</option>
          <option value="USD" <?= $datosCotizacion['moneda'] === 'USD' ? 'selected' : '' ?>>USD — Dólar estadounidense</option>
        </select>
      </label>
      <label class="cotizacion-field">
        <span>Condiciones de pago</span>
        <input type="text" name="condiciones_pago" maxlength="255" placeholder="Ej. 50% anticipo, 50% contra entrega" value="<?= escaparCotizacion($datosCotizacion['condiciones_pago']) ?>">
      </label>
      <label class="cotizacion-field">
        <span>Tiempo de entrega</span>
        <input type="text" name="tiempo_entrega" maxlength="180" placeholder="Ej. 5 a 7 días hábiles" value="<?= escaparCotizacion($datosCotizacion['tiempo_entrega']) ?>">
      </label>
      <label class="cotizacion-field">
        <span>Garantía general</span>
        <input type="text" name="garantia" maxlength="180" placeholder="Ej. Garantía indicada por producto" value="<?= escaparCotizacion($datosCotizacion['garantia']) ?>">
      </label>
      <label class="cotizacion-field cotizacion-field-wide">
        <span>Notas y observaciones</span>
        <textarea name="notas" rows="3" maxlength="3000" placeholder="Aclaraciones adicionales para el cliente"><?= escaparCotizacion($datosCotizacion['notas']) ?></textarea>
      </label>
    </div>
  </section>

  <div class="cotizacion-submit-row">
    <span><i class="fa-solid fa-circle-info" aria-hidden="true"></i> El total se calcula con el descuento e impuesto de cada concepto.</span>
    <div>
      <?php if ($editarCotizacion) { ?>
        <a href="cotizacion.php" class="btn btn-outline-secondary">Cancelar edición</a>
        <button type="submit" class="btn btn-primary btn-lg"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Guardar cambios</button>
      <?php } else { ?>
        <button type="submit" class="btn btn-primary btn-lg"><i class="fa-solid fa-file-circle-check" aria-hidden="true"></i> Guardar y generar cotización</button>
      <?php } ?>
    </div>
  </div>
</form>
