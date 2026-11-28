<?php
date_default_timezone_set('America/Mexico_City');
mysqli_report(MYSQLI_REPORT_OFF);
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/movimientos_db.php';

if (!$conn) {
    error_log('No fue posible conectar con la base de datos de recargas.');
    http_response_code(500);
    exit('No se pudieron cargar las recargas.');
}
try {
    prepararTablaMovimientos($conn);
} catch (RuntimeException $error) {
    error_log($error->getMessage());
    http_response_code(500);
    exit('No se pudo preparar el registro de recargas.');
}

$hoy = new DateTimeImmutable('today');
$desde = isset($_GET['desde']) && is_string($_GET['desde']) ? $_GET['desde'] : $hoy->modify('first day of this month')->format('Y-m-d');
$hasta = isset($_GET['hasta']) && is_string($_GET['hasta']) ? $_GET['hasta'] : $hoy->format('Y-m-d');
$fechaDesde = DateTimeImmutable::createFromFormat('!Y-m-d', $desde);
$erroresDesde = DateTimeImmutable::getLastErrors();
$fechaHasta = DateTimeImmutable::createFromFormat('!Y-m-d', $hasta);
$erroresHasta = DateTimeImmutable::getLastErrors();
if ($fechaDesde === false || $fechaHasta === false ||
    ($erroresDesde !== false && ($erroresDesde['warning_count'] || $erroresDesde['error_count'])) ||
    ($erroresHasta !== false && ($erroresHasta['warning_count'] || $erroresHasta['error_count'])) ||
    $fechaDesde->format('Y-m-d') !== $desde || $fechaHasta->format('Y-m-d') !== $hasta || $desde > $hasta) {
    http_response_code(400);
    exit('El rango de fechas no es válido.');
}

$inicio = $desde . ' 00:00:00';
$fin = $fechaHasta->modify('+1 day')->format('Y-m-d') . ' 00:00:00';
$origenes = "SELECT m.fecha_movimiento AS fecha, m.tipo, m.concepto, m.cantidad, m.importe,
                    CASE WHEN m.fuente = 'excel' THEN 'Excel' ELSE 'Manual' END AS origen, m.fecha_estimada
             FROM movimientos m
             WHERE m.categoria = 'Recargas' AND m.fecha_movimiento >= ? AND m.fecha_movimiento < ?
             UNION ALL
             SELECT v.fecha_venta, CASE WHEN v.costo < 0 THEN 'gasto' ELSE 'ingreso' END,
                    v.descripcion, COALESCE(v.cantidad, 0), ABS(v.costo), 'Venta', 0
             FROM ventas v INNER JOIN seccion s ON v.seccion = s.id_seccion
             WHERE s.descripcionSeccion = 'Recargas' AND v.fecha_venta >= ? AND v.fecha_venta < ?";

$consulta = mysqli_prepare($conn, "SELECT
    COALESCE(SUM(CASE WHEN tipo = 'ingreso' THEN importe ELSE 0 END), 0),
    COALESCE(SUM(CASE WHEN tipo = 'gasto' THEN importe ELSE 0 END), 0), COUNT(*)
    FROM ($origenes) AS recargas");
if (!$consulta || !mysqli_stmt_bind_param($consulta, 'ssss', $inicio, $fin, $inicio, $fin) ||
    !mysqli_stmt_execute($consulta) || !mysqli_stmt_bind_result($consulta, $ventas, $compras, $cantidadOperaciones) ||
    !mysqli_stmt_fetch($consulta)) {
    $error = $consulta ? mysqli_stmt_error($consulta) : mysqli_error($conn);
    error_log('No fue posible consultar el resumen de recargas: ' . $error);
    if ($consulta) mysqli_stmt_close($consulta);
    http_response_code(500);
    exit('No se pudo consultar el resumen de recargas.');
}
mysqli_stmt_close($consulta);

$consulta = mysqli_prepare($conn, "SELECT fecha, tipo, concepto, cantidad, importe, origen, fecha_estimada
    FROM ($origenes) AS recargas ORDER BY fecha DESC, concepto LIMIT 500");
if (!$consulta || !mysqli_stmt_bind_param($consulta, 'ssss', $inicio, $fin, $inicio, $fin) ||
    !mysqli_stmt_execute($consulta) ||
    !mysqli_stmt_bind_result($consulta, $fecha, $tipo, $concepto, $cantidad, $importe, $origen, $fechaEstimada)) {
    $error = $consulta ? mysqli_stmt_error($consulta) : mysqli_error($conn);
    error_log('No fue posible consultar el detalle de recargas: ' . $error);
    if ($consulta) mysqli_stmt_close($consulta);
    http_response_code(500);
    exit('No se pudo consultar el detalle de recargas.');
}
$operaciones = array();
while (mysqli_stmt_fetch($consulta)) {
    $operaciones[] = array(
        'fecha' => $fecha, 'tipo' => $tipo, 'concepto' => $concepto,
        'cantidad' => $cantidad, 'importe' => $importe, 'origen' => $origen,
        'fecha_estimada' => (int) $fechaEstimada
    );
}
if (mysqli_stmt_errno($consulta) !== 0) {
    error_log('No fue posible leer el detalle de recargas: ' . mysqli_stmt_error($consulta));
    mysqli_stmt_close($consulta);
    http_response_code(500);
    exit('No se pudo leer el detalle de recargas.');
}
mysqli_stmt_close($consulta);

$mensaje = isset($_SESSION['recarga_mensaje']) && is_array($_SESSION['recarga_mensaje']) ? $_SESSION['recarga_mensaje'] : null;
unset($_SESSION['recarga_mensaje']);
$escapar = function ($valor) { return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8'); };
$dinero = function ($valor) { return '$' . number_format((float) $valor, 2, '.', ','); };
include_once __DIR__ . '/encabezado.php';
?>
<main class="movimiento-page">
  <section class="movimiento-hero">
    <div>
      <span class="movimiento-eyebrow">Control de operaciones</span>
      <h1>Recargas</h1>
      <p>Registra las ventas a clientes y las compras de saldo para recargas.</p>
    </div>
    <a class="btn btn-primary" href="#registrar-recarga"><i class="fa-solid fa-plus" aria-hidden="true"></i> Registrar operación</a>
  </section>

  <?php if ($mensaje) { ?>
  <div class="alert <?= isset($mensaje['tipo']) && $mensaje['tipo'] === 'error' ? 'alert-danger' : 'alert-success' ?>" role="status"><?= $escapar(isset($mensaje['texto']) ? $mensaje['texto'] : '') ?></div>
  <?php } ?>

  <section class="movimiento-resumen" aria-label="Resumen de recargas">
    <article class="movimiento-resumen-card movimiento-recargas">
      <span><i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i> Recargas vendidas</span>
      <strong><?= $dinero($ventas) ?></strong>
      <small>Separadas de los ingresos generales</small>
    </article>
    <article class="movimiento-resumen-card movimiento-ganancia">
      <span><i class="fa-solid fa-sack-dollar" aria-hidden="true"></i> Ganancia de recargas</span>
      <strong><?= $dinero((float) $ventas * 0.06) ?></strong>
      <small>6% de las recargas vendidas en el periodo</small>
    </article>
    <article class="movimiento-resumen-card movimiento-egresos">
      <span><i class="fa-solid fa-cart-shopping" aria-hidden="true"></i> Compras y salidas</span>
      <strong><?= $dinero($compras) ?></strong>
      <small>Incluidas en los gastos</small>
    </article>
    <article class="movimiento-resumen-card movimiento-ganancia">
      <span><i class="fa-solid fa-list" aria-hidden="true"></i> Operaciones</span>
      <strong><?= number_format((int) $cantidadOperaciones) ?></strong>
      <small>En el periodo seleccionado</small>
    </article>
  </section>

  <section class="movimiento-panel movimiento-filtros">
    <div class="movimiento-panel-titulo"><div><h2>Consultar periodo</h2><p>El resumen y el detalle utilizan las mismas fechas.</p></div></div>
    <form method="get" action="recargas.php" class="movimiento-formulario-filtros recargas-filtros">
      <div><label for="recargas-desde">Desde</label><input id="recargas-desde" class="form-control" type="date" name="desde" value="<?= $escapar($desde) ?>" required></div>
      <div><label for="recargas-hasta">Hasta</label><input id="recargas-hasta" class="form-control" type="date" name="hasta" value="<?= $escapar($hasta) ?>" required></div>
      <button class="btn btn-primary" type="submit"><i class="fa-solid fa-filter" aria-hidden="true"></i> Consultar</button>
    </form>
  </section>

  <section id="registrar-recarga" class="movimiento-panel">
    <div class="movimiento-panel-titulo">
      <div><h2>Registrar operación</h2><p>Las ventas se muestran por separado y las compras de saldo se incluyen en los gastos del registro diario.</p></div>
    </div>
    <form action="guardarMovimiento.php" method="post" class="movimiento-formulario recargas-formulario">
      <input type="hidden" name="token_csrf" value="<?= $escapar(token_csrf()) ?>">
      <input type="hidden" name="origen_formulario" value="recargas">
      <input type="hidden" name="categoria" value="Recargas">
      <div><label for="recarga-fecha">Fecha</label><input id="recarga-fecha" class="form-control" type="date" name="fecha" value="<?= $escapar($hoy->format('Y-m-d')) ?>" required></div>
      <div><label for="recarga-tipo">Operación</label><select id="recarga-tipo" class="form-control" name="tipo" required><option value="ingreso">Venta a cliente</option><option value="gasto">Compra de saldo</option></select></div>
      <div class="movimiento-formulario-concepto"><label for="recarga-concepto">Concepto</label><input id="recarga-concepto" class="form-control" type="text" name="concepto" maxlength="150" required></div>
      <div><label for="recarga-cantidad">Cantidad <span>(opcional)</span></label><input id="recarga-cantidad" class="form-control" type="number" name="cantidad" min="0" step="0.01" value="0"></div>
      <div><label for="recarga-importe">Importe ($)</label><input id="recarga-importe" class="form-control" type="number" name="importe" min="0.01" step="0.01" required></div>
      <div class="movimiento-formulario-accion"><button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Guardar</button></div>
    </form>
  </section>

  <section class="movimiento-panel movimiento-tabla-panel">
    <div class="movimiento-panel-titulo"><div><h2>Detalle de recargas</h2><p>Incluye operaciones manuales, importadas y registradas en ventas.</p></div><span class="movimiento-contador"><?= count($operaciones) ?> registros</span></div>
    <div class="table-responsive">
      <table class="table movimiento-tabla">
        <thead><tr><th>Fecha</th><th>Operación</th><th>Concepto</th><th>Cantidad</th><th>Importe</th><th>Origen</th></tr></thead>
        <tbody>
          <?php foreach ($operaciones as $operacion) { ?>
          <tr>
            <td><?= $escapar(date('d/m/Y', strtotime($operacion['fecha']))) ?><?php if ($operacion['fecha_estimada']) { ?> <small class="movimiento-fecha-estimada">Fecha estimada</small><?php } ?></td>
            <td><?= $operacion['tipo'] === 'gasto' ? 'Compra / salida' : 'Venta a cliente' ?></td>
            <td><?= $escapar($operacion['concepto']) ?></td>
            <td><?= number_format((float) $operacion['cantidad'], 2, '.', ',') ?></td>
            <td><?= $dinero($operacion['importe']) ?></td>
            <td><?= $escapar($operacion['origen']) ?></td>
          </tr>
          <?php } ?>
          <?php if (!$operaciones) { ?><tr><td colspan="6">No hay operaciones en este periodo.</td></tr><?php } ?>
        </tbody>
      </table>
    </div>
    <?php if ($cantidadOperaciones > 500) { ?><p class="movimiento-limite">Se muestran las 500 operaciones más recientes. Ajusta el rango para consultar un periodo menor.</p><?php } ?>
  </section>
</main>
<?php include_once __DIR__ . '/footer.php'; ?>
