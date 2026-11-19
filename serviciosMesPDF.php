<?php

require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
mysqli_report(MYSQLI_REPORT_OFF);
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/dompdf/autoload.inc.php';

use Dompdf\Dompdf;

date_default_timezone_set('America/Mexico_City');

if (!$conn) {
    error_log('No fue posible conectar con la base de datos para generar el reporte mensual de servicios.');
    http_response_code(500);
    exit('No se pudo generar el reporte de servicios.');
}

$inicioMes = new DateTimeImmutable('first day of this month 00:00:00');
$finMes = $inicioMes->modify('+1 month');
$inicio = $inicioMes->format('Y-m-d H:i:s');
$fin = $finMes->format('Y-m-d H:i:s');

$consulta = mysqli_prepare(
    $conn,
    'SELECT n.id_nota, n.fecha_alta, n.costoServicio, n.ganancia, n.descripcionServicio,
            c.nombre, c.apellidos, e.descripcionEquipo, s.tipoServicio, es.estadoServicio
     FROM notas n
     LEFT JOIN clientes c ON n.id_cliente = c.id_cliente
     LEFT JOIN equipo e ON n.equipo = e.id_equipo
     LEFT JOIN servicio s ON n.servicio = s.id_servicio
     LEFT JOIN estadoservicio es ON n.estado = es.id_estado
     WHERE n.fecha_alta >= ? AND n.fecha_alta < ?
     ORDER BY n.fecha_alta ASC, n.id_nota ASC'
);

if ($consulta === false) {
    error_log('No fue posible preparar el reporte mensual de servicios: ' . mysqli_error($conn));
    http_response_code(500);
    exit('No se pudo generar el reporte de servicios.');
}

if (
    !mysqli_stmt_bind_param($consulta, 'ss', $inicio, $fin) ||
    !mysqli_stmt_execute($consulta) ||
    !mysqli_stmt_bind_result(
        $consulta,
        $idNota,
        $fechaAlta,
        $costoServicio,
        $ganancia,
        $descripcionServicio,
        $nombreCliente,
        $apellidosCliente,
        $equipo,
        $tipoServicio,
        $estadoServicio
    )
) {
    error_log('No fue posible consultar el reporte mensual de servicios: ' . mysqli_stmt_error($consulta));
    mysqli_stmt_close($consulta);
    http_response_code(500);
    exit('No se pudo generar el reporte de servicios.');
}

$servicios = array();
$montoTotal = 0.0;
$gananciaTotal = 0.0;

while (mysqli_stmt_fetch($consulta)) {
    $servicio = array(
        'id_nota' => $idNota,
        'fecha_alta' => $fechaAlta,
        'costoServicio' => (float) $costoServicio,
        'ganancia' => (float) $ganancia,
        'descripcionServicio' => $descripcionServicio,
        'cliente' => trim((string) $nombreCliente . ' ' . (string) $apellidosCliente),
        'equipo' => $equipo,
        'tipoServicio' => $tipoServicio,
        'estadoServicio' => $estadoServicio
    );
    $servicios[] = $servicio;
    $montoTotal += $servicio['costoServicio'];
    $gananciaTotal += $servicio['ganancia'];
}

if (mysqli_stmt_errno($consulta) !== 0) {
    error_log('Ocurrió un error al leer los servicios del mes: ' . mysqli_stmt_error($consulta));
    mysqli_stmt_close($consulta);
    http_response_code(500);
    exit('No se pudo generar el reporte de servicios.');
}
mysqli_stmt_close($consulta);

function escaparServicioPdf($valor)
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
$periodo = ucfirst($nombresMeses[(int) $inicioMes->format('n')]) . ' ' . $inicioMes->format('Y');
$contenidoLogo = file_get_contents(__DIR__ . '/img/logos2.jpg');
$logoData = $contenidoLogo === false
    ? ''
    : 'data:image/jpeg;base64,' . base64_encode($contenidoLogo);

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Servicios de <?php echo escaparServicioPdf($periodo); ?></title>
    <style>
        @page {
            margin: 28px;
        }

        body {
            color: #23344b;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9px;
            line-height: 1.4;
        }

        .report-header {
            background: #173c66;
            color: #fff;
            margin-bottom: 18px;
            padding: 16px;
        }

        .header-layout {
            border-collapse: collapse;
            width: 100%;
        }

        .header-layout td {
            vertical-align: middle;
        }

        .logo-cell {
            width: 25%;
        }

        .brand-logo {
            background: #fff;
            padding: 5px;
            width: 130px;
        }

        .report-title {
            color: #fff;
            font-size: 17px;
            margin: 0 0 5px;
        }

        .report-period {
            color: #d7e3ef;
            font-size: 10px;
        }

        .summary {
            border-collapse: separate;
            border-spacing: 8px 0;
            margin: 0 -8px 18px;
            width: 100%;
        }

        .summary td {
            background: #f4f8fc;
            border: 1px solid #e1e9f2;
            border-top: 3px solid #3980b8;
            padding: 10px 12px;
            width: 33.33%;
        }

        .summary-label {
            color: #6b7c91;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .summary-value {
            color: #173c66;
            font-size: 15px;
            font-weight: bold;
            margin-top: 5px;
        }

        h2 {
            border-left: 3px solid #3980b8;
            color: #29415e;
            font-size: 11px;
            margin: 16px 0 8px;
            padding-left: 8px;
        }

        .services {
            border-collapse: collapse;
            table-layout: fixed;
            width: 100%;
        }

        .services th {
            background: #214f79;
            color: #fff;
            font-size: 8px;
            padding: 7px 5px;
            text-align: left;
        }

        .services td {
            border-bottom: 1px solid #e5ebf2;
            color: #40536b;
            padding: 6px 5px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .services tr:nth-child(even) td {
            background: #f7f9fc;
        }

        .amount {
            text-align: right;
            white-space: nowrap;
        }

        .empty {
            color: #718096;
            padding: 18px;
            text-align: center;
        }

        .footer {
            border-top: 1px solid #dce5ef;
            color: #8793a3;
            font-size: 8px;
            margin-top: 18px;
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
                    <?php } ?>
                </td>
                <td>
                    <h1 class="report-title">Reporte mensual de servicios</h1>
                    <div class="report-period"><?php echo escaparServicioPdf($periodo); ?></div>
                </td>
            </tr>
        </table>
    </div>

    <table class="summary">
        <tr>
            <td>
                <div class="summary-label">Servicios registrados</div>
                <div class="summary-value"><?php echo count($servicios); ?></div>
            </td>
            <td>
                <div class="summary-label">Monto total</div>
                <div class="summary-value">$<?php echo number_format($montoTotal, 2, '.', ','); ?></div>
            </td>
            <td>
                <div class="summary-label">Ganancias</div>
                <div class="summary-value">$<?php echo number_format($gananciaTotal, 2, '.', ','); ?></div>
            </td>
        </tr>
    </table>

    <h2>Detalle de servicios</h2>
    <table class="services">
        <thead>
            <tr>
                <th style="width: 6%">Nota</th>
                <th style="width: 10%">Fecha</th>
                <th style="width: 16%">Cliente</th>
                <th style="width: 11%">Servicio</th>
                <th style="width: 18%">Descripción</th>
                <th style="width: 11%">Equipo</th>
                <th style="width: 8%">Estado</th>
                <th class="amount" style="width: 10%">Monto</th>
                <th class="amount" style="width: 10%">Ganancia</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($servicios) === 0) { ?>
                <tr>
                    <td class="empty" colspan="9">No hay servicios registrados durante este mes.</td>
                </tr>
            <?php } ?>
            <?php foreach ($servicios as $servicio) { ?>
                <tr>
                    <td><?php echo escaparServicioPdf($servicio['id_nota']); ?></td>
                    <td><?php echo escaparServicioPdf($servicio['fecha_alta']); ?></td>
                    <td><?php echo escaparServicioPdf($servicio['cliente']); ?></td>
                    <td><?php echo escaparServicioPdf($servicio['tipoServicio']); ?></td>
                    <td><?php echo escaparServicioPdf($servicio['descripcionServicio']); ?></td>
                    <td><?php echo escaparServicioPdf($servicio['equipo']); ?></td>
                    <td><?php echo escaparServicioPdf($servicio['estadoServicio']); ?></td>
                    <td class="amount">$<?php echo number_format($servicio['costoServicio'], 2, '.', ','); ?></td>
                    <td class="amount">$<?php echo number_format($servicio['ganancia'], 2, '.', ','); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="footer">Garesp Servicio Informático · Reporte generado el <?php echo date('d/m/Y H:i'); ?></div>
</body>
</html>
<?php
$html = ob_get_clean();
$dompdf = new Dompdf();
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('letter', 'landscape');
$dompdf->render();
$dompdf->stream('servicios-' . $inicioMes->format('Y-m') . '.pdf', array('Attachment' => false));
