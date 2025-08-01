<?php
include('conexion.php');

if (isset($_GET['updVenta']))
  $idVenta = $_GET['updVenta'];

$sqlSearchVenta = ("SELECT * FROM ventas WHERE id_venta='" . $idVenta . "' LIMIT 1 ");
$queryVenta = mysqli_query($conn, $sqlSearchVenta);
$dataVenta = mysqli_fetch_array($queryVenta);

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


  <!-- SELECT2 -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />


  <!-- FUENTES -->
  <link rel="stylesheet" href="fontawesome-free-6.5.1-web/fontawesome-free-6.5.1-web/css/all.min.css" />
</head>

<body>
  <h2 style="  text-align: center;">Editar Ventas</h2>
  <div id="registro" class="container">
    <form action="actualizarVentas.php" method="post">
      <div class="row">
        <div class="col-12">
          <fieldset>
            <legend>ID Venta</legend>
            <label for="">ID Venta</label>
            <input class="form-control" type="text" name="id" value="<?php echo $dataVenta['id_venta'] ?>" />
          </fieldset>
        </div>
        <div class="col-12" style="display:none">
          <div class="row">
            <label for="">Fecha de venta</label>
            <input type="datetime" name="fechaVenta" value="<?php echo $dataVenta['fecha_venta'] ?>"
              class="form-control datepicker">
          </div>
        </div>
        <div class="col-2">

          <label for="">Cantidad</label>
          <input type="number" value="<?php echo $dataVenta['cantidad'] ?>" name="cantidad" class="form-control">
        </div>
        <div class="col-10">

          <label for="">Descripcion</label>
          <input type="text" name="descripcion" class="form-control" value="<?php echo $dataVenta['descripcion'] ?>">
        </div>
        <div class="col-6">

          <label for="">Costo</label>
          <input type="number" name="costo" class="form-control" step="0.01" value="<?php echo $dataVenta['costo'] ?>">
        </div>
        <div class="col-md-4 col-xs-12">
          <label for="">Precio compra</label>
          <input type="number" name="pc" class="form-control" step="0.01" value="<?php echo $dataVenta['preciocosto'] ?>">
        </div>
        <div class="col-md-4 col-xs-12">
          <label for="">Ganancia</label>
          <input type="number" name="g" class="form-control" step="0.01" value="<?php echo $dataVenta['ganancia'] ?>">
        </div>
        <div class="col-6">
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
                  <?php echo $profesion ?>
                </option>
              <?php } else { ?>
                <option value="<?php echo $id; ?>">
                  <?php echo $profesion ?>
                </option>

            <?php
              }
            }
            ?>
          </select>
        </div>
        <div class="col-6">
          <label for="">Factura</label>
          <select name="factura" class="select">
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
                  <?php echo $profesion ?>
                </option>
              <?php } else { ?>
                <option value="<?php echo $id; ?>">
                  <?php echo $profesion ?>
                </option>

            <?php
              }
            }
            ?>
          </select>
        </div>


        <button type="submit" class="btn btn-primary">Actualizar</button>
      </div>

    </form>
  </div>

</body>

</html>