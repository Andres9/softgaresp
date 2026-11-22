<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
mysqli_report(MYSQLI_REPORT_OFF);
require_once __DIR__ . '/ventas_db.php';

$idVenta = filter_input(INPUT_GET, 'updVenta', FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
if (!$idVenta) {
  http_response_code(400);
  exit('La venta solicitada no es válida.');
}
$consultaVenta = mysqli_prepare($conn, 'SELECT * FROM ventas WHERE id_venta = ?');
if (!$consultaVenta) {
  error_log('No se pudo preparar la edición de venta: ' . mysqli_error($conn));
  http_response_code(500);
  exit('No se pudo cargar la venta.');
}
$consultaVenta->bind_param('i', $idVenta);
if (!$consultaVenta->execute() || !($queryVenta = $consultaVenta->get_result())) {
  error_log('No se pudo consultar la venta: ' . $consultaVenta->error);
  http_response_code(500);
  exit('No se pudo cargar la venta.');
}
$dataVenta = $queryVenta->fetch_assoc();
$consultaVenta->close();
if (!$dataVenta) {
  http_response_code(404);
  exit('No se encontró la venta.');
}
$escapar = function ($valor) { return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8'); };

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SoftGaresp</title>
  <link rel="icon" href="img/logotipo.png" />
  <!-- ARCHIVOS PERSONALIZADOS -->
  <link rel="stylesheet" href="css/style.css" />
  <!-- BOOTSTRAP -->
  <link href="css/bootstrap.min.css" rel="stylesheet" />

  <!-- DATATABLES -->
  <link rel="stylesheet" href="css/datatables.min.css" />
  <link rel="stylesheet" href="css/responsive.dataTables.min.css" />


  <link rel="stylesheet" href="css/select2.min.css" />
  <link rel="stylesheet" href="css/selectores.css" />
  <script src="js/jquery-3.7.1.js" defer></script>
  <script src="js/select2.min.js" defer></script>
  <script src="js/selectores.js" defer></script>


  <!-- FUENTES -->
  <link rel="stylesheet" href="fontawesome-free-6.5.1-web/fontawesome-free-6.5.1-web/css/all.min.css" />
</head>

<body>
  <h2 style="  text-align: center;">Editar Ventas</h2>
  <div id="registro" class="container">
    <form action="actualizarVentas.php" method="post">
      <input type="hidden" name="token_csrf" value="<?= $escapar(token_csrf()) ?>">
      <div class="row">
        <div class="col-12">
          <fieldset>
            <legend>ID Venta</legend>
            <label for="">ID Venta</label>
            <input class="form-control" type="text" name="id" value="<?= $escapar($dataVenta['id_venta']) ?>" readonly />
            <?php if ($dataVenta['id_grupo'] !== null) { ?><small>Grupo <?= (int) $dataVenta['id_grupo'] ?>. La factura y la fecha se comparten con todos sus productos.</small><?php } ?>
          </fieldset>
        </div>
        <div class="col-12" style="display:none">
          <div class="row">
            <label for="">Fecha de venta</label>
            <input type="text" name="fechaVenta" value="<?= $escapar($dataVenta['fecha_venta']) ?>"
              class="form-control datepicker">
          </div>
        </div>
        <div class="col-2">

          <label for="">Cantidad</label>
          <input type="number" min="1" step="1" required value="<?= $escapar($dataVenta['cantidad']) ?>" name="cantidad" class="form-control">
        </div>
        <div class="col-10">

          <label for="">Descripcion</label>
          <input type="text" name="descripcion" maxlength="50" required class="form-control" value="<?= $escapar($dataVenta['descripcion']) ?>">
        </div>
        <div class="col-6">

          <label for="">Importe total del producto</label>
          <input type="number" name="costo" required class="form-control" step="0.01" value="<?= $escapar($dataVenta['costo']) ?>">
        </div>
        <div class="col-md-4 col-xs-12">
          <label for="">Precio compra</label>
          <input type="number" name="pc" class="form-control" step="0.01" value="<?= $escapar($dataVenta['preciocosto']) ?>">
        </div>
        <div class="col-md-4 col-xs-12">
          <label for="">Ganancia</label>
          <input type="number" name="g" class="form-control" step="0.01" value="<?= $escapar($dataVenta['ganancia']) ?>">
        </div>
        <div class="col-4">
          <label for="">Seccion</label>
          <select name="opcionnegocio" class="select">
            <option value="">Selecciona una opcion</option>
            <?php
            $ventasTotales = "SELECT * FROM seccion";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $id = $mostrar["id_seccion"];
              $profesion = $mostrar["descripcionSeccion"];

              if ($id == $dataVenta['seccion']) {
            ?>
                <option value="<?php echo $id; ?>" selected>
                  <?= $escapar($profesion) ?>
                </option>
              <?php } else { ?>
                <option value="<?php echo $id; ?>">
                  <?= $escapar($profesion) ?>
                </option>

            <?php
              }
            }
            ?>
          </select>
        </div>

             <div class="col-4">

          <label for="">Factura</label>
          <input type="text" value="<?= $escapar($dataVenta['factura']) ?>" name="factura" class="form-control">
        </div>


        <button type="submit" class="btn btn-primary">Actualizar</button>
      </div>

    </form>
  </div>

</body>

</html>