<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
require_once __DIR__ . '/cotizaciones_db.php';

$errorCotizacion = isset($_SESSION['cotizacion_error']) ? $_SESSION['cotizacion_error'] : '';
unset($_SESSION['cotizacion_error']);
$creada = isset($_GET['creada']) ? filter_var($_GET['creada'], FILTER_VALIDATE_INT) : false;
$actualizada = isset($_GET['actualizada']) ? filter_var($_GET['actualizada'], FILTER_VALIDATE_INT) : false;
$cotizaciones = array();
$resultadoCotizaciones = mysqli_query(
    $conn,
    'SELECT id_cotizacion, folio, cliente_nombre, fecha_cotizacion, vigencia_hasta, moneda, total, estado
     FROM cotizaciones ORDER BY id_cotizacion DESC LIMIT 50'
);

if ($resultadoCotizaciones === false) {
    error_log('No fue posible consultar las cotizaciones: ' . mysqli_error($conn));
    http_response_code(500);
    exit('No se pudieron cargar las cotizaciones.');
}

while ($cotizacion = mysqli_fetch_assoc($resultadoCotizaciones)) {
    $cotizaciones[] = $cotizacion;
}

$editarCotizacion = false;
$datosCotizacion = array(
    'cliente_nombre' => '',
    'cliente_empresa' => '',
    'cliente_email' => '',
    'cliente_telefono' => '',
    'cliente_rfc' => '',
    'cliente_direccion' => '',
    'vigencia_hasta' => date('Y-m-d', strtotime('+15 days')),
    'moneda' => 'MXN',
    'condiciones_pago' => '',
    'tiempo_entrega' => '',
    'garantia' => '',
    'notas' => ''
);
$itemsFormulario = array(array(
    'tipo_producto' => '',
    'marca' => '',
    'modelo' => '',
    'sku' => '',
    'descripcion' => '',
    'especificaciones' => '',
    'cantidad' => 1,
    'precio_unitario' => 0,
    'descuento_porcentaje' => 0,
    'impuesto_porcentaje' => 16,
    'garantia' => '',
    'imagen' => ''
));

if (isset($_GET['editar'])) {
    $idEditar = filter_var($_GET['editar'], FILTER_VALIDATE_INT);
    if ($idEditar === false || $idEditar < 1) {
        http_response_code(400);
        exit('La cotización seleccionada no es válida.');
    }

    $consultaEdicion = mysqli_prepare($conn, 'SELECT * FROM cotizaciones WHERE id_cotizacion = ? LIMIT 1');
    if ($consultaEdicion === false) {
        error_log('No fue posible preparar la cotización para edición: ' . mysqli_error($conn));
        http_response_code(500);
        exit('No se pudo cargar la cotización.');
    }
    $consultaEdicion->bind_param('i', $idEditar);
    if (!$consultaEdicion->execute()) {
        error_log('No fue posible cargar la cotización para edición: ' . $consultaEdicion->error);
        $consultaEdicion->close();
        http_response_code(500);
        exit('No se pudo cargar la cotización.');
    }
    $datosEdicion = $consultaEdicion->get_result()->fetch_assoc();
    $consultaEdicion->close();
    if (!$datosEdicion) {
        http_response_code(404);
        exit('No se encontró la cotización seleccionada.');
    }

    $consultaDetallesEdicion = mysqli_prepare(
        $conn,
        'SELECT tipo_producto, marca, modelo, sku, descripcion, especificaciones, cantidad,
                precio_unitario, descuento_porcentaje, impuesto_porcentaje, garantia, imagen
         FROM cotizacion_detalles WHERE id_cotizacion = ? ORDER BY orden, id_detalle'
    );
    if ($consultaDetallesEdicion === false) {
        error_log('No fue posible preparar los conceptos para edición: ' . mysqli_error($conn));
        http_response_code(500);
        exit('No se pudieron cargar los conceptos.');
    }
    $consultaDetallesEdicion->bind_param('i', $idEditar);
    if (!$consultaDetallesEdicion->execute()) {
        error_log('No fue posible cargar los conceptos para edición: ' . $consultaDetallesEdicion->error);
        $consultaDetallesEdicion->close();
        http_response_code(500);
        exit('No se pudieron cargar los conceptos.');
    }
    $detallesEdicion = $consultaDetallesEdicion->get_result()->fetch_all(MYSQLI_ASSOC);
    $consultaDetallesEdicion->close();

    $editarCotizacion = true;
    $datosCotizacion = $datosEdicion;
    $itemsFormulario = count($detallesEdicion) > 0 ? $detallesEdicion : $itemsFormulario;
}

function escaparCotizacion($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

include __DIR__ . '/encabezado.php';
?>
<main class="cotizacion-page">
  <section class="cotizacion-intro">
    <div>
      <span class="cotizacion-eyebrow"><i class="fa-solid fa-microchip" aria-hidden="true"></i> GESTIÓN COMERCIAL · SISTEMAS</span>
      <h1><?= $editarCotizacion ? 'Editar cotización' : 'Cotizaciones' ?></h1>
      <p><?= $editarCotizacion ? 'Actualiza los datos y conceptos de ' . escaparCotizacion($datosCotizacion['folio']) . '.' : 'Administra propuestas de tecnología, equipo de cómputo y servicios.' ?></p>
    </div>
    <div class="cotizacion-intro-side">
      <?php if ($editarCotizacion) { ?><span class="cotizacion-folio-chip"><?= escaparCotizacion($datosCotizacion['folio']) ?></span><?php } ?>
      <span class="cotizacion-intro-icon" aria-hidden="true"><i class="fa-solid fa-file-invoice-dollar"></i></span>
    </div>
  </section>

  <?php if ($errorCotizacion !== '') { ?>
    <div class="alert alert-danger" role="alert"><?= escaparCotizacion($errorCotizacion) ?></div>
  <?php } ?>
  <?php if ($creada !== false && $creada > 0) { ?>
    <div class="alert alert-success cotizacion-success" role="status">
      Cotización guardada correctamente.
      <a class="btn btn-success btn-sm" href="cotizacionPDF.php?id=<?= (int) $creada ?>" target="_blank" rel="noopener">Ver reporte PDF</a>
    </div>
  <?php } ?>
  <?php if ($actualizada !== false && $actualizada > 0) { ?>
    <div class="alert alert-success cotizacion-success" role="status">
      Cotización actualizada correctamente.
      <a class="btn btn-success btn-sm" href="cotizacionPDF.php?id=<?= (int) $actualizada ?>" target="_blank" rel="noopener">Ver reporte PDF actualizado</a>
    </div>
  <?php } ?>

  <?php include __DIR__ . '/cotizacion_formulario.php'; ?>

  <section class="cotizacion-panel cotizacion-history">
    <div class="cotizacion-panel-heading">
      <div><span class="cotizacion-step"><i class="fa-solid fa-clock-rotate-left"></i></span><h2>Cotizaciones recientes</h2></div>
      <p>Últimas 50 propuestas registradas.</p>
    </div>
    <div class="table-responsive">
      <table class="table table-hover cotizacion-table">
        <thead><tr><th>Folio</th><th>Cliente</th><th>Fecha</th><th>Vigencia</th><th>Total</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
          <?php if (count($cotizaciones) === 0) { ?>
            <tr><td colspan="7" class="cotizacion-empty">Aún no hay cotizaciones registradas.</td></tr>
          <?php } ?>
          <?php foreach ($cotizaciones as $fila) { ?>
            <tr>
              <td><strong><?= escaparCotizacion($fila['folio']) ?></strong></td>
              <td><?= escaparCotizacion($fila['cliente_nombre']) ?></td>
              <td><?= escaparCotizacion(date('d/m/Y', strtotime($fila['fecha_cotizacion']))) ?></td>
              <td><?= escaparCotizacion(date('d/m/Y', strtotime($fila['vigencia_hasta']))) ?></td>
              <td><?= escaparCotizacion($fila['moneda']) ?> $<?= number_format((float) $fila['total'], 2) ?></td>
              <td><span class="cotizacion-status"><?= escaparCotizacion($fila['estado']) ?></span></td>
              <td class="cotizacion-actions">
                <a class="cotizacion-edit-link" href="cotizacion.php?editar=<?= (int) $fila['id_cotizacion'] ?>" aria-label="Editar cotización <?= escaparCotizacion($fila['folio']) ?>"><i class="fa-solid fa-pen-to-square"></i> Editar</a>
                <a class="cotizacion-pdf-link" href="cotizacionPDF.php?id=<?= (int) $fila['id_cotizacion'] ?>" target="_blank" rel="noopener" aria-label="Abrir PDF <?= escaparCotizacion($fila['folio']) ?>"><i class="fa-solid fa-file-pdf"></i> PDF</a>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </section>
</main>
<script src="js/cotizacion.js"></script>
<?php include __DIR__ . '/footer.php'; ?>
