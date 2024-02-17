<?php 
include('conexion.php');

if (isset($_GET['updNotas'])) $idNota= $_GET['updNotas'];

$sqlSearchNotas   = ("SELECT * FROM notas WHERE id_nota='".$idNota."' LIMIT 1 ");
$queryNotas    = mysqli_query($conn, $sqlSearchNotas);
$dataNota     = mysqli_fetch_array($queryNotas);
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
<h2 style="  text-align: center;">Edición Notas </h2>


<div id="registro" class="container">
  <form action="actualizarNotas.php" method="post">
    <div class="row">
      <div class="col-12 col-sm-12">
        <fieldset>
          <legend>ID Cliente</legend>
          <label for="">ID Cliente</label>
          <input
          class="form-control"
            type="text"
            name="id"
            value="<?php echo $dataNota['id_nota']?>"
          />
        </fieldset>
      </div>
    </div>
    <div class="row">
      <div class="col-12  col-sm-12">
        <fieldset>
          <legend>Fecha</legend>
          <label for="">Fecha de entrada</label>
          <input
          class="form-control"
            type="datetime"
            name="fechaEntrada"
            value="<?php echo $dataNota['fecha_alta']?>"
          />
        </fieldset>
      </div>
    </div>

    <div class="row">
      <div class="col-12  col-sm-12">
        <fieldset>
          <legend>Datos del cliente</legend>
          <label for="">Nombre cliente</label>
          <select name="cliente" class="select">
            <option value="">Selecciona a un cliente</option>
            <?php
              $ventasTotales = "SELECT * FROM clientes";  
                  $resultado = mysqli_query($conn,$ventasTotales);
                  while($mostrar = mysqli_fetch_array($resultado)){
                    $id=$mostrar["id_cliente"];
                    $nombre = $mostrar["nombre"];
                    $apellidos = $mostrar["apellidos"];

                    if ($id==$dataNota['id_cliente']) {
            ?>
            <option value="<?php echo $id; ?>" selected>
              <?php echo $nombre ." ".$apellidos?>
            </option>
      <?php }else{?>
<option value="<?php echo $id; ?>">
<?php echo $nombre ." ".$apellidos?>
            </option>

            <?php
                  } }
              ?>
          </select>
        </fieldset>
      </div>
    </div>

    <fieldset>
      <legend>Datos del equipo</legend>
     <div class="col-12  col-sm-12">
     <label for="">Equipo</label>
      <select name="equipo" class="select">
        <option value="">Selecciona un equipo</option>
        <?php
              $ventasTotales = "SELECT * FROM equipo";  
                  $resultado = mysqli_query($conn,$ventasTotales);
                  while($mostrar = mysqli_fetch_array($resultado)){
                    $id=$mostrar["id_equipo"];
                    $descripcion = $mostrar["descripcionEquipo"];

                    if ($id==$dataNota['equipo']) {
            ?>
            <option value="<?php echo $id; ?>" selected>
              <?php echo $descripcion?>
            </option>
      <?php }else{?>
<option value="<?php echo $id; ?>">
<?php echo $descripcion?>
            </option>

            <?php
                  } }
              ?>
      </select>
     </div>
     <div class="row">
     <div class="col-6  col-sm-12">
      <label for="marca">Marca</label>
      <input type="text" name="marca" class="form-control" value="<?php echo $dataNota['marca']?>"/>
      </div>
      <div class="col-6  col-sm-12">
      <label for="modelo">Modelo</label>
      <input type="text" name="modelo" class="form-control" value="<?php echo $dataNota['modelo']?>"/>
      </div>
     </div>
    
     
     
    </fieldset>

    <fieldset>
      <legend>Servicio</legend>
      <label for="">Servicio</label>
      <select name="servicio" class="select">
        <option value="">Seleccione una servicio</option>
        <?php
              $ventasTotales = "SELECT * FROM servicio";  
                  $resultado = mysqli_query($conn,$ventasTotales);
                  while($mostrar = mysqli_fetch_array($resultado)){
                    $id=$mostrar["id_servicio"];
                    $descripcion = $mostrar["tipoServicio"];
                    if ($id==$dataNota['servicio']) {
            ?>
            <option value="<?php echo $id; ?>" selected>
              <?php echo $descripcion?>
            </option>
      <?php }else{?>
<option value="<?php echo $id; ?>">
              <?php echo $descripcion?>
            </option>

            <?php
                  } }
              ?>
      </select>

      <label for="">Descripcion</label>
        <textarea id="" cols="45" rows="3" name="descripcionServicio" class="form-control">
        <?php echo $dataNota['descripcionServicio'];?>
      </textarea>
    </fieldset>

    <fieldset>
      <legend>Estado del equipo</legend>
      <label for="">Estado del equipo</label>
    
      <select name="estado" class="select">
        <option value="">Seleccione un estado</option>
        <?php
              $ventasTotales = "SELECT * FROM estadoservicio";  
                  $resultado = mysqli_query($conn,$ventasTotales);
                  while($mostrar = mysqli_fetch_array($resultado)){
                    $id=$mostrar["id_estado"];
                    $descripcion = $mostrar["estadoServicio"];

                    if ($id==$dataNota['estado']) {
            ?>
            <option value="<?php echo $id; ?>" selected>
              <?php echo $descripcion?>
            </option>
      <?php }else{?>
<option value="<?php echo $id; ?>">
<?php echo $descripcion?>
            </option>

            <?php
                  } }
              ?>
      </select>
    </fieldset>
    <fieldset>
      <legend>Costo</legend>
      <div class="row">
        <div class="col-md-4  col-sm-12">
          <label for="">Costo del servicio</label>
          <input type="number" name="costoServicio" value="<?php echo $dataNota['costoServicio']?>" class="form-control"/>
        </div>
        <div class="col-md-4  col-sm-12">
          <label for="">Ganancia</label>
          <input type="number" name="ganancia" value="<?php echo $dataNota['ganancia']?>" class="form-control"/>
        </div>
        <div class="col-md-4  col-sm-12">
          <label for="">Anticipo</label>
          <input type="number" name="anticipo" value="<?php echo $dataNota['anticipo']?>" class="form-control"/>
        </div>
        <div class="col-md-4  col-sm-12">
          <label for="">Restante</label>
          <input type="number" name="restante" value="<?php echo $dataNota['restante']?>" class="form-control"/>
        </div>
        <div class="col-md-4  col-sm-12">
          <label for="">Pagado</label>
          <input type="number" name="pagado" value="<?php echo $dataNota['pagado']?>" class="form-control"/>
        </div>
      </div>
    </fieldset>

    <fieldset>
   
      <legend>Notas</legend>
      <label for="">Ingrese las notas adicionales</label>
      <div class="row">
        <div class="col-12 col-sm-12">

           <textarea name="nota" id="" cols="45" rows="3" class="form-control">
             <?php echo $dataNota['notas'];?>
           </textarea>
        </div>
        </div>
  
 
    </fieldset>

    <button type="submit" class="btn btn-primary">Actualizar</button>
  </form>
</div>
    
</body>
</html>