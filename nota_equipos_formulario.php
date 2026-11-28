<?php
$catalogoEquipos = $conn->query('SELECT id_equipo, descripcionEquipo FROM equipo ORDER BY descripcionEquipo, id_equipo');
if (!$catalogoEquipos) {
    error_log('No se pudo consultar el catálogo de equipos: ' . $conn->error);
    http_response_code(500);
    exit('No se pudo consultar el catálogo de equipos.');
}
$catalogoEquipos = $catalogoEquipos->fetch_all(MYSQLI_ASSOC);
$lineasEquipos = isset($dataNota) ? equiposDeNota($conn, $dataNota) : array(array('id_equipo' => '', 'marca' => '', 'modelo' => '', 'numero_serie' => ''));
?>
<fieldset class="nota-equipos">
  <legend>Datos de los equipos</legend>
  <p>Registra cada dispositivo que el cliente deja en el taller (hasta 20).</p>
  <noscript><p>Activa JavaScript para agregar más de un equipo.</p></noscript>
  <div class="nota-equipos-lista">
    <?php foreach ($lineasEquipos as $lineaEquipo) { ?>
    <div class="nota-equipo-linea">
      <div class="nota-equipo-titulo">
        <strong>Equipo <span class="nota-equipo-numero"></span></strong>
        <button type="button" class="btn btn-outline-danger nota-equipo-quitar">Quitar</button>
      </div>
      <div class="row">
        <div class="col-md-6">
          <label>Tipo de equipo</label><select name="equipos[]" class="select nota-equipo-selector" required>
              <option value="">Selecciona un equipo</option>
              <?php foreach ($catalogoEquipos as $opcionEquipo) { ?>
              <option value="<?= (int) $opcionEquipo['id_equipo'] ?>" data-patron="<?= equipoAdmitePatron($opcionEquipo['descripcionEquipo']) ? '1' : '0' ?>" <?= (string) $lineaEquipo['id_equipo'] === (string) $opcionEquipo['id_equipo'] ? 'selected' : '' ?>><?= escaparRecepcionHtml($opcionEquipo['descripcionEquipo']) ?></option>
              <?php } ?>
            </select>
        </div>
        <div class="col-md-6">
          <label>Número de serie o IMEI
            <input type="text" name="series[]" maxlength="100" class="form-control" value="<?= escaparRecepcionHtml($lineaEquipo['numero_serie']) ?>">
          </label>
        </div>
        <div class="col-md-6">
          <label>Marca
            <input type="text" name="marcas[]" maxlength="25" class="form-control" value="<?= escaparRecepcionHtml($lineaEquipo['marca']) ?>">
          </label>
        </div>
        <div class="col-md-6">
          <label>Modelo
            <input type="text" name="modelos[]" maxlength="25" class="form-control" value="<?= escaparRecepcionHtml($lineaEquipo['modelo']) ?>">
          </label>
        </div>
      </div>
    </div>
    <?php } ?>
  </div>
  <button type="button" class="btn btn-outline-primary nota-equipo-agregar">Agregar equipo</button>
</fieldset>
