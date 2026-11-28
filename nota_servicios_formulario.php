<?php
$catalogoNota = $conn->query('SELECT id_servicio, tipoServicio, precio FROM servicio ORDER BY tipoServicio, id_servicio');
if (!$catalogoNota) {
    error_log('No se pudo consultar el catálogo para la nota: ' . $conn->error);
    http_response_code(500);
    exit('No se pudo consultar el catálogo de servicios.');
}
$catalogoNota = $catalogoNota->fetch_all(MYSQLI_ASSOC);
$lineasNota = isset($dataNota) ? serviciosDeNota($conn, $dataNota) : array(array('id_servicio' => '', 'precio' => 0));
?>
<fieldset class="nota-servicios">
  <legend>Servicios</legend>
  <p>Selecciona hasta 200 servicios y ajusta sus precios si es necesario. Importes en pesos enteros.</p>
  <noscript><p>Activa JavaScript para agregar servicios y ver los cálculos antes de guardar.</p></noscript>
  <div class="nota-servicios-lista">
    <?php foreach ($lineasNota as $lineaNota) { ?>
    <div class="nota-servicio-linea row">
      <div class="col-md-6">
        <label>Servicio</label><select name="servicios[]" class="select nota-servicio-selector" required>
            <option value="">Selecciona un servicio</option>
            <?php foreach ($catalogoNota as $opcionNota) { ?>
            <option value="<?= (int) $opcionNota['id_servicio'] ?>" data-precio="<?= (int) $opcionNota['precio'] ?>" <?= (string) $lineaNota['id_servicio'] === (string) $opcionNota['id_servicio'] ? 'selected' : '' ?>><?= escaparRecepcionHtml($opcionNota['tipoServicio']) ?></option>
            <?php } ?>
          </select>
      </div>
      <div class="col-md-4">
        <label>Precio
          <input type="number" name="precios[]" class="form-control nota-servicio-precio" min="0" max="2147483647" step="1" value="<?= escaparRecepcionHtml($lineaNota['precio']) ?>" required>
        </label>
      </div>
      <div class="col-md-2"><button type="button" class="btn btn-outline-danger nota-servicio-quitar">Quitar</button></div>
    </div>
    <?php } ?>
  </div>
  <button type="button" class="btn btn-outline-primary nota-servicio-agregar">Agregar servicio</button>
  <label>Descripción general
    <textarea cols="45" rows="3" name="<?= isset($dataNota) ? 'descripcionServicio' : 'descripcion' ?>" class="form-control" maxlength="150"><?= isset($dataNota) ? escaparRecepcionHtml($dataNota['descripcionServicio']) : '' ?></textarea>
  </label>
</fieldset>
