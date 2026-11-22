<?php 
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
header('Cache-Control: no-store, private');
include('conexion.php');
require_once __DIR__ . '/notas_recepcion.php';
prepararRecepcionNotas($conn);

$idNota = isset($_GET['updNotas']) ? filter_var($_GET['updNotas'], FILTER_VALIDATE_INT) : false;
if ($idNota === false || $idNota < 1) {
  http_response_code(400);
  exit('La nota solicitada no es válida.');
}

$consultaNota = mysqli_prepare($conn, 'SELECT n.*, r.cargador, r.accesorios, r.numero_serie, r.estado_fisico, r.contrasena_cifrada, r.patron_cifrado FROM notas n LEFT JOIN nota_recepcion r ON r.id_nota = n.id_nota WHERE n.id_nota = ? LIMIT 1');
if (!$consultaNota) {
  error_log('No se pudo preparar la edición de la nota: ' . mysqli_error($conn));
  http_response_code(500);
  exit('No se pudo cargar la nota.');
}
$consultaNota->bind_param('i', $idNota);
if (!$consultaNota->execute()) {
  error_log('No se pudo consultar la nota: ' . $consultaNota->error);
  http_response_code(500);
  exit('No se pudo cargar la nota.');
}
$dataNota = $consultaNota->get_result()->fetch_assoc();
$consultaNota->close();
if (!$dataNota) {
  http_response_code(404);
  exit('No se encontró la nota.');
}
$recepcion = $dataNota;
if (canalSeguroNota()) {
  try {
    $recepcion['clave_dispositivo'] = descifrarRecepcionNota($dataNota['contrasena_cifrada']);
    $recepcion['patron_dispositivo'] = descifrarRecepcionNota($dataNota['patron_cifrado']);
  } catch (RuntimeException $error) {
    error_log('No se pudo leer el acceso del dispositivo: ' . $error->getMessage());
    http_response_code(500);
    exit('No se pudo leer la información de acceso de la nota.');
  }
}
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
  <link rel="stylesheet" href="css/nota.css" />
  <link rel="stylesheet" href="css/select2.min.css" />
  <link rel="stylesheet" href="css/selectores.css" />
  <script src="js/jquery-3.7.1.js" defer></script>
  <script src="js/select2.min.js" defer></script>
  <script src="js/selectores.js" defer></script>
  <script src="js/nota.js" defer></script>


  <!-- FUENTES -->
  <link rel="stylesheet" href="fontawesome-free-6.5.1-web/fontawesome-free-6.5.1-web/css/all.min.css" />
</head>
<body>
<h2 style="  text-align: center;">Edición Notas </h2>


<div id="registro" class="container">
  <form action="actualizarNotas.php" method="post" class="nota-formulario">
    <input type="hidden" name="token_csrf" value="<?= htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8') ?>">
    <div class="row">
      <div class="col-12 col-sm-12">
        <fieldset>
          <legend>Nota</legend>
          <label for="">ID de nota</label>
          <input
          class="form-control"
            type="text"
            name="id"
            value="<?= (int) $dataNota['id_nota'] ?>"
            readonly
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
            value="<?= escaparRecepcionHtml($dataNota['fecha_alta']) ?>"
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
            <option value="<?= (int) $id ?>" selected>
              <?= escaparRecepcionHtml($nombre . ' ' . $apellidos) ?>
            </option>
      <?php }else{?>
<option value="<?= (int) $id ?>">
<?= escaparRecepcionHtml($nombre . ' ' . $apellidos) ?>
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
      <select id="nota-equipo" name="equipo" class="select">
        <option value="">Selecciona un equipo</option>
        <?php
              $ventasTotales = "SELECT * FROM equipo";  
                  $resultado = mysqli_query($conn,$ventasTotales);
                  while($mostrar = mysqli_fetch_array($resultado)){
                    $id=$mostrar["id_equipo"];
                    $descripcion = $mostrar["descripcionEquipo"];

                    if ($id==$dataNota['equipo']) {
            ?>
            <option value="<?= (int) $id ?>" data-patron="<?= equipoAdmitePatron($descripcion) ? '1' : '0' ?>" selected>
              <?= htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') ?>
            </option>
      <?php }else{?>
<option value="<?= (int) $id ?>" data-patron="<?= equipoAdmitePatron($descripcion) ? '1' : '0' ?>">
<?= htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') ?>
            </option>

            <?php
                  } }
              ?>
      </select>
     </div>
     <div class="row">
     <div class="col-6  col-sm-12">
      <label for="marca">Marca</label>
      <input type="text" name="marca" class="form-control" value="<?= escaparRecepcionHtml($dataNota['marca']) ?>"/>
      </div>
      <div class="col-6  col-sm-12">
      <label for="modelo">Modelo</label>
      <input type="text" name="modelo" class="form-control" value="<?= escaparRecepcionHtml($dataNota['modelo']) ?>"/>
      </div>
     </div>
    
     
     
    </fieldset>

    <?php include __DIR__ . '/nota_recepcion_formulario.php'; ?>

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
            <option value="<?= (int) $id ?>" selected>
              <?= escaparRecepcionHtml($descripcion) ?>
            </option>
      <?php }else{?>
<option value="<?= (int) $id ?>">
              <?= escaparRecepcionHtml($descripcion) ?>
            </option>

            <?php
                  } }
              ?>
      </select>

      <label for="">Descripcion</label>
        <textarea id="" cols="45" rows="3" name="descripcionServicio" class="form-control"><?= escaparRecepcionHtml($dataNota['descripcionServicio']) ?></textarea>
    </fieldset>

    <fieldset>
      <legend>Estado del equipo</legend>
      <label for="">Estado del equipo</label>
    
      <select id="nota-estado" name="estado" class="select">
        <option value="">Seleccione un estado</option>
        <?php
              $ventasTotales = "SELECT * FROM estadoservicio";  
                  $resultado = mysqli_query($conn,$ventasTotales);
                  while($mostrar = mysqli_fetch_array($resultado)){
                    $id=$mostrar["id_estado"];
                    $descripcion = $mostrar["estadoServicio"];

                    if ($id==$dataNota['estado']) {
            ?>
            <option value="<?= (int) $id ?>" selected>
              <?= escaparRecepcionHtml($descripcion) ?>
            </option>
      <?php }else{?>
<option value="<?= (int) $id ?>">
<?= escaparRecepcionHtml($descripcion) ?>
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
          <input type="number" name="costoServicio" value="<?= escaparRecepcionHtml($dataNota['costoServicio']) ?>" class="form-control"/>
        </div>
        <div class="col-md-4  col-sm-12">
          <label for="">Ganancia</label>
          <input type="number" name="ganancia" value="<?= escaparRecepcionHtml($dataNota['ganancia']) ?>" class="form-control"/>
        </div>
        <div class="col-md-4  col-sm-12">
          <label for="">Anticipo</label>
          <input type="number" name="anticipo" value="<?= escaparRecepcionHtml($dataNota['anticipo']) ?>" class="form-control"/>
        </div>
        <div class="col-md-4  col-sm-12">
          <label for="">Restante</label>
          <input type="number" name="restante" value="<?= escaparRecepcionHtml($dataNota['restante']) ?>" class="form-control"/>
        </div>
        <div class="col-md-4  col-sm-12">
          <label for="">Pagado</label>
          <input type="number" name="pagado" value="<?= escaparRecepcionHtml($dataNota['pagado']) ?>" class="form-control"/>
        </div>
      </div>
    </fieldset>

    <fieldset>
   
      <legend>Notas</legend>
      <label for="">Ingrese las notas adicionales</label>
      <div class="row">
        <div class="col-12 col-sm-12">

           <textarea name="nota" id="" cols="45" rows="3" class="form-control"><?= escaparRecepcionHtml($dataNota['notas']) ?></textarea>
        </div>
        </div>
  
 
    </fieldset>

    <button type="submit" class="btn btn-primary">Actualizar</button>
  </form>
</div>
    
</body>
</html>