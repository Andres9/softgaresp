<?php
mysqli_report(MYSQLI_REPORT_OFF);
include_once 'encabezado.php';

if (!$conn) {
  error_log('No fue posible conectar con la base de datos para generar los gráficos de ventas.');
  http_response_code(500);
  exit('No se pudieron cargar los gráficos de ventas.');
}

$inicioSemana = (new DateTimeImmutable('today'))->modify('monday this week');
$finSemana = $inicioSemana->modify('+7 days');
$fechaInicioSemana = $inicioSemana->format('Y-m-d H:i:s');
$fechaFinSemana = $finSemana->format('Y-m-d H:i:s');
$nombresDias = array('Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom');
$ventasPorDia = array();

for ($indiceDia = 0; $indiceDia < 7; $indiceDia++) {
  $fechaDia = $inicioSemana->modify('+' . $indiceDia . ' days');
  $claveFecha = $fechaDia->format('Y-m-d');
  $ventasPorDia[$claveFecha] = array(
    'nombre' => $nombresDias[$indiceDia],
    'fecha' => $fechaDia->format('d/m'),
    'cantidad' => 0,
    'total' => 0.0
  );
}

$totalSemanal = 0.0;
$cantidadSemanal = 0;
$consultaSemana = mysqli_prepare(
  $conn,
  'SELECT DATE(fecha_venta) AS dia, COUNT(*) AS cantidad, COALESCE(SUM(costo), 0) AS total
   FROM ventas
   WHERE fecha_venta >= ? AND fecha_venta < ?
   GROUP BY DATE(fecha_venta)
   ORDER BY dia'
);

if ($consultaSemana === false) {
  error_log('No fue posible preparar el resumen semanal de ventas: ' . mysqli_error($conn));
  http_response_code(500);
  exit('No se pudo cargar el gráfico semanal de ventas.');
}

if (
  !mysqli_stmt_bind_param($consultaSemana, 'ss', $fechaInicioSemana, $fechaFinSemana) ||
  !mysqli_stmt_execute($consultaSemana) ||
  !mysqli_stmt_bind_result($consultaSemana, $dia, $cantidad, $totalDia)
) {
  error_log('No fue posible consultar las ventas de la semana: ' . mysqli_stmt_error($consultaSemana));
  mysqli_stmt_close($consultaSemana);
  http_response_code(500);
  exit('No se pudo cargar el gráfico semanal de ventas.');
}

while (mysqli_stmt_fetch($consultaSemana)) {
  $dia = (string) $dia;
  if (isset($ventasPorDia[$dia])) {
    $ventasPorDia[$dia]['cantidad'] = (int) $cantidad;
    $ventasPorDia[$dia]['total'] = (float) $totalDia;
    $cantidadSemanal += (int) $cantidad;
    $totalSemanal += (float) $totalDia;
  }
}

if (mysqli_stmt_errno($consultaSemana) !== 0) {
  error_log('Ocurrió un error al leer las ventas semanales: ' . mysqli_stmt_error($consultaSemana));
  mysqli_stmt_close($consultaSemana);
  http_response_code(500);
  exit('No se pudo cargar el gráfico semanal de ventas.');
}
mysqli_stmt_close($consultaSemana);

$inicioMes = (new DateTimeImmutable('today'))->modify('first day of this month');
$finMes = $inicioMes->modify('+1 month');
$fechaInicioMes = $inicioMes->format('Y-m-d H:i:s');
$fechaFinMes = $finMes->format('Y-m-d H:i:s');
$ventasPorSemanaMes = array();
$numeroSemanaMes = 1;

for ($inicioSemanaMes = $inicioMes->modify('monday this week'); $inicioSemanaMes < $finMes; $inicioSemanaMes = $inicioSemanaMes->modify('+7 days')) {
  $finSemanaMes = $inicioSemanaMes->modify('+7 days');
  $inicioPeriodo = $inicioSemanaMes < $inicioMes ? $inicioMes : $inicioSemanaMes;
  $finPeriodo = $finSemanaMes > $finMes ? $finMes : $finSemanaMes;
  $fechaFinalPeriodo = $finPeriodo->modify('-1 day');
  $claveSemanaMes = $inicioSemanaMes->format('Y-m-d');
  $ventasPorSemanaMes[$claveSemanaMes] = array(
    'nombre' => 'Sem ' . $numeroSemanaMes,
    'periodo' => $inicioPeriodo->format('d/m') . ' - ' . $fechaFinalPeriodo->format('d/m'),
    'cantidad' => 0,
    'total' => 0.0
  );
  $numeroSemanaMes++;
}

$totalVentasMes = 0.0;
$cantidadVentasMes = 0;
$consultaMes = mysqli_prepare(
  $conn,
  'SELECT DATE_SUB(DATE(fecha_venta), INTERVAL WEEKDAY(fecha_venta) DAY) AS semana,
          COUNT(*) AS cantidad, COALESCE(SUM(costo), 0) AS total
   FROM ventas
   WHERE fecha_venta >= ? AND fecha_venta < ? AND costo >= 0
   GROUP BY semana
   ORDER BY semana'
);

if ($consultaMes === false) {
  error_log('No fue posible preparar el resumen mensual por semana: ' . mysqli_error($conn));
  http_response_code(500);
  exit('No se pudo cargar el gráfico mensual de ventas.');
}

if (
  !mysqli_stmt_bind_param($consultaMes, 'ss', $fechaInicioMes, $fechaFinMes) ||
  !mysqli_stmt_execute($consultaMes) ||
  !mysqli_stmt_bind_result($consultaMes, $semanaMes, $cantidadSemanaMes, $totalSemanaMes)
) {
  error_log('No fue posible consultar las ventas del mes por semana: ' . mysqli_stmt_error($consultaMes));
  mysqli_stmt_close($consultaMes);
  http_response_code(500);
  exit('No se pudo cargar el gráfico mensual de ventas.');
}

while (mysqli_stmt_fetch($consultaMes)) {
  $semanaMes = (string) $semanaMes;
  if (isset($ventasPorSemanaMes[$semanaMes])) {
    $ventasPorSemanaMes[$semanaMes]['cantidad'] = (int) $cantidadSemanaMes;
    $ventasPorSemanaMes[$semanaMes]['total'] = (float) $totalSemanaMes;
    $cantidadVentasMes += (int) $cantidadSemanaMes;
    $totalVentasMes += (float) $totalSemanaMes;
  }
}

if (mysqli_stmt_errno($consultaMes) !== 0) {
  error_log('Ocurrió un error al leer las ventas mensuales por semana: ' . mysqli_stmt_error($consultaMes));
  mysqli_stmt_close($consultaMes);
  http_response_code(500);
  exit('No se pudo cargar el gráfico mensual de ventas.');
}
mysqli_stmt_close($consultaMes);

$maximoDiario = max(array_column($ventasPorDia, 'total'));
$pasoEje = max($maximoDiario / 4, 1);
$ordenMagnitud = pow(10, floor(log10($pasoEje)));
$pasoEje = ceil($pasoEje / $ordenMagnitud) * $ordenMagnitud;
$maximoEje = $pasoEje * 4;
$decimalesEje = $pasoEje < 1 ? 2 : 0;
$anchoGrafica = 900;
$altoGrafica = 320;
$margenIzquierdo = 100;
$margenDerecho = 22;
$margenSuperior = 24;
$margenInferior = 58;
$anchoPlano = $anchoGrafica - $margenIzquierdo - $margenDerecho;
$altoPlano = $altoGrafica - $margenSuperior - $margenInferior;
$anchoDia = $anchoPlano / 7;
$anchoBarra = min(58, $anchoDia * 0.52);
$maximoSemanaMes = max(array_column($ventasPorSemanaMes, 'total'));
$pasoEjeMes = max($maximoSemanaMes / 4, 1);
$ordenMagnitudMes = pow(10, floor(log10($pasoEjeMes)));
$pasoEjeMes = ceil($pasoEjeMes / $ordenMagnitudMes) * $ordenMagnitudMes;
$maximoEjeMes = $pasoEjeMes * 4;
$decimalesEjeMes = $pasoEjeMes < 1 ? 2 : 0;
$anchoGraficaMes = 520;
$altoGraficaMes = 300;
$margenIzquierdoMes = 72;
$margenDerechoMes = 12;
$margenSuperiorMes = 24;
$margenInferiorMes = 48;
$anchoPlanoMes = $anchoGraficaMes - $margenIzquierdoMes - $margenDerechoMes;
$altoPlanoMes = $altoGraficaMes - $margenSuperiorMes - $margenInferiorMes;
$anchoSemanaMes = $anchoPlanoMes / count($ventasPorSemanaMes);
$anchoBarraMes = min(48, $anchoSemanaMes * 0.55);
?>

  <section>
    <div class="seccion">
      <div>
        <span>
          <h5 class="numerototal">
            <?php
            $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE fecha_venta >= DATE(NOW()) ";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }
            ?>
          </h5>
        </span>
        <span>
          <h3>Venta</h3>
        </span>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <h2>Registro Ventas</h2>
        <div id="registro" class="container">
          <form action="functions.php" method="post">
            <div class="row">
              <div class="col-md-12" style="display: none">
                <div class="row">
                  <label for="">Fecha de venta</label>
                  <input type="datetime" name="fechaVenta" value="<?php echo $fecha_actual ?>"
                    class="form-control datepicker" />
                </div>
              </div>
              <div class="col-md-2 col-xs-12">
                <label for="">Cant.</label>
                <input type="number" value="1" name="cantidad" class="form-control" />
              </div>
              <div class="col-md-10 col-xs-12">
                <label for="">Descripción</label>
                <input type="text" name="descripcion" class="form-control" />
              </div>
              <div class="col-md-4 col-xs-12">
                <label for="">Costo</label>
                <input type="number" name="costo" class="form-control" step="0.01" />
              </div>
              <div class="col-md-4 col-xs-12">
                <label for="">Precio compra</label>
                <input type="number" name="pc" class="form-control" step="0.01" />
              </div>
              <div class="col-md-4 col-xs-12">
                <label for="">Ganancia</label>
                <input type="number" name="g" class="form-control" step="0.01" />
              </div>
              <div class="col-md-8 col-xs-12">
                <label for="">Sección</label>
                <select name="opcionnegocio" class="select">
                  <option value="">Selecciona una opcion</option>
                  <?php
                  $ventasTotales = "SELECT * FROM seccion ORDER BY descripcionSeccion";
                  $resultado = mysqli_query($conn, $ventasTotales);
                  while ($mostrar = mysqli_fetch_array($resultado)) {
                    $id = $mostrar["id_seccion"];
                    $descripcion = $mostrar["descripcionSeccion"];
                    ?>
                    <option value="<?php echo $id; ?>">
                      <?php echo $descripcion ?>
                    </option>
                    <?php
                  }
                  ?>
                </select>
              </div>
              <div class="col-md-3 col-xs-12">
                <label for="">Facturar</label>
                <select name="factura" class="select">
                  <option value="no">No</option>
                  <option value="factura">Si</option>
                </select>
              </div>
            </div>
            <button type="submit" class="btn btn-primary">
              <i class="fa-solid fa-floppy-disk"></i> Registrar
            </button>
          </form>
        </div>
      </div>

      <div class="col-md-6">
        <h2>Movimientos</h2>
        <table id="tablaventas" class="display responsive nowrap" style="width: 100%">
          <thead>
            <tr>
              <th>ID</th>
              <th>Cantidad</th>
              <th>Descripcion</th>
              <th>Precio</th>
              <th>Categoria</th>
              <th>Fecha</th>
              <th>Editar</th>
              <th>Eliminar</th>
            </tr>
          </thead>

          <tbody>
            <?php
            $consulta = "SELECT * FROM ventas 
                WHERE fecha_venta >= DATE(NOW()) 
                ";
            $resultado = mysqli_query($conn, $consulta);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              ?>
              <tr>
                <td><?php echo $mostrar['id_venta'] ?></td>
                <td><?php echo $mostrar['cantidad'] ?></td>
                <td><?php echo $mostrar['descripcion'] ?></td>
                <td><?php echo $mostrar['costo'] ?></td>
                <td><?php echo $mostrar['seccion'] ?></td>
                <td><?php echo $mostrar['fecha'] ?></td>
                <td>
                  <a href="editarVentas.php?updVenta=<?= $mostrar['id_venta'] ?>"><i class="fa-solid fa-pen"></i></a>
                </td>
                <td>
                  <a href="#"><i class="fa-solid fa-trash"></i></a>
                </td>
              </tr>
              <?php
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="ventas-graficas-controles">
      <h2>Gráficos de ventas</h2>
      <button id="ventas-graficas-toggle" class="ventas-graficas-toggle" type="button"
        aria-expanded="false" aria-controls="ventas-graficas-contenedor"
        aria-label="Mostrar gráficos de ventas">
        <span aria-hidden="true"></span>
      </button>
    </div>
    <div id="ventas-graficas-contenedor" class="ventas-graficas-contenedor" hidden>
    <div class="ventas-semana-card">
      <div class="ventas-semana-encabezado">
        <div>
          <h2>Ventas de la semana</h2>
          <p>Del <?= $inicioSemana->format('d/m/Y') ?> al <?= $finSemana->modify('-1 day')->format('d/m/Y') ?></p>
        </div>
        <div class="ventas-semana-total">
          <span>Total semanal</span>
          <strong>$<?= number_format($totalSemanal, 2, '.', ',') ?></strong>
          <small><?= number_format($cantidadSemanal) ?> ventas</small>
        </div>
      </div>

      <p id="ventas-semana-seleccion" class="ventas-semana-seleccion" role="status" aria-live="polite">
        Haz clic en un día del gráfico para ver su monto.
      </p>

      <div class="ventas-semana-scroll">
        <svg class="ventas-semana-grafica" viewBox="0 0 <?= $anchoGrafica ?> <?= $altoGrafica ?>"
          role="group" aria-labelledby="ventas-semana-titulo ventas-semana-descripcion">
          <title id="ventas-semana-titulo">Ventas por día de la semana actual</title>
          <desc id="ventas-semana-descripcion">
            Gráfico de barras con el monto vendido cada día, de lunes a domingo.
          </desc>
          <defs>
            <linearGradient id="ventas-semana-gradiente" x1="0" x2="0" y1="0" y2="1">
              <stop offset="0%" stop-color="#3989c5" />
              <stop offset="100%" stop-color="#145789" />
            </linearGradient>
          </defs>
          <?php for ($marca = 0; $marca <= 4; $marca++) {
              $valorEje = $pasoEje * $marca;
              $posicionY = $margenSuperior + $altoPlano - ($altoPlano * $marca / 4);
              ?>
            <line class="ventas-semana-grid" x1="<?= $margenIzquierdo ?>"
              y1="<?= number_format($posicionY, 2, '.', '') ?>"
              x2="<?= $anchoGrafica - $margenDerecho ?>"
              y2="<?= number_format($posicionY, 2, '.', '') ?>" />
            <text class="ventas-semana-eje" x="<?= $margenIzquierdo - 10 ?>"
              y="<?= number_format($posicionY + 4, 2, '.', '') ?>" text-anchor="end">
              $<?= number_format($valorEje, $decimalesEje, '.', ',') ?>
            </text>
          <?php } ?>
          <line class="ventas-semana-eje-linea" x1="<?= $margenIzquierdo ?>"
            y1="<?= $margenSuperior + $altoPlano ?>"
            x2="<?= $anchoGrafica - $margenDerecho ?>"
            y2="<?= $margenSuperior + $altoPlano ?>" />
          <?php $indiceGrafica = 0; ?>
          <?php foreach ($ventasPorDia as $fechaDia => $datosDia) {
              $altoBarra = $altoPlano * $datosDia['total'] / $maximoEje;
              $posicionX = $margenIzquierdo + ($indiceGrafica + 0.5) * $anchoDia - $anchoBarra / 2;
              $posicionY = $margenSuperior + $altoPlano - $altoBarra;
              $etiquetaDia = $datosDia['nombre'] . ' ' . $datosDia['fecha'];
              $montoDia = number_format($datosDia['total'], 2, '.', ',');
              ?>
            <g class="ventas-semana-dia-dato" data-ventas-dia="<?= htmlspecialchars($etiquetaDia, ENT_QUOTES, 'UTF-8') ?>"
              data-ventas-total="<?= number_format($datosDia['total'], 2, '.', '') ?>"
              data-ventas-cantidad="<?= $datosDia['cantidad'] ?>" role="button" tabindex="0" aria-pressed="false"
              aria-label="<?= htmlspecialchars($etiquetaDia . ': $' . $montoDia . ', ' . $datosDia['cantidad'] . ' ventas', ENT_QUOTES, 'UTF-8') ?>">
              <rect class="ventas-semana-area" x="<?= number_format($margenIzquierdo + $indiceGrafica * $anchoDia, 2, '.', '') ?>"
                y="<?= $margenSuperior ?>" width="<?= number_format($anchoDia, 2, '.', '') ?>"
                height="<?= $altoPlano ?>" />
          <?php if ($datosDia['total'] > 0) { ?>
            <rect class="ventas-semana-barra" x="<?= number_format($posicionX, 2, '.', '') ?>"
              y="<?= number_format($posicionY, 2, '.', '') ?>"
              width="<?= number_format($anchoBarra, 2, '.', '') ?>"
              height="<?= number_format($altoBarra, 2, '.', '') ?>" rx="6">
              <title><?= $etiquetaDia ?>: <?= $datosDia['cantidad'] ?> ventas, $<?= $montoDia ?></title>
            </rect>
          <?php } ?>
            <text class="ventas-semana-dia"
              x="<?= number_format($posicionX + $anchoBarra / 2, 2, '.', '') ?>"
              y="<?= $margenSuperior + $altoPlano + 24 ?>" text-anchor="middle">
              <tspan x="<?= number_format($posicionX + $anchoBarra / 2, 2, '.', '') ?>"><?= $datosDia['nombre'] ?></tspan>
              <tspan class="ventas-semana-fecha" x="<?= number_format($posicionX + $anchoBarra / 2, 2, '.', '') ?>" dy="18"><?= $datosDia['fecha'] ?></tspan>
            </text>
            </g>
          <?php $indiceGrafica++; } ?>
        </svg>
      </div>
      <?php if ($cantidadSemanal === 0) { ?>
        <p class="ventas-semana-vacia">Aún no hay ventas registradas esta semana.</p>
      <?php } ?>
    </div>

    <div class="ventas-mes-card">
      <div class="ventas-semana-encabezado">
        <div>
          <h2>Ventas del mes por semana</h2>
          <p><?= $inicioMes->format('m/Y') ?></p>
        </div>
        <div class="ventas-semana-total">
          <span>Total del mes</span>
          <strong>$<?= number_format($totalVentasMes, 2, '.', ',') ?></strong>
          <small><?= number_format($cantidadVentasMes) ?> ventas</small>
        </div>
      </div>

      <p id="ventas-mes-seleccion" class="ventas-semana-seleccion" role="status" aria-live="polite">
        Haz clic en una semana para ver su monto.
      </p>

      <div class="ventas-semana-scroll">
        <svg class="ventas-mes-grafica" viewBox="0 0 <?= $anchoGraficaMes ?> <?= $altoGraficaMes ?>"
          role="group" aria-labelledby="ventas-mes-titulo ventas-mes-descripcion">
          <title id="ventas-mes-titulo">Ventas por semana de <?= $inicioMes->format('m/Y') ?></title>
          <desc id="ventas-mes-descripcion">
            Gráfico de barras con el monto vendido en cada semana del mes.
          </desc>
          <defs>
            <linearGradient id="ventas-mes-gradiente" x1="0" x2="0" y1="0" y2="1">
              <stop offset="0%" stop-color="#56A77B" />
              <stop offset="100%" stop-color="#28734A" />
            </linearGradient>
          </defs>
          <?php for ($marcaMes = 0; $marcaMes <= 4; $marcaMes++) {
              $valorEjeMes = $pasoEjeMes * $marcaMes;
              $posicionYMes = $margenSuperiorMes + $altoPlanoMes - ($altoPlanoMes * $marcaMes / 4);
              ?>
            <line class="ventas-semana-grid" x1="<?= $margenIzquierdoMes ?>"
              y1="<?= number_format($posicionYMes, 2, '.', '') ?>"
              x2="<?= $anchoGraficaMes - $margenDerechoMes ?>"
              y2="<?= number_format($posicionYMes, 2, '.', '') ?>" />
            <text class="ventas-semana-eje" x="<?= $margenIzquierdoMes - 8 ?>"
              y="<?= number_format($posicionYMes + 4, 2, '.', '') ?>" text-anchor="end">
              $<?= number_format($valorEjeMes, $decimalesEjeMes, '.', ',') ?>
            </text>
          <?php } ?>
          <line class="ventas-semana-eje-linea" x1="<?= $margenIzquierdoMes ?>"
            y1="<?= $margenSuperiorMes + $altoPlanoMes ?>"
            x2="<?= $anchoGraficaMes - $margenDerechoMes ?>"
            y2="<?= $margenSuperiorMes + $altoPlanoMes ?>" />
          <?php $indiceSemanaMes = 0; ?>
          <?php foreach ($ventasPorSemanaMes as $datosSemanaMes) {
              $altoBarraMesActual = $altoPlanoMes * $datosSemanaMes['total'] / $maximoEjeMes;
              $centroXMes = $margenIzquierdoMes + ($indiceSemanaMes + 0.5) * $anchoSemanaMes;
              $posicionXMes = $centroXMes - $anchoBarraMes / 2;
              $posicionYMes = $margenSuperiorMes + $altoPlanoMes - $altoBarraMesActual;
              $etiquetaSemanaMes = $datosSemanaMes['nombre'] . ' (' . $datosSemanaMes['periodo'] . ')';
              $montoSemanaMes = number_format($datosSemanaMes['total'], 2, '.', ',');
              ?>
            <g class="ventas-semana-dia-dato" data-ventas-dia="<?= htmlspecialchars($etiquetaSemanaMes, ENT_QUOTES, 'UTF-8') ?>"
              data-ventas-total="<?= number_format($datosSemanaMes['total'], 2, '.', '') ?>"
              data-ventas-cantidad="<?= $datosSemanaMes['cantidad'] ?>" role="button" tabindex="0" aria-pressed="false"
              aria-label="<?= htmlspecialchars($etiquetaSemanaMes . ': $' . $montoSemanaMes . ', ' . $datosSemanaMes['cantidad'] . ' ventas', ENT_QUOTES, 'UTF-8') ?>">
              <rect class="ventas-semana-area" x="<?= number_format($margenIzquierdoMes + $indiceSemanaMes * $anchoSemanaMes, 2, '.', '') ?>"
                y="<?= $margenSuperiorMes ?>" width="<?= number_format($anchoSemanaMes, 2, '.', '') ?>"
                height="<?= $altoPlanoMes ?>" />
              <?php if ($datosSemanaMes['total'] > 0) { ?>
              <rect class="ventas-mes-barra" x="<?= number_format($posicionXMes, 2, '.', '') ?>"
                y="<?= number_format($posicionYMes, 2, '.', '') ?>"
                width="<?= number_format($anchoBarraMes, 2, '.', '') ?>"
                height="<?= number_format($altoBarraMesActual, 2, '.', '') ?>" rx="6">
                <title><?= $etiquetaSemanaMes ?>: <?= $datosSemanaMes['cantidad'] ?> ventas, $<?= $montoSemanaMes ?></title>
              </rect>
              <?php } ?>
              <text class="ventas-semana-dia" x="<?= number_format($centroXMes, 2, '.', '') ?>"
                y="<?= $margenSuperiorMes + $altoPlanoMes + 22 ?>" text-anchor="middle">
                <tspan x="<?= number_format($centroXMes, 2, '.', '') ?>"><?= $datosSemanaMes['nombre'] ?></tspan>
                <tspan class="ventas-semana-fecha" x="<?= number_format($centroXMes, 2, '.', '') ?>"
                  dy="16"><?= $datosSemanaMes['periodo'] ?></tspan>
              </text>
            </g>
          <?php $indiceSemanaMes++; } ?>
        </svg>
      </div>
      <?php if ($cantidadVentasMes === 0) { ?>
        <p class="ventas-semana-vacia">Aún no hay ventas registradas este mes.</p>
      <?php } ?>
    </div>
    </div>

  </section>

  <script>
    const graficaVentasSemana = document.querySelector('.ventas-semana-grafica');
    const seleccionVentasSemana = document.getElementById('ventas-semana-seleccion');

    function mostrarMontoDia(elementoDia) {
      const nombreDia = elementoDia.dataset.ventasDia;
      const monto = Number(elementoDia.dataset.ventasTotal);
      const cantidad = Number(elementoDia.dataset.ventasCantidad);
      const montoFormateado = '$' + monto.toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
      const ventasTexto = cantidad === 1 ? '1 venta' : cantidad + ' ventas';

      seleccionVentasSemana.textContent = nombreDia + ': ' + montoFormateado + ' (' + ventasTexto + ')';
      graficaVentasSemana.querySelectorAll('.ventas-semana-dia-dato').forEach(function (dia) {
        dia.setAttribute('aria-pressed', dia === elementoDia ? 'true' : 'false');
      });
    }

    graficaVentasSemana.addEventListener('click', function (evento) {
      const elementoDia = evento.target.closest('.ventas-semana-dia-dato');
      if (elementoDia) {
        mostrarMontoDia(elementoDia);
      }
    });

    graficaVentasSemana.addEventListener('keydown', function (evento) {
      if (evento.key === 'Enter' || evento.key === ' ') {
        const elementoDia = evento.target.closest('.ventas-semana-dia-dato');
        if (elementoDia) {
          evento.preventDefault();
          mostrarMontoDia(elementoDia);
        }
      }
    });

    const graficaVentasMes = document.querySelector('.ventas-mes-grafica');
    const seleccionVentasMes = document.getElementById('ventas-mes-seleccion');

    function mostrarMontoSemanaMes(elementoSemana) {
      const nombreSemana = elementoSemana.dataset.ventasDia;
      const monto = Number(elementoSemana.dataset.ventasTotal);
      const cantidad = Number(elementoSemana.dataset.ventasCantidad);
      const montoFormateado = '$' + monto.toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
      const ventasTexto = cantidad === 1 ? '1 venta' : cantidad + ' ventas';

      seleccionVentasMes.textContent = nombreSemana + ': ' + montoFormateado + ' (' + ventasTexto + ')';
      graficaVentasMes.querySelectorAll('.ventas-semana-dia-dato').forEach(function (semana) {
        semana.setAttribute('aria-pressed', semana === elementoSemana ? 'true' : 'false');
      });
    }

    graficaVentasMes.addEventListener('click', function (evento) {
      const elementoSemana = evento.target.closest('.ventas-semana-dia-dato');
      if (elementoSemana) {
        mostrarMontoSemanaMes(elementoSemana);
      }
    });

    graficaVentasMes.addEventListener('keydown', function (evento) {
      if (evento.key === 'Enter' || evento.key === ' ') {
        const elementoSemana = evento.target.closest('.ventas-semana-dia-dato');
        if (elementoSemana) {
          evento.preventDefault();
          mostrarMontoSemanaMes(elementoSemana);
        }
      }
    });

    const botonGraficas = document.getElementById('ventas-graficas-toggle');
    const contenedorGraficas = document.getElementById('ventas-graficas-contenedor');

    botonGraficas.addEventListener('click', function () {
      const mostrarGraficas = botonGraficas.getAttribute('aria-expanded') !== 'true';
      botonGraficas.setAttribute('aria-expanded', String(mostrarGraficas));
      botonGraficas.setAttribute(
        'aria-label',
        mostrarGraficas ? 'Ocultar gráficos de ventas' : 'Mostrar gráficos de ventas'
      );
      contenedorGraficas.hidden = !mostrarGraficas;
    });
  </script>

  <?php include_once 'footer.php'; ?>
</body>