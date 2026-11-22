<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
header('Cache-Control: no-store, private');
ob_start();

include('conexion.php');
require_once __DIR__ . '/notas_recepcion.php';
prepararRecepcionNotas($conn);

$idNota = isset($_GET['idNota']) ? filter_var($_GET['idNota'], FILTER_VALIDATE_INT) : false;
if ($idNota === false || $idNota < 1) {
    http_response_code(400);
    exit('La nota solicitada no es válida.');
}
$consultaNota = mysqli_prepare($conn, 'SELECT n.*, c.nombre, c.apellidos, e.descripcionEquipo,
    r.cargador, r.accesorios, r.numero_serie, r.estado_fisico
    FROM notas n
    JOIN clientes c ON n.id_cliente = c.id_cliente
    JOIN equipo e ON n.equipo = e.id_equipo
    JOIN servicio s ON n.servicio = s.id_servicio
    JOIN estadoservicio es ON n.estado = es.id_estado
    LEFT JOIN nota_recepcion r ON r.id_nota = n.id_nota
    WHERE n.id_nota = ? LIMIT 1');
if (!$consultaNota) {
    error_log('No se pudo preparar la nota PDF: ' . mysqli_error($conn));
    http_response_code(500);
    exit('No se pudo generar la nota de entrada.');
}
$consultaNota->bind_param('i', $idNota);
if (!$consultaNota->execute()) {
    error_log('No se pudo consultar la nota PDF: ' . $consultaNota->error);
    http_response_code(500);
    exit('No se pudo generar la nota de entrada.');
}
$dataNota = $consultaNota->get_result()->fetch_assoc();
$consultaNota->close();
if (!$dataNota) {
    http_response_code(404);
    exit('No se encontró la nota.');
}
$hayDatosRecepcion = isset($dataNota['cargador']) ||
    ($dataNota['accesorios'] !== null && $dataNota['accesorios'] !== '') ||
    ($dataNota['numero_serie'] !== null && $dataNota['numero_serie'] !== '') ||
    ($dataNota['estado_fisico'] !== null && $dataNota['estado_fisico'] !== '');
$estilosReporte = file_get_contents(__DIR__ . '/css/styleReport.css');
if ($estilosReporte === false) {
    error_log('No se pudieron cargar los estilos de la nota de entrada.');
    http_response_code(500);
    exit('No se pudo generar la nota de entrada.');
}
$contenidoLogo = file_get_contents(__DIR__ . '/img/logos2.jpg');
if ($contenidoLogo === false) {
    error_log('No se pudo cargar el logotipo de la nota de entrada.');
}
$logoData = $contenidoLogo === false ? '' : 'data:image/jpeg;base64,' . base64_encode($contenidoLogo);

function escaparNotaPdf($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

$formasIconos = array(
    'nota' => '<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4M9 11h6M9 15h6"/>',
    'cliente' => '<circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-4 3-6 7-6s7 2 7 6"/>',
    'equipo' => '<rect x="3" y="4" width="18" height="13" rx="1"/><path d="M12 17v3M8 20h8"/>',
    'servicio' => '<path d="M14 6a5 5 0 0 0-6 6L3 17l4 4 5-5a5 5 0 0 0 6-6l-3 3-3-3z"/>',
    'pago' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 9h18M7 15h4"/>',
    'fecha' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4M17 3v4M3 10h18M7 14h3M7 17h3"/>',
    'ubicacion' => '<path d="M12 21s6-6 6-11a6 6 0 0 0-12 0c0 5 6 11 6 11z"/><circle cx="12" cy="10" r="2"/>',
    'telefono' => '<path d="M6 3l3 4-2 2c1 3 3 5 6 6l2-2 4 3-1 4c-1 2-5 1-9-3S3 9 3 6z"/>'
);
$iconos = array();
foreach ($formasIconos as $nombreIcono => $formaIcono) {
    $svgIcono = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#315f9e" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $formaIcono . '</svg>';
    $iconos[$nombreIcono] = 'data:image/svg+xml;base64,' . base64_encode($svgIcono);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SoftGaresp</title>
    <link rel="icon" href="img/logosAZUL.png" />
    <style><?= $estilosReporte ?></style>
</head>

<body<?= $hayDatosRecepcion ? ' class="nota-con-recepcion"' : '' ?>>
    <div class="container">
        <div class="headReport">
            <div id="logo">
                <?php if ($logoData !== '') { ?><img src="<?= $logoData ?>" id="imglogo" alt="Garesp"><?php } ?>
                <div id="dataFecha">
                    <p>RFC: GAEA941128IT5</p>
                </div>
            </div>
            <div id="servicios">
                <p>Servicio Técnico</p>
                <p>Computadoras e impresoras</p>
                <p>Cotización de equipos de cómputo</p>
                <p>Desarrollo de páginas web | ecommerce</p>
            </div>
            <div id="entrada">
                <div class="entrada-contenido">
                    <div class="entrada-etiqueta"><img class="icono" src="<?= $iconos['fecha'] ?>" alt=""> Fecha de entrada</div>
                    <div class="entrada-fecha"><?= escaparNotaPdf($dataNota['fecha_alta']) ?></div>
                </div>
            </div>
        </div>

        <table class="portada">
            <tr>
                <td class="portada-titulo">
                    <div class="portada-etiqueta">Recepción de equipos</div>
                    <div class="portada-nombre"><img class="icono icono-titulo" src="<?= $iconos['nota'] ?>" alt=""> Nota de entrada</div>
                </td>
                <td class="portada-folio">
                    <div class="folio-etiqueta">Núm. de nota</div>
                    <div id="idnota"><?= escaparNotaPdf($dataNota['id_nota']) ?></div>
                </td>
            </tr>
        </table>

        <section id="bodyReport">
            <h5 class="titulo"><img class="icono" src="<?= $iconos['cliente'] ?>" alt=""> Datos del cliente</h5>
            <p class="cliente-nombre"><span>Nombre completo</span> <strong><?= escaparNotaPdf(trim($dataNota['nombre'] . ' ' . $dataNota['apellidos'])) ?></strong></p>

            <h5 class="titulo"><img class="icono" src="<?= $iconos['equipo'] ?>" alt=""> Información del equipo</h5>
            <table class="datos-equipo">
                <tr>
                    <td><span>Equipo</span><strong><?= escaparNotaPdf($dataNota['descripcionEquipo']) ?></strong></td>
                    <td><span>Marca</span><strong><?= escaparNotaPdf($dataNota['marca']) ?></strong></td>
                    <td><span>Modelo</span><strong><?= escaparNotaPdf($dataNota['modelo']) ?></strong></td>
                </tr>
            </table>

            <?php if ($hayDatosRecepcion) { ?>
            <h5 class="titulo"><img class="icono" src="<?= $iconos['nota'] ?>" alt=""> Datos de recepción</h5>
            <table class="recepcion-pdf">
                <?php if (isset($dataNota['cargador']) || ($dataNota['numero_serie'] !== null && $dataNota['numero_serie'] !== '')) { ?>
                <tr>
                    <td><?php if (isset($dataNota['cargador'])) { ?><strong>Cargador:</strong> <?= (int) $dataNota['cargador'] === 1 ? 'Sí' : 'No' ?><?php } ?></td>
                    <td><?php if ($dataNota['numero_serie'] !== null && $dataNota['numero_serie'] !== '') { ?><strong>Serie o IMEI:</strong> <?= escaparNotaPdf($dataNota['numero_serie']) ?><?php } ?></td>
                </tr>
                <?php } ?>
                <?php if (($dataNota['accesorios'] !== null && $dataNota['accesorios'] !== '') || ($dataNota['estado_fisico'] !== null && $dataNota['estado_fisico'] !== '')) { ?>
                <tr>
                    <td><?php if ($dataNota['accesorios'] !== null && $dataNota['accesorios'] !== '') { ?><strong>Accesorios:</strong> <?= escaparNotaPdf($dataNota['accesorios']) ?><?php } ?></td>
                    <td><?php if ($dataNota['estado_fisico'] !== null && $dataNota['estado_fisico'] !== '') { ?><strong>Estado físico:</strong> <?= escaparNotaPdf($dataNota['estado_fisico']) ?><?php } ?></td>
                </tr>
                <?php } ?>
            </table>
            <?php } ?>

            <h5 class="titulo"><img class="icono" src="<?= $iconos['servicio'] ?>" alt=""> Descripción del servicio</h5>
            <div class="descripcion-servicio"><?= nl2br(escaparNotaPdf($dataNota['descripcionServicio'])) ?></div>

            <h5 class="titulo"><img class="icono" src="<?= $iconos['pago'] ?>" alt=""> Resumen de pago</h5>
            <table id="cantidades">
                <tr>
                    <?php if ($dataNota['anticipo'] != 0) { ?>
                        <td><span>Anticipo</span><strong>$<?= number_format((float) $dataNota['anticipo'], 2) ?></strong></td>
                    <?php } else { ?>
                        <td class="sin-pago"></td>
                    <?php } ?>
                    <td class="total-pago"><span>Total servicio</span><strong>$<?= number_format((float) $dataNota['costoServicio'], 2) ?></strong></td>
                    <?php if ($dataNota['anticipo'] != 0) { ?>
                        <td><span>Restante</span><strong>$<?= number_format((float) $dataNota['costoServicio'] - (float) $dataNota['anticipo'], 2) ?></strong></td>
                    <?php } else { ?>
                        <td class="sin-pago"></td>
                    <?php } ?>
                </tr>
            </table>
        </section>

        <div id="footerReport">
            <p id="direccion"><img class="icono icono-pie" src="<?= $iconos['ubicacion'] ?>" alt=""> Los pinos #17, col. La Mixteca, Tlaxiaco, Oaxaca</p>
            <div>
                <img class="icono icono-pie" src="<?= $iconos['telefono'] ?>" alt=""> <span id="contacto">9531272241</span>
            </div>
            <div>
                <span>GARESP - Servicio Informático</span>
            </div>
        </div>
    </div>
</body>

</html>

<?php

$html = ob_get_clean();
require_once 'dompdf/autoload.inc.php';

use Dompdf\Dompdf;

$dompdf = new Dompdf();
$options = $dompdf->getOptions();
$options->set(array('isRemoteEnabled' => true));
$dompdf->setOptions($options);
$dompdf->loadHtml($html);
$dompdf->set_paper(array(0, 0, 609.4488, 396.85), 'mediacarta');
$dompdf->render();
$dompdf->stream("nota_.pdf", array("Attachment" => false));

?> 