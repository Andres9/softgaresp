<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
require_once __DIR__ . '/pedidos_db.php';
require_once __DIR__ . '/dompdf/autoload.inc.php';

use Dompdf\Dompdf;

function escaparPedidoPdf($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function imagenPedidoPdf($nombre)
{
    $nombre = imagenCatalogoPedidoDisponible($nombre);
    if ($nombre === '') return '';
    $ruta = __DIR__ . '/uploads/catalogo_pixal/' . $nombre;
    $contenido = file_get_contents($ruta);
    if ($contenido === false) {
        return '';
    }
    $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
    $tipo = $extension === 'png' ? 'image/png' : 'image/jpeg';
    return 'data:' . $tipo . ';base64,' . base64_encode($contenido);
}

$idPedido = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : false;
if ($idPedido === false || $idPedido < 1) {
    http_response_code(400);
    exit('El pedido solicitado no es válido.');
}

$consultaPedido = mysqli_prepare($conn, 'SELECT * FROM pedidos WHERE id_pedido = ? LIMIT 1');
if (!$consultaPedido) {
    error_log('No fue posible preparar el PDF de pedido: ' . mysqli_error($conn));
    http_response_code(500);
    exit('No se pudo generar el PDF de pedido.');
}
$consultaPedido->bind_param('i', $idPedido);
if (!$consultaPedido->execute()) {
    error_log('No fue posible consultar el PDF de pedido: ' . $consultaPedido->error);
    $consultaPedido->close();
    http_response_code(500);
    exit('No se pudo generar el PDF de pedido.');
}
$pedido = $consultaPedido->get_result()->fetch_assoc();
$consultaPedido->close();
if (!$pedido) {
    http_response_code(404);
    exit('No se encontró el pedido solicitado.');
}

$consultaDetalles = mysqli_prepare(
    $conn,
    'SELECT * FROM pedido_detalles WHERE id_pedido = ? ORDER BY orden, id_detalle'
);
if (!$consultaDetalles) {
    error_log('No fue posible preparar los detalles de pedido PDF: ' . mysqli_error($conn));
    http_response_code(500);
    exit('No se pudo generar el PDF de pedido.');
}
$consultaDetalles->bind_param('i', $idPedido);
if (!$consultaDetalles->execute()) {
    error_log('No fue posible consultar los detalles de pedido PDF: ' . $consultaDetalles->error);
    $consultaDetalles->close();
    http_response_code(500);
    exit('No se pudo generar el PDF de pedido.');
}
$detalles = $consultaDetalles->get_result()->fetch_all(MYSQLI_ASSOC);
$consultaDetalles->close();
$gananciaTotalPedido = -(float) $pedido['envio'];
foreach ($detalles as $detalleGanancia) {
    $gananciaTotalPedido += (float) $detalleGanancia['ganancia_total'];
}
$gananciaTotalPedido = round($gananciaTotalPedido, 2);

$logoContenido = is_file(__DIR__ . '/img/logos2.jpg') ? file_get_contents(__DIR__ . '/img/logos2.jpg') : false;
$logoData = $logoContenido === false ? '' : 'data:image/jpeg;base64,' . base64_encode($logoContenido);

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Pedido <?= escaparPedidoPdf($pedido['folio']) ?></title>
  <style>
    @page { margin: 30px 30px 48px; }
    body { color: #24384d; font-family: DejaVu Sans, Arial, sans-serif; font-size: 8px; line-height: 1.4; }
    .header { margin-bottom: 12px; padding: 8px 0 12px; border-bottom: 2px solid #333; color: #222; }
    .header table { width: 100%; }
    .logo { width: 115px; padding: 4px; background: #fff; }
    h1 { margin: 0; color: #222; font-size: 21px; }
    .folio { margin-top: 4px; color: #555; }
    .metadata { width: 100%; margin-bottom: 12px; border-collapse: collapse; }
    .metadata td { padding: 6px 8px; border: 1px solid #ccc; }
    .label { color: #087f8b; font-weight: bold; text-transform: uppercase; }
    .items { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .items th { padding: 7px 3px; border: 1px solid #999; color: #222; font-size: 7px; text-align: center; }
    .items td { padding: 8px 4px; border: 1px solid #aeb8c4; text-align: center; vertical-align: middle; word-wrap: break-word; }
    .items tr { page-break-inside: avoid; }
    .product-image { width: 42px; height: 42px; object-fit: contain; }
    .model, .quantity { font-weight: bold; }
    .quantity { font-size: 15px; }
    .price-final, .price-sale { color: #222; font-weight: bold; }
    .summary { width: 42%; margin: 12px 0 0 auto; border-collapse: collapse; }
    .summary td { padding: 7px; border: 1px solid #aaa; color: #222; font-weight: bold; }
    .summary td:last-child { text-align: right; }
    .notes { margin-top: 12px; padding: 8px 10px; border: 1px solid #ccc; }
    .footer { position: fixed; right: 0; bottom: -28px; left: 0; padding-top: 6px; border-top: 1px solid #cbd7e1; color: #53677c; font-size: 7px; text-align: right; }
  </style>
</head>
<body>
  <div class="header">
    <table><tr>
      <td style="width:28%"><?php if ($logoData !== '') { ?><img class="logo" src="<?= $logoData ?>" alt="Garesp"><?php } else { ?><strong>GARESP</strong><?php } ?></td>
      <td><h1>Pedido</h1><div class="folio"><?= escaparPedidoPdf($pedido['folio']) ?></div></td>
    </tr></table>
  </div>
  <table class="metadata">
    <tr><td class="label">Fecha</td><td><?= escaparPedidoPdf(date('d/m/Y', strtotime($pedido['fecha_pedido']))) ?></td><td class="label">Proveedor</td><td><?= escaparPedidoPdf($pedido['proveedor'] ?: 'Sin proveedor') ?></td></tr>
  </table>
  <table class="items">
    <thead><tr>
      <th style="width:7%">Modelo</th><th style="width:6%">Cantidad</th><th style="width:27%">Descripción</th>
      <th style="width:8%">P. compra</th><th style="width:8%">P. total</th><th style="width:6%">Margen</th>
      <th class="price-sale" style="width:8%">P. venta</th><th class="price-final" style="width:8%">P. final</th>
      <th style="width:6%">IVA</th><th style="width:7%">G. ind.</th><th style="width:7%">G. total</th><th style="width:8%">Total venta</th>
    </tr></thead>
    <tbody>
      <?php foreach ($detalles as $detalle) { ?>
      <tr>
        <td class="model"><?= escaparPedidoPdf($detalle['modelo']) ?></td>
        <td class="quantity"><?= escaparPedidoPdf(number_format((float) $detalle['cantidad'], 2)) ?></td>
        <td style="text-align:left;">
          <?php $imagen = imagenPedidoPdf($detalle['imagen']); if ($imagen !== '') { ?>
            <img class="product-image" src="<?= $imagen ?>" alt="Producto" style="margin-right:6px;vertical-align:middle;">
          <?php } ?>
          <span style="vertical-align:middle"><?= escaparPedidoPdf($detalle['descripcion']) ?></span>
        </td>
        <td>$<?= number_format((float) $detalle['precio_compra'], 2) ?></td>
        <td>$<?= number_format((float) $detalle['total_compra'], 2) ?></td>
        <td><?= (float) $detalle['precio_final'] > 0 ? number_format((float) $detalle['margen_porcentaje'], 2) . '%' : 'No definido' ?></td>
        <td class="price-sale">$<?= number_format((float) $detalle['precio_venta'], 2) ?></td>
        <td class="price-final">$<?= number_format((float) $detalle['precio_final'], 2) ?></td>
        <td><?= number_format((float) $detalle['iva_porcentaje'], 2) ?>%<br>$<?= number_format((float) $detalle['iva_monto'], 2) ?></td>
        <td>$<?= number_format((float) $detalle['ganancia_individual'], 2) ?></td>
        <td>$<?= number_format((float) $detalle['ganancia_total'], 2) ?></td>
        <td>$<?= number_format((float) $detalle['total_venta'], 2) ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
  <table class="summary">
    <tr><td>Envío (incluido en compra)</td><td>$<?= number_format((float) $pedido['envio'], 2) ?></td></tr>
    <tr><td>Total de compra</td><td>$<?= number_format((float) $pedido['total_compra'], 2) ?></td></tr>
    <tr><td>Total de venta</td><td>$<?= number_format((float) $pedido['total_venta'], 2) ?></td></tr>
    <tr><td>Ganancia total de ventas</td><td>$<?= number_format($gananciaTotalPedido, 2) ?></td></tr>
  </table>
  <?php if ($pedido['notas'] !== '') { ?><div class="notes"><strong>Notas:</strong> <?= nl2br(escaparPedidoPdf($pedido['notas'])) ?></div><?php } ?>
  <div class="footer">Garesp Servicio Informático · Pedido <?= escaparPedidoPdf($pedido['folio']) ?> · <?= date('d/m/Y H:i') ?></div>
</body>
</html>
<?php
$html = ob_get_clean();
$dompdf = new Dompdf();
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('letter', 'landscape');
$dompdf->render();
$dompdf->stream('pedido-' . preg_replace('/[^A-Za-z0-9-]/', '-', $pedido['folio']) . '.pdf', array('Attachment' => false));
