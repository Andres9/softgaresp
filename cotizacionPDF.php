<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
require_once __DIR__ . '/cotizaciones_db.php';
require_once __DIR__ . '/dompdf/autoload.inc.php';
require_once __DIR__ . '/comercial_pdf.php';

use Dompdf\Dompdf;

date_default_timezone_set('America/Mexico_City');

function escaparCotizacionPdf($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function fechaCotizacionPdf($valor)
{
    $fecha = strtotime((string) $valor);
    return $fecha === false ? escaparCotizacionPdf($valor) : date('d/m/Y', $fecha);
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

if (!function_exists('imagecreatefrompng')) {
    error_log('La extensión GD de PHP es necesaria para mostrar imágenes PNG en el PDF de cotización.');
    http_response_code(500);
    exit('No se pudo generar el PDF: falta la extensión GD de PHP.');
}
$contenidoLogo = @file_get_contents(__DIR__ . '/img/logosinfondo.png');
if ($contenidoLogo === false) {
    error_log('No fue posible leer el logotipo para la cotización PDF.');
    http_response_code(500);
    exit('No se pudo generar el PDF.');
}
$logoData = 'data:image/png;base64,' . base64_encode($contenidoLogo);
$estilosPdf = @file_get_contents(__DIR__ . '/css/comercial_pdf.css');
if ($estilosPdf === false) {
    error_log('No fue posible leer los estilos del PDF comercial.');
    http_response_code(500);
    exit('No se pudo generar el PDF.');
}
$moneda = escaparCotizacionPdf($cotizacion['moneda']);
$clienteNombre = trim((string) $cotizacion['cliente_nombre']);
$clienteEmpresa = trim((string) $cotizacion['cliente_empresa']);
$clienteEmail = trim((string) $cotizacion['cliente_email']);
$clienteTelefono = trim((string) $cotizacion['cliente_telefono']);
$clienteRfc = trim((string) $cotizacion['cliente_rfc']);
$clienteDireccion = trim((string) $cotizacion['cliente_direccion']);
$atendio = trim((string) $cotizacion['creado_por']);
$observaciones = trim((string) $cotizacion['notas']);
$condicionesPago = trim((string) $cotizacion['condiciones_pago']);
$tiempoEntrega = trim((string) $cotizacion['tiempo_entrega']);
$garantia = trim((string) $cotizacion['garantia']);

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización <?= escaparCotizacionPdf($cotizacion['folio']) ?></title>
    <style>
<?= $estilosPdf ?>    </style>
</head>
<body>
    <div class="top-stripe"></div>
    <div class="top-accent"></div>
    <div class="footer">Garesp Servicio Informático &middot; Cotización <?= escaparCotizacionPdf($cotizacion['folio']) ?> &middot; Generada el <?= date('d/m/Y H:i') ?></div>
    <div class="page-container">
      <table class="header-table">
            <tr>
                <td>
                    <img class="brand-logo" src="<?= $logoData ?>" alt="GARESP Servicio Informático">
                    <div class="brand-tagline">Soluciones tecnológicas, equipamiento y soporte especializado</div>
                </td>
                <td class="doc-box">
                    <div class="badge">Propuesta Comercial</div>
                    <div class="doc-title">Cotización</div>
                    <div class="doc-folio">Folio: <?= escaparCotizacionPdf($cotizacion['folio']) ?></div>
                </td>
            </tr>
        </table>

    <table class="cards-table">
        <tr>
            <td class="card-cell card-client">
                <div class="card-label">Preparada para</div>
                <div class="client-name"><?= escaparCotizacionPdf($clienteNombre) ?></div>
                <?php if ($clienteEmpresa !== '') { ?><div class="client-detail"><?= escaparCotizacionPdf($clienteEmpresa) ?></div><?php } ?>
                <?php if ($clienteEmail !== '') { ?><div class="client-detail"><?= escaparCotizacionPdf($clienteEmail) ?></div><?php } ?>
                <?php if ($clienteTelefono !== '') { ?><div class="client-detail"><?= escaparCotizacionPdf($clienteTelefono) ?></div><?php } ?>
                <?php if ($clienteRfc !== '') { ?><div class="client-detail">RFC: <?= escaparCotizacionPdf($clienteRfc) ?></div><?php } ?>
                <?php if ($clienteDireccion !== '') { ?><div class="client-detail"><?= nl2br(escaparCotizacionPdf($clienteDireccion)) ?></div><?php } ?>
            </td>
            <td class="card-spacer"></td>
            <td class="card-cell card-meta">
                <div class="card-label">Datos de la propuesta</div>
                <table class="meta-inner">
                    <tr><td class="lbl">Fecha de emisión:</td><td class="val"><?= fechaCotizacionPdf($cotizacion['fecha_cotizacion']) ?></td></tr>
                    <tr><td class="lbl">Vigencia:</td><td class="val"><?= fechaCotizacionPdf($cotizacion['vigencia_hasta']) ?></td></tr>
                    <tr><td class="lbl">Moneda:</td><td class="val"><?= $moneda ?></td></tr>
                    <tr><td class="lbl">Atendió:</td><td class="val"><?= escaparCotizacionPdf($atendio) ?></td></tr>
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
                        <span class="model-pill"><?= escaparCotizacionPdf(trim((string) $detalle['modelo']) !== '' ? $detalle['modelo'] : '—') ?></span>
                    </td>
                    <td>
                        <span class="item-title"><?= escaparCotizacionPdf($detalle['descripcion']) ?></span>
                        <?php if (count($identificadores) > 0) { ?><span class="item-sub"><?= escaparCotizacionPdf(implode(' · ', $identificadores)) ?></span><?php } ?>
                        <?php if (trim((string) $detalle['especificaciones']) !== '') { ?><div class="item-sub"><?= nl2br(escaparCotizacionPdf($detalle['especificaciones'])) ?></div><?php } ?>
                        <?php if (trim((string) $detalle['garantia']) !== '') { ?><div class="item-sub">Garantía: <?= escaparCotizacionPdf($detalle['garantia']) ?></div><?php } ?>
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
                <div class="note-box">
                    <strong>Observaciones comerciales</strong>
                    <?php if ($observaciones !== '') { ?>
                        <?= nl2br(escaparCotizacionPdf($observaciones)) ?>
                    <?php } else { ?>
                        Precios expresados en <?= $moneda ?>. Cotización sujeta a disponibilidad de inventario al momento de confirmar el pedido.
                    <?php } ?>
                </div>
            </td>
            <td class="summary-box-cell">
                <table class="totals-table">
                    <tr><td class="t-lbl">Subtotal</td><td class="t-val"><?= $moneda ?> $<?= number_format((float) $cotizacion['subtotal'], 2) ?></td></tr>
                    <tr><td class="t-lbl">Descuentos</td><td class="t-val">&minus; <?= $moneda ?> $<?= number_format((float) $cotizacion['descuento_total'], 2) ?></td></tr>
                    <tr><td class="t-lbl">Impuestos</td><td class="t-val"><?= $moneda ?> $<?= number_format((float) $cotizacion['impuestos_total'], 2) ?></td></tr>
                    <tr class="grand-row"><td>Total</td><td style="text-align:right"><?= $moneda ?> $<?= number_format((float) $cotizacion['total'], 2) ?></td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="section-heading">Condiciones comerciales</div>
    <table class="terms-table">
        <tr>
            <td class="term-card">
                <div class="term-title">Condiciones de pago</div>
                <div class="term-val"><?= escaparCotizacionPdf($condicionesPago !== '' ? $condicionesPago : 'Por definir') ?></div>
            </td>
            <td class="term-card">
                <div class="term-title">Tiempo de entrega</div>
                <div class="term-val"><?= escaparCotizacionPdf($tiempoEntrega !== '' ? $tiempoEntrega : 'Por definir') ?></div>
            </td>
        </tr>
        <tr>
            <td class="term-card">
                <div class="term-title">Garantía general</div>
                <div class="term-val"><?= escaparCotizacionPdf($garantia !== '' ? $garantia : 'Según garantía indicada para cada concepto') ?></div>
            </td>
            <td class="term-card">
                <div class="term-title">Vigencia de la oferta</div>
                <div class="term-val">Hasta el <?= fechaCotizacionPdf($cotizacion['vigencia_hasta']) ?></div>
            </td>
        </tr>
    </table>

    <table class="signatures-table">
        <tr>
            <td class="sig-cell">
                <div class="sig-line">Aceptación del cliente</div>
                <div class="sig-sub"><?= escaparCotizacionPdf($clienteNombre) ?></div>
            </td>
            <td class="sig-space"></td>
            <td class="sig-cell">
                <div class="sig-line">Garesp Servicio Informático</div>
                <div class="sig-sub">Atendió: <?= escaparCotizacionPdf($atendio) ?></div>
            </td>
        </tr>
    </table>
    </div>
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
