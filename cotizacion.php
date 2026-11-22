<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
require_once __DIR__ . '/cotizaciones_db.php';

$errorCotizacion = isset($_SESSION['cotizacion_error']) ? $_SESSION['cotizacion_error'] : '';
unset($_SESSION['cotizacion_error']);
$errorVenta = isset($_SESSION['venta_error']) ? $_SESSION['venta_error'] : '';
unset($_SESSION['venta_error']);
$creada = isset($_GET['creada']) ? filter_var($_GET['creada'], FILTER_VALIDATE_INT) : false;
$actualizada = isset($_GET['actualizada']) ? filter_var($_GET['actualizada'], FILTER_VALIDATE_INT) : false;
$ventaCreada = isset($_GET['venta_creada']) ? filter_var($_GET['venta_creada'], FILTER_VALIDATE_INT) : false;
$cotizaciones = array();
$resultadoCotizaciones = mysqli_query(
    $conn,
    'SELECT c.id_cotizacion, c.folio, c.cliente_nombre, c.fecha_cotizacion, c.vigencia_hasta,
            c.moneda, c.total, c.estado, v.id_venta, v.folio AS folio_venta,
            v.total_abonado, v.saldo
     FROM cotizaciones c
     LEFT JOIN cotizacion_ventas v ON v.id_cotizacion = c.id_cotizacion
     ORDER BY c.id_cotizacion DESC LIMIT 50'
);

if ($resultadoCotizaciones === false) {
    error_log('No fue posible consultar las cotizaciones: ' . mysqli_error($conn));
    http_response_code(500);
    exit('No se pudieron cargar las cotizaciones.');
}

while ($cotizacion = mysqli_fetch_assoc($resultadoCotizaciones)) {
    $cotizaciones[] = $cotizacion;
}

$cotizacionParaVenta = null;
if (isset($_GET['venta'])) {
    $idCotizacionVenta = filter_var($_GET['venta'], FILTER_VALIDATE_INT);
    if ($idCotizacionVenta === false || $idCotizacionVenta < 1) {
        http_response_code(400);
        exit('La cotización seleccionada para venta no es válida.');
    }
    $consultaVenta = mysqli_prepare(
        $conn,
        'SELECT c.id_cotizacion, c.folio, c.cliente_nombre, c.moneda, c.total, c.estado, v.id_venta
         FROM cotizaciones c LEFT JOIN cotizacion_ventas v ON v.id_cotizacion = c.id_cotizacion
         WHERE c.id_cotizacion = ? LIMIT 1'
    );
    if ($consultaVenta === false) {
        error_log('No fue posible preparar la cotización para venta: ' . mysqli_error($conn));
        http_response_code(500);
        exit('No se pudo cargar la cotización para venta.');
    }
    $consultaVenta->bind_param('i', $idCotizacionVenta);
    if (!$consultaVenta->execute()) {
        error_log('No fue posible cargar la cotización para venta: ' . $consultaVenta->error);
        $consultaVenta->close();
        http_response_code(500);
        exit('No se pudo cargar la cotización para venta.');
    }
    $cotizacionParaVenta = $consultaVenta->get_result()->fetch_assoc();
    $consultaVenta->close();
    if (!$cotizacionParaVenta) {
        http_response_code(404);
        exit('No se encontró la cotización seleccionada.');
    }
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
    if ($datosEdicion['estado'] === 'Vendida') {
        http_response_code(409);
        exit('Una cotización convertida en venta ya no se puede modificar.');
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
  <?php if ($errorVenta !== '') { ?>
    <div class="alert alert-danger" role="alert"><?= escaparCotizacion($errorVenta) ?></div>
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
  <?php if ($ventaCreada !== false && $ventaCreada > 0) { ?>
    <div class="alert alert-success cotizacion-success" role="status">
      Venta creada correctamente. El saldo por cobrar se actualizará con cada abono.
      <a class="btn btn-success btn-sm" href="ventaPDF.php?id=<?= (int) $ventaCreada ?>" target="_blank" rel="noopener">Ver PDF de venta</a>
    </div>
  <?php } ?>

  <?php if ($cotizacionParaVenta && !$cotizacionParaVenta['id_venta'] && $cotizacionParaVenta['estado'] !== 'Vendida') { ?>
    <section class="cotizacion-conversion">
      <h2>Convertir <?= escaparCotizacion($cotizacionParaVenta['folio']) ?> en venta</h2>
      <p>Cliente: <?= escaparCotizacion($cotizacionParaVenta['cliente_nombre']) ?> · Total: <?= escaparCotizacion($cotizacionParaVenta['moneda']) ?> $<?= number_format((float) $cotizacionParaVenta['total'], 2) ?></p>
      <form action="guardarVentaCotizacion.php" method="post">
        <input type="hidden" name="token_csrf" value="<?= escaparCotizacion(token_csrf()) ?>">
        <input type="hidden" name="id_cotizacion" value="<?= (int) $cotizacionParaVenta['id_cotizacion'] ?>">
        <label>Anticipo inicial
          <input type="number" name="anticipo" min="0" max="<?= escaparCotizacion($cotizacionParaVenta['total']) ?>" step="0.01" value="0" required>
        </label>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-cash-register"></i> Crear venta</button>
        <a href="cotizacion.php" class="btn btn-outline-secondary">Cancelar</a>
      </form>
    </section>
  <?php } ?>

  <?php include __DIR__ . '/cotizacion_formulario.php'; ?>

  <section class="cotizacion-panel cotizacion-history">
    <div class="cotizacion-panel-heading">
      <div><span class="cotizacion-step"><i class="fa-solid fa-clock-rotate-left"></i></span><h2>Cotizaciones recientes</h2></div>
      <p>Últimas 50 propuestas registradas.</p>
    </div>
    <div class="table-responsive">
      <table class="table table-hover cotizacion-table">
        <thead><tr><th>Folio</th><th>Cliente</th><th>Fecha</th><th>Vigencia</th><th>Total</th><th>Saldo por cobrar</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
          <?php if (count($cotizaciones) === 0) { ?>
            <tr><td colspan="8" class="cotizacion-empty">Aún no hay cotizaciones registradas.</td></tr>
          <?php } ?>
          <?php foreach ($cotizaciones as $fila) { ?>
            <tr>
              <td><strong><?= escaparCotizacion($fila['folio']) ?></strong></td>
              <td><?= escaparCotizacion($fila['cliente_nombre']) ?></td>
              <td><?= escaparCotizacion(date('d/m/Y', strtotime($fila['fecha_cotizacion']))) ?></td>
              <td><?= escaparCotizacion(date('d/m/Y', strtotime($fila['vigencia_hasta']))) ?></td>
              <td><?= escaparCotizacion($fila['moneda']) ?> $<?= number_format((float) $fila['total'], 2) ?></td>
              <td><?= $fila['id_venta'] ? escaparCotizacion($fila['moneda']) . ' $' . number_format((float) $fila['saldo'], 2) : '—' ?></td>
              <td><span class="cotizacion-status"><?= escaparCotizacion($fila['id_venta'] ? ($fila['saldo'] > 0 ? 'Venta con saldo' : 'Venta pagada') : $fila['estado']) ?></span></td>
              <td class="cotizacion-actions">
                <?php if (!$fila['id_venta']) { ?>
                <a class="cotizacion-edit-link" href="cotizacion.php?editar=<?= (int) $fila['id_cotizacion'] ?>" aria-label="Editar cotización <?= escaparCotizacion($fila['folio']) ?>"><i class="fa-solid fa-pen-to-square"></i> Editar</a>
                <?php } ?>
                <a class="cotizacion-pdf-link" href="cotizacionPDF.php?id=<?= (int) $fila['id_cotizacion'] ?>" target="_blank" rel="noopener" aria-label="Abrir PDF <?= escaparCotizacion($fila['folio']) ?>"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                <?php if ($fila['id_venta']) { ?>
                  <a class="cotizacion-sale-link" href="ventaPDF.php?id=<?= (int) $fila['id_venta'] ?>" target="_blank" rel="noopener">PDF venta</a>
                  <?php if ((float) $fila['saldo'] > 0) { ?>
                  <form class="cotizacion-payment-form" action="guardarAbonoVenta.php" method="post">
                    <input type="hidden" name="token_csrf" value="<?= escaparCotizacion(token_csrf()) ?>">
                    <input type="hidden" name="id_venta" value="<?= (int) $fila['id_venta'] ?>">
                    <input type="number" name="abono" min="0.01" max="<?= escaparCotizacion($fila['saldo']) ?>" step="0.01" placeholder="Abono" required aria-label="Abono a la venta <?= escaparCotizacion($fila['folio_venta']) ?>">
                    <button type="submit">Registrar</button>
                  </form>
                  <?php } ?>
                <?php } else { ?>
                  <a class="cotizacion-edit-link" href="cotizacion.php?venta=<?= (int) $fila['id_cotizacion'] ?>"><i class="fa-solid fa-arrow-right-arrow-left"></i> Convertir</a>
                <?php } ?>
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
