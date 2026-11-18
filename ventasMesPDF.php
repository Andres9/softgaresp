<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
require_once 'conexion.php';
require_once 'dompdf/autoload.inc.php';

use Dompdf\Dompdf;

date_default_timezone_set('America/Mexico_City');

$mesInicio = new DateTimeImmutable('first day of this month 00:00:00');
$mesSiguiente = $mesInicio->modify('+1 month');
$inicio = $mesInicio->format('Y-m-d H:i:s');
$fin = $mesSiguiente->format('Y-m-d H:i:s');

$consulta = $conn->prepare(
    'SELECT v.id_venta, v.cantidad, v.descripcion, v.costo, v.factura, v.fecha_venta,
            s.descripcionSeccion
     FROM ventas v
     LEFT JOIN seccion s ON v.seccion = s.id_seccion
     WHERE v.fecha_venta >= ? AND v.fecha_venta < ? AND v.costo >= 0
     ORDER BY v.fecha_venta ASC, v.id_venta ASC'
);

if ($consulta === false) {
    http_response_code(500);
    exit('No se pudo preparar el reporte de ventas.');
}

$consulta->bind_param('ss', $inicio, $fin);

if (!$consulta->execute()) {
    http_response_code(500);
    exit('No se pudieron consultar las ventas del mes.');
}

$resultado = $consulta->get_result();
if ($resultado === false) {
    http_response_code(500);
    exit('No se pudieron leer las ventas del mes.');
}

$ventas = $resultado->fetch_all(MYSQLI_ASSOC);
$consulta->close();

function escaparVentaPdf($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

$nombresMeses = array(
    1 => 'enero',
    2 => 'febrero',
    3 => 'marzo',
    4 => 'abril',
    5 => 'mayo',
    6 => 'junio',
    7 => 'julio',
    8 => 'agosto',
    9 => 'septiembre',
    10 => 'octubre',
    11 => 'noviembre',
    12 => 'diciembre'
);
$periodo = ucfirst($nombresMeses[(int) $mesInicio->format('n')]) . ' ' . $mesInicio->format('Y');
$contenidoLogo = file_get_contents(__DIR__ . '/img/logos2.jpg');
$logoData = $contenidoLogo === false
    ? ''
    : 'data:image/jpeg;base64,' . base64_encode($contenidoLogo);
$totalVentas = 0;
$ventasFacturadas = 0;
$totalFacturado = 0;
$ventasSinFactura = 0;
$totalSinFactura = 0;

foreach ($ventas as $venta) {
    $importe = (float) $venta['costo'];
    $totalVentas += $importe;

    if (strtolower(trim((string) $venta['factura'])) === 'factura') {
        $ventasFacturadas++;
        $totalFacturado += $importe;
    } else {
        $ventasSinFactura++;
        $totalSinFactura += $importe;
    }
}

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ventas de <?php echo escaparVentaPdf($periodo); ?></title>
    <style>
        @page {
            margin: 30px 30px 42px;
        }

        body {
            color: #23344b;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9px;
            line-height: 1.4;
        }

        .report-header {
            background: #173c66;
            margin-bottom: 20px;
            padding: 17px;
        }

        .header-layout {
            border-collapse: collapse;
            width: 100%;
        }

        .header-layout td {
            vertical-align: middle;
        }

        .logo-cell {
            width: 29%;
        }

        .brand-logo {
            background: #fff;
            padding: 5px;
            width: 145px;
        }

        .brand {
            color: #fff;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 1.8px;
        }

        .brand-subtitle {
            color: #c9d8e8;
            font-size: 8px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .header-copy {
            padding-left: 12px;
        }

        h1 {
            color: #fff;
            font-size: 16px;
            margin: 0 0 5px;
        }

        .eyebrow {
            color: #9dc9ed;
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 1.4px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .period {
            color: #d7e3ef;
            font-size: 9px;
        }

        .header-tag {
            border: 1px solid #6686a6;
            color: #d7e3ef;
            font-size: 7px;
            letter-spacing: 1px;
            padding: 6px 8px;
            text-align: center;
            text-transform: uppercase;
            width: 17%;
        }

        .summary {
            border-collapse: separate;
            border-spacing: 9px 0;
            margin: 0 -9px 20px;
            width: 100%;
        }

        .summary td {
            background: #f4f8fc;
            border: 1px solid #e1e9f2;
            border-top: 3px solid #3980b8;
            padding: 11px 12px;
            width: 33.33%;
        }

        .summary-label {
            color: #6b7c91;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .summary-value {
            color: #173c66;
            font-size: 15px;
            font-weight: bold;
            margin-top: 6px;
        }

        h2 {
            border-left: 3px solid #3980b8;
            color: #29415e;
            font-size: 10px;
            margin: 19px 0 8px;
            padding: 2px 0 2px 8px;
        }

        .sales {
            border-collapse: collapse;
            table-layout: fixed;
            width: 100%;
        }

        .sales th {
            background: #214f79;
            color: #fff;
            font-size: 8px;
            padding: 8px 6px;
            text-align: left;
        }

        .sales td {
            border-bottom: 1px solid #e5ebf2;
            color: #40536b;
            padding: 7px 6px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .sales tr:nth-child(even) td {
            background: #f7f9fc;
        }

        .sales .number,
        .sales .amount {
            text-align: right;
        }

        .sales .center {
            text-align: center;
        }

        .empty {
            background: #f7f9fc;
            color: #718096;
            padding: 20px;
            text-align: center;
        }

        .invoice-yes {
            background: #e8f5ee;
            color: #26734d;
            font-weight: bold;
        }

        .invoice-no {
            background: #f1f3f6;
            color: #67778a;
        }

        .invoice-summary {
            border-collapse: collapse;
            width: 100%;
        }

        .invoice-summary th,
        .invoice-summary td {
            border: 1px solid #e1e8f0;
            padding: 8px 10px;
            text-align: left;
        }

        .invoice-summary th {
            background: #edf3f8;
            color: #40536b;
        }

        .invoice-summary .total-row th {
            background: #173c66;
            color: #fff;
        }

        .invoice-summary .amount {
            text-align: right;
        }

        .footer {
            border-top: 1px solid #dce5ef;
            color: #8793a3;
            font-size: 8px;
            margin-top: 20px;
            padding-top: 7px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="report-header">
        <table class="header-layout">
            <tr>
                <td class="logo-cell">
                    <?php if ($logoData !== '') { ?>
                        <img class="brand-logo" src="<?php echo $logoData; ?>" alt="Garesp">
                    <?php } else { ?>
                        <div class="brand">GARESP</div>
                        <div class="brand-subtitle">Servicio Informático</div>
                    <?php } ?>
                </td>
                <td class="header-copy">
                    <div class="eyebrow">Informe mensual</div>
                    <h1>Reporte de ventas</h1>
                    <div class="period"><?php echo escaparVentaPdf($periodo); ?></div>
                </td>
                <td class="header-tag">Control<br>de ventas</td>
            </tr>
        </table>
    </div>

    <table class="summary">
        <tr>
            <td>
                <div class="summary-label">Operaciones</div>
                <div class="summary-value"><?php echo count($ventas); ?></div>
            </td>
            <td>
                <div class="summary-label">Total vendido</div>
                <div class="summary-value">$<?php echo number_format($totalVentas, 2, '.', ','); ?></div>
            </td>
            <td>
                <div class="summary-label">Ventas con factura</div>
                <div class="summary-value"><?php echo $ventasFacturadas; ?></div>
            </td>
        </tr>
    </table>

    <h2>Detalle de ventas</h2>
    <table class="sales">
        <thead>
            <tr>
                <th style="width: 7%">ID</th>
                <th style="width: 15%">Fecha</th>
                <th class="number" style="width: 8%">Cant.</th>
                <th style="width: 29%">Descripción</th>
                <th style="width: 17%">Sección</th>
                <th class="center" style="width: 10%">Factura</th>
                <th class="amount" style="width: 14%">Importe</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($ventas) === 0) { ?>
                <tr>
                    <td class="empty" colspan="7">No hay ventas registradas durante este mes.</td>
                </tr>
            <?php } ?>
            <?php foreach ($ventas as $venta) {
                $conFactura = strtolower(trim((string) $venta['factura'])) === 'factura';
                $importe = (float) $venta['costo'];
                ?>
                <tr>
                    <td><?php echo escaparVentaPdf($venta['id_venta']); ?></td>
                    <td><?php echo escaparVentaPdf($venta['fecha_venta']); ?></td>
                    <td class="number"><?php echo escaparVentaPdf($venta['cantidad']); ?></td>
                    <td><?php echo nl2br(escaparVentaPdf($venta['descripcion'])); ?></td>
                    <td><?php echo escaparVentaPdf($venta['descripcionSeccion'] ?: 'Sin sección'); ?></td>
                    <td class="center <?php echo $conFactura ? 'invoice-yes' : 'invoice-no'; ?>">
                        <?php echo $conFactura ? 'Sí' : 'No'; ?>
                    </td>
                    <td class="amount">$<?php echo number_format($importe, 2, '.', ','); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <h2>Resumen de facturación</h2>
    <table class="invoice-summary">
        <thead>
            <tr>
                <th>Tipo de venta</th>
                <th>Operaciones</th>
                <th class="amount">Importe</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Con factura</td>
                <td><?php echo $ventasFacturadas; ?></td>
                <td class="amount">$<?php echo number_format($totalFacturado, 2, '.', ','); ?></td>
            </tr>
            <tr>
                <td>Sin factura</td>
                <td><?php echo $ventasSinFactura; ?></td>
                <td class="amount">$<?php echo number_format($totalSinFactura, 2, '.', ','); ?></td>
            </tr>
            <tr class="total-row">
                <th>Total</th>
                <th><?php echo count($ventas); ?></th>
                <th class="amount">$<?php echo number_format($totalVentas, 2, '.', ','); ?></th>
            </tr>
        </tbody>
    </table>

    <div class="footer">Garesp Servicio Informático · Reporte generado el <?php echo date('d/m/Y H:i'); ?></div>
</body>
</html>
<?php
$html = ob_get_clean();
$dompdf = new Dompdf();
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();
$dompdf->stream('ventas-' . $mesInicio->format('Y-m') . '.pdf', array('Attachment' => false));
?>
