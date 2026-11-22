<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
require_once __DIR__ . '/cotizaciones_db.php';
require_once __DIR__ . '/dompdf/autoload.inc.php';

use Dompdf\Dompdf;

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
    'SELECT v.id_venta, v.folio AS folio_venta, v.fecha_venta, v.total, v.total_abonado,
            v.saldo, v.estado, c.*
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

$logoContenido = is_file(__DIR__ . '/img/logos2.jpg') ? file_get_contents(__DIR__ . '/img/logos2.jpg') : false;
$logoData = $logoContenido === false ? '' : 'data:image/jpeg;base64,' . base64_encode($logoContenido);
$moneda = escaparVentaPdf($venta['moneda']);

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Venta <?= escaparVentaPdf($venta['folio_venta']) ?></title>
  <style>
    @page { margin: 30px 34px 50px; }
    body { color: #24384d; font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; line-height: 1.4; }
    .header { margin-bottom: 12px; padding: 8px 0 12px; border-bottom: 2px solid #333; color: #222; }
    .header table { width: 100%; }
    .logo { width: 115px; padding: 4px; background: #fff; }
    h1 { margin: 0; color: #222; font-size: 21px; }
    .folio { margin-top: 4px; color: #555; }
    .columns { width: 100%; margin-bottom: 12px; border-spacing: 8px 0; }
    .card { padding: 9px 12px; border: 1px solid #ccc; vertical-align: top; }
    .label { color: #087f8b; font-size: 8px; font-weight: bold; text-transform: uppercase; }
    .metadata td { padding: 3px; border-bottom: 1px solid #dfe7ee; }
    h2 { margin: 13px 0 6px; padding: 6px 0; border-bottom: 1px solid #777; color: #222; font-size: 10px; }
    .items { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .items th { padding: 7px 5px; border-bottom: 1px solid #555; color: #222; text-align: left; }
    .items td { padding: 7px 5px; border-bottom: 1px solid #dfe7ee; vertical-align: top; }
    .items tr { page-break-inside: avoid; }
    .items .right { text-align: right; }
    .summary { width: 48%; margin: 10px 0 0 auto; border-collapse: collapse; }
    .summary td { padding: 6px 8px; border-bottom: 1px solid #dfe7ee; }
    .summary .amount { text-align: right; font-weight: bold; }
    .summary .due td { border-top: 1px solid #555; color: #222; font-size: 11px; font-weight: bold; }
    .payments { width: 100%; border-collapse: collapse; }
    .payments td, .payments th { padding: 6px; border: 1px solid #dfe7ee; text-align: left; }
    .payments th { color: #222; border-bottom: 1px solid #777; }
    .footer { position: fixed; right: 0; bottom: -30px; left: 0; padding-top: 6px; border-top: 1px solid #cbd7e1; color: #53677c; font-size: 8px; text-align: right; }
  </style>
</head>
<body>
  <div class="header">
    <table><tr>
      <td style="width:28%"><?php if ($logoData !== '') { ?><img class="logo" src="<?= $logoData ?>" alt="Garesp"><?php } else { ?><strong>GARESP</strong><?php } ?></td>
      <td><h1>Venta</h1><div class="folio"><?= escaparVentaPdf($venta['folio_venta']) ?> · Cotización <?= escaparVentaPdf($venta['folio']) ?></div></td>
    </tr></table>
  </div>

  <table class="columns">
    <tr>
      <td class="card" style="width:50%">
        <div class="label">Cliente</div>
        <strong><?= escaparVentaPdf($venta['cliente_nombre']) ?></strong><br>
        <?= escaparVentaPdf($venta['cliente_empresa']) ?><br>
        <?= escaparVentaPdf($venta['cliente_email']) ?><br>
        <?= escaparVentaPdf($venta['cliente_telefono']) ?>
      </td>
      <td class="card" style="width:50%">
        <table class="metadata" style="width:100%">
          <tr><td class="label">Fecha</td><td><?= escaparVentaPdf(date('d/m/Y', strtotime($venta['fecha_venta']))) ?></td></tr>
          <tr><td class="label">Estado</td><td><?= escaparVentaPdf($venta['estado']) ?></td></tr>
          <tr><td class="label">Atendió</td><td><?= escaparVentaPdf($venta['creado_por']) ?></td></tr>
        </table>
      </td>
    </tr>
  </table>

  <h2>Productos y servicios</h2>
  <table class="items">
    <thead><tr><th style="width:8%">Cantidad</th><th style="width:42%">Concepto</th><th style="width:20%">Precio unitario</th><th style="width:12%">Descuento</th><th style="width:18%">Importe</th></tr></thead>
    <tbody>
      <?php foreach ($detalles as $detalle) { ?>
      <tr>
        <td><?= number_format((float) $detalle['cantidad'], 2) ?></td>
        <td><strong><?= escaparVentaPdf($detalle['descripcion']) ?></strong><br><?= escaparVentaPdf(trim($detalle['marca'] . ' ' . $detalle['modelo'] . ' ' . $detalle['sku'])) ?></td>
        <td class="right"><?= $moneda ?> $<?= number_format((float) $detalle['precio_unitario'], 2) ?></td>
        <td class="right"><?= number_format((float) $detalle['descuento_porcentaje'], 2) ?>%</td>
        <td class="right"><?= $moneda ?> $<?= number_format((float) $detalle['total'], 2) ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>

  <table class="summary">
    <tr><td>Total de venta</td><td class="amount"><?= $moneda ?> $<?= number_format((float) $venta['total'], 2) ?></td></tr>
    <tr><td>Total abonado</td><td class="amount"><?= $moneda ?> $<?= number_format((float) $venta['total_abonado'], 2) ?></td></tr>
    <tr class="due"><td>Saldo por cobrar</td><td class="amount"><?= $moneda ?> $<?= number_format((float) $venta['saldo'], 2) ?></td></tr>
  </table>

  <h2>Abonos registrados</h2>
  <table class="payments">
    <thead><tr><th>Fecha</th><th>Recibió</th><th>Concepto</th><th style="text-align:right">Monto</th></tr></thead>
    <tbody>
      <?php if (count($abonos) === 0) { ?><tr><td colspan="4">Sin abonos registrados.</td></tr><?php } ?>
      <?php foreach ($abonos as $abono) { ?>
      <tr>
        <td><?= escaparVentaPdf(date('d/m/Y H:i', strtotime($abono['fecha_abono']))) ?></td>
        <td><?= escaparVentaPdf($abono['recibido_por']) ?></td>
        <td><?= escaparVentaPdf($abono['notas'] !== '' ? $abono['notas'] : 'Abono') ?></td>
        <td style="text-align:right"><?= $moneda ?> $<?= number_format((float) $abono['monto'], 2) ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
  <div class="footer">Garesp Servicio Informático · <?= escaparVentaPdf($venta['folio_venta']) ?> · <?= date('d/m/Y H:i') ?></div>
</body>
</html>
<?php
$html = ob_get_clean();
$dompdf = new Dompdf();
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();
$dompdf->stream('venta-' . preg_replace('/[^A-Za-z0-9-]/', '-', $venta['folio_venta']) . '.pdf', array('Attachment' => false));
