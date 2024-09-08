<?php
include ('conexion.php');

date_default_timezone_set('America/Mexico_City');
$fecha_actual = date("Y-m-d H:i:s");
?>

<!DOCTYPE html>
<html lang="es">

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
          <a href="clientes.php">
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
          <a href="consultas.php" class="active">
            <i class="fa-solid fa-file-invoice-dollar"></i> Reportes</a>
        </li>
      </ul>
    </nav>
  </header>

  <section>

    <!-- DETALLES -->
    <div class="seccion">
      <div>
        <h5 class="numerototal">
          <?php
          $ventasTotales = "SELECT COUNT(id_venta) FROM ventas WHERE YEAR(fecha_venta) = 2024";
          $resultado = mysqli_query($conn, $ventasTotales);
          while ($mostrar = mysqli_fetch_array($resultado)) {
            echo ($mostrar[0] . "");
          }

          ?>
        </h5>
        <span>
          <h3>Ventas</h3>
        </span>
      </div>
      <div>
        <h5 class="numerototal">
          <?php
          $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE YEAR(fecha_venta) = 2024";
          $resultado = mysqli_query($conn, $ventasTotales);
          while ($mostrar = mysqli_fetch_array($resultado)) {
            echo ("$" . $mostrar[0] . "");
          }

          ?>
        </h5>
        <span>
          <h3>Total</h3>
        </span>

      </div>

      <div>
        <h5>
          <a href=""> <i class="fa-solid fa-file-pdf"></i></a>

        </h5>
        <span>
          <h3>GENERAR PDF</h3>
        </span>
      </div>
    </div>

    <h2>Ventas</h2>
    <table id="myTable" class="display responsive nowrap" style="width: 100%">
      <thead>
        <tr>
          <th>ID</th>
          <th>Cantidad</th>
          <th>Descripcion</th>
          <th>Costo</th>
          <th>Seccion</th>
          <th>fecha_venta</th>
          <th>PDF</th>
          <th>Editar</th>
          <th>Eliminar</th>
        </tr>
      </thead>

      <tbody>
        <?php
        $consulta = "select * from ventas v
                            JOIN seccion s ON v.seccion  = s.id_seccion 
                            WHERE YEAR(fecha_venta) = 2024";

        $resultado = mysqli_query($conn, $consulta);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          ?>
          <tr>
            <td>
              <?php echo $mostrar['id_venta'] ?>
            </td>
            <td>
              <?php echo $mostrar['cantidad'] ?>
            </td>
            <td>
              <?php echo $mostrar['descripcion'] ?>
            </td>
            <td>
              <?php echo $mostrar['costo'] ?>
            </td>
            <td>
              <?php echo $mostrar['descripcionSeccion'] ?>
            </td>
            <td>
              <?php echo $mostrar['fecha_venta'] ?>
            </td>
            <td>
              <a href="#" target="_blank"><i class="fa-solid fa-file-pdf"></i></a>
            </td>
            <td>
              <a href="editarVentas.php?updVenta=<?= $mostrar['id_venta'] ?>"><i class="fa-solid fa-pen"></i></a>
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

  <section>


    <!-- DETALLES -->
    <div class="seccion">
      <div>
        <h5 class="numerototal">
          <?php
          $ventasTotales = "SELECT COUNT(id_nota) FROM notas WHERE YEAR(fecha_alta) = 2024 ";
          $resultado = mysqli_query($conn, $ventasTotales);
          while ($mostrar = mysqli_fetch_array($resultado)) {
            echo ($mostrar[0] . "");
          }

          ?>
        </h5>
        <span>
          <h3>Servicios</h3>
        </span>
      </div>
      <div>
        <h5 class="numerototal">
          <?php
          $ventasTotales = "SELECT SUM(ganancia) FROM notas WHERE YEAR(fecha_alta) = 2024";
          $resultado = mysqli_query($conn, $ventasTotales);
          while ($mostrar = mysqli_fetch_array($resultado)) {
            echo ("$" . $mostrar[0] . "");
          }

          ?>
        </h5>
        <span>
          <h3>Total</h3>
        </span>

      </div>

      <div>
        <h5>
          <a href=""> <i class="fa-solid fa-file-pdf"></i></a>

        </h5>
        <span>
          <h3>GENERAR PDF</h3>
        </span>
      </div>
    </div>

    <h2>Notas</h2>
    <table id="myTable2" class="display responsive nowrap" style="width: 100%">
      <thead>
        <tr>
          <th>ID</th>
          <th>Cliente</th>
          <th>Fecha alta</th>
          <th>Equipo</th>
          <th>Marca</th>
          <th>Modelo</th>
          <th>Servicio</th>
          <th>Costo</th>
          <th>Descripcion</th>
          <th>Notas</th>
          <th>Estado</th>
          <th>Anticipo</th>
          <th>Ganancia</th>
          <th>Restante</th>
          <th>Pagado</th>
          <th>PDF</th>
          <th>Editar</th>
          <th>Eliminar</th>
        </tr>
      </thead>

      <tbody>
        <?php
        $consulta = "select * from notas n 
                            JOIN clientes c ON n.id_cliente  = c.id_CLIENTE 
                            JOIN equipo e ON n.equipo =  e.id_equipo 
                            JOIN servicio s ON n.servicio =  s.id_servicio
                            JOIN estadoservicio es ON n.estado =  es.id_estado 
                          ";
                         /*  WHERE YEAR(fecha_alta) = 2024 */
        /* WHERE fecha_alta >= DATE(NOW()) */
        $resultado = mysqli_query($conn, $consulta);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          ?>
          <tr>
            <td><?php echo $mostrar['id_nota'] ?></td>
            <td><?php echo $mostrar['nombre'] . " " . $mostrar["apellidos"] ?></td>
            <td><?php echo $mostrar['fecha_alta'] ?></td>
            <td><?php echo $mostrar['descripcionEquipo'] ?></td>
            <td><?php echo $mostrar['marca'] ?></td>
            <td><?php echo $mostrar['modelo'] ?></td>
            <td><?php echo $mostrar['tipoServicio'] ?></td>
            <td><?php echo $mostrar['costoServicio'] ?></td>
            <td><?php echo $mostrar['descripcionServicio'] ?></td>
            <td><?php echo $mostrar['notas'] ?></td>
            <td><?php echo $mostrar['estadoServicio'] ?></td>
            <td><?php echo $mostrar['anticipo'] ?></td>
            <td><?php echo $mostrar['ganancia'] ?></td>
            <td><?php echo $mostrar['restante'] ?></td>
            <td><?php echo $mostrar['pagado'] ?></td>
            <td>
              <a href="reportes.php?idNota=<?= $mostrar['id_nota'] ?>" target="_blank"><i
                  class="fa-solid fa-file-pdf"></i></a>
            </td>
            <td>
              <a href="editarNotas.php?updNotas=<?= $mostrar['id_nota'] ?>"><i class="fa-solid fa-pen"></i></a>
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