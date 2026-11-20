<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
require_once __DIR__ . '/cotizaciones_db.php';
require_once __DIR__ . '/dompdf/autoload.inc.php';

use Dompdf\Dompdf;

date_default_timezone_set('America/Mexico_City');

function escaparCotizacionPdf($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function imagenCotizacionPdf($nombre)
{
    if (!is_string($nombre) || !preg_match('/^[a-f0-9]{32}\.(jpg|png)$/', $nombre)) {
        return '';
    }

    $rutaImagen = __DIR__ . '/uploads/cotizaciones/' . $nombre;
    if (!is_file($rutaImagen)) {
        return '';
    }

    $contenido = file_get_contents($rutaImagen);
    if ($contenido === false) {
        return '';
    }

    $tipo = substr($nombre, -3) === 'png' ? 'image/png' : 'image/jpeg';
    return 'data:' . $tipo . ';base64,' . base64_encode($contenido);
}

$idCotizacion = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : false;
if ($idCotizacion === false || $idCotizacion < 1) {
    http_response_code(400);
    exit('La cotización solicitada no es válida.');
}

$consulta = mysqli_prepare($conn, 'SELECT * FROM cotizaciones WHERE id_cotizacion = ? LIMIT 1');
if ($consulta === false) {
    error_log('No fue posible preparar la consulta de cotización PDF: ' . mysqli_error($conn));
    http_response_code(500);
    exit('No se pudo generar el PDF.');
}
$consulta->bind_param('i', $idCotizacion);
if (!$consulta->execute()) {
    error_log('No fue posible consultar la cotización PDF: ' . $consulta->error);
    $consulta->close();
    http_response_code(500);
    exit('No se pudo generar el PDF.');
}
$cotizacion = $consulta->get_result()->fetch_assoc();
$consulta->close();

if (!$cotizacion) {
    http_response_code(404);
    exit('No se encontró la cotización solicitada.');
}

$consultaDetalles = mysqli_prepare(
    $conn,
    'SELECT * FROM cotizacion_detalles WHERE id_cotizacion = ? ORDER BY orden, id_detalle'
);
if ($consultaDetalles === false) {
    error_log('No fue posible preparar los conceptos para el PDF: ' . mysqli_error($conn));
    http_response_code(500);
    exit('No se pudo generar el PDF.');
}
$consultaDetalles->bind_param('i', $idCotizacion);
if (!$consultaDetalles->execute()) {
    error_log('No fue posible consultar los conceptos para el PDF: ' . $consultaDetalles->error);
    $consultaDetalles->close();
    http_response_code(500);
    exit('No se pudo generar el PDF.');
}
$detalles = $consultaDetalles->get_result()->fetch_all(MYSQLI_ASSOC);
$consultaDetalles->close();

$contenidoLogo = is_file(__DIR__ . '/img/logos2.jpg') ? file_get_contents(__DIR__ . '/img/logos2.jpg') : false;
$logoData = $contenidoLogo === false ? '' : 'data:image/jpeg;base64,' . base64_encode($contenidoLogo);
$moneda = escaparCotizacionPdf($cotizacion['moneda']);

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización <?= escaparCotizacionPdf($cotizacion['folio']) ?></title>
    <style>
        @page { margin: 30px 34px 54px; }
        body { color: #24384d; font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; line-height: 1.5; }
        .header { background: #102b45; border-bottom: 4px solid #21b6c7; margin-bottom: 18px; padding: 17px 20px; color: #fff; }
        .header-layout { border-collapse: collapse; width: 100%; }
        .header-layout td { vertical-align: middle; }
        .logo-cell { width: 27%; }
        .logo { background: #fff; border-radius: 3px; padding: 5px; width: 135px; }
        .brand { color: #fff; font-size: 19px; font-weight: bold; letter-spacing: 2px; }
        .brand-subtitle { color: #c9d8e8; font-size: 8px; letter-spacing: 1px; text-transform: uppercase; }
        .header-copy { padding-left: 12px; }
        .eyebrow { color: #72e0df; font-size: 8px; font-weight: bold; letter-spacing: 1.5px; }
        h1 { color: #fff; font-size: 20px; margin: 5px 0; }
        .folio { color: #dce8f1; font-size: 10px; letter-spacing: .5px; }
        .header-label { background: #087f8b; border: 1px solid #4bc7c9; color: #fff; font-size: 8px; font-weight: bold; letter-spacing: 1px; padding: 8px 10px; text-align: center; text-transform: uppercase; }
        .columns { border-collapse: separate; border-spacing: 8px 0; margin: 0 -8px 16px; width: 100%; }
        .columns td { vertical-align: top; width: 50%; }
        .info-card { background: #f4f8fb; border: 1px solid #d8e3ec; padding: 12px 14px; }
        .section-label { color: #087f8b; font-size: 8px; font-weight: bold; letter-spacing: 1px; margin-bottom: 7px; text-transform: uppercase; }
        .client-name { color: #173c66; font-size: 12px; font-weight: bold; margin-bottom: 4px; }
        .muted { color: #53677c; font-size: 8px; }
        .metadata { margin-top: 10px; width: 100%; }
        .metadata td { border-bottom: 1px solid #dfe7ee; padding: 5px 3px; }
        .metadata .label { color: #4c6278; font-weight: bold; width: 36%; }
        h2 { background: #173c66; border-left: 4px solid #21b6c7; color: #fff; font-size: 10px; margin: 18px 0 9px; padding: 6px 9px; }
        .items { border-collapse: collapse; table-layout: fixed; width: 100%; }
        .items thead { display: table-header-group; }
        .items tr { page-break-inside: avoid; }
        .items th { background: #102b45; border-bottom: 2px solid #21b6c7; color: #fff; font-size: 8px; padding: 8px 5px; text-align: left; }
        .items td { border-bottom: 1px solid #dfe7ee; color: #334b61; font-size: 8px; padding: 8px 5px; vertical-align: top; word-wrap: break-word; }
        .items tr:nth-child(even) td { background: #f5f8fb; }
        .items .right { text-align: right; }
        .product-image { background: #fff; border: 1px solid #d9e3eb; height: 48px; object-fit: contain; padding: 2px; width: 48px; }
        .product-title { color: #173c66; font-weight: bold; }
        .product-detail { color: #52677d; font-size: 8px; margin-top: 3px; }
        .summary-wrap { margin-top: 12px; text-align: right; }
        .summary { border-collapse: collapse; margin-left: auto; width: 48%; }
        .summary td { border-bottom: 1px solid #dfe7ee; padding: 7px 9px; }
        .summary .label { color: #4c6278; text-align: left; }
        .summary .amount { color: #263f56; font-weight: bold; text-align: right; }
        .summary .total td { background: #087f8b; border: 0; color: #fff; font-size: 12px; font-weight: bold; }
        .conditions { background: #f4f8fb; border: 1px solid #d8e3ec; padding: 12px 14px; }
        .conditions-grid { border-collapse: collapse; width: 100%; }
        .conditions-grid td { padding: 5px 8px 5px 0; vertical-align: top; width: 50%; }
        .condition-label { color: #087f8b; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .condition-value { color: #334b61; font-size: 9px; }
        .notes { margin-top: 9px; }
        .acceptance { margin-top: 34px; page-break-inside: avoid; width: 100%; }
        .acceptance td { color: #52677d; padding-top: 22px; text-align: center; width: 50%; }
        .signature { border-top: 1px solid #8da1b3; display: inline-block; padding-top: 7px; width: 72%; }
        .footer { position: fixed; right: 0; bottom: -34px; left: 0; border-top: 1px solid #cbd7e1; color: #53677c; font-size: 8px; padding-top: 7px; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-layout">
            <tr>
                <td class="logo-cell">
                    <?php if ($logoData !== '') { ?>
                        <img class="logo" src="<?= $logoData ?>" alt="Garesp">
                    <?php } else { ?>
                        <div class="brand">GARESP</div><div class="brand-subtitle">Servicio Informático</div>
                    <?php } ?>
                </td>
                <td class="header-copy">
                    <div class="eyebrow">PROPUESTA COMERCIAL</div>
                    <h1>Cotización</h1>
                    <div class="folio"><?= escaparCotizacionPdf($cotizacion['folio']) ?></div>
                </td>
                <td class="header-label">Propuesta<br>para cliente</td>
            </tr>
        </table>
    </div>

    <table class="columns">
        <tr>
            <td style="padding-right:7px">
                <div class="info-card">
                    <div class="section-label">Preparada para</div>
                    <div class="client-name"><?= escaparCotizacionPdf($cotizacion['cliente_nombre']) ?></div>
                    <?php if ($cotizacion['cliente_empresa'] !== '') { ?><div><?= escaparCotizacionPdf($cotizacion['cliente_empresa']) ?></div><?php } ?>
                    <?php if ($cotizacion['cliente_email'] !== '') { ?><div class="muted"><?= escaparCotizacionPdf($cotizacion['cliente_email']) ?></div><?php } ?>
                    <?php if ($cotizacion['cliente_telefono'] !== '') { ?><div class="muted"><?= escaparCotizacionPdf($cotizacion['cliente_telefono']) ?></div><?php } ?>
                    <?php if ($cotizacion['cliente_rfc'] !== '') { ?><div class="muted">RFC: <?= escaparCotizacionPdf($cotizacion['cliente_rfc']) ?></div><?php } ?>
                    <?php if ($cotizacion['cliente_direccion'] !== '') { ?><div class="muted"><?= nl2br(escaparCotizacionPdf($cotizacion['cliente_direccion'])) ?></div><?php } ?>
                </div>
            </td>
            <td style="padding-left:7px">
                <div class="info-card">
                    <div class="section-label">Datos de la propuesta</div>
                    <table class="metadata">
                        <tr><td class="label">Fecha</td><td><?= escaparCotizacionPdf(date('d/m/Y', strtotime($cotizacion['fecha_cotizacion']))) ?></td></tr>
                        <tr><td class="label">Vigencia</td><td><?= escaparCotizacionPdf(date('d/m/Y', strtotime($cotizacion['vigencia_hasta']))) ?></td></tr>
                        <tr><td class="label">Moneda</td><td><?= $moneda ?></td></tr>
                        <tr><td class="label">Atendió</td><td><?= escaparCotizacionPdf($cotizacion['creado_por']) ?></td></tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <h2>Detalle de productos y servicios</h2>
    <table class="items">
        <thead>
            <tr>
                <th style="width:9%">Imagen</th>
                <th style="width:34%">Concepto</th>
                <th class="right" style="width:7%">Cant.</th>
                <th class="right" style="width:13%">P. unitario</th>
                <th class="right" style="width:9%">Desc.</th>
                <th class="right" style="width:9%">Impuesto</th>
                <th class="right" style="width:19%">Importe</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalles as $detalle) {
                $imagenProducto = imagenCotizacionPdf($detalle['imagen']);
                $identificadores = array_filter(array($detalle['tipo_producto'], $detalle['marca'], $detalle['modelo'], $detalle['sku']));
                ?>
                <tr>
                    <td><?php if ($imagenProducto !== '') { ?><img class="product-image" src="<?= $imagenProducto ?>" alt="Producto"><?php } else { ?>—<?php } ?></td>
                    <td>
                        <div class="product-title"><?= escaparCotizacionPdf($detalle['descripcion']) ?></div>
                        <?php if (count($identificadores) > 0) { ?><div class="product-detail"><?= escaparCotizacionPdf(implode(' · ', $identificadores)) ?></div><?php } ?>
                        <?php if ($detalle['especificaciones'] !== '') { ?><div class="product-detail"><?= nl2br(escaparCotizacionPdf($detalle['especificaciones'])) ?></div><?php } ?>
                        <?php if ($detalle['garantia'] !== '') { ?><div class="product-detail">Garantía: <?= escaparCotizacionPdf($detalle['garantia']) ?></div><?php } ?>
                    </td>
                    <td class="right"><?= number_format((float) $detalle['cantidad'], 2) ?></td>
                    <td class="right"><?= $moneda ?> $<?= number_format((float) $detalle['precio_unitario'], 2) ?></td>
                    <td class="right"><?= number_format((float) $detalle['descuento_porcentaje'], 2) ?>%</td>
                    <td class="right"><?= number_format((float) $detalle['impuesto_porcentaje'], 2) ?>%</td>
                    <td class="right"><?= $moneda ?> $<?= number_format((float) $detalle['total'], 2) ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="summary-wrap">
        <table class="summary">
            <tr><td class="label">Subtotal</td><td class="amount"><?= $moneda ?> $<?= number_format((float) $cotizacion['subtotal'], 2) ?></td></tr>
            <tr><td class="label">Descuentos</td><td class="amount">− <?= $moneda ?> $<?= number_format((float) $cotizacion['descuento_total'], 2) ?></td></tr>
            <tr><td class="label">Impuestos</td><td class="amount"><?= $moneda ?> $<?= number_format((float) $cotizacion['impuestos_total'], 2) ?></td></tr>
            <tr class="total"><td>Total</td><td class="amount"><?= $moneda ?> $<?= number_format((float) $cotizacion['total'], 2) ?></td></tr>
        </table>
    </div>

    <h2>Condiciones comerciales</h2>
    <div class="conditions">
        <table class="conditions-grid">
            <tr>
                <td><div class="condition-label">Condiciones de pago</div><div class="condition-value"><?= $cotizacion['condiciones_pago'] !== '' ? escaparCotizacionPdf($cotizacion['condiciones_pago']) : 'Por definir' ?></div></td>
                <td><div class="condition-label">Tiempo de entrega</div><div class="condition-value"><?= $cotizacion['tiempo_entrega'] !== '' ? escaparCotizacionPdf($cotizacion['tiempo_entrega']) : 'Por definir' ?></div></td>
            </tr>
            <tr>
                <td><div class="condition-label">Garantía general</div><div class="condition-value"><?= $cotizacion['garantia'] !== '' ? escaparCotizacionPdf($cotizacion['garantia']) : 'Según garantía indicada para cada concepto' ?></div></td>
                <td><div class="condition-label">Vigencia de la oferta</div><div class="condition-value">Hasta el <?= escaparCotizacionPdf(date('d/m/Y', strtotime($cotizacion['vigencia_hasta']))) ?></div></td>
            </tr>
        </table>
        <?php if ($cotizacion['notas'] !== '') { ?>
            <div class="notes"><div class="condition-label">Notas</div><div class="condition-value"><?= nl2br(escaparCotizacionPdf($cotizacion['notas'])) ?></div></div>
        <?php } ?>
    </div>

    <table class="acceptance">
        <tr>
            <td><span class="signature">Aceptación del cliente</span></td>
            <td><span class="signature">Garesp Servicio Informático</span></td>
        </tr>
    </table>
    <div class="footer">Garesp Servicio Informático · Cotización <?= escaparCotizacionPdf($cotizacion['folio']) ?> · Generada el <?= date('d/m/Y H:i') ?></div>
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
$dompdf->stream('cotizacion-' . preg_replace('/[^A-Za-z0-9-]/', '-', $cotizacion['folio']) . '.pdf', array('Attachment' => false));
