<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
header('Cache-Control: no-store, private');
ob_start();

include('conexion.php');
require_once __DIR__ . '/notas_recepcion.php';
prepararRecepcionNotas($conn);
require_once __DIR__ . '/notas_servicios.php';
prepararServiciosNota($conn);

$idNota = isset($_GET['idNota']) ? filter_var($_GET['idNota'], FILTER_VALIDATE_INT) : false;
if ($idNota === false || $idNota < 1) {
    http_response_code(400);
    exit('La nota solicitada no es válida.');
}
$consultaNota = mysqli_prepare($conn, 'SELECT n.*, c.nombre, c.apellidos, c.telefono AS telefonoCliente, e.descripcionEquipo,
    es.estadoServicio, r.cargador, r.accesorios, r.numero_serie, r.estado_fisico
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
$serviciosNota = serviciosDeNota($conn, $dataNota);
$equiposNota = equiposDeNota($conn, $dataNota);
$contenidoLogo = file_get_contents(__DIR__ . '/img/logonegro-reporte.jpg');
$estilosReporte = file_get_contents(__DIR__ . '/css/estilos_nota_garesp.css');
$contenidoQr = file_get_contents(__DIR__ . '/img/qr-whatsapp.svg');
if ($contenidoLogo === false || $estilosReporte === false || $contenidoQr === false) {
    error_log('No se pudieron cargar los recursos de la nota de entrada.');
    http_response_code(500);
    exit('No se pudo generar la nota de entrada.');
}
$logoData = 'data:image/jpeg;base64,' . base64_encode($contenidoLogo);
$qrData = 'data:image/svg+xml;base64,' . base64_encode($contenidoQr);
$folioNota = str_pad((string) (int) $dataNota['id_nota'], 5, '0', STR_PAD_LEFT);
$fechaNota = strtotime((string) $dataNota['fecha_alta']);
$fechaNotaFormateada = $fechaNota === false ? (string) $dataNota['fecha_alta'] : date('d/m/Y H:i', $fechaNota);
$costoServicio = (float) $dataNota['costoServicio'];
$anticipo = (float) $dataNota['anticipo'];
$restante = (float) $dataNota['restante'];
$accesoriosTexto = trim((string) $dataNota['accesorios']);
$estadoFisico = trim((string) $dataNota['estado_fisico']);
$descripcionTexto = trim((string) $dataNota['descripcionServicio']);
$descripcionConMuchosSaltos = substr_count($descripcionTexto, "\n") > 4;
$contenidoExtenso = count($serviciosNota) > 2 || count($equiposNota) > 1 || mb_strlen($estadoFisico, 'UTF-8') + mb_strlen($accesoriosTexto, 'UTF-8')
    + mb_strlen($descripcionTexto, 'UTF-8') > 160 || $descripcionConMuchosSaltos;
$cargadorRegistrado = $dataNota['cargador'] !== null;
$cargadorIncluido = $cargadorRegistrado && (int) $dataNota['cargador'] === 1;
$esquemaEquipo = '<svg xmlns="http://www.w3.org/2000/svg" width="140" height="46" viewBox="0 0 140 46" fill="none" stroke="#1c2541" stroke-width="1.2">
<rect x="6" y="4" width="52" height="32" rx="2" fill="#ffffff"/>
<rect x="9" y="7" width="46" height="26" stroke="#94a3b8" stroke-width="0.8"/>
<polygon points="2,36 62,36 66,43 0,43" fill="#e2e8f0"/>
<rect x="78" y="6" width="56" height="35" rx="3" fill="#ffffff"/>
<circle cx="106" cy="23.5" r="4" stroke="#94a3b8" stroke-width="1"/></svg>';
$esquemaEquipoData = 'data:image/svg+xml;base64,' . base64_encode($esquemaEquipo);

function escaparNotaPdf($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Orden de Servicio Ejecutiva - GARESP #<?= escaparNotaPdf($folioNota) ?></title>
<style>
<?= $estilosReporte ?>
/* Adaptación para el motor de PDF, que no admite grid ni flex. */
* { font-family: DejaVu Sans, sans-serif; }
body { display: block; padding: 0; background: #fff; color: #0f172a; }
.doc-sheet { display: block; width: auto; height: auto; padding: 10px 12px; border: 1px solid #cbd5e1; box-shadow: none; overflow: visible; }
.header-top, .sub-bar, .section-bar, .grid-client, .equipment-split, .footer-split, .bottom-auth { display: table; width: 100%; table-layout: fixed; }
.header-top { border-bottom: 2px solid #0b132b; padding-bottom: 5px; }
.brand-lockup, .order-meta, .sub-bar > div, .section-bar > *, .grid-client > div, .equipment-split > div, .footer-split > div, .bottom-auth > div { display: table-cell; vertical-align: top; }
.brand-lockup { width: 65%; }
.brand-logo { display: block; width: 1.25in; height: auto; }
.brand-details .sub { color: #b45309; }
.brand-contact { color: #475569; }
.order-meta { width: 35%; border-left: 1px solid #cbd5e1; vertical-align: middle; }
.order-tag, .section-bar span { color: #475569; }
.order-folio, .section-bar h2 { color: #0b132b; }
.order-folio span { color: #b45309; }
.order-date { color: #0f172a; }
.sub-bar { background: #f8fafc; border-bottom: 1px solid #cbd5e1; color: #1c2541; }
.sub-bar > div:last-child { text-align: right; }
.section-bar { border-bottom: 1px solid #0b132b; }
.section-bar span { text-align: right; }
.grid-client > div:first-child { width: 62%; }
.grid-client > div:last-child { width: 38%; }
.field-line { display: block; border-bottom: 1px solid #94a3b8; }
.field-line label { display: block; color: #475569; }
.field-line .value { color: #0f172a; overflow-wrap: break-word; word-wrap: break-word; }
.eq-fields { width: 58%; padding-right: 8px; }
.eq-fields table { width: 100%; table-layout: fixed; border-collapse: collapse; }
.eq-fields td { width: 50%; padding-right: 5px; vertical-align: top; }
.inspection-box { width: 42%; display: table-cell; border: 1px solid #cbd5e1; background: #f8fafc; }
.insp-title { color: #1c2541; }
.diagram-wrap { display: block; text-align: center; }
.diagram-wrap img { width: 1.3in; height: auto; }
.acc-grid { display: block; border-top: 1px dashed #cbd5e1; }
.acc-item { display: block; }
.box-chk { display: inline-block; line-height: 9px; text-align: center; border: 1px solid #0b132b; }
.box-chk.checked { background: #0b132b; color: #fff; }
.reception-detail { margin-top: 3px; font-size: 7.5px; color: #475569; overflow-wrap: break-word; word-wrap: break-word; }
.reception-details-wide { padding: 2px 6px; background: #f8fafc; }
.work-table { table-layout: fixed; }
.work-table th { background: #0b132b; }
.work-table td { color: #0f172a; border-color: #cbd5e1; overflow-wrap: break-word; word-wrap: break-word; }
.work-table td[colspan] { text-align: left; font-weight: normal; }
.footer-split { border-spacing: 8px 0; margin-left: -8px; }
.legal-terms { width: 62%; border: 1px solid #cbd5e1; background: #f8fafc; color: #475569; }
.legal-terms strong { color: #0b132b; }
.totals-panel { width: 38%; border: 1px solid #0b132b; }
.tot-row, .tot-final { display: table; width: 100%; }
.tot-row > *, .tot-final > * { display: table-cell; }
.tot-row > :last-child, .tot-final > :last-child { text-align: right; }
.tot-final { background: #0b132b; color: #fff; border-top: 2px solid #b45309; }
.bottom-auth { margin-top: 4px; }
.bottom-auth > div { vertical-align: bottom; }
.sign-block { width: 40%; padding-right: 8px; }
.sign-rule { color: #0b132b; border-color: #0b132b; }
.sign-caption, .qr-label, .doc-footer { color: #475569; }
.qr-wrapper { width: 20%; text-align: center; }
.qr-box { width: 72px; height: 72px; border-color: #cbd5e1; }
.qr-box img { width: 72px; height: 72px; }
.doc-footer { margin-top: 2px; padding-top: 2px; border-color: #cbd5e1; }
.compacto .eq-fields .value { font-size: 7px; }
.compacto .work-table td { font-size: 7.5px; padding: 1.5px 5px; }
.compacto .reception-detail { font-size: 7px; }
</style>
</head>
<body>
<div class="doc-sheet<?= $contenidoExtenso ? ' compacto' : '' ?>">
  <div>
    <div class="header-top">
      <div class="brand-lockup">
        <img class="brand-logo" src="<?= escaparNotaPdf($logoData) ?>" alt="GARESP Servicio Informático">
        <div class="brand-details">
          <div class="sub">RFC: GAEA941128IT5</div>
          <div class="brand-contact">Los Pinos #17, Col. La Mixteca, Tlaxiaco, Oax.<br>Tel: <strong>953 127 2241</strong></div>
        </div>
      </div>
      <div class="order-meta">
        <div class="order-tag">Orden de Servicio</div>
        <div class="order-folio"><span>#</span><?= escaparNotaPdf($folioNota) ?></div>
        <div class="order-date"><strong>Ingreso:</strong> <?= escaparNotaPdf($fechaNotaFormateada) ?></div>
      </div>
    </div>
    <div class="sub-bar">
      <div>Cómputo &bull; Impresoras &bull; Cotizaciones &bull; Web</div>
      <div>Estado: <?= escaparNotaPdf($dataNota['estadoServicio']) ?></div>
    </div>

    <div class="section-bar"><h2>I. Información del Cliente</h2><span>Datos de contacto</span></div>
    <div class="grid-client">
      <div class="field-line"><label>Nombre Completo / Razón Social</label><div class="value"><?= escaparNotaPdf(trim($dataNota['nombre'] . ' ' . $dataNota['apellidos'])) ?></div></div>
      <div class="field-line"><label>Teléfono / WhatsApp</label><div class="value"><?= escaparNotaPdf(trim((string) $dataNota['telefonoCliente']) !== '' ? $dataNota['telefonoCliente'] : 'No registrado') ?></div></div>
    </div>

    <div class="section-bar"><h2>II. Especificaciones e Inspección Física</h2><span>Recepción de taller</span></div>
    <div class="equipment-split">
      <div class="eq-fields">
        <?php if (count($equiposNota) > 1) { ?>
        <table class="eq-lista">
          <tr><th>Equipo</th><th>Marca</th><th>Modelo</th><th>No. de serie</th></tr>
          <?php foreach ($equiposNota as $equipoNota) { ?>
          <tr>
            <td><?= escaparNotaPdf($equipoNota['descripcion']) ?></td>
            <td><?= escaparNotaPdf(trim((string) $equipoNota['marca']) !== '' ? $equipoNota['marca'] : 'No registrada') ?></td>
            <td><?= escaparNotaPdf(trim((string) $equipoNota['modelo']) !== '' ? $equipoNota['modelo'] : 'No registrado') ?></td>
            <td><?= escaparNotaPdf(trim((string) $equipoNota['numero_serie']) !== '' ? $equipoNota['numero_serie'] : 'No registrada') ?></td>
          </tr>
          <?php } ?>
        </table>
        <?php } else { $equipoNota = $equiposNota[0]; ?>
        <table>
          <tr>
            <td><div class="field-line"><label>Tipo de Equipo</label><div class="value"><?= escaparNotaPdf($equipoNota['descripcion']) ?></div></div></td>
            <td><div class="field-line"><label>Marca</label><div class="value"><?= escaparNotaPdf(trim((string) $equipoNota['marca']) !== '' ? $equipoNota['marca'] : 'No registrada') ?></div></div></td>
          </tr>
          <tr>
            <td><div class="field-line"><label>Modelo</label><div class="value"><?= escaparNotaPdf(trim((string) $equipoNota['modelo']) !== '' ? $equipoNota['modelo'] : 'No registrado') ?></div></div></td>
            <td><div class="field-line"><label>Número de Serie</label><div class="value"><?= escaparNotaPdf(trim((string) $equipoNota['numero_serie']) !== '' ? $equipoNota['numero_serie'] : 'No registrada') ?></div></div></td>
          </tr>
        </table>
        <?php } ?>
      </div>
      <div class="inspection-box">
        <div class="insp-title">Marcar Golpes / Rayones</div>
        <div class="diagram-wrap"><img src="<?= escaparNotaPdf($esquemaEquipoData) ?>" alt="Esquema de equipo para marcar golpes o rayones"></div>
        <div class="acc-grid"><div class="acc-item"><span class="box-chk<?= $cargadorIncluido ? ' checked' : '' ?>"><?= $cargadorIncluido ? 'X' : '&nbsp;' ?></span> Cargador: <?= $cargadorRegistrado ? ($cargadorIncluido ? 'Sí' : 'No') : 'Sin indicar' ?></div></div>
        <?php if (!$contenidoExtenso) { ?>
          <div class="reception-detail"><strong>Estado físico:</strong> <?= escaparNotaPdf($estadoFisico !== '' ? $estadoFisico : 'No registrado') ?></div>
          <div class="reception-detail"><strong>Accesorios:</strong> <?= escaparNotaPdf($accesoriosTexto !== '' ? $accesoriosTexto : 'No registrados') ?></div>
        <?php } ?>
      </div>
    </div>
    <?php if ($contenidoExtenso) { ?>
      <div class="reception-details-wide">
        <div class="reception-detail"><strong>Estado físico:</strong> <?= escaparNotaPdf($estadoFisico !== '' ? $estadoFisico : 'No registrado') ?></div>
        <div class="reception-detail"><strong>Accesorios:</strong> <?= escaparNotaPdf($accesoriosTexto !== '' ? $accesoriosTexto : 'No registrados') ?></div>
      </div>
    <?php } ?>

    <div class="section-bar"><h2>III. Descripción del Servicio / Diagnóstico</h2><span>Detalle técnico</span></div>
    <table class="work-table">
      <thead><tr><th>Concepto / Trabajo Solicitado</th><th>Importe</th></tr></thead>
      <tbody>
        <?php foreach ($serviciosNota as $detalleServicio) { ?>
        <tr><td><?= escaparNotaPdf($detalleServicio['nombre']) ?></td><td>$<?= number_format((float) $detalleServicio['precio'], 2, '.', ',') ?></td></tr>
        <?php } ?>
        <?php if ($descripcionTexto !== '') { ?>
        <tr><td colspan="2"><strong>Diagnóstico:</strong> <?= $descripcionConMuchosSaltos ? escaparNotaPdf($descripcionTexto) : nl2br(escaparNotaPdf($descripcionTexto)) ?></td></tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
  <div>
    <div class="footer-split">
      <div class="legal-terms">
        <strong>Cláusulas de Servicio y Garantía</strong>
        1. El respaldo de información es responsabilidad exclusiva del cliente; GARESP no responde por pérdida de datos.<br>
        2. Todo diagnóstico genera honorarios mínimos en caso de no aprobarse el presupuesto.<br>
        3. Pasados 30 días de notificada la entrega, el taller no se responsabiliza por equipos abandonados.<br>
        4. Garantía de 30 días en mano de obra presentando este documento.
      </div>
      <div class="totals-panel">
        <div class="tot-body">
          <div class="tot-row"><span>Subtotal:</span><strong>$<?= number_format($costoServicio, 2, '.', ',') ?></strong></div>
          <div class="tot-row"><span>Anticipo:</span><span>$<?= number_format($anticipo, 2, '.', ',') ?></span></div>
          <div class="tot-row"><span>Pago adicional:</span><span>$<?= number_format((float) $dataNota['pagado'], 2, '.', ',') ?></span></div>
          <div class="tot-row"><span>Restante:</span><span>$<?= number_format($restante, 2, '.', ',') ?></span></div>
        </div>
        <div class="tot-final"><span>TOTAL</span><span>$<?= number_format($costoServicio, 2, '.', ',') ?></span></div>
      </div>
    </div>
    <div class="bottom-auth">
      <div class="sign-block"><div class="sign-rule">Firma de Conformidad del Cliente</div><div class="sign-caption">Acepto estado físico y condiciones de servicio</div></div>
      <div class="sign-block"><div class="sign-rule">GARESP Servicio Informático</div><div class="sign-caption">Técnico receptor autorizado</div></div>
      <div class="qr-wrapper" title="Escanea para contactar por WhatsApp"><div class="qr-box"><img src="<?= escaparNotaPdf($qrData) ?>" alt="Código QR de WhatsApp"></div><div class="qr-label">WhatsApp</div></div>
    </div>
    <div class="doc-footer">GARESP Servicio Informático &bull; Tlaxiaco, Oaxaca &bull; Atención y seguimiento: 953 127 2241</div>
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
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper(array(0, 0, 396.85, 609.45));
$dompdf->render();
$dompdf->stream('nota-' . $folioNota . '.pdf', array('Attachment' => false));
?>
