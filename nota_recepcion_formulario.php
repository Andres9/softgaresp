<?php
if (isset($_SERVER['SCRIPT_FILENAME']) && basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    http_response_code(404);
    exit;
}

$recepcion = isset($recepcion) ? $recepcion : array();
$canalSeguro = canalSeguroNota();
$valorRecepcion = static function ($campo) use ($recepcion) {
    return htmlspecialchars((string) ($recepcion[$campo] ?? ''), ENT_QUOTES, 'UTF-8');
};
?>
<fieldset class="nota-recepcion">
  <legend>Recepción del equipo</legend>
  <div class="nota-recepcion-grid">
    <div>
      <label for="nota-cargador">¿Deja cargador?</label>
      <select id="nota-cargador" name="cargador" class="form-control">
        <option value="" <?= !isset($recepcion['cargador']) ? 'selected' : '' ?>>Sin indicar</option>
        <option value="1" <?= isset($recepcion['cargador']) && (int) $recepcion['cargador'] === 1 ? 'selected' : '' ?>>Sí</option>
        <option value="0" <?= isset($recepcion['cargador']) && (int) $recepcion['cargador'] === 0 ? 'selected' : '' ?>>No</option>
      </select>
    </div>
    <div>
      <label for="nota-accesorios">Otros accesorios entregados</label>
      <input id="nota-accesorios" name="accesorios" type="text" maxlength="255" class="form-control" value="<?= $valorRecepcion('accesorios') ?>" placeholder="Ej. funda, cable, memoria">
    </div>
    <div>
      <label for="nota-estado-fisico">Estado físico al recibirlo</label>
      <input id="nota-estado-fisico" name="estado_fisico" type="text" maxlength="255" class="form-control" value="<?= $valorRecepcion('estado_fisico') ?>" placeholder="Ej. pantalla rota, rayones">
    </div>
  </div>

  <div class="nota-acceso">
    <h3>Acceso al dispositivo</h3>
    <p>Registra la contraseña o el patrón solo con autorización del cliente. Se guardan cifrados, no aparecen en el PDF y se conservan tras la entrega.</p>
    <?php if (!$canalSeguro) { ?>
      <p class="nota-aviso" role="status">Para consultar o modificar credenciales usa HTTPS o accede desde este equipo mediante localhost. Las credenciales existentes se conservarán al guardar.</p>
    <?php } ?>
    <label for="nota-clave">Contraseña o PIN</label>
    <div class="nota-clave-control">
      <input id="nota-clave" name="clave_dispositivo" type="password" maxlength="256" autocomplete="off" class="form-control" value="<?= $valorRecepcion('clave_dispositivo') ?>" <?= !$canalSeguro ? 'disabled' : '' ?>>
      <button type="button" class="nota-clave-toggle" aria-controls="nota-clave" aria-pressed="false" <?= !$canalSeguro ? 'disabled' : '' ?>>Mostrar</button>
    </div>
    <div id="nota-patron-seccion" class="nota-patron-seccion" hidden>
      <label>Patrón de desbloqueo</label>
      <p>Une al menos cuatro puntos arrastrando o selecciónalos en orden. Usa “Limpiar” para corregirlo.</p>
      <input type="hidden" name="patron_dispositivo" id="nota-patron-valor" value="<?= $valorRecepcion('patron_dispositivo') ?>" <?= !$canalSeguro ? 'disabled' : '' ?>>
      <div class="nota-patron" aria-label="Patrón de nueve puntos" data-deshabilitado="<?= $canalSeguro ? '0' : '1' ?>">
        <svg class="nota-patron-lineas" viewBox="0 0 240 240" aria-hidden="true"><polyline points=""></polyline></svg>
        <?php for ($punto = 1; $punto <= 9; $punto++) { ?>
          <button type="button" class="nota-patron-punto" data-punto="<?= $punto ?>" aria-label="Punto <?= $punto ?>" aria-pressed="false" <?= !$canalSeguro ? 'disabled' : '' ?>><span></span></button>
        <?php } ?>
      </div>
      <button type="button" class="nota-patron-limpiar" <?= !$canalSeguro ? 'disabled' : '' ?>>Limpiar patrón</button>
      <p class="nota-patron-estado" role="status" aria-live="polite"></p>
    </div>
    <noscript><p class="nota-aviso">Activa JavaScript para dibujar o modificar el patrón.</p></noscript>
  </div>
</fieldset>
