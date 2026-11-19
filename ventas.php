<?php
$mostrarGraficosEnReportes = defined('SOFTGARESP_REPORTE_GRAFICOS_VENTAS');
mysqli_report(MYSQLI_REPORT_OFF);
if (!$mostrarGraficosEnReportes) {
  include_once 'encabezado.php';
}

if (!$conn) {
  error_log('No fue posible conectar con la base de datos para cargar la información de ventas.');
  http_response_code(500);
  exit('No se pudo cargar la información de ventas.');
}

if ($mostrarGraficosEnReportes) {
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

$serviciosPorDia = array();
foreach ($ventasPorDia as $claveFecha => $datosVentaDia) {
  $serviciosPorDia[$claveFecha] = array(
    'nombre' => $datosVentaDia['nombre'],
    'fecha' => $datosVentaDia['fecha'],
    'cantidad' => 0,
    'monto' => 0.0,
    'ganancia' => 0.0
  );
}

$totalServiciosSemana = 0;
$montoServiciosSemana = 0.0;
$gananciaServiciosSemana = 0.0;
$consultaServiciosSemana = mysqli_prepare(
  $conn,
  'SELECT DATE(fecha_alta) AS dia, COUNT(*) AS cantidad,
          COALESCE(SUM(CASE WHEN costoServicio > 0 THEN costoServicio ELSE 0 END), 0) AS monto,
          COALESCE(SUM(CASE WHEN ganancia > 0 THEN ganancia ELSE 0 END), 0) AS ganancia
   FROM notas
   WHERE fecha_alta >= ? AND fecha_alta < ?
   GROUP BY DATE(fecha_alta)
   ORDER BY dia'
);

if ($consultaServiciosSemana === false) {
  error_log('No fue posible preparar el resumen semanal de servicios: ' . mysqli_error($conn));
  http_response_code(500);
  exit('No se pudo cargar el gráfico semanal de servicios.');
}

if (
  !mysqli_stmt_bind_param($consultaServiciosSemana, 'ss', $fechaInicioSemana, $fechaFinSemana) ||
  !mysqli_stmt_execute($consultaServiciosSemana) ||
  !mysqli_stmt_bind_result($consultaServiciosSemana, $diaServicio, $cantidadServicioDia, $montoServicioDia, $gananciaServicioDia)
) {
  error_log('No fue posible consultar los servicios de la semana: ' . mysqli_stmt_error($consultaServiciosSemana));
  mysqli_stmt_close($consultaServiciosSemana);
  http_response_code(500);
  exit('No se pudo cargar el gráfico semanal de servicios.');
}

while (mysqli_stmt_fetch($consultaServiciosSemana)) {
  $diaServicio = (string) $diaServicio;
  if (isset($serviciosPorDia[$diaServicio])) {
    $serviciosPorDia[$diaServicio]['cantidad'] = (int) $cantidadServicioDia;
    $serviciosPorDia[$diaServicio]['monto'] = (float) $montoServicioDia;
    $serviciosPorDia[$diaServicio]['ganancia'] = (float) $gananciaServicioDia;
    $totalServiciosSemana += (int) $cantidadServicioDia;
    $montoServiciosSemana += (float) $montoServicioDia;
    $gananciaServiciosSemana += (float) $gananciaServicioDia;
  }
}

if (mysqli_stmt_errno($consultaServiciosSemana) !== 0) {
  error_log('Ocurrió un error al leer los servicios semanales: ' . mysqli_stmt_error($consultaServiciosSemana));
  mysqli_stmt_close($consultaServiciosSemana);
  http_response_code(500);
  exit('No se pudo cargar el gráfico semanal de servicios.');
}
mysqli_stmt_close($consultaServiciosSemana);

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

$serviciosPorSemanaMes = array();
foreach ($ventasPorSemanaMes as $claveSemanaServicio => $datosSemanaVenta) {
  $serviciosPorSemanaMes[$claveSemanaServicio] = array(
    'nombre' => $datosSemanaVenta['nombre'],
    'periodo' => $datosSemanaVenta['periodo'],
    'cantidad' => 0,
    'monto' => 0.0,
    'ganancia' => 0.0
  );
}

$totalServiciosMes = 0;
$montoServiciosMes = 0.0;
$gananciaServiciosMes = 0.0;
$consultaServiciosMes = mysqli_prepare(
  $conn,
  'SELECT DATE_SUB(DATE(fecha_alta), INTERVAL WEEKDAY(fecha_alta) DAY) AS semana,
          COUNT(*) AS cantidad,
          COALESCE(SUM(CASE WHEN costoServicio > 0 THEN costoServicio ELSE 0 END), 0) AS monto,
          COALESCE(SUM(CASE WHEN ganancia > 0 THEN ganancia ELSE 0 END), 0) AS ganancia
   FROM notas
   WHERE fecha_alta >= ? AND fecha_alta < ?
   GROUP BY semana
   ORDER BY semana'
);

if ($consultaServiciosMes === false) {
  error_log('No fue posible preparar el resumen mensual de servicios por semana: ' . mysqli_error($conn));
  http_response_code(500);
  exit('No se pudo cargar el gráfico mensual de servicios.');
}

if (
  !mysqli_stmt_bind_param($consultaServiciosMes, 'ss', $fechaInicioMes, $fechaFinMes) ||
  !mysqli_stmt_execute($consultaServiciosMes) ||
  !mysqli_stmt_bind_result($consultaServiciosMes, $semanaServicioMes, $cantidadServicioMes, $montoServicioMes, $gananciaServicioMes)
) {
  error_log('No fue posible consultar los servicios mensuales por semana: ' . mysqli_stmt_error($consultaServiciosMes));
  mysqli_stmt_close($consultaServiciosMes);
  http_response_code(500);
  exit('No se pudo cargar el gráfico mensual de servicios.');
}

while (mysqli_stmt_fetch($consultaServiciosMes)) {
  $semanaServicioMes = (string) $semanaServicioMes;
  if (isset($serviciosPorSemanaMes[$semanaServicioMes])) {
    $serviciosPorSemanaMes[$semanaServicioMes]['cantidad'] = (int) $cantidadServicioMes;
    $serviciosPorSemanaMes[$semanaServicioMes]['monto'] = (float) $montoServicioMes;
    $serviciosPorSemanaMes[$semanaServicioMes]['ganancia'] = (float) $gananciaServicioMes;
    $totalServiciosMes += (int) $cantidadServicioMes;
    $montoServiciosMes += (float) $montoServicioMes;
    $gananciaServiciosMes += (float) $gananciaServicioMes;
  }
}

if (mysqli_stmt_errno($consultaServiciosMes) !== 0) {
  error_log('Ocurrió un error al leer los servicios mensuales por semana: ' . mysqli_stmt_error($consultaServiciosMes));
  mysqli_stmt_close($consultaServiciosMes);
  http_response_code(500);
  exit('No se pudo cargar el gráfico mensual de servicios.');
}
mysqli_stmt_close($consultaServiciosMes);

$serviciosPorMesTipo = array();
$consultaServiciosPorTipoMes = mysqli_prepare(
  $conn,
  'SELECT YEAR(n.fecha_alta) AS anio, MONTH(n.fecha_alta) AS mes,
          COALESCE(s.tipoServicio, ?) AS tipoServicio, COUNT(*) AS cantidad,
          COALESCE(SUM(CASE WHEN n.costoServicio > 0 THEN n.costoServicio ELSE 0 END), 0) AS monto,
          COALESCE(SUM(CASE WHEN n.ganancia > 0 THEN n.ganancia ELSE 0 END), 0) AS ganancia
   FROM notas n
   LEFT JOIN servicio s ON n.servicio = s.id_servicio
   WHERE n.fecha_alta IS NOT NULL
   GROUP BY YEAR(n.fecha_alta), MONTH(n.fecha_alta), n.servicio, s.tipoServicio
   ORDER BY anio DESC, mes DESC, monto DESC'
);

if ($consultaServiciosPorTipoMes === false) {
  error_log('No fue posible preparar el resumen mensual por tipo de servicio: ' . mysqli_error($conn));
  http_response_code(500);
  exit('No se pudo cargar el gráfico mensual por tipo de servicio.');
}

$tipoServicioSinNombre = 'Sin tipo';
if (
  !mysqli_stmt_bind_param($consultaServiciosPorTipoMes, 's', $tipoServicioSinNombre) ||
  !mysqli_stmt_execute($consultaServiciosPorTipoMes) ||
  !mysqli_stmt_bind_result($consultaServiciosPorTipoMes, $anioServicioTipo, $mesServicioTipo, $tipoServicio, $cantidadServicioTipo, $montoServicioTipo, $gananciaServicioTipo)
) {
  error_log('No fue posible consultar los servicios mensuales por tipo: ' . mysqli_stmt_error($consultaServiciosPorTipoMes));
  mysqli_stmt_close($consultaServiciosPorTipoMes);
  http_response_code(500);
  exit('No se pudo cargar el gráfico mensual por tipo de servicio.');
}

$nombresMesesGrafica = array(
  'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
  'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
);
while (mysqli_stmt_fetch($consultaServiciosPorTipoMes)) {
  $anioServicioTipo = (int) $anioServicioTipo;
  $mesServicioTipo = (int) $mesServicioTipo;
  $clavePeriodoServicio = sprintf('%04d-%02d', $anioServicioTipo, $mesServicioTipo);
  if (!isset($serviciosPorMesTipo[$clavePeriodoServicio])) {
    $serviciosPorMesTipo[$clavePeriodoServicio] = array(
      'nombre' => $nombresMesesGrafica[$mesServicioTipo - 1] . ' ' . $anioServicioTipo,
      'monto' => 0.0,
      'ganancia' => 0.0,
      'tipos' => array()
    );
  }

  $tipoServicioActual = array(
    'nombre' => (string) $tipoServicio,
    'cantidad' => (int) $cantidadServicioTipo,
    'monto' => (float) $montoServicioTipo,
    'ganancia' => (float) $gananciaServicioTipo
  );
  $serviciosPorMesTipo[$clavePeriodoServicio]['tipos'][] = $tipoServicioActual;
  $serviciosPorMesTipo[$clavePeriodoServicio]['monto'] += $tipoServicioActual['monto'];
  $serviciosPorMesTipo[$clavePeriodoServicio]['ganancia'] += $tipoServicioActual['ganancia'];
}

if (mysqli_stmt_errno($consultaServiciosPorTipoMes) !== 0) {
  error_log('Ocurrió un error al leer los servicios por tipo y mes: ' . mysqli_stmt_error($consultaServiciosPorTipoMes));
  mysqli_stmt_close($consultaServiciosPorTipoMes);
  http_response_code(500);
  exit('No se pudo cargar el gráfico mensual por tipo de servicio.');
}
mysqli_stmt_close($consultaServiciosPorTipoMes);

$periodosServiciosTipo = array_keys($serviciosPorMesTipo);
$mesesDisponiblesPorAnioServicio = array();
foreach ($periodosServiciosTipo as $periodoServicio) {
  $partesPeriodoServicio = explode('-', $periodoServicio);
  $anioPeriodoServicio = $partesPeriodoServicio[0];
  $mesPeriodoServicio = $partesPeriodoServicio[1];
  if (!isset($mesesDisponiblesPorAnioServicio[$anioPeriodoServicio])) {
    $mesesDisponiblesPorAnioServicio[$anioPeriodoServicio] = array();
  }
  $mesesDisponiblesPorAnioServicio[$anioPeriodoServicio][] = $mesPeriodoServicio;
}

$aniosServiciosTipo = array_keys($mesesDisponiblesPorAnioServicio);
$anioServicioSeleccionado = date('Y');
if (!isset($mesesDisponiblesPorAnioServicio[$anioServicioSeleccionado])) {
  $anioServicioSeleccionado = count($aniosServiciosTipo) > 0 ? $aniosServiciosTipo[0] : date('Y');
}
$mesesAnioServicioSeleccionado = isset($mesesDisponiblesPorAnioServicio[$anioServicioSeleccionado])
  ? $mesesDisponiblesPorAnioServicio[$anioServicioSeleccionado]
  : array();
$mesServicioSeleccionado = date('m');
if (!in_array($mesServicioSeleccionado, $mesesAnioServicioSeleccionado, true)) {
  $mesServicioSeleccionado = count($mesesAnioServicioSeleccionado) > 0
    ? $mesesAnioServicioSeleccionado[0]
    : date('m');
}
$periodoServicioSeleccionado = $anioServicioSeleccionado . '-' . $mesServicioSeleccionado;
$resumenAnualServicios = array();
foreach ($serviciosPorMesTipo as $periodoServicio => $datosMesServicio) {
  $anioResumenServicio = substr($periodoServicio, 0, 4);
  if (!isset($resumenAnualServicios[$anioResumenServicio])) {
    $resumenAnualServicios[$anioResumenServicio] = array(
      'monto' => 0.0,
      'ganancia' => 0.0
    );
  }
  $resumenAnualServicios[$anioResumenServicio]['monto'] += $datosMesServicio['monto'];
  $resumenAnualServicios[$anioResumenServicio]['ganancia'] += $datosMesServicio['ganancia'];
}

$ventasPorCategoria = array();
$consultaCategorias = mysqli_prepare(
  $conn,
  'SELECT COALESCE(s.descripcionSeccion, ?) AS categoria,
          COALESCE(SUM(v.cantidad), 0) AS unidades,
          COALESCE(SUM(CASE WHEN v.costo > 0 THEN v.costo ELSE 0 END), 0) AS monto,
          COALESCE(SUM(CASE WHEN v.ganancia > 0 THEN v.ganancia ELSE 0 END), 0) AS ganancia
   FROM ventas v
   LEFT JOIN seccion s ON s.id_seccion = v.seccion
   WHERE v.fecha_venta >= ? AND v.fecha_venta < ?
   GROUP BY v.seccion, s.descripcionSeccion
   ORDER BY monto DESC, unidades DESC'
);

if ($consultaCategorias === false) {
  error_log('No fue posible preparar el resumen mensual por categoría: ' . mysqli_error($conn));
  http_response_code(500);
  exit('No se pudo cargar el gráfico por categoría.');
}

$categoriaSinNombre = 'Sin categoría';
if (
  !mysqli_stmt_bind_param($consultaCategorias, 'sss', $categoriaSinNombre, $fechaInicioMes, $fechaFinMes) ||
  !mysqli_stmt_execute($consultaCategorias) ||
  !mysqli_stmt_bind_result($consultaCategorias, $nombreCategoria, $unidadesCategoria, $montoCategoria, $gananciaCategoria)
) {
  error_log('No fue posible consultar las ventas del mes por categoría: ' . mysqli_stmt_error($consultaCategorias));
  mysqli_stmt_close($consultaCategorias);
  http_response_code(500);
  exit('No se pudo cargar el gráfico por categoría.');
}

while (mysqli_stmt_fetch($consultaCategorias)) {
  $ventasPorCategoria[] = array(
    'nombre' => (string) $nombreCategoria,
    'unidades' => (float) $unidadesCategoria,
    'monto' => (float) $montoCategoria,
    'ganancia' => (float) $gananciaCategoria
  );
}

if (mysqli_stmt_errno($consultaCategorias) !== 0) {
  error_log('Ocurrió un error al leer las ventas mensuales por categoría: ' . mysqli_stmt_error($consultaCategorias));
  mysqli_stmt_close($consultaCategorias);
  http_response_code(500);
  exit('No se pudo cargar el gráfico por categoría.');
}
mysqli_stmt_close($consultaCategorias);

$ventasPorAnio = array();
$consultaVentasAnuales = mysqli_prepare(
  $conn,
  'SELECT YEAR(v.fecha_venta) AS anio,
          COALESCE(s.descripcionSeccion, ?) AS categoria,
          COALESCE(SUM(v.cantidad), 0) AS unidades,
          COALESCE(SUM(CASE WHEN v.costo > 0 THEN v.costo ELSE 0 END), 0) AS monto,
          COALESCE(SUM(CASE WHEN v.ganancia > 0 THEN v.ganancia ELSE 0 END), 0) AS ganancia
   FROM ventas v
   LEFT JOIN seccion s ON s.id_seccion = v.seccion
   GROUP BY YEAR(v.fecha_venta), v.seccion, s.descripcionSeccion
   ORDER BY anio DESC, monto DESC, unidades DESC'
);

if ($consultaVentasAnuales === false) {
  error_log('No fue posible preparar el resumen anual por categoría: ' . mysqli_error($conn));
  http_response_code(500);
  exit('No se pudo cargar el gráfico anual de ventas.');
}

$categoriaAnualSinNombre = 'Sin categoría';
if (
  !mysqli_stmt_bind_param($consultaVentasAnuales, 's', $categoriaAnualSinNombre) ||
  !mysqli_stmt_execute($consultaVentasAnuales) ||
  !mysqli_stmt_bind_result($consultaVentasAnuales, $anioVenta, $categoriaAnual, $unidadesAnuales, $montoAnual, $gananciaAnual)
) {
  error_log('No fue posible consultar las ventas anuales por categoría: ' . mysqli_stmt_error($consultaVentasAnuales));
  mysqli_stmt_close($consultaVentasAnuales);
  http_response_code(500);
  exit('No se pudo cargar el gráfico anual de ventas.');
}

while (mysqli_stmt_fetch($consultaVentasAnuales)) {
  $anioVenta = (int) $anioVenta;
  if (!isset($ventasPorAnio[$anioVenta])) {
    $ventasPorAnio[$anioVenta] = array(
      'monto' => 0.0,
      'ganancia' => 0.0,
      'categorias' => array()
    );
  }

  $categoriaVenta = array(
    'nombre' => (string) $categoriaAnual,
    'unidades' => (float) $unidadesAnuales,
    'monto' => (float) $montoAnual,
    'ganancia' => (float) $gananciaAnual
  );
  $ventasPorAnio[$anioVenta]['categorias'][] = $categoriaVenta;
  $ventasPorAnio[$anioVenta]['monto'] += $categoriaVenta['monto'];
  $ventasPorAnio[$anioVenta]['ganancia'] += $categoriaVenta['ganancia'];
}

if (mysqli_stmt_errno($consultaVentasAnuales) !== 0) {
  error_log('Ocurrió un error al leer las ventas anuales por categoría: ' . mysqli_stmt_error($consultaVentasAnuales));
  mysqli_stmt_close($consultaVentasAnuales);
  http_response_code(500);
  exit('No se pudo cargar el gráfico anual de ventas.');
}
mysqli_stmt_close($consultaVentasAnuales);

$aniosDisponibles = array_keys($ventasPorAnio);
$anioActual = (int) date('Y');
$anioGraficaSeleccionado = in_array($anioActual, $aniosDisponibles, true)
    ? $anioActual
    : (count($aniosDisponibles) > 0 ? $aniosDisponibles[0] : $anioActual);

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
$maximoServiciosDia = max(array_column($serviciosPorDia, 'cantidad'));
$pasoEjeServicios = max(ceil($maximoServiciosDia / 4), 1);
$maximoEjeServicios = $pasoEjeServicios * 4;
$anchoBarraServicios = min(58, $anchoDia * 0.52);
$maximoServiciosSemanaMes = max(array_column($serviciosPorSemanaMes, 'cantidad'));
$pasoEjeServiciosMes = max(ceil($maximoServiciosSemanaMes / 4), 1);
$maximoEjeServiciosMes = $pasoEjeServiciosMes * 4;
$anchoGraficaServiciosMes = 620;
$altoGraficaServiciosMes = 320;
$margenIzquierdoServiciosMes = 74;
$margenDerechoServiciosMes = 12;
$margenSuperiorServiciosMes = 24;
$margenInferiorServiciosMes = 62;
$anchoPlanoServiciosMes = $anchoGraficaServiciosMes - $margenIzquierdoServiciosMes - $margenDerechoServiciosMes;
$altoPlanoServiciosMes = $altoGraficaServiciosMes - $margenSuperiorServiciosMes - $margenInferiorServiciosMes;
$anchoSemanaServiciosMes = $anchoPlanoServiciosMes / count($serviciosPorSemanaMes);
$anchoBarraServiciosMes = min(48, $anchoSemanaServiciosMes * 0.55);
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
}
?>

<?php if (!$mostrarGraficosEnReportes) { ?>
  <section>
    <div class="seccion reportes-resumen">
      <article class="reportes-resumen-card reportes-resumen-card-total">
        <div class="reportes-resumen-cabecera">
          <span class="reportes-resumen-etiqueta">Venta de hoy</span>
          <span class="reportes-resumen-icono" aria-hidden="true"><i class="fa-solid fa-chart-line"></i></span>
        </div>
        <strong class="reportes-resumen-valor">
            <?php
            $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE fecha_venta >= DATE(NOW()) ";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }
            ?>
        </strong>
        <p class="reportes-resumen-detalle">Total registrado hoy</p>
      </article>
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

  </section>
<?php } ?>

<?php if ($mostrarGraficosEnReportes) { ?>
  <section>
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

    <div class="ventas-categoria-card">
      <div class="ventas-categoria-encabezado">
        <div>
          <h2>Categorías más vendidas del mes</h2>
          <p>Ordenadas por monto total · <?= $inicioMes->format('m/Y') ?></p>
        </div>
      </div>

      <div class="ventas-categoria-resumen" aria-live="polite">
        <div>
          <span>Detalle de categoría</span>
          <strong id="ventas-categoria-nombre"><?= count($ventasPorCategoria) > 0 ? htmlspecialchars($ventasPorCategoria[0]['nombre'], ENT_QUOTES, 'UTF-8') : 'Sin ventas' ?></strong>
        </div>
        <div>
          <span>Unidades vendidas</span>
          <strong id="ventas-categoria-unidades"><?= count($ventasPorCategoria) > 0 ? number_format($ventasPorCategoria[0]['unidades']) : '0' ?></strong>
        </div>
        <div>
          <span>Monto total</span>
          <strong id="ventas-categoria-monto">$<?= count($ventasPorCategoria) > 0 ? number_format($ventasPorCategoria[0]['monto'], 2, '.', ',') : '0.00' ?></strong>
        </div>
        <div>
          <span>Ganancia</span>
          <strong id="ventas-categoria-ganancia">$<?= count($ventasPorCategoria) > 0 ? number_format($ventasPorCategoria[0]['ganancia'], 2, '.', ',') : '0.00' ?></strong>
        </div>
      </div>

      <?php if (count($ventasPorCategoria) > 0) { ?>
        <div class="ventas-categoria-grafica" role="group" aria-label="Ventas por categoría">
          <?php $maximoMontoCategoria = max(array_column($ventasPorCategoria, 'monto')); ?>
          <?php foreach ($ventasPorCategoria as $indiceCategoria => $datosCategoria) {
              $anchoBarraCategoria = $maximoMontoCategoria > 0
                  ? $datosCategoria['monto'] / $maximoMontoCategoria * 100
                  : 0;
              $etiquetaAccesibleCategoria = $datosCategoria['nombre'] . ': ' .
                  number_format($datosCategoria['unidades']) . ' unidades, monto $' .
                  number_format($datosCategoria['monto'], 2, '.', ',') . ', ganancia $' .
                  number_format($datosCategoria['ganancia'], 2, '.', ',');
              ?>
            <button type="button" class="ventas-categoria-fila" data-categoria="<?= htmlspecialchars($datosCategoria['nombre'], ENT_QUOTES, 'UTF-8') ?>"
              data-unidades="<?= number_format($datosCategoria['unidades'], 2, '.', '') ?>"
              data-monto="<?= number_format($datosCategoria['monto'], 2, '.', '') ?>"
              data-ganancia="<?= number_format($datosCategoria['ganancia'], 2, '.', '') ?>"
              aria-pressed="<?= $indiceCategoria === 0 ? 'true' : 'false' ?>"
              aria-label="<?= htmlspecialchars($etiquetaAccesibleCategoria, ENT_QUOTES, 'UTF-8') ?>">
              <span class="ventas-categoria-nombre"><?= htmlspecialchars($datosCategoria['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
              <span class="ventas-categoria-barra-fondo">
                <span class="ventas-categoria-barra" style="width: <?= number_format($anchoBarraCategoria, 2, '.', '') ?>%"></span>
              </span>
              <span class="ventas-categoria-cantidad">$<?= number_format($datosCategoria['monto'], 2, '.', ',') ?></span>
            </button>
          <?php } ?>
        </div>
      <?php } else { ?>
        <p class="ventas-semana-vacia">Aún no hay ventas por categoría este mes.</p>
      <?php } ?>
    </div>

    <div class="ventas-anual-card">
      <div class="ventas-anual-encabezado">
        <div>
          <h2>Ventas anuales por categoría</h2>
          <p>Selecciona un año para comparar el monto total y la ganancia.</p>
        </div>
        <div class="ventas-anual-filtro">
          <label for="ventas-anual-anio">Año</label>
          <select id="ventas-anual-anio" class="form-control">
            <?php if (count($aniosDisponibles) === 0) { ?>
              <option value="<?= $anioGraficaSeleccionado ?>"><?= $anioGraficaSeleccionado ?></option>
            <?php } ?>
            <?php foreach ($aniosDisponibles as $anioDisponible) { ?>
              <option value="<?= $anioDisponible ?>" <?= $anioDisponible === $anioGraficaSeleccionado ? 'selected' : '' ?>>
                <?= $anioDisponible ?>
              </option>
            <?php } ?>
          </select>
        </div>
      </div>

      <?php if (count($ventasPorAnio) > 0) { ?>
        <?php foreach ($ventasPorAnio as $anioGrafica => $datosAnio) {
            $maximoMontoAnual = max(array_column($datosAnio['categorias'], 'monto'));
            $categoriaPrincipalAnual = $datosAnio['categorias'][0];
            ?>
          <div class="ventas-anual-vista" data-anio="<?= $anioGrafica ?>" <?= $anioGrafica === $anioGraficaSeleccionado ? '' : 'hidden' ?>>
            <div class="ventas-categoria-resumen ventas-anual-total">
              <div>
                <span>Monto total del año</span>
                <strong>$<?= number_format($datosAnio['monto'], 2, '.', ',') ?></strong>
              </div>
              <div>
                <span>Ganancia total del año</span>
                <strong>$<?= number_format($datosAnio['ganancia'], 2, '.', ',') ?></strong>
              </div>
            </div>

            <div class="ventas-categoria-resumen ventas-anual-detalle" aria-live="polite">
              <div>
                <span>Detalle de categoría</span>
                <strong><?= htmlspecialchars($categoriaPrincipalAnual['nombre'], ENT_QUOTES, 'UTF-8') ?></strong>
              </div>
              <div>
                <span>Unidades vendidas</span>
                <strong><?= number_format($categoriaPrincipalAnual['unidades']) ?></strong>
              </div>
              <div>
                <span>Monto total</span>
                <strong>$<?= number_format($categoriaPrincipalAnual['monto'], 2, '.', ',') ?></strong>
              </div>
              <div>
                <span>Ganancia</span>
                <strong>$<?= number_format($categoriaPrincipalAnual['ganancia'], 2, '.', ',') ?></strong>
              </div>
            </div>

            <div class="ventas-categoria-grafica" role="group" aria-label="Ventas por categoría en <?= $anioGrafica ?>">
              <?php foreach ($datosAnio['categorias'] as $indiceCategoriaAnual => $datosCategoriaAnual) {
                  $anchoBarraAnual = $maximoMontoAnual > 0
                      ? $datosCategoriaAnual['monto'] / $maximoMontoAnual * 100
                      : 0;
                  $etiquetaAccesibleAnual = $datosCategoriaAnual['nombre'] . ': ' .
                      number_format($datosCategoriaAnual['unidades']) . ' unidades, monto $' .
                      number_format($datosCategoriaAnual['monto'], 2, '.', ',') . ', ganancia $' .
                      number_format($datosCategoriaAnual['ganancia'], 2, '.', ',');
                  ?>
                <button type="button" class="ventas-categoria-fila ventas-anual-categoria-fila"
                  data-categoria="<?= htmlspecialchars($datosCategoriaAnual['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                  data-unidades="<?= number_format($datosCategoriaAnual['unidades'], 2, '.', '') ?>"
                  data-monto="<?= number_format($datosCategoriaAnual['monto'], 2, '.', '') ?>"
                  data-ganancia="<?= number_format($datosCategoriaAnual['ganancia'], 2, '.', '') ?>"
                  aria-pressed="<?= $indiceCategoriaAnual === 0 ? 'true' : 'false' ?>"
                  aria-label="<?= htmlspecialchars($etiquetaAccesibleAnual, ENT_QUOTES, 'UTF-8') ?>">
                  <span class="ventas-categoria-nombre"><?= htmlspecialchars($datosCategoriaAnual['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
                  <span class="ventas-categoria-barra-fondo">
                    <span class="ventas-categoria-barra" style="width: <?= number_format($anchoBarraAnual, 2, '.', '') ?>%"></span>
                  </span>
                  <span class="ventas-categoria-cantidad">$<?= number_format($datosCategoriaAnual['monto'], 2, '.', ',') ?></span>
                </button>
              <?php } ?>
            </div>
          </div>
        <?php } ?>
      <?php } else { ?>
        <p class="ventas-semana-vacia">Aún no hay ventas registradas por año y categoría.</p>
      <?php } ?>
    </div>
    </div>

  </section>

  <div class="ventas-graficas-controles servicios-graficas-controles">
    <h2>Gráficos de servicios</h2>
    <button id="servicios-graficas-toggle" class="ventas-graficas-toggle" type="button"
      aria-expanded="false" aria-controls="servicios-graficas-contenedor"
      aria-label="Mostrar gráficos de servicios">
      <span aria-hidden="true"></span>
    </button>
  </div>
  <section id="servicios-graficas-contenedor" class="servicios-graficas-contenedor" hidden>
    <div class="servicios-semana-card">
      <div class="servicios-semana-encabezado">
        <div>
          <h2>Servicios por día</h2>
          <p>Del <?= $inicioSemana->format('d/m/Y') ?> al <?= $finSemana->modify('-1 day')->format('d/m/Y') ?></p>
        </div>
        <div class="servicios-semana-resumen">
          <span><?= number_format($totalServiciosSemana) ?> servicios</span>
          <strong>$<?= number_format($montoServiciosSemana, 2, '.', ',') ?></strong>
          <small>Ganancia: $<?= number_format($gananciaServiciosSemana, 2, '.', ',') ?></small>
        </div>
      </div>

      <p id="servicios-semana-seleccion" class="ventas-semana-seleccion" role="status" aria-live="polite">
        Haz clic en un día del gráfico para ver sus servicios.
      </p>

      <div class="ventas-semana-scroll">
        <svg class="servicios-semana-grafica" viewBox="0 0 <?= $anchoGrafica ?> <?= $altoGrafica ?>"
          role="group" aria-labelledby="servicios-semana-titulo servicios-semana-descripcion">
          <title id="servicios-semana-titulo">Servicios por día de la semana actual</title>
          <desc id="servicios-semana-descripcion">
            Gráfico de barras con la cantidad de servicios registrados cada día, de lunes a domingo.
          </desc>
          <defs>
            <linearGradient id="servicios-semana-gradiente" x1="0" x2="0" y1="0" y2="1">
              <stop offset="0%" stop-color="#56A77B" />
              <stop offset="100%" stop-color="#28734A" />
            </linearGradient>
          </defs>
          <?php for ($marcaServicios = 0; $marcaServicios <= 4; $marcaServicios++) {
              $valorEjeServicios = $pasoEjeServicios * $marcaServicios;
              $posicionYServicios = $margenSuperior + $altoPlano - ($altoPlano * $marcaServicios / 4);
              ?>
            <line class="ventas-semana-grid" x1="<?= $margenIzquierdo ?>"
              y1="<?= number_format($posicionYServicios, 2, '.', '') ?>"
              x2="<?= $anchoGrafica - $margenDerecho ?>"
              y2="<?= number_format($posicionYServicios, 2, '.', '') ?>" />
            <text class="ventas-semana-eje" x="<?= $margenIzquierdo - 10 ?>"
              y="<?= number_format($posicionYServicios + 4, 2, '.', '') ?>" text-anchor="end">
              <?= number_format($valorEjeServicios) ?>
            </text>
          <?php } ?>
          <line class="ventas-semana-eje-linea" x1="<?= $margenIzquierdo ?>"
            y1="<?= $margenSuperior + $altoPlano ?>"
            x2="<?= $anchoGrafica - $margenDerecho ?>"
            y2="<?= $margenSuperior + $altoPlano ?>" />
          <?php $indiceServicioDia = 0; ?>
          <?php foreach ($serviciosPorDia as $fechaServicio => $datosServicioDia) {
              $altoBarraServicio = $altoPlano * $datosServicioDia['cantidad'] / $maximoEjeServicios;
              $posicionXServicio = $margenIzquierdo + ($indiceServicioDia + 0.5) * $anchoDia - $anchoBarraServicios / 2;
              $posicionYServicio = $margenSuperior + $altoPlano - $altoBarraServicio;
              $etiquetaServicioDia = $datosServicioDia['nombre'] . ' ' . $datosServicioDia['fecha'];
              $etiquetaAccesibleServicio = $etiquetaServicioDia . ': ' . $datosServicioDia['cantidad'] .
                  ' servicios, monto $' . number_format($datosServicioDia['monto'], 2, '.', ',') .
                  ', ganancia $' . number_format($datosServicioDia['ganancia'], 2, '.', ',');
              ?>
            <g class="servicios-semana-dia-dato" data-dia="<?= htmlspecialchars($etiquetaServicioDia, ENT_QUOTES, 'UTF-8') ?>"
              data-cantidad="<?= $datosServicioDia['cantidad'] ?>"
              data-monto="<?= number_format($datosServicioDia['monto'], 2, '.', '') ?>"
              data-ganancia="<?= number_format($datosServicioDia['ganancia'], 2, '.', '') ?>"
              role="button" tabindex="0" aria-pressed="false"
              aria-label="<?= htmlspecialchars($etiquetaAccesibleServicio, ENT_QUOTES, 'UTF-8') ?>">
              <rect class="ventas-semana-area"
                x="<?= number_format($margenIzquierdo + $indiceServicioDia * $anchoDia, 2, '.', '') ?>"
                y="<?= $margenSuperior ?>" width="<?= number_format($anchoDia, 2, '.', '') ?>"
                height="<?= $altoPlano ?>" />
              <?php if ($datosServicioDia['cantidad'] > 0) { ?>
                <rect class="servicios-semana-barra"
                  x="<?= number_format($posicionXServicio, 2, '.', '') ?>"
                  y="<?= number_format($posicionYServicio, 2, '.', '') ?>"
                  width="<?= number_format($anchoBarraServicios, 2, '.', '') ?>"
                  height="<?= number_format($altoBarraServicio, 2, '.', '') ?>" rx="6">
                  <title><?= htmlspecialchars($etiquetaAccesibleServicio, ENT_QUOTES, 'UTF-8') ?></title>
                </rect>
              <?php } ?>
              <text class="ventas-semana-dia"
                x="<?= number_format($posicionXServicio + $anchoBarraServicios / 2, 2, '.', '') ?>"
                y="<?= $margenSuperior + $altoPlano + 24 ?>" text-anchor="middle">
                <tspan x="<?= number_format($posicionXServicio + $anchoBarraServicios / 2, 2, '.', '') ?>"><?= $datosServicioDia['nombre'] ?></tspan>
                <tspan class="ventas-semana-fecha"
                  x="<?= number_format($posicionXServicio + $anchoBarraServicios / 2, 2, '.', '') ?>"
                  dy="18"><?= $datosServicioDia['fecha'] ?></tspan>
              </text>
            </g>
          <?php $indiceServicioDia++; } ?>
        </svg>
      </div>
      <?php if ($totalServiciosSemana === 0) { ?>
        <p class="ventas-semana-vacia">Aún no hay servicios registrados esta semana.</p>
      <?php } ?>
    </div>

    <div class="servicios-semana-card servicios-mes-card">
      <div class="servicios-semana-encabezado">
        <div>
          <h2>Servicios por semana del mes</h2>
          <p><?= $inicioMes->format('m/Y') ?></p>
        </div>
        <div class="servicios-semana-resumen">
          <span><?= number_format($totalServiciosMes) ?> servicios</span>
          <strong>$<?= number_format($montoServiciosMes, 2, '.', ',') ?></strong>
          <small>Ganancia: $<?= number_format($gananciaServiciosMes, 2, '.', ',') ?></small>
        </div>
      </div>

      <p id="servicios-mes-seleccion" class="ventas-semana-seleccion" role="status" aria-live="polite">
        Haz clic en una semana para ver sus servicios.
      </p>

      <div class="ventas-semana-scroll">
        <svg class="servicios-mes-grafica" viewBox="0 0 <?= $anchoGraficaServiciosMes ?> <?= $altoGraficaServiciosMes ?>"
          role="group" aria-labelledby="servicios-mes-titulo servicios-mes-descripcion">
          <title id="servicios-mes-titulo">Servicios por semana de <?= $inicioMes->format('m/Y') ?></title>
          <desc id="servicios-mes-descripcion">
            Gráfico de barras con la cantidad de servicios registrados cada semana del mes.
          </desc>
          <defs>
            <linearGradient id="servicios-mes-gradiente" x1="0" x2="0" y1="0" y2="1">
              <stop offset="0%" stop-color="#56A77B" />
              <stop offset="100%" stop-color="#28734A" />
            </linearGradient>
          </defs>
          <?php for ($marcaServiciosMes = 0; $marcaServiciosMes <= 4; $marcaServiciosMes++) {
              $valorEjeServiciosMes = $pasoEjeServiciosMes * $marcaServiciosMes;
              $posicionYServiciosMes = $margenSuperiorServiciosMes + $altoPlanoServiciosMes -
                  ($altoPlanoServiciosMes * $marcaServiciosMes / 4);
              ?>
            <line class="ventas-semana-grid" x1="<?= $margenIzquierdoServiciosMes ?>"
              y1="<?= number_format($posicionYServiciosMes, 2, '.', '') ?>"
              x2="<?= $anchoGraficaServiciosMes - $margenDerechoServiciosMes ?>"
              y2="<?= number_format($posicionYServiciosMes, 2, '.', '') ?>" />
            <text class="ventas-semana-eje" x="<?= $margenIzquierdoServiciosMes - 10 ?>"
              y="<?= number_format($posicionYServiciosMes + 4, 2, '.', '') ?>" text-anchor="end">
              <?= number_format($valorEjeServiciosMes) ?>
            </text>
          <?php } ?>
          <line class="ventas-semana-eje-linea" x1="<?= $margenIzquierdoServiciosMes ?>"
            y1="<?= $margenSuperiorServiciosMes + $altoPlanoServiciosMes ?>"
            x2="<?= $anchoGraficaServiciosMes - $margenDerechoServiciosMes ?>"
            y2="<?= $margenSuperiorServiciosMes + $altoPlanoServiciosMes ?>" />
          <?php $indiceSemanaServiciosMes = 0; ?>
          <?php foreach ($serviciosPorSemanaMes as $fechaSemanaServicios => $datosSemanaServicios) {
              $altoBarraSemanaServicios = $altoPlanoServiciosMes *
                  $datosSemanaServicios['cantidad'] / $maximoEjeServiciosMes;
              $centroXServiciosMes = $margenIzquierdoServiciosMes +
                  ($indiceSemanaServiciosMes + 0.5) * $anchoSemanaServiciosMes;
              $posicionXServiciosMes = $centroXServiciosMes - $anchoBarraServiciosMes / 2;
              $posicionYServiciosMes = $margenSuperiorServiciosMes +
                  $altoPlanoServiciosMes - $altoBarraSemanaServicios;
              $etiquetaSemanaServicios = $datosSemanaServicios['nombre'] . ' (' .
                  $datosSemanaServicios['periodo'] . ')';
              $detalleSemanaServicios = $etiquetaSemanaServicios . ': ' .
                  $datosSemanaServicios['cantidad'] . ' servicios, monto $' .
                  number_format($datosSemanaServicios['monto'], 2, '.', ',') . ', ganancia $' .
                  number_format($datosSemanaServicios['ganancia'], 2, '.', ',');
              ?>
            <g class="servicios-mes-semana-dato"
              data-semana="<?= htmlspecialchars($etiquetaSemanaServicios, ENT_QUOTES, 'UTF-8') ?>"
              data-cantidad="<?= $datosSemanaServicios['cantidad'] ?>"
              data-monto="<?= number_format($datosSemanaServicios['monto'], 2, '.', '') ?>"
              data-ganancia="<?= number_format($datosSemanaServicios['ganancia'], 2, '.', '') ?>"
              role="button" tabindex="0" aria-pressed="false"
              aria-label="<?= htmlspecialchars($detalleSemanaServicios, ENT_QUOTES, 'UTF-8') ?>">
              <rect class="ventas-semana-area"
                x="<?= number_format($margenIzquierdoServiciosMes +
                    $indiceSemanaServiciosMes * $anchoSemanaServiciosMes, 2, '.', '') ?>"
                y="<?= $margenSuperiorServiciosMes ?>"
                width="<?= number_format($anchoSemanaServiciosMes, 2, '.', '') ?>"
                height="<?= $altoPlanoServiciosMes ?>" />
              <?php if ($datosSemanaServicios['cantidad'] > 0) { ?>
                <rect class="servicios-mes-barra"
                  x="<?= number_format($posicionXServiciosMes, 2, '.', '') ?>"
                  y="<?= number_format($posicionYServiciosMes, 2, '.', '') ?>"
                  width="<?= number_format($anchoBarraServiciosMes, 2, '.', '') ?>"
                  height="<?= number_format($altoBarraSemanaServicios, 2, '.', '') ?>" rx="6">
                  <title><?= htmlspecialchars($detalleSemanaServicios, ENT_QUOTES, 'UTF-8') ?></title>
                </rect>
              <?php } ?>
              <text class="ventas-semana-dia"
                x="<?= number_format($centroXServiciosMes, 2, '.', '') ?>"
                y="<?= $margenSuperiorServiciosMes + $altoPlanoServiciosMes + 22 ?>"
                text-anchor="middle">
                <tspan x="<?= number_format($centroXServiciosMes, 2, '.', '') ?>"><?= $datosSemanaServicios['nombre'] ?></tspan>
                <tspan class="ventas-semana-fecha"
                  x="<?= number_format($centroXServiciosMes, 2, '.', '') ?>"
                  dy="18"><?= $datosSemanaServicios['periodo'] ?></tspan>
              </text>
            </g>
          <?php $indiceSemanaServiciosMes++; } ?>
        </svg>
      </div>
      <?php if ($totalServiciosMes === 0) { ?>
        <p class="ventas-semana-vacia">Aún no hay servicios registrados este mes.</p>
      <?php } ?>
    </div>

    <div class="ventas-categoria-card servicios-total-anual-card">
      <div class="ventas-categoria-encabezado">
        <div>
          <h2>Totales de servicios por año</h2>
          <p>Montos y ganancias positivas registrados cada año.</p>
        </div>
      </div>
      <?php if (count($resumenAnualServicios) > 0) { ?>
        <?php
        $valoresAnualesServicios = array_merge(
          array_column($resumenAnualServicios, 'monto'),
          array_column($resumenAnualServicios, 'ganancia')
        );
        $maximoAnualServicios = max($valoresAnualesServicios);
        ?>
        <ul class="servicios-totales-anuales" aria-label="Comparación anual de montos y ganancias">
          <?php foreach ($resumenAnualServicios as $anioResumenServicio => $totalesAnualesServicio) {
              $anchoMontoAnualServicio = $maximoAnualServicios > 0
                ? ($totalesAnualesServicio['monto'] / $maximoAnualServicios) * 100
                : 0;
              $anchoGananciaAnualServicio = $maximoAnualServicios > 0
                ? ($totalesAnualesServicio['ganancia'] / $maximoAnualServicios) * 100
                : 0;
              ?>
            <li class="servicios-anual-fila">
              <strong class="servicios-anual-ano"><?= htmlspecialchars($anioResumenServicio, ENT_QUOTES, 'UTF-8') ?></strong>
              <div class="servicios-anual-indicadores">
                <div class="servicios-anual-indicador">
                  <div class="servicios-anual-indicador-encabezado">
                    <span>Monto total</span>
                    <strong>$<?= number_format($totalesAnualesServicio['monto'], 2, '.', ',') ?></strong>
                  </div>
                  <div class="servicios-anual-barra" aria-hidden="true">
                    <span style="width: <?= number_format($anchoMontoAnualServicio, 2, '.', '') ?>%"></span>
                  </div>
                </div>
                <div class="servicios-anual-indicador servicios-anual-indicador-ganancia">
                  <div class="servicios-anual-indicador-encabezado">
                    <span>Ganancia total</span>
                    <strong>$<?= number_format($totalesAnualesServicio['ganancia'], 2, '.', ',') ?></strong>
                  </div>
                  <div class="servicios-anual-barra" aria-hidden="true">
                    <span style="width: <?= number_format($anchoGananciaAnualServicio, 2, '.', '') ?>%"></span>
                  </div>
                </div>
              </div>
            </li>
          <?php } ?>
        </ul>
      <?php } else { ?>
        <p class="ventas-semana-vacia">Aún no hay servicios registrados por año.</p>
      <?php } ?>
    </div>

    <div class="servicios-semana-card servicios-tipo-mes-card">
      <div class="servicios-semana-encabezado servicios-tipo-encabezado">
        <div>
          <h2>Monto y ganancia por tipo de servicio</h2>
          <p>Selecciona un año y un mes para consultar sus tipos de servicio.</p>
        </div>
        <div class="servicios-tipo-filtros">
          <div class="servicios-tipo-filtro">
            <label for="servicios-tipo-anio">Año</label>
            <select id="servicios-tipo-anio" class="form-control" <?= count($aniosServiciosTipo) === 0 ? 'disabled' : '' ?>>
              <?php if (count($aniosServiciosTipo) === 0) { ?>
                <option value="<?= htmlspecialchars($anioServicioSeleccionado, ENT_QUOTES, 'UTF-8') ?>"
                  data-meses="">
                  <?= htmlspecialchars($anioServicioSeleccionado, ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php } ?>
              <?php foreach ($aniosServiciosTipo as $anioServicio) { ?>
                <option value="<?= htmlspecialchars($anioServicio, ENT_QUOTES, 'UTF-8') ?>"
                  data-meses="<?= htmlspecialchars(implode(',', $mesesDisponiblesPorAnioServicio[$anioServicio]), ENT_QUOTES, 'UTF-8') ?>"
                  <?= $anioServicio === $anioServicioSeleccionado ? 'selected' : '' ?>>
                  <?= htmlspecialchars($anioServicio, ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php } ?>
            </select>
          </div>
          <div class="servicios-tipo-filtro">
            <label for="servicios-tipo-mes">Mes</label>
            <select id="servicios-tipo-mes" class="form-control" <?= count($periodosServiciosTipo) === 0 ? 'disabled' : '' ?>>
              <?php foreach ($nombresMesesGrafica as $indiceMesGrafica => $nombreMesGrafica) {
                  $valorMesGrafica = sprintf('%02d', $indiceMesGrafica + 1);
                  ?>
                <option value="<?= $valorMesGrafica ?>"
                  <?= $valorMesGrafica === $mesServicioSeleccionado ? 'selected' : '' ?>>
                  <?= htmlspecialchars($nombreMesGrafica, ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php } ?>
            </select>
          </div>
        </div>
      </div>

      <?php if (count($periodosServiciosTipo) > 0) { ?>
        <?php foreach ($serviciosPorMesTipo as $periodoServicio => $datosMesServicio) {
            $maximoMontoTipoServicio = max(array_column($datosMesServicio['tipos'], 'monto'));
            $tipoServicioPrincipal = $datosMesServicio['tipos'][0];
            ?>
          <div class="servicios-tipo-vista" data-periodo="<?= htmlspecialchars($periodoServicio, ENT_QUOTES, 'UTF-8') ?>"
            <?= $periodoServicio === $periodoServicioSeleccionado ? '' : 'hidden' ?>>
            <div class="ventas-categoria-resumen servicios-tipo-totales">
              <div>
                <span>Monto total del mes</span>
                <strong>$<?= number_format($datosMesServicio['monto'], 2, '.', ',') ?></strong>
              </div>
              <div>
                <span>Ganancia total del mes</span>
                <strong>$<?= number_format($datosMesServicio['ganancia'], 2, '.', ',') ?></strong>
              </div>
            </div>

            <div class="ventas-categoria-resumen servicios-tipo-detalle" aria-live="polite">
              <div>
                <span>Tipo de servicio</span>
                <strong><?= htmlspecialchars($tipoServicioPrincipal['nombre'], ENT_QUOTES, 'UTF-8') ?></strong>
              </div>
              <div>
                <span>Servicios</span>
                <strong><?= number_format($tipoServicioPrincipal['cantidad']) ?></strong>
              </div>
              <div>
                <span>Monto total</span>
                <strong>$<?= number_format($tipoServicioPrincipal['monto'], 2, '.', ',') ?></strong>
              </div>
              <div>
                <span>Ganancia</span>
                <strong>$<?= number_format($tipoServicioPrincipal['ganancia'], 2, '.', ',') ?></strong>
              </div>
            </div>

            <div class="ventas-categoria-grafica" role="group"
              aria-label="Tipos de servicio de <?= htmlspecialchars($datosMesServicio['nombre'], ENT_QUOTES, 'UTF-8') ?>">
              <?php foreach ($datosMesServicio['tipos'] as $indiceTipoServicio => $datosTipoServicio) {
                  $anchoBarraTipoServicio = $maximoMontoTipoServicio > 0
                      ? $datosTipoServicio['monto'] / $maximoMontoTipoServicio * 100
                      : 0;
                  $etiquetaTipoServicio = $datosTipoServicio['nombre'] . ': ' .
                      number_format($datosTipoServicio['cantidad']) . ' servicios, monto $' .
                      number_format($datosTipoServicio['monto'], 2, '.', ',') . ', ganancia $' .
                      number_format($datosTipoServicio['ganancia'], 2, '.', ',');
                  ?>
                <button type="button" class="ventas-categoria-fila servicios-tipo-fila"
                  data-tipo="<?= htmlspecialchars($datosTipoServicio['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                  data-cantidad="<?= $datosTipoServicio['cantidad'] ?>"
                  data-monto="<?= number_format($datosTipoServicio['monto'], 2, '.', '') ?>"
                  data-ganancia="<?= number_format($datosTipoServicio['ganancia'], 2, '.', '') ?>"
                  aria-pressed="<?= $indiceTipoServicio === 0 ? 'true' : 'false' ?>"
                  aria-label="<?= htmlspecialchars($etiquetaTipoServicio, ENT_QUOTES, 'UTF-8') ?>">
                  <span class="ventas-categoria-nombre"><?= htmlspecialchars($datosTipoServicio['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
                  <span class="ventas-categoria-barra-fondo">
                    <span class="ventas-categoria-barra"
                      style="width: <?= number_format($anchoBarraTipoServicio, 2, '.', '') ?>%"></span>
                  </span>
                  <span class="ventas-categoria-cantidad">$<?= number_format($datosTipoServicio['monto'], 2, '.', ',') ?></span>
                </button>
              <?php } ?>
            </div>
          </div>
        <?php } ?>
      <?php } else { ?>
        <p class="ventas-semana-vacia">Aún no hay servicios registrados por tipo y mes.</p>
      <?php } ?>
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

    const filasCategoria = document.querySelectorAll('.ventas-categoria-card .ventas-categoria-fila');
    const detalleCategoria = {
      nombre: document.getElementById('ventas-categoria-nombre'),
      unidades: document.getElementById('ventas-categoria-unidades'),
      monto: document.getElementById('ventas-categoria-monto'),
      ganancia: document.getElementById('ventas-categoria-ganancia')
    };

    filasCategoria.forEach(function (fila) {
      fila.addEventListener('click', function () {
        detalleCategoria.nombre.textContent = fila.dataset.categoria;
        detalleCategoria.unidades.textContent = Number(fila.dataset.unidades).toLocaleString('es-MX', {
          maximumFractionDigits: 2
        });
        detalleCategoria.monto.textContent = '$' + Number(fila.dataset.monto).toLocaleString('es-MX', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        });
        detalleCategoria.ganancia.textContent = '$' + Number(fila.dataset.ganancia).toLocaleString('es-MX', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        });

        filasCategoria.forEach(function (otraFila) {
          otraFila.setAttribute('aria-pressed', otraFila === fila ? 'true' : 'false');
        });
      });
    });

    const selectorAnioVentas = document.getElementById('ventas-anual-anio');
    const vistasVentasAnuales = document.querySelectorAll('.ventas-anual-vista');

    selectorAnioVentas.addEventListener('change', function () {
      vistasVentasAnuales.forEach(function (vista) {
        vista.hidden = vista.dataset.anio !== selectorAnioVentas.value;
      });
    });

    vistasVentasAnuales.forEach(function (vista) {
      const filasCategoriaAnual = vista.querySelectorAll('.ventas-anual-categoria-fila');
      const camposDetalleAnual = vista.querySelectorAll('.ventas-anual-detalle strong');

      filasCategoriaAnual.forEach(function (fila) {
        fila.addEventListener('click', function () {
          camposDetalleAnual[0].textContent = fila.dataset.categoria;
          camposDetalleAnual[1].textContent = Number(fila.dataset.unidades).toLocaleString('es-MX', {
            maximumFractionDigits: 2
          });
          camposDetalleAnual[2].textContent = '$' + Number(fila.dataset.monto).toLocaleString('es-MX', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          });
          camposDetalleAnual[3].textContent = '$' + Number(fila.dataset.ganancia).toLocaleString('es-MX', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          });

          filasCategoriaAnual.forEach(function (otraFila) {
            otraFila.setAttribute('aria-pressed', otraFila === fila ? 'true' : 'false');
          });
        });
      });
    });

    const graficaServiciosSemana = document.querySelector('.servicios-semana-grafica');
    const seleccionServiciosSemana = document.getElementById('servicios-semana-seleccion');

    function mostrarServiciosDia(elementoDiaServicio) {
      const cantidad = Number(elementoDiaServicio.dataset.cantidad);
      const monto = Number(elementoDiaServicio.dataset.monto).toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
      const ganancia = Number(elementoDiaServicio.dataset.ganancia).toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
      const serviciosTexto = cantidad === 1 ? '1 servicio' : cantidad + ' servicios';

      seleccionServiciosSemana.textContent = elementoDiaServicio.dataset.dia + ': ' +
        serviciosTexto + ', monto $' + monto + ', ganancia $' + ganancia;
      graficaServiciosSemana.querySelectorAll('.servicios-semana-dia-dato').forEach(function (dia) {
        dia.setAttribute('aria-pressed', dia === elementoDiaServicio ? 'true' : 'false');
      });
    }

    graficaServiciosSemana.addEventListener('click', function (evento) {
      const elementoDiaServicio = evento.target.closest('.servicios-semana-dia-dato');
      if (elementoDiaServicio) {
        mostrarServiciosDia(elementoDiaServicio);
      }
    });

    graficaServiciosSemana.addEventListener('keydown', function (evento) {
      if (evento.key === 'Enter' || evento.key === ' ') {
        const elementoDiaServicio = evento.target.closest('.servicios-semana-dia-dato');
        if (elementoDiaServicio) {
          evento.preventDefault();
          mostrarServiciosDia(elementoDiaServicio);
        }
      }
    });

    const graficaServiciosMes = document.querySelector('.servicios-mes-grafica');
    const seleccionServiciosMes = document.getElementById('servicios-mes-seleccion');

    function mostrarServiciosSemanaMes(elementoSemanaServicio) {
      const cantidad = Number(elementoSemanaServicio.dataset.cantidad);
      const monto = Number(elementoSemanaServicio.dataset.monto).toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
      const ganancia = Number(elementoSemanaServicio.dataset.ganancia).toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
      const serviciosTexto = cantidad === 1 ? '1 servicio' : cantidad + ' servicios';

      seleccionServiciosMes.textContent = elementoSemanaServicio.dataset.semana + ': ' +
        serviciosTexto + ', monto $' + monto + ', ganancia $' + ganancia;
      graficaServiciosMes.querySelectorAll('.servicios-mes-semana-dato').forEach(function (semana) {
        semana.setAttribute('aria-pressed', semana === elementoSemanaServicio ? 'true' : 'false');
      });
    }

    graficaServiciosMes.addEventListener('click', function (evento) {
      const elementoSemanaServicio = evento.target.closest('.servicios-mes-semana-dato');
      if (elementoSemanaServicio) {
        mostrarServiciosSemanaMes(elementoSemanaServicio);
      }
    });

    graficaServiciosMes.addEventListener('keydown', function (evento) {
      if (evento.key === 'Enter' || evento.key === ' ') {
        const elementoSemanaServicio = evento.target.closest('.servicios-mes-semana-dato');
        if (elementoSemanaServicio) {
          evento.preventDefault();
          mostrarServiciosSemanaMes(elementoSemanaServicio);
        }
      }
    });

    const selectorAnioServicios = document.getElementById('servicios-tipo-anio');
    const selectorMesServicios = document.getElementById('servicios-tipo-mes');
    const vistasServiciosPorTipo = document.querySelectorAll('.servicios-tipo-vista');

    function mostrarPeriodoServiciosPorTipo() {
      const periodoSeleccionado = selectorAnioServicios.value + '-' + selectorMesServicios.value;
      vistasServiciosPorTipo.forEach(function (vista) {
        vista.hidden = vista.dataset.periodo !== periodoSeleccionado;
      });
    }

    function actualizarMesesServiciosPorTipo() {
      const opcionAnioSeleccionado = selectorAnioServicios.options[selectorAnioServicios.selectedIndex];
      const mesesDisponibles = (opcionAnioSeleccionado.dataset.meses || '').split(',');
      let primerMesDisponible = '';

      Array.from(selectorMesServicios.options).forEach(function (opcionMes) {
        opcionMes.disabled = mesesDisponibles.indexOf(opcionMes.value) === -1;
        if (!opcionMes.disabled && primerMesDisponible === '') {
          primerMesDisponible = opcionMes.value;
        }
      });

      if (selectorMesServicios.options[selectorMesServicios.selectedIndex].disabled) {
        selectorMesServicios.value = primerMesDisponible;
      }
      selectorMesServicios.disabled = primerMesDisponible === '';
      mostrarPeriodoServiciosPorTipo();
    }

    selectorAnioServicios.addEventListener('change', actualizarMesesServiciosPorTipo);
    selectorMesServicios.addEventListener('change', mostrarPeriodoServiciosPorTipo);
    actualizarMesesServiciosPorTipo();

    vistasServiciosPorTipo.forEach(function (vista) {
      const filasTipoServicio = vista.querySelectorAll('.servicios-tipo-fila');
      const detalleTipoServicio = vista.querySelectorAll('.servicios-tipo-detalle strong');

      filasTipoServicio.forEach(function (fila) {
        fila.addEventListener('click', function () {
          detalleTipoServicio[0].textContent = fila.dataset.tipo;
          detalleTipoServicio[1].textContent = Number(fila.dataset.cantidad).toLocaleString('es-MX');
          detalleTipoServicio[2].textContent = '$' + Number(fila.dataset.monto).toLocaleString('es-MX', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          });
          detalleTipoServicio[3].textContent = '$' + Number(fila.dataset.ganancia).toLocaleString('es-MX', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          });

          filasTipoServicio.forEach(function (otraFila) {
            otraFila.setAttribute('aria-pressed', otraFila === fila ? 'true' : 'false');
          });
        });
      });
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

    const botonServiciosGraficas = document.getElementById('servicios-graficas-toggle');
    const contenedorServiciosGraficas = document.getElementById('servicios-graficas-contenedor');

    botonServiciosGraficas.addEventListener('click', function () {
      const mostrarServicios = botonServiciosGraficas.getAttribute('aria-expanded') !== 'true';
      botonServiciosGraficas.setAttribute('aria-expanded', String(mostrarServicios));
      botonServiciosGraficas.setAttribute(
        'aria-label',
        mostrarServicios ? 'Ocultar gráficos de servicios' : 'Mostrar gráficos de servicios'
      );
      contenedorServiciosGraficas.hidden = !mostrarServicios;
    });
  </script>
<?php } ?>

<?php if (!$mostrarGraficosEnReportes) { ?>
  <?php include_once 'footer.php'; ?>
</body>
<?php } ?>