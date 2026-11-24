<?php
date_default_timezone_set('America/Mexico_City');
mysqli_report(MYSQLI_REPORT_OFF);
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/movimientos_db.php';

if (!$conn) {
    error_log('No fue posible conectar con la base de datos para cargar los movimientos.');
    http_response_code(500);
    exit('No se pudo cargar el registro de movimientos.');
}

try {
    prepararTablaMovimientos($conn);
} catch (RuntimeException $error) {
    error_log($error->getMessage());
    http_response_code(500);
    exit('No se pudo preparar el registro de movimientos.');
}

$hoy = new DateTimeImmutable('today');
$inicioMes = $hoy->modify('first day of this month')->format('Y-m-d');
$desde = isset($_GET['desde']) && is_string($_GET['desde']) ? $_GET['desde'] : $inicioMes;
$hasta = isset($_GET['hasta']) && is_string($_GET['hasta']) ? $_GET['hasta'] : $hoy->format('Y-m-d');
$fechaDesdeValidada = DateTimeImmutable::createFromFormat('!Y-m-d', $desde);
$fechaHastaValidada = DateTimeImmutable::createFromFormat('!Y-m-d', $hasta);
$erroresDesde = DateTimeImmutable::getLastErrors();
$erroresHasta = DateTimeImmutable::getLastErrors();
if ($fechaDesdeValidada === false || $fechaHastaValidada === false ||
    ($erroresDesde !== false && ($erroresDesde['warning_count'] || $erroresDesde['error_count'])) ||
    ($erroresHasta !== false && ($erroresHasta['warning_count'] || $erroresHasta['error_count'])) ||
    $fechaDesdeValidada->format('Y-m-d') !== $desde || $fechaHastaValidada->format('Y-m-d') !== $hasta ||
    $desde > $hasta) {
    http_response_code(400);
    exit('El rango de fechas no es válido.');
}

$categoriaSeleccionada = isset($_GET['categoria']) && is_string($_GET['categoria']) ? trim($_GET['categoria']) : '';
$categorias = array('Impresiones', 'Servicios', 'Recargas', 'Gastos', 'Otros ingresos');
$resultadoCategorias = mysqli_query($conn, 'SELECT descripcionSeccion FROM seccion ORDER BY descripcionSeccion');
if (!$resultadoCategorias) {
    error_log('No fue posible consultar las categorías de ventas: ' . mysqli_error($conn));
    http_response_code(500);
    exit('No se pudieron cargar las categorías de movimientos.');
}
while ($categoria = mysqli_fetch_assoc($resultadoCategorias)) {
    if (!in_array($categoria['descripcionSeccion'], $categorias, true)) {
        $categorias[] = $categoria['descripcionSeccion'];
    }
}
if ($categoriaSeleccionada !== '' && !in_array($categoriaSeleccionada, $categorias, true)) {
    http_response_code(400);
    exit('La categoría seleccionada no es válida.');
}

$inicioPeriodo = $desde . ' 00:00:00';
$finPeriodo = (new DateTimeImmutable($hasta))->modify('+1 day')->format('Y-m-d') . ' 00:00:00';
$registrosMovimiento = "
    SELECT v.fecha_venta AS fecha, CASE WHEN v.costo < 0 THEN 'gasto' ELSE 'ingreso' END AS tipo,
           COALESCE(s.descripcionSeccion, 'Ventas') AS categoria, v.descripcion AS concepto,
           COALESCE(v.cantidad, 0) AS cantidad, ABS(v.costo) AS importe, COALESCE(v.ganancia, 0) AS ganancia,
           'Venta' AS origen, 0 AS fecha_estimada, 0 AS es_conteo, 0 AS nota_incompleta,
           0 AS servicio_importado_reciente
    FROM ventas v LEFT JOIN seccion s ON v.seccion = s.id_seccion
    UNION ALL
    SELECT i.fecha_impresion, CASE WHEN i.precio < 0 THEN 'gasto' ELSE 'ingreso' END,
           'Impresiones', i.concepto, COALESCE(i.cant, 0), ABS(i.precio), 0, 'Impresión', 0, 1, 0, 0
    FROM impresion i
    UNION ALL
    SELECT m.fecha_movimiento, m.tipo, m.categoria, m.concepto, m.cantidad, m.importe, m.ganancia,
           CASE WHEN m.fuente = 'excel' THEN 'Excel' ELSE 'Manual' END, m.fecha_estimada,
           CASE WHEN m.categoria = 'Impresiones' AND m.concepto = 'Impresiones del día' THEN 1 ELSE 0 END, 0,
           CASE WHEN m.fuente = 'excel' AND m.categoria = 'Servicios' AND m.fecha_movimiento >= '2026-11-01' THEN 1 ELSE 0 END
    FROM movimientos m
    UNION ALL
    SELECT n.fecha_alta, CASE WHEN n.costoServicio < 0 THEN 'gasto' ELSE 'ingreso' END,
           'Servicios', CONCAT('Nota #', n.id_nota, ' - ', COALESCE(NULLIF(TRIM(n.descripcionServicio), ''), 'Servicio')),
           1, ABS(COALESCE(n.costoServicio, 0)), COALESCE(n.ganancia, 0), 'Nota de servicio', 0, 0,
           CASE WHEN n.costoServicio IS NULL OR n.ganancia IS NULL THEN 1 ELSE 0 END, 0
    FROM notas n
    WHERE n.fecha_alta >= '2026-11-01 00:00:00'";
$consultaResumen = mysqli_prepare(
    $conn,
    "SELECT
        COALESCE(SUM(CASE WHEN resumen.tipo = 'ingreso' AND resumen.categoria <> 'Recargas' THEN resumen.importe ELSE 0 END), 0) AS ingresos,
        COALESCE(SUM(CASE WHEN resumen.tipo = 'ingreso' AND resumen.categoria = 'Recargas' THEN resumen.importe ELSE 0 END), 0) AS recargas,
        COALESCE(SUM(CASE WHEN resumen.tipo = 'gasto' THEN resumen.importe ELSE 0 END), 0) AS gastos,
        COALESCE(SUM(resumen.ganancia), 0) AS ganancia,
        COALESCE(SUM(CASE WHEN resumen.es_conteo = 1 THEN resumen.cantidad ELSE 0 END), 0) AS impresiones,
        COALESCE(SUM(resumen.nota_incompleta), 0),
        COALESCE(SUM(resumen.servicio_importado_reciente), 0)
     FROM ($registrosMovimiento) AS resumen
     WHERE resumen.fecha >= ? AND resumen.fecha < ?
       AND (? = '' OR resumen.categoria = ?)"
);
if (!$consultaResumen || !mysqli_stmt_bind_param($consultaResumen, 'ssss', $inicioPeriodo, $finPeriodo, $categoriaSeleccionada, $categoriaSeleccionada) ||
    !mysqli_stmt_execute($consultaResumen) ||
    !mysqli_stmt_bind_result($consultaResumen, $ingresos, $recargas, $gastos, $ganancia, $impresiones, $notasIncompletas, $serviciosImportadosRecientes)) {
    $error = $consultaResumen ? mysqli_stmt_error($consultaResumen) : mysqli_error($conn);
    error_log('No fue posible preparar el resumen de movimientos: ' . $error);
    http_response_code(500);
    exit('No se pudo calcular el resumen de movimientos.');
}
if (!mysqli_stmt_fetch($consultaResumen)) {
    error_log('No fue posible leer el resumen de movimientos: ' . mysqli_stmt_error($consultaResumen));
    mysqli_stmt_close($consultaResumen);
    http_response_code(500);
    exit('No se pudo leer el resumen de movimientos.');
}
mysqli_stmt_close($consultaResumen);

$consultaMovimientos = mysqli_prepare(
    $conn,
    "    SELECT registro.fecha, registro.tipo, registro.categoria, registro.concepto, registro.cantidad,
           registro.importe, registro.ganancia, registro.origen, registro.fecha_estimada
     FROM ($registrosMovimiento) AS registro
     WHERE registro.fecha >= ? AND registro.fecha < ?
       AND (? = '' OR registro.categoria = ?)
     ORDER BY registro.fecha DESC, registro.categoria, registro.concepto
     LIMIT 500"
);
if (!$consultaMovimientos ||
    !mysqli_stmt_bind_param($consultaMovimientos, 'ssss', $inicioPeriodo, $finPeriodo, $categoriaSeleccionada, $categoriaSeleccionada) ||
    !mysqli_stmt_execute($consultaMovimientos) ||
    !mysqli_stmt_bind_result(
        $consultaMovimientos,
        $fechaMovimiento,
        $tipoMovimiento,
        $categoriaMovimiento,
        $conceptoMovimiento,
        $cantidadMovimiento,
        $importeMovimiento,
        $gananciaMovimiento,
        $origenMovimiento,
        $fechaEstimadaMovimiento
    )) {
    $error = $consultaMovimientos ? mysqli_stmt_error($consultaMovimientos) : mysqli_error($conn);
    error_log('No fue posible consultar el detalle de movimientos: ' . $error);
    http_response_code(500);
    exit('No se pudo cargar el detalle de movimientos.');
}

$movimientos = array();
while (mysqli_stmt_fetch($consultaMovimientos)) {
    $movimientos[] = array(
        'fecha' => $fechaMovimiento,
        'tipo' => $tipoMovimiento,
        'categoria' => $categoriaMovimiento,
        'concepto' => $conceptoMovimiento,
        'cantidad' => $cantidadMovimiento,
        'importe' => $importeMovimiento,
        'ganancia' => $gananciaMovimiento,
        'origen' => $origenMovimiento,
        'fecha_estimada' => (int) $fechaEstimadaMovimiento
    );
}
if (mysqli_stmt_errno($consultaMovimientos) !== 0) {
    error_log('Ocurrió un error al leer los movimientos: ' . mysqli_stmt_error($consultaMovimientos));
    mysqli_stmt_close($consultaMovimientos);
    http_response_code(500);
    exit('No se pudo leer el detalle de movimientos.');
}
mysqli_stmt_close($consultaMovimientos);

$mensajeMovimiento = isset($_SESSION['movimiento_mensaje']) && is_array($_SESSION['movimiento_mensaje'])
    ? $_SESSION['movimiento_mensaje']
    : null;
unset($_SESSION['movimiento_mensaje']);
$escaparMovimiento = function ($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
};

include_once __DIR__ . '/encabezado.php';
?>

<main class="movimiento-page">
  <section class="movimiento-hero">
    <div>
      <span class="movimiento-eyebrow">Control diario</span>
      <h1>Movimiento</h1>
      <p>Consulta ingresos, gastos, ventas, servicios e impresiones en un solo registro.</p>
    </div>
    <div class="movimiento-hero-acciones">
      <div class="movimiento-periodo">
        <i class="fa-regular fa-calendar" aria-hidden="true"></i>
        <span><?= $escaparMovimiento(date('d/m/Y', strtotime($desde))) ?> — <?= $escaparMovimiento(date('d/m/Y', strtotime($hasta))) ?></span>
      </div>
      <a class="btn btn-primary" href="#nuevo-movimiento"><i class="fa-solid fa-plus" aria-hidden="true"></i> Registrar movimiento</a>
    </div>
  </section>

  <nav class="movimiento-subnav" aria-label="Submenú de movimientos">
    <a href="movimiento.php" class="active" aria-current="page"><i class="fa-solid fa-list" aria-hidden="true"></i> Registro diario</a>
    <a href="cierre_mensual.php"><i class="fa-solid fa-chart-pie" aria-hidden="true"></i> Cierre mensual</a>
    <a href="#importar-historico"><i class="fa-solid fa-file-import" aria-hidden="true"></i> Importar histórico</a>
  </nav>

  <?php if ($mensajeMovimiento) { ?>
  <div class="alert <?= $mensajeMovimiento['tipo'] === 'error' ? 'alert-danger' : 'alert-success' ?>" role="status">
    <?= $escaparMovimiento(isset($mensajeMovimiento['texto']) ? $mensajeMovimiento['texto'] : '') ?>
  </div>
  <?php } ?>

  <section id="resumen-movimientos" class="movimiento-resumen movimiento-resumen-diario" aria-label="Resumen del periodo">
    <article class="movimiento-resumen-card movimiento-ingresos">
      <span><i class="fa-solid fa-arrow-trend-up" aria-hidden="true"></i> Ingresos</span>
      <strong>$<?= number_format((float) $ingresos, 2, '.', ',') ?></strong>
      <small>Ventas, servicios e impresiones, sin recargas</small>
    </article>
    <article class="movimiento-resumen-card movimiento-recargas">
      <span><i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i> Recargas vendidas</span>
      <strong>$<?= number_format((float) $recargas, 2, '.', ',') ?></strong>
      <small>Importe separado de ingresos</small>
    </article>
    <article class="movimiento-resumen-card movimiento-egresos">
      <span><i class="fa-solid fa-arrow-trend-down" aria-hidden="true"></i> Gastos</span>
      <strong>$<?= number_format((float) $gastos, 2, '.', ',') ?></strong>
      <small>Salidas y ajustes registrados</small>
    </article>
    <article class="movimiento-resumen-card movimiento-ganancia">
      <span><i class="fa-solid fa-sack-dollar" aria-hidden="true"></i> Ganancia registrada</span>
      <strong>$<?= number_format((float) $ganancia, 2, '.', ',') ?></strong>
      <small>Ganancia capturada en ventas, notas y Excel</small>
    </article>
    <article class="movimiento-resumen-card movimiento-impresiones">
      <span><i class="fa-solid fa-print" aria-hidden="true"></i> Impresiones</span>
      <strong><?= number_format((float) $impresiones, 0, '.', ',') ?></strong>
      <small>Unidades en el periodo</small>
    </article>
  </section>
  <p class="movimiento-notas-aviso">Las notas de servicio se incluyen desde el 01/11/2026 por su fecha de entrada y costo total registrado; este importe no necesariamente ha sido cobrado.</p>
  <?php if ((int) $serviciosImportadosRecientes > 0) { ?>
  <p class="alert alert-warning" role="status">El periodo incluye <?= (int) $serviciosImportadosRecientes ?> servicios importados desde el 01/11/2026. Si también están registrados como notas, pueden duplicar ingresos.</p>
  <?php } ?>
  <?php if ((int) $notasIncompletas > 0) { ?>
  <p class="alert alert-warning" role="status"><?= (int) $notasIncompletas ?> notas tienen costo o ganancia sin registrar; los importes faltantes no se suman.</p>
  <?php } ?>

  <section id="filtros-movimientos" class="movimiento-panel movimiento-filtros">
    <div class="movimiento-panel-titulo">
      <div>
        <h2>Filtrar movimientos</h2>
        <p>El resumen y el detalle se actualizan con el mismo rango.</p>
      </div>
    </div>
    <form method="get" action="movimiento.php" class="movimiento-formulario-filtros">
      <div>
        <label for="movimiento-desde">Desde</label>
        <input id="movimiento-desde" class="form-control" type="date" name="desde" value="<?= $escaparMovimiento($desde) ?>" required>
      </div>
      <div>
        <label for="movimiento-hasta">Hasta</label>
        <input id="movimiento-hasta" class="form-control" type="date" name="hasta" value="<?= $escaparMovimiento($hasta) ?>" required>
      </div>
      <div>
        <label for="movimiento-categoria">Categoría</label>
        <select id="movimiento-categoria" class="form-control" name="categoria">
          <option value="">Todas las categorías</option>
          <?php foreach ($categorias as $categoria) { ?>
          <option value="<?= $escaparMovimiento($categoria) ?>" <?= $categoriaSeleccionada === $categoria ? 'selected' : '' ?>><?= $escaparMovimiento($categoria) ?></option>
          <?php } ?>
        </select>
      </div>
      <button class="btn btn-primary" type="submit"><i class="fa-solid fa-filter" aria-hidden="true"></i> Aplicar</button>
    </form>
  </section>

  <section id="nuevo-movimiento" class="movimiento-panel">
    <div class="movimiento-panel-titulo">
      <div>
        <h2>Registrar ingreso o gasto</h2>
        <p>Las ventas, notas de servicio e impresiones se consultan directamente de sus módulos. Registra las recargas en <a href="recargas.php">Recargas</a>.</p>
      </div>
    </div>
    <form action="guardarMovimiento.php" method="post" class="movimiento-formulario">
      <input type="hidden" name="token_csrf" value="<?= $escaparMovimiento(token_csrf()) ?>">
      <div>
        <label for="nuevo-movimiento-fecha">Fecha</label>
        <input id="nuevo-movimiento-fecha" class="form-control" type="date" name="fecha" value="<?= $escaparMovimiento(date('Y-m-d')) ?>" required>
      </div>
      <div>
        <label for="nuevo-movimiento-tipo">Tipo</label>
        <select id="nuevo-movimiento-tipo" class="form-control" name="tipo" required>
          <option value="ingreso">Ingreso</option>
          <option value="gasto">Gasto</option>
        </select>
      </div>
      <div>
        <label for="nuevo-movimiento-categoria">Categoría</label>
        <select id="nuevo-movimiento-categoria" class="form-control" name="categoria" required>
          <option value="Otros ingresos">Otros ingresos</option>
          <option value="Gastos">Gastos</option>
        </select>
      </div>
      <div class="movimiento-formulario-concepto">
        <label for="nuevo-movimiento-concepto">Concepto</label>
        <input id="nuevo-movimiento-concepto" class="form-control" type="text" name="concepto" maxlength="150" required>
      </div>
      <div>
        <label for="nuevo-movimiento-cantidad">Cantidad <span>(opcional)</span></label>
        <input id="nuevo-movimiento-cantidad" class="form-control" type="number" name="cantidad" min="0" step="0.01" value="0">
      </div>
      <div>
        <label for="nuevo-movimiento-importe">Importe</label>
        <div class="movimiento-importe-input">
          <span>$</span>
          <input id="nuevo-movimiento-importe" class="form-control" type="number" name="importe" min="0.01" step="0.01" required>
        </div>
      </div>
      <div class="movimiento-formulario-accion">
        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-plus" aria-hidden="true"></i> Guardar movimiento</button>
      </div>
    </form>
  </section>

  <section id="importar-historico" class="movimiento-panel movimiento-importar">
    <div class="movimiento-panel-titulo">
      <div>
        <h2>Importar histórico del Excel</h2>
        <p>Importa las hojas mensuales de movimientos. No se modifican las ventas ni las impresiones existentes.</p>
      </div>
      <span class="movimiento-importar-icono"><i class="fa-solid fa-file-import" aria-hidden="true"></i></span>
    </div>
    <form action="importarMovimiento.php" method="post" enctype="multipart/form-data" class="movimiento-formulario-importar" onsubmit="return confirm('Se importarán los movimientos de las hojas mensuales. Los registros duplicados se omitirán. ¿Continuar?');">
      <input type="hidden" name="token_csrf" value="<?= $escaparMovimiento(token_csrf()) ?>">
      <input class="form-control" type="file" name="archivo_excel" accept=".xlsx" required aria-label="Seleccionar libro Excel">
      <button class="btn btn-outline-primary" type="submit"><i class="fa-solid fa-upload" aria-hidden="true"></i> Importar XLSX</button>
      <small>Solo archivos .xlsx de hasta 10 MB. La importación es idempotente.</small>
    </form>
  </section>

  <section id="detalle-movimientos" class="movimiento-panel movimiento-tabla-panel">
    <div class="movimiento-panel-titulo">
      <div>
        <h2>Detalle de movimientos</h2>
        <p>Ventas, notas de servicio e impresiones provienen de sus tablas actuales; los registros históricos y manuales se guardan en la base de datos.</p>
      </div>
      <span class="movimiento-contador"><?= count($movimientos) ?> registros</span>
    </div>
    <div class="table-responsive">
      <table id="tablaventas" class="display responsive nowrap movimiento-tabla" style="width: 100%">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Tipo</th>
            <th>Categoría</th>
            <th>Concepto</th>
            <th>Cantidad</th>
            <th>Importe</th>
            <th>Ganancia</th>
            <th>Origen</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($movimientos as $movimiento) { ?>
          <tr>
            <td>
              <?= $escaparMovimiento(date('d/m/Y', strtotime($movimiento['fecha']))) ?>
              <?php if ($movimiento['fecha_estimada']) { ?><small class="movimiento-fecha-estimada">Fecha estimada</small><?php } ?>
            </td>
            <td><span class="movimiento-tipo movimiento-tipo-<?= $escaparMovimiento($movimiento['tipo']) ?>"><?= $movimiento['tipo'] === 'gasto' ? 'Gasto' : 'Ingreso' ?></span></td>
            <td><?= $escaparMovimiento($movimiento['categoria']) ?></td>
            <td><?= $escaparMovimiento($movimiento['concepto']) ?></td>
            <td><?= number_format((float) $movimiento['cantidad'], 2, '.', ',') ?></td>
            <td>$<?= number_format((float) $movimiento['importe'], 2, '.', ',') ?></td>
            <td>$<?= number_format((float) $movimiento['ganancia'], 2, '.', ',') ?></td>
            <td><?= $escaparMovimiento($movimiento['origen']) ?></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
    <?php if (count($movimientos) === 500) { ?>
    <p class="movimiento-limite">Se muestran los 500 movimientos más recientes del filtro. Ajusta el rango para consultar un periodo menor.</p>
    <?php } ?>
  </section>
</main>

<?php include_once __DIR__ . '/footer.php'; ?>
