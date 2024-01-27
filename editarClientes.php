<?php 
include('conexion.php');

if (isset($_GET['updCliente'])) $idCliente= $_GET['updCliente'];

$sqlSearchCliente   = ("SELECT * FROM clientes WHERE id_cliente='".$idCliente."' LIMIT 1 ");
$queryCliente    = mysqli_query($conn, $sqlSearchCliente);
$dataCliente     = mysqli_fetch_array($queryCliente);

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
<h2 style="  text-align: center;">Editar Cliente</h2>
        <div id="registro" class="container">
            <form action="actualizarClientes.php" method="post">
            <div class="col-12">
        <fieldset>
          <legend>ID Cliente</legend>
          <label for="">ID Cliente</label>
          <input
          class="form-control"
            type="text"
            name="id"
            value="<?php echo $dataCliente['id_cliente']?>"
          />
        </fieldset>
      </div>
                <div class="row" style="display:none">
                <label for="">Fecha Registro</label>
                <input type="datetime" name="fechaRegistro" value="<?php echo $dataCliente['fecha_alta']?>" class="form-control">
                </div>

                <div class="row">
                    <div class="col-6">
                    <label for="">Nombre</label>
                <input type="text" name="nombre" class="form-control" value="<?php echo $dataCliente['nombre']?>">
                    </div>
                    <div class="col-6">
                    <label for="">Apellidos</label>
                <input type="text" name="apellidos" class="form-control" value="<?php echo $dataCliente['apellidos']?>">
                    </div>
                </div>
               
             
         
                <label for="">Profesion</label>
                <select name="profesionocupacion" class="select">
                    <option value="">Selecciona una profesion o ocupacion</option>
                    <?php
                    $ventasTotales = "SELECT * FROM profesion";  
                    $resultado = mysqli_query($conn,$ventasTotales);
                       while($mostrar = mysqli_fetch_array($resultado)){
                    $id=$mostrar["id_profesion"];
                    $profesion = $mostrar["descripcionProfesion"];

                    if ($id==$dataCliente['profesion']) {
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
                <label for="">Calle</label>
                <input type="text" name="calle" class="form-control" value="<?php echo $dataCliente['calle']?>">
                
                <label for="">Colonia - Localidad</label>
                <input type="text" name="domicilio" class="form-control" value="<?php echo $dataCliente['domicilio']?>">
                <label for="">Telefono</label>
                <input type="text" name="telefono" class="form-control" value="<?php echo $dataCliente['telefono']?>">
              
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </form>
        </div>
    
</body>
</html>