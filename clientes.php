<?php
include ('conexion.php');

date_default_timezone_set('America/Mexico_City');
$fecha_actual = date("Y-m-d H:i:s");

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
  <header>
    <div>
      <img src="img/logotipo2.png" alt="logotipo" />
    </div>
    <nav>
      <ul>
        <li>
          <a href="index.php">
            <i class="fa-solid fa-list"></i> Ventas</a>
        </li>
        <li>
          <a href="clientes.php" class="active">
            <i class="fa-solid fa-users"></i> Clientes</a>
        </li>
        <li>
          <a href="notaCliente.php"> <i class="fa-solid fa-clipboard-user"></i> Nota</a>
        </li>
        <li>
          <a href="contabilidad.php">
            <i class="fa-solid fa-file-invoice-dollar"></i> Contabilidad</a>
        </li>
        <li>
          <a href="consultas.php">
            <i class="fa-solid fa-file-invoice-dollar"></i> Reportes</a>
        </li>
      </ul>
    </nav>
  </header>

  <section>
    <div class="seccion">
      <div>
        <span>

          <h5 class="numerototal">
            <?php
            $ventasTotales = "SELECT COUNT(id_cliente) FROM clientes";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              echo ($mostrar[0]);
            }

            ?>
          </h5>
        </span>
        <span>

          <h3>Clientes</h3>
        </span>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <h2>Registro Cliente</h2>
        <div id="registro" class="container">
          <form action="registroClientes.php" method="post">
            <div class="row" style="display:none">
              <label for="">Fecha Registro</label>
              <input type="datetime" name="fechaRegistro" value="<?php echo $fecha_actual ?>" class="form-control">
            </div>
            <div class="row">
              <div class="col-md-6 col-xs-12">
                <label for="">Nombre</label>
                <input type="text" name="nombre" class="form-control">
              </div>
              <div class="col-md-6 col-xs-12">
                <label for="">Apellidos</label>
                <input type="text" name="apellidos" class="form-control">
              </div>
            </div>

            <label for="">Profesion</label>
            <select name="profesionocupacion" class="select">
              <option value="">Selecciona una profesion o ocupacion</option>
              <?php
              $ventasTotales = "SELECT * FROM profesion";
              $resultado = mysqli_query($conn, $ventasTotales);
              while ($mostrar = mysqli_fetch_array($resultado)) {
                $id = $mostrar["id_profesion"];
                $descripcion = $mostrar["descripcionProfesion"];
                ?>
                <option value="<?php echo $id; ?>">
                  <?php echo $descripcion ?>
                </option>
                <?php
              }
              ?>
            </select>
            <label for="">Calle</label>
            <input type="text" name="calle" class="form-control">

            <label for="">Colonia - Localidad</label>
            <input type="text" name="domicilio" class="form-control">
            <label for="">Telefono</label>
            <input type="text" name="telefono" class="form-control">

            <button type="submit" class="btn btn-primary">Agregar</button>
          </form>
        </div>
      </div>
      <div class="col-md-6">
        <h2>Movimientos</h2>
        <table id="tablaclientes" class="display responsive nowrap" style="width: 100%">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Apellidos</th>
              <th>Descripcion</th>
              <th>Calle</th>
              <th>Domicilio</th>
              <th>Servicios</th>
              <th>Telefono</th>
              <th>Editar</th>
              <th>Eliminar</th>
            </tr>
          </thead>

          <tbody>
            <?php
            $consulta = "SELECT * FROM clientes c 
                JOIN profesion p ON c.profesion  = p.id_profesion ";
            $resultado = mysqli_query($conn, $consulta);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              ?>
              <tr>
                <td><?php echo $mostrar['id_cliente'] ?></td>
                <td><?php echo $mostrar['nombre'] ?></td>
                <td><?php echo $mostrar['apellidos'] ?></td>
                <td><?php echo $mostrar['descripcionProfesion'] ?></td>
                <td><?php echo $mostrar['calle'] ?></td>
                <td><?php echo $mostrar['domicilio'] ?></td>
                <td><?php echo $mostrar['numServicio'] ?></td>
                <td><?php echo $mostrar['telefono'] ?></td>

                <td>
                  <a href="editarClientes.php?updCliente=<?= $mostrar['id_cliente'] ?>"><i
                      class="fa-solid fa-pen"></i></a>
                </td>
                <td><a href="#"><i class="fa-solid fa-trash"></i></a></td>
              </tr>
              <?php
            }
            ?>
          </tbody>

        </table>
      </div>
    </div>

  </section>

  <footer>
    <div>
      <img src="img/logotipo-blanco.png" alt="logo" />
    </div>
    <p>Todos los derechos reservados - garespservicioinformatico</p>
  </footer>

</body>

<!-- SCRIPTS -->
<script src="js/jquery.min.js"></script>

<script src="js/bootstrap.min.js"></script>

<script src="js/datatables.min.js"></script> 
<script src="js/dataTables.responsive.js"></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="js/index.js"></script>

</html>