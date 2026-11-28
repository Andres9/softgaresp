<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
require_once __DIR__ . '/cotizaciones_db.php';
require_once __DIR__ . '/dompdf/autoload.inc.php';
require_once __DIR__ . '/comercial_pdf.php';

use Dompdf\Dompdf;

date_default_timezone_set('America/Mexico_City');

function escaparVentaPdf($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

$idVenta = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : false;
if ($idVenta === false || $idVenta < 1) {
    http_response_code(400);
    exit('La venta solicitada no es válida.');
}

$consultaVenta = mysqli_prepare(
    $conn,
    'SELECT v.id_venta, v.folio AS folio_venta, v.fecha_venta, v.total AS total_venta,
            v.total_abonado, v.saldo, v.estado AS estado_venta, v.creado_por AS vendido_por, c.*
     FROM cotizacion_ventas v
     INNER JOIN cotizaciones c ON c.id_cotizacion = v.id_cotizacion
     WHERE v.id_venta = ? LIMIT 1'
);
if (!$consultaVenta) {
    error_log('No fue posible preparar el PDF de venta: ' . mysqli_error($conn));
    http_response_code(500);
    exit('No se pudo generar el PDF de venta.');
}
$consultaVenta->bind_param('i', $idVenta);
if (!$consultaVenta->execute()) {
    error_log('No fue posible consultar el PDF de venta: ' . $consultaVenta->error);
    $consultaVenta->close();
    http_response_code(500);
    exit('No se pudo generar el PDF de venta.');
}
$venta = $consultaVenta->get_result()->fetch_assoc();
$consultaVenta->close();
if (!$venta) {
    http_response_code(404);
    exit('No se encontró la venta solicitada.');
}

$consultaDetalles = mysqli_prepare(
    $conn,
    'SELECT * FROM cotizacion_detalles WHERE id_cotizacion = ? ORDER BY orden, id_detalle'
);
if (!$consultaDetalles) {
    error_log('No fue posible preparar los productos del PDF de venta: ' . mysqli_error($conn));
    http_response_code(500);
    exit('No se pudo generar el PDF de venta.');
}
$consultaDetalles->bind_param('i', $venta['id_cotizacion']);
if (!$consultaDetalles->execute()) {
    error_log('No fue posible consultar los productos del PDF de venta: ' . $consultaDetalles->error);
    $consultaDetalles->close();
    http_response_code(500);
    exit('No se pudo generar el PDF de venta.');
}
$detalles = $consultaDetalles->get_result()->fetch_all(MYSQLI_ASSOC);
$consultaDetalles->close();

$consultaAbonos = mysqli_prepare(
    $conn,
    'SELECT fecha_abono, monto, recibido_por, notas
     FROM cotizacion_venta_abonos WHERE id_venta = ? ORDER BY fecha_abono, id_abono'
);
if (!$consultaAbonos) {
    error_log('No fue posible preparar los abonos para el PDF de venta: ' . mysqli_error($conn));
    http_response_code(500);
    exit('No se pudo generar el PDF de venta.');
}
$consultaAbonos->bind_param('i', $idVenta);
if (!$consultaAbonos->execute()) {
    error_log('No fue posible consultar los abonos para el PDF de venta: ' . $consultaAbonos->error);
    $consultaAbonos->close();
    http_response_code(500);
    exit('No se pudo generar el PDF de venta.');
}
$abonos = $consultaAbonos->get_result()->fetch_all(MYSQLI_ASSOC);
$consultaAbonos->close();

if (!function_exists('imagecreatefrompng')) {
    error_log('La extensión GD de PHP es necesaria para mostrar imágenes PNG en el PDF de venta.');
    http_response_code(500);
    exit('No se pudo generar el PDF de venta: falta la extensión GD de PHP.');
}
$logoContenido = @file_get_contents(__DIR__ . '/img/logosinfondo.png');
$estilosPdf = @file_get_contents(__DIR__ . '/css/comercial_pdf.css');
if ($logoContenido === false || $estilosPdf === false) {
    error_log('No fue posible leer los recursos del PDF de venta.');
    http_response_code(500);
    exit('No se pudo generar el PDF de venta.');
}
$logoData = 'data:image/png;base64,' . base64_encode($logoContenido);
$moneda = escaparVentaPdf($venta['moneda']);

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Venta <?= escaparVentaPdf($venta['folio_venta']) ?></title>
  <style>
<?= $estilosPdf ?>
  </style>
</head>
<body>
  <div class="top-stripe"></div>
  <div class="top-accent"></div>
  <div class="footer">Garesp Servicio Informático &middot; Venta <?= escaparVentaPdf($venta['folio_venta']) ?> &middot; Generada el <?= date('d/m/Y H:i') ?></div>
  <table class="header-table">
    <tr>
      <td>
        <img class="brand-logo" src="<?= $logoData ?>" alt="GARESP Servicio Informático">
        <div class="brand-tagline">Soluciones tecnológicas, equipamiento y soporte especializado</div>
      </td>
      <td class="doc-box">
        <div class="badge">Comprobante de venta</div>
        <div class="doc-title">Venta</div>
        <div class="doc-folio">Folio: <?= escaparVentaPdf($venta['folio_venta']) ?></div>
      </td>
    </tr>
  </table>

  <table class="cards-table">
    <tr>
      <td class="card-cell card-client">
        <div class="card-label">Cliente</div>
        <div class="client-name"><?= escaparVentaPdf($venta['cliente_nombre']) ?></div>
        <?php if (trim((string) $venta['cliente_empresa']) !== '') { ?><div class="client-detail"><?= escaparVentaPdf($venta['cliente_empresa']) ?></div><?php } ?>
        <?php if (trim((string) $venta['cliente_email']) !== '') { ?><div class="client-detail"><?= escaparVentaPdf($venta['cliente_email']) ?></div><?php } ?>
        <?php if (trim((string) $venta['cliente_telefono']) !== '') { ?><div class="client-detail"><?= escaparVentaPdf($venta['cliente_telefono']) ?></div><?php } ?>
        <?php if (trim((string) $venta['cliente_rfc']) !== '') { ?><div class="client-detail">RFC: <?= escaparVentaPdf($venta['cliente_rfc']) ?></div><?php } ?>
        <?php if (trim((string) $venta['cliente_direccion']) !== '') { ?><div class="client-detail"><?= nl2br(escaparVentaPdf($venta['cliente_direccion'])) ?></div><?php } ?>
      </td>
      <td class="card-spacer"></td>
      <td class="card-cell card-meta">
        <div class="card-label">Datos de la venta</div>
        <table class="meta-inner">
          <tr><td class="lbl">Cotización:</td><td class="val"><?= escaparVentaPdf($venta['folio']) ?></td></tr>
          <tr><td class="lbl">Fecha:</td><td class="val"><?= escaparVentaPdf(date('d/m/Y', strtotime($venta['fecha_venta']))) ?></td></tr>
          <tr><td class="lbl">Estado:</td><td class="val"><?= escaparVentaPdf($venta['estado_venta']) ?></td></tr>
          <tr><td class="lbl">Moneda:</td><td class="val"><?= $moneda ?></td></tr>
          <tr><td class="lbl">Atendió:</td><td class="val"><?= escaparVentaPdf($venta['vendido_por']) ?></td></tr>
        </table>
      </td>
    </tr>
  </table>

  <div class="section-heading">Detalle de productos y servicios</div>
  <table class="items-table">
    <thead>
      <tr>
        <th class="center" style="width:7%">Cant.</th>
        <th class="center" style="width:13%">Modelo</th>
        <th style="width:32%">Concepto</th>
        <th class="right" style="width:16%">P. Unitario</th>
        <th class="right" style="width:8%">Desc.</th>
        <th class="right" style="width:8%">Imp.</th>
        <th class="right" style="width:16%">Importe</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($detalles as $detalle) {
          $imagenProducto = imagenComercialPdf($detalle['imagen']);
          $identificadores = array_filter(
              array($detalle['tipo_producto'], $detalle['marca'], $detalle['modelo'], $detalle['sku']),
              function ($valor) {
                  return trim((string) $valor) !== '';
              }
          );
          ?>
      <tr>
        <td class="center"><strong><?= number_format((float) $detalle['cantidad'], 2) ?></strong></td>
        <td class="center">
          <?php if ($imagenProducto !== '') { ?><img class="product-image" src="<?= $imagenProducto ?>" alt="Producto"><?php } ?>
          <?php if ($imagenProducto === '' && $detalle['imagen'] !== '') { ?><div class="item-sub">Imagen no disponible</div><?php } ?>
          <span class="model-pill"><?= escaparVentaPdf(trim((string) $detalle['modelo']) !== '' ? $detalle['modelo'] : '—') ?></span>
        </td>
        <td>
          <span class="item-title"><?= escaparVentaPdf($detalle['descripcion']) ?></span>
          <?php if (count($identificadores) > 0) { ?><span class="item-sub"><?= escaparVentaPdf(implode(' · ', $identificadores)) ?></span><?php } ?>
          <?php if (trim((string) $detalle['especificaciones']) !== '') { ?><div class="item-sub"><?= nl2br(escaparVentaPdf($detalle['especificaciones'])) ?></div><?php } ?>
          <?php if (trim((string) $detalle['garantia']) !== '') { ?><div class="item-sub">Garantía: <?= escaparVentaPdf($detalle['garantia']) ?></div><?php } ?>
        </td>
        <td class="right"><?= $moneda ?> $<?= number_format((float) $detalle['precio_unitario'], 2) ?></td>
        <td class="right"><?= number_format((float) $detalle['descuento_porcentaje'], 2) ?>%</td>
        <td class="right"><?= number_format((float) $detalle['impuesto_porcentaje'], 2) ?>%</td>
        <td class="right"><strong><?= $moneda ?> $<?= number_format((float) $detalle['total'], 2) ?></strong></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>

  <table class="summary-wrapper">
    <tr>
      <td class="summary-notes">
        <?php if (trim((string) $venta['notas']) !== '') { ?>
          <div class="note-box"><strong>Observaciones comerciales</strong><?= nl2br(escaparVentaPdf($venta['notas'])) ?></div>
        <?php } ?>
      </td>
      <td class="summary-box-cell">
        <table class="totals-table">
          <tr><td class="t-lbl">Total de venta</td><td class="t-val"><?= $moneda ?> $<?= number_format((float) $venta['total_venta'], 2) ?></td></tr>
          <tr><td class="t-lbl">Total abonado</td><td class="t-val"><?= $moneda ?> $<?= number_format((float) $venta['total_abonado'], 2) ?></td></tr>
          <tr class="grand-row"><td>Saldo por cobrar</td><td style="text-align:right"><?= $moneda ?> $<?= number_format((float) $venta['saldo'], 2) ?></td></tr>
        </table>
      </td>
    </tr>
  </table>

  <div class="section-heading">Abonos registrados</div>
  <table class="items-table">
    <thead><tr><th style="width:23%">Fecha</th><th style="width:23%">Recibió</th><th style="width:34%">Concepto</th><th class="right" style="width:20%">Monto</th></tr></thead>
    <tbody>
      <?php if (count($abonos) === 0) { ?><tr><td colspan="4">Sin abonos registrados.</td></tr><?php } ?>
      <?php foreach ($abonos as $abono) { ?>
      <tr>
        <td><?= escaparVentaPdf(date('d/m/Y H:i', strtotime($abono['fecha_abono']))) ?></td>
        <td><?= escaparVentaPdf($abono['recibido_por']) ?></td>
        <td><?= escaparVentaPdf($abono['notas'] !== '' ? $abono['notas'] : 'Abono') ?></td>
        <td class="right"><?= $moneda ?> $<?= number_format((float) $abono['monto'], 2) ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</body>
</html>
<?php
$html = ob_get_clean();
$dompdf = new Dompdf();
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();
$font = $dompdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
$dompdf->getCanvas()->page_text(34, 770, 'Página {PAGE_NUM} de {PAGE_COUNT}', $font, 7, array(0.32, 0.40, 0.48));
$dompdf->stream('venta-' . preg_replace('/[^A-Za-z0-9-]/', '-', $venta['folio_venta']) . '.pdf', array('Attachment' => false));
