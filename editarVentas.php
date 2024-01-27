<?php 
include('conexion.php');

if (isset($_GET['updVenta'])) $idVenta= $_GET['updVenta'];

$sqlSearchVenta   = ("SELECT * FROM ventas WHERE id_venta='".$idVenta."' LIMIT 1 ");
$queryVenta    = mysqli_query($conn, $sqlSearchVenta);
$dataVenta     = mysqli_fetch_array($queryVenta);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SoftGaresp</title>
    <link rel="icon" href="logotipo.png">

    <!-- ARCHIVOS PERSONALIZADOS -->
    <script src="jquery.js"></script>
    <script src="index.js"></script>
    <link rel="stylesheet" href="style.css">
    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <!-- DATATABLES -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.css" />
    <link rel="stylesheet" href="responsive.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.js"></script>
    <script src="dataTables.responsive.min.js"></script> 

     <!-- SELECT2 -->
     <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- FUENTES -->
    <link rel="stylesheet" href="fontawesome-free-6.5.1-web/fontawesome-free-6.5.1-web/css/all.min.css">
</head>
<body>
<h2 style="  text-align: center;">Editar Ventas</h2>
        <div id="registro" class="container">
        <form action="actualizarVentas.php" method="post">
                <div class="row" >
                <div class="col-12">
        <fieldset>
          <legend>ID Venta</legend>
          <label for="">ID Venta</label>
          <input
          class="form-control"
            type="text"
            name="id"
            value="<?php echo $dataVenta['id_venta']?>"
          />
        </fieldset>
      </div>
                    <div class="col-12" style="display:none">
                        <div class="row">
                            <label for="">Fecha de venta</label>
                            <input type="datetime" name="fechaVenta" value="<?php echo $dataVenta['fecha_venta']?>" class="form-control datepicker">
                        </div>
                    </div>
                    <div class="col-2">

                        <label for="">Cantidad</label>
                        <input type="number" value="<?php echo $dataVenta['cantidad']?>" name="cantidad" class="form-control" >
                    </div>
                    <div class="col-10">

                        <label for="">Descripcion</label>
                        <input type="text" name="descripcion" class="form-control" value="<?php echo $dataVenta['descripcion']?>">
                    </div>
                    <div class="col-6">

                        <label for="">Costo</label>
                        <input type="number" name="costo" class="form-control" step="0.01" value="<?php echo $dataVenta['costo']?>">
                    </div>
                    <div class="col-6">

                        <label for="">Seccion</label>
                        <select name="opcionnegocio" class="select">
                            <option value="">Selecciona una opcion</option>
                            <?php
                    $ventasTotales = "SELECT * FROM seccion";  
                    $resultado = mysqli_query($conn,$ventasTotales);
                       while($mostrar = mysqli_fetch_array($resultado)){
                    $id=$mostrar["id_seccion"];
                    $profesion = $mostrar["descripcionSeccion"];

                    if ($id==$dataVenta['seccion']) {
            ?>
            <option value="<?php echo $id; ?>" selected>
              <?php echo $profesion?>
            </option>
      <?php }else{?>
        <option value="<?php echo $id; ?>">
        <?php echo $profesion?>
            </option>

            <?php
                  } }
              ?>
                        </select>
                    </div>
              
                <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
                
            </form>
        </div>
    
</body>
</html>