<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
mysqli_report(MYSQLI_REPORT_OFF);
require_once __DIR__ . '/ventas_db.php';

date_default_timezone_set('America/Mexico_City');
$hoy = new DateTimeImmutable('today');
$desde = isset($_GET['reinversion_desde']) ? $_GET['reinversion_desde'] : $hoy->modify('first day of this month')->format('Y-m-d');
$hasta = isset($_GET['reinversion_hasta']) ? $_GET['reinversion_hasta'] : $hoy->format('Y-m-d');
if (!is_string($desde) || !is_string($hasta)) {
    http_response_code(400);
    exit('El rango de fechas para reinversión no es válido.');
}
$fechaDesde = DateTimeImmutable::createFromFormat('!Y-m-d', $desde);
$erroresDesde = DateTimeImmutable::getLastErrors();
$fechaHasta = DateTimeImmutable::createFromFormat('!Y-m-d', $hasta);
$erroresHasta = DateTimeImmutable::getLastErrors();
if ($fechaDesde === false || $fechaHasta === false ||
    ($erroresDesde !== false && ($erroresDesde['warning_count'] || $erroresDesde['error_count'])) ||
    ($erroresHasta !== false && ($erroresHasta['warning_count'] || $erroresHasta['error_count'])) ||
    $fechaDesde->format('Y-m-d') !== $desde || $fechaHasta->format('Y-m-d') !== $hasta || $desde > $hasta) {
    http_response_code(400);
    exit('El rango de fechas para reinversión no es válido.');
}

$inicio = $desde . ' 00:00:00';
$fin = $fechaHasta->modify('+1 day')->format('Y-m-d') . ' 00:00:00';
$consulta = mysqli_prepare(
    $conn,
    "SELECT COALESCE(SUM(v.preciocosto), 0), COUNT(*),
            COALESCE(SUM(CASE WHEN v.preciocosto IS NULL THEN 1 ELSE 0 END), 0),
            COALESCE(SUM(CASE WHEN v.preciocosto = 0 AND v.costo > 0 THEN 1 ELSE 0 END), 0),
            COALESCE(SUM(CASE WHEN v.preciocosto < 0 THEN 1 ELSE 0 END), 0)
     FROM ventas v
     LEFT JOIN (SELECT id_grupo, SUM(costo) AS total_grupo FROM ventas WHERE id_grupo IS NOT NULL GROUP BY id_grupo) g
       ON g.id_grupo = v.id_grupo
     WHERE v.fecha_venta >= ? AND v.fecha_venta < ?
       AND ((v.id_grupo IS NULL AND v.costo >= 0) OR (v.id_grupo IS NOT NULL AND g.total_grupo >= 0))"
);
if (!$consulta || !mysqli_stmt_bind_param($consulta, 'ss', $inicio, $fin) ||
    !mysqli_stmt_execute($consulta) ||
    !mysqli_stmt_bind_result($consulta, $totalReinversion, $productos, $sinCosto, $costoCero, $costoNegativo) ||
    !mysqli_stmt_fetch($consulta)) {
    $error = $consulta ? mysqli_stmt_error($consulta) : mysqli_error($conn);
    error_log('No fue posible consultar la reinversión de ventas: ' . $error);
    if ($consulta) mysqli_stmt_close($consulta);
    http_response_code(500);
    exit('No se pudo consultar la reinversión de ventas.');
}
mysqli_stmt_close($consulta);
$escaparReinversion = function ($valor) { return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8'); };
$reinversionAbierta = isset($_GET['reinversion_desde']) || isset($_GET['reinversion_hasta']);
include_once __DIR__ . '/encabezado.php';
?>

<section>

  <h2>Consultas Personalizadas</h2>

  <?php
  define('SOFTGARESP_REPORTE_GRAFICOS_VENTAS', true);
  include_once 'ventas.php';
  ?>


  <section id="resultado_busqueda"></section>

  <section id="reinversion">
    <div class="ventas-graficas-controles">
      <h2>Reinvertir</h2>
      <button id="reinversion-toggle" class="ventas-graficas-toggle" type="button"
        aria-expanded="<?= $reinversionAbierta ? 'true' : 'false' ?>" aria-controls="reinversion-contenedor"
        aria-label="<?= $reinversionAbierta ? 'Ocultar' : 'Mostrar' ?> reinversión">
        <span aria-hidden="true"></span>
      </button>
    </div>
    <div id="reinversion-contenedor"<?= $reinversionAbierta ? '' : ' hidden' ?>>
      <div class="card p-3 mt-3 mb-4">
        <div class="card-body">
          <h3 class="h4">Reinversión de ventas</h3>
          <p>Consulta la compra total registrada de los productos vendidos en el periodo.</p>
          <form method="get" action="consultas.php#reinversion" class="row g-3 align-items-end">
            <div class="col-sm-5 col-lg-3">
              <label for="reinversion-desde" class="form-label">Desde</label>
              <input id="reinversion-desde" type="date" name="reinversion_desde" class="form-control" value="<?= $escaparReinversion($desde) ?>" required>
            </div>
            <div class="col-sm-5 col-lg-3">
              <label for="reinversion-hasta" class="form-label">Hasta</label>
              <input id="reinversion-hasta" type="date" name="reinversion_hasta" class="form-control" value="<?= $escaparReinversion($hasta) ?>" required>
            </div>
            <div class="col-sm-auto"><button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter" aria-hidden="true"></i> Consultar</button></div>
          </form>
          <div class="mt-4" aria-live="polite">
            <span class="d-block text-muted">Compra registrada para reinvertir</span>
            <strong class="h3" id="reinversion-total">$<?= number_format((float) $totalReinversion, 2, '.', ',') ?></strong>
            <p class="mb-1"><?= (int) $productos ?> productos registrados en el periodo.</p>
            <?php if ((int) $sinCosto > 0 || (int) $costoCero > 0 || (int) $costoNegativo > 0) { ?>
            <p class="alert alert-warning mt-3" role="status">
              Revisa los costos: <?= (int) $sinCosto ?> sin costo registrado, <?= (int) $costoCero ?> con costo cero y <?= (int) $costoNegativo ?> con costo negativo.
              Los costos sin registrar no se incluyen en el importe.
            </p>
            <?php } ?>
            <small class="text-muted">El importe usa la compra total capturada en ventas; no representa el efectivo disponible y no incluye cotizaciones ni salidas independientes.</small>
          </div>
        </div>
      </div>
    </div>
  </section>


  <h2>Total impresiones</h2>

  <section class="seccion reportes-resumen">
    <article class="reportes-resumen-card reportes-resumen-card-total">
      <div class="reportes-resumen-cabecera">
        <span class="reportes-resumen-etiqueta">Impresiones</span>
        <span class="reportes-resumen-icono" aria-hidden="true"><i class="fa-solid fa-print"></i></span>
      </div>
      <strong class="reportes-resumen-valor">
        <?php
        $ventasTotales = "SELECT SUM(precio) FROM impresion WHERE YEAR(fecha_impresion) = 2026 AND precio>0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </strong>
      <p class="reportes-resumen-detalle">Total de 2026</p>
    </article>

  </section>
  <?php

// Establecer la zona horaria (opcional pero recomendado)
date_default_timezone_set('America/Mexico_City');

$meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

  $mesActual = $meses[(int) date('n')];
  $anioActual = date('Y');

  echo ("<h2>Venta Factura - " . $mesActual . " " . $anioActual . "</h2>");
  ?>

  <!-- <h2>Venta Factura - NOVIEMBRE 2026</h2> -->

  <section class="seccion reportes-resumen">
    <article class="reportes-resumen-card reportes-resumen-card-total">
      <div class="reportes-resumen-cabecera">
        <span class="reportes-resumen-etiqueta">Venta con factura</span>
        <span class="reportes-resumen-icono" aria-hidden="true"><i class="fa-solid fa-file-invoice-dollar"></i></span>
      </div>
      <strong class="reportes-resumen-valor">
        <?php
        $mes = date('n'); 
        $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE YEAR(fecha_venta) = 2026 AND MONTH(fecha_venta) = $mes AND FACTURA='FACTURA'";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </strong>
      <p class="reportes-resumen-detalle">Total de <?= htmlspecialchars($mesActual . ' ' . $anioActual, ENT_QUOTES, 'UTF-8') ?></p>
    </article>

  </section>
 

</section>



<?php include_once 'footer.php' ?>
<script src="busquedaReporte.js"></script>
<script>
  const botonReinversion = document.getElementById('reinversion-toggle');
  const contenedorReinversion = document.getElementById('reinversion-contenedor');
  botonReinversion.addEventListener('click', function () {
    const mostrar = botonReinversion.getAttribute('aria-expanded') !== 'true';
    botonReinversion.setAttribute('aria-expanded', String(mostrar));
    botonReinversion.setAttribute('aria-label', mostrar ? 'Ocultar reinversión' : 'Mostrar reinversión');
    contenedorReinversion.hidden = !mostrar;
  });
</script>