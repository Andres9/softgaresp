<?php
date_default_timezone_set('America/Mexico_City');
mysqli_report(MYSQLI_REPORT_OFF);
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/cierres_mensuales_db.php';

if (!$conn) {
    error_log('No fue posible conectar con la base de datos para cargar los cierres mensuales.');
    http_response_code(500);
    exit('No se pudo cargar el cierre mensual.');
}

try {
    prepararTablasCierreMensual($conn);
} catch (RuntimeException $error) {
    error_log($error->getMessage());
    http_response_code(500);
    exit('No se pudo preparar el cierre mensual.');
}

$mes = isset($_GET['mes']) && is_string($_GET['mes']) ? $_GET['mes'] : date('Y-m');
$mesValidado = DateTimeImmutable::createFromFormat('!Y-m', $mes);
$erroresMes = DateTimeImmutable::getLastErrors();
if ($mesValidado === false ||
    ($erroresMes !== false && ($erroresMes['warning_count'] || $erroresMes['error_count'])) ||
    $mesValidado->format('Y-m') !== $mes) {
    http_response_code(400);
    exit('El periodo seleccionado no es válido.');
}
$periodo = $mesValidado->format('Y-m-01');

try {
    $cierre = consultarCierreMensual($conn, $periodo);
    $cierres = listarCierresMensuales($conn);
} catch (RuntimeException $error) {
    error_log($error->getMessage());
    http_response_code(500);
    exit('No se pudo consultar la información del cierre mensual.');
}

$mensajeCierre = isset($_SESSION['cierre_mensual_mensaje']) && is_array($_SESSION['cierre_mensual_mensaje'])
    ? $_SESSION['cierre_mensual_mensaje']
    : null;
unset($_SESSION['cierre_mensual_mensaje']);

$escaparCierre = function ($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
};
$formatearMes = function ($fecha) {
    $nombresMeses = array(
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
        5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
        9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
    );
    $fechaMes = new DateTimeImmutable($fecha);
    return ucfirst($nombresMeses[(int) $fechaMes->format('n')]) . ' ' . $fechaMes->format('Y');
};
$formatearImporte = function ($valor) {
    return '$' . number_format((float) $valor, 2, '.', ',');
};
$sumarDetalles = function ($detalles, $seccion) {
    $total = 0.0;
    if (isset($detalles[$seccion])) {
        foreach ($detalles[$seccion] as $detalle) {
            $total += (float) $detalle['importe'];
        }
    }
    return $total;
};

$plantillas = array(
    'local' => array('Local', 'Internet', 'Movimiento'),
    'fondo' => array('Fondo', 'Efectivo', 'Monedas', 'Abad'),
    'deuda' => array('Teléfono', 'Papelería', 'Mamá', 'Pixal', 'Duracell', 'Copilot')
);
$detallesCierre = $cierre ? $cierre['detalles'] : array();
$cuentasCierre = $cierre ? $cierre['cuentas'] : array();
$totalCuentas = 0.0;
foreach ($cuentasCierre as $cuenta) {
    $totalCuentas += (float) $cuenta['saldo'];
}
$valoresCierre = array(
    'local_adeudo_ganancia' => $cierre ? $cierre['local_adeudo_ganancia'] : '0.00',
    'local_ganancia' => $cierre ? $cierre['local_ganancia'] : '0.00',
    'inventario_actual' => $cierre ? $cierre['inventario_actual'] : '0.00',
    'inventario_deuda' => $cierre ? $cierre['inventario_deuda'] : '0.00'
);
include_once __DIR__ . '/encabezado.php';
?>

<main class="movimiento-page cierre-mensual-page">
  <section class="movimiento-hero">
    <div>
      <span class="movimiento-eyebrow">Control financiero</span>
      <h1>Cierre mensual</h1>
      <p>Registra el local, las cuentas bancarias, el fondo, las deudas y el inventario del mes.</p>
    </div>
    <div class="movimiento-periodo">
      <i class="fa-regular fa-calendar" aria-hidden="true"></i>
      <span><?= $escaparCierre($formatearMes($periodo)) ?></span>
    </div>
  </section>

  <nav class="movimiento-subnav" aria-label="Submenú de movimientos">
    <a href="movimiento.php"><i class="fa-solid fa-list" aria-hidden="true"></i> Registro diario</a>
    <a href="cierre_mensual.php" class="active" aria-current="page"><i class="fa-solid fa-chart-pie" aria-hidden="true"></i> Cierre mensual</a>
    <a href="movimiento.php#importar-historico"><i class="fa-solid fa-file-import" aria-hidden="true"></i> Importar histórico</a>
  </nav>

  <?php if ($mensajeCierre) { ?>
  <div class="alert <?= isset($mensajeCierre['tipo']) && $mensajeCierre['tipo'] === 'error' ? 'alert-danger' : 'alert-success' ?>" role="status">
    <?= $escaparCierre(isset($mensajeCierre['texto']) ? $mensajeCierre['texto'] : '') ?>
  </div>
  <?php } ?>

  <section class="movimiento-panel cierre-selector">
    <form method="get" action="cierre_mensual.php">
      <div>
        <label for="cierre-mes">Periodo del cierre</label>
        <input id="cierre-mes" class="form-control" type="month" name="mes" value="<?= $escaparCierre($mes) ?>" required>
      </div>
      <button class="btn btn-outline-primary" type="submit"><i class="fa-solid fa-calendar-check" aria-hidden="true"></i> Consultar mes</button>
      <span><?= $cierre ? 'Este mes ya tiene un cierre guardado. Puedes actualizarlo.' : 'Completa y guarda el cierre de este mes.' ?></span>
    </form>
  </section>

  <div class="cierre-borrador" id="cierre-borrador-estado" role="status" aria-live="polite" hidden>
    <span></span>
    <button type="button" class="btn btn-sm btn-outline-secondary" id="cierre-descartar-borrador" hidden>Descartar borrador</button>
  </div>
  <form action="guardarCierreMensual.php" method="post" class="cierre-formulario"
    data-borrador-clave="<?= $escaparCierre(hash('sha256', strtolower(trim($_SESSION['usuario'])))) ?>-<?= $escaparCierre($mes) ?>"
    data-borrador-guardado="<?= $escaparCierre(isset($mensajeCierre['borrador_guardado']) ? $mensajeCierre['borrador_guardado'] : '') ?>">
    <input type="hidden" name="token_csrf" value="<?= $escaparCierre(token_csrf()) ?>">
    <input type="hidden" name="mes" value="<?= $escaparCierre($mes) ?>">
    <input type="hidden" name="borrador_revision" value="">

    <section class="movimiento-panel cierre-panel cierre-cuentas">
      <div class="cierre-panel-cabecera">
        <div>
          <span class="cierre-panel-icono"><i class="fa-solid fa-building-columns" aria-hidden="true"></i></span>
          <div>
            <h2>Cuentas bancarias</h2>
            <p>Captura el saldo y la fecha de cada cuenta. El total es independiente de los demás saldos.</p>
          </div>
        </div>
      </div>
      <div class="cierre-cuentas-grid">
        <?php foreach (cuentasCierreMensual() as $claveCuenta => $nombreCuenta) {
            $cuenta = isset($cuentasCierre[$claveCuenta]) ? $cuentasCierre[$claveCuenta] : array('saldo' => '', 'fecha' => '');
        ?>
        <div class="cierre-cuenta">
          <h3><?= $escaparCierre($nombreCuenta) ?></h3>
          <div class="cierre-cuenta-campos">
            <label for="cuenta-<?= $escaparCierre($claveCuenta) ?>-saldo">Saldo ($)</label>
            <input id="cuenta-<?= $escaparCierre($claveCuenta) ?>-saldo" class="form-control" type="number" step="0.01"
              name="cuentas[<?= $escaparCierre($claveCuenta) ?>][saldo]" value="<?= $escaparCierre($cuenta['saldo']) ?>" data-cuenta-saldo>
            <label for="cuenta-<?= $escaparCierre($claveCuenta) ?>-fecha">Fecha del saldo</label>
            <input id="cuenta-<?= $escaparCierre($claveCuenta) ?>-fecha" class="form-control" type="date"
              name="cuentas[<?= $escaparCierre($claveCuenta) ?>][fecha]" value="<?= $escaparCierre($cuenta['fecha']) ?>" data-cuenta-fecha>
          </div>
        </div>
        <?php } ?>
      </div>
      <div class="cierre-cuentas-total" aria-live="polite">
        <span>Total de cuentas</span>
        <strong data-saldo="cuentas"><?= $formatearImporte($totalCuentas) ?></strong>
      </div>
    </section>

    <?php foreach (array_keys(seccionesCierreMensual()) as $seccion) {
        $filas = isset($detallesCierre[$seccion]) && count($detallesCierre[$seccion]) > 0
            ? $detallesCierre[$seccion]
            : array_map(function ($concepto) {
                return array('concepto' => $concepto, 'importe' => '');
            }, $plantillas[$seccion]);
        $etiquetas = array(
            'local' => array('Local del mes', 'Rentas, servicios y referencia de ganancia o adeudo', 'fa-store'),
            'fondo' => array('Fondo actual', 'Desglose del efectivo disponible', 'fa-wallet'),
            'deuda' => array('Deuda actual', 'Detalle de compromisos pendientes', 'fa-file-invoice-dollar')
        );
        $totalSeccion = $sumarDetalles($detallesCierre, $seccion);
    ?>
    <section class="movimiento-panel cierre-panel" data-cierre-seccion="<?= $escaparCierre($seccion) ?>">
      <div class="cierre-panel-cabecera">
        <div>
          <span class="cierre-panel-icono"><i class="fa-solid <?= $escaparCierre($etiquetas[$seccion][2]) ?>" aria-hidden="true"></i></span>
          <div>
            <h2><?= $escaparCierre($etiquetas[$seccion][0]) ?></h2>
            <p><?= $escaparCierre($etiquetas[$seccion][1]) ?></p>
          </div>
        </div>
        <strong class="cierre-total" data-total="<?= $escaparCierre($seccion) ?>"><?= $formatearImporte($totalSeccion) ?></strong>
      </div>
      <div class="cierre-renglones" data-renglones="<?= $escaparCierre($seccion) ?>">
        <?php foreach ($filas as $fila) { ?>
        <div class="cierre-renglon">
          <label>
            <span>Concepto</span>
            <input class="form-control" type="text" name="items[<?= $escaparCierre($seccion) ?>][concepto][]" maxlength="80" value="<?= $escaparCierre($fila['concepto']) ?>" aria-label="Concepto de <?= $escaparCierre($etiquetas[$seccion][0]) ?>">
          </label>
          <label>
            <span>Importe</span>
            <div class="cierre-importe-input"><span>$</span><input class="form-control" type="number" name="items[<?= $escaparCierre($seccion) ?>][importe][]" step="0.01" value="<?= $escaparCierre($fila['importe']) ?>" aria-label="Importe de <?= $escaparCierre($fila['concepto']) ?>"></div>
          </label>
          <button class="cierre-quitar-renglon" type="button" aria-label="Quitar concepto"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        </div>
        <?php } ?>
      </div>
      <button class="cierre-agregar-renglon" type="button" data-agregar="<?= $escaparCierre($seccion) ?>"><i class="fa-solid fa-plus" aria-hidden="true"></i> Agregar concepto</button>
      <?php if ($seccion === 'local') { ?>
      <div class="cierre-referencias">
        <label for="cierre-local-adeudo">Adeudo / ganancia</label>
        <div class="cierre-importe-input"><span>$</span><input id="cierre-local-adeudo" class="form-control" type="number" name="local_adeudo_ganancia" step="0.01" value="<?= $escaparCierre($valoresCierre['local_adeudo_ganancia']) ?>" required></div>
        <label for="cierre-local-ganancia">Ganancia</label>
        <div class="cierre-importe-input"><span>$</span><input id="cierre-local-ganancia" class="form-control" type="number" name="local_ganancia" step="0.01" value="<?= $escaparCierre($valoresCierre['local_ganancia']) ?>" required></div>
      </div>
      <?php } ?>
    </section>
    <?php } ?>

    <section class="movimiento-panel cierre-panel cierre-inventario">
      <div class="cierre-panel-cabecera">
        <div>
          <span class="cierre-panel-icono"><i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i></span>
          <div>
            <h2>Inventario</h2>
            <p>El saldo se calcula restando la deuda al inventario actual.</p>
          </div>
        </div>
        <strong class="cierre-total" data-total="inventario"><?= $formatearImporte((float) $valoresCierre['inventario_actual'] - (float) $valoresCierre['inventario_deuda']) ?></strong>
      </div>
      <div class="cierre-inventario-campos">
        <label for="cierre-inventario-actual">Inventario actual
          <div class="cierre-importe-input"><span>$</span><input id="cierre-inventario-actual" class="form-control" type="number" name="inventario_actual" min="0" step="0.01" value="<?= $escaparCierre($valoresCierre['inventario_actual']) ?>" required data-calculo="inventario-actual"></div>
        </label>
        <label for="cierre-inventario-deuda">Deuda
          <div class="cierre-importe-input"><span>$</span><input id="cierre-inventario-deuda" class="form-control" type="number" name="inventario_deuda" min="0" step="0.01" value="<?= $escaparCierre($valoresCierre['inventario_deuda']) ?>" required data-calculo="inventario-deuda"></div>
        </label>
        <div class="cierre-saldo" aria-live="polite">
          <span>Saldo neto</span>
          <strong data-saldo="inventario"><?= $formatearImporte((float) $valoresCierre['inventario_actual'] - (float) $valoresCierre['inventario_deuda']) ?></strong>
        </div>
      </div>
    </section>

    <div class="cierre-acciones">
      <span><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> El registro se guarda por mes y puedes editarlo cuando lo necesites.</span>
      <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> <?= $cierre ? 'Actualizar cierre' : 'Guardar cierre' ?></button>
    </div>
  </form>

  <section class="movimiento-panel cierre-historial">
    <div class="movimiento-panel-titulo">
      <div>
        <h2>Historial de cierres</h2>
        <p>Consulta los últimos 24 periodos registrados.</p>
      </div>
      <span class="movimiento-contador"><?= count($cierres) ?> periodos</span>
    </div>
    <?php if (count($cierres) > 0) { ?>
    <div class="table-responsive">
      <table class="table cierre-tabla">
        <thead>
          <tr><th>Periodo</th><th>Fondo</th><th>Deuda</th><th>Inventario neto</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($cierres as $filaCierre) {
              $mesCierre = substr($filaCierre['periodo'], 0, 7);
              $saldoInventario = (float) $filaCierre['inventario_actual'] - (float) $filaCierre['inventario_deuda'];
          ?>
          <tr>
            <td><?= $escaparCierre($formatearMes($filaCierre['periodo'])) ?></td>
            <td><?= $formatearImporte($filaCierre['total_fondo']) ?></td>
            <td><?= $formatearImporte($filaCierre['total_deuda']) ?></td>
            <td><?= $formatearImporte($saldoInventario) ?></td>
            <td><a class="btn btn-sm btn-outline-primary" href="cierre_mensual.php?mes=<?= $escaparCierre($mesCierre) ?>">Abrir</a></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
    <?php } else { ?>
    <p class="cierre-sin-historial">Aún no hay cierres guardados. El primero aparecerá aquí.</p>
    <?php } ?>
  </section>
</main>

<?php include_once __DIR__ . '/footer.php'; ?>
