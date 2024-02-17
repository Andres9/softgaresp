<?php 
include('conexion.php');

date_default_timezone_set('America/Mexico_City');
$fecha_actual=date("Y-m-d H:i:s");

?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SoftGaresp</title>
    <link rel="icon" href="logotipo.png" />
    <!-- ARCHIVOS PERSONALIZADOS -->
    <script src="jquery.js"></script>
    <script src="index.js"></script>
    <link rel="stylesheet" href="style.css" />
    <!-- BOOTSTRAP -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
      crossorigin="anonymous"
    />

    <!-- DATATABLES -->
    <link
      rel="stylesheet"
      href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.css"
    />
    <link rel="stylesheet" href="responsive.dataTables.min.css" />
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.js"></script>
    <script src="dataTables.responsive.min.js"></script>

    <!-- SELECT2 -->
    <link
      href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"
      rel="stylesheet"
    />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- FUENTES -->
    <link
      rel="stylesheet"
      href="fontawesome-free-6.5.1-web/fontawesome-free-6.5.1-web/css/all.min.css"
    />
  </head>

  <body>
    <header>
      <div>
        <img src="logotipo2.png" alt="logotipo" />
      </div>
      <nav>
        <ul>
          <!--  <li class="logo"> <img src="logotipo2.png" alt="logotipo"></li> -->
          <li>
            <a href="index.php" class="active">
              <i class="fa-solid fa-list"></i> Ventas</a
            >
          </li>
          <li>
            <a href="clientes.php">
            <i class="fa-solid fa-users"></i> Clientes</a
            >
          </li>
          <li>
            <a href="notaCliente.php"> <i class="fa-solid fa-clipboard-user"></i> Nota</a>
          </li>
          <li>
            <a href="contabilidad.php">
            <i class="fa-solid fa-file-invoice-dollar"></i> Contabilidad</a
            >
          </li>
        </ul>
      </nav>
    </header>
    <section>
      <div class="seccion">
        <div>
          <h3>Venta</h3>
          <h5 id="total">
            <?php
                        $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE fecha_venta >= DATE(NOW()) ";  
                        $resultado = mysqli_query($conn,$ventasTotales);
                        while($mostrar = mysqli_fetch_array($resultado)){
                            echo ($mostrar[0]);
                        }
                      
                    ?>
          </h5>
        </div>
      </div> 

      <h2>Registro Ventas</h2>
      <div id="registro" class="container">
        <form action="functions.php" method="post">
          <div class="row">
            <div class="col-md-12" style="display: none">
              <div class="row">
                <label for="">Fecha de venta</label>
                <input
                  type="datetime"
                  name="fechaVenta"
                  value="<?php echo $fecha_actual?>"
                  class="form-control datepicker"
                />
              </div>
            </div>
            <div class="col-md-2 col-xs-12">
              <label for="">Cantidad</label>
              <input
                type="number"
                value="1"
                name="cantidad"
                class="form-control"
              />
            </div>
            <div class="col-md-10 col-xs-12">
              <label for="">Descripción</label>
              <input type="text" name="descripcion" class="form-control" />
            </div>
            <div class="col-md-6 col-xs-12">
              <label for="">Costo</label>
              <input
                type="number"
                name="costo"
                class="form-control"
                step="0.01"
              />
            </div>
            <div class="col-md-6 col-xs-12">
              <label for="">Sección</label>
              <select name="opcionnegocio" class="select">
                <option value="">Selecciona una opcion</option>
                <?php
              $ventasTotales = "SELECT * FROM seccion";  
                  $resultado = mysqli_query($conn,$ventasTotales);
                  while($mostrar = mysqli_fetch_array($resultado)){
                      $id = $mostrar["id_seccion"];
                      $descripcion = $mostrar["descripcionSeccion"];
            ?>
                <option value="<?php echo $id; ?>">
                  <?php echo $descripcion?>
                </option>
                <?php
                  }
              ?>
              </select>
            </div>
          </div>
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Agregar
          </button>
        </form>
      </div>

      <h2>Movimientos</h2>
      <table id="myTable" class="display responsive nowrap" style="width: 100%">
        <thead>
          <tr>
            <th>ID</th>
            <th>Cantidad</th>
            <th>Descripcion</th>
            <th>Precio</th>
            <th>Categoria</th>
            <th>Fecha</th>
            <th>Editar</th>
            <th>Eliminar</th>
          </tr>
        </thead>

        <tbody>
          <?php
                $consulta = "SELECT * FROM ventas WHERE fecha_venta >= DATE(NOW())";
                $resultado = mysqli_query($conn,$consulta);
                  while($mostrar = mysqli_fetch_array($resultado)){
                ?>
          <tr>
            <td><?php echo $mostrar['id_venta']?></td>
            <td><?php echo $mostrar['cantidad']?></td>
            <td><?php echo $mostrar['descripcion']?></td>
            <td><?php echo $mostrar['costo']?></td>
            <td><?php echo $mostrar['seccion']?></td>
            <td><?php echo $mostrar['fecha']?></td>
            <td>
              <a href="editarVentas.php?updVenta=<?=$mostrar['id_venta']?>"
                ><i class="fa-solid fa-pen"></i
              ></a>
            </td>
            <td>
              <a href="#"><i class="fa-solid fa-trash"></i></a>
            </td>
          </tr>
          <?php
                      }
                 ?>
        </tbody>
      </table>
    </section>
    <footer>
      <div>
        <img src="logotipo-blanco.png" alt="logo" />
      </div>
      <p>Todos los derechos reservados - garespservicioinformatico</p>
    </footer>
  </body>
</html>
