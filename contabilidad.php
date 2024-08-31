<?php
include('conexion.php');
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
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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
          <a href="contabilidad.php" class="active">
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

    <h2>ventas prestamo</h2>
    <section class="seccion" id="seccionuno">

      <table class="table">
        <thead>
          <tr>
            <th>Icon</th>
            <th>Categoria</th>
            <th>Cantidad</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th> <i class="fa-solid fa-users"></i></th>
            <th>Patricia</th>
            <th class="cantidad">
              <?php
              $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=2";
              $resultado = mysqli_query($conn, $ventasTotales);
              while ($mostrar = mysqli_fetch_array($resultado)) {
                $vt  = number_format($mostrar[0],2, '.', ',');
                echo ("$" . $vt.PHP_EOL . "");
              }

              ?>
            </th>
          </tr>
          <tr>
            <th> <i class="fa-solid fa-users"></i></i></th>
            <th>Gaudencio</th>
            <th class="cantidad">
              <?php
              $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=3";
              $resultado = mysqli_query($conn, $ventasTotales);
              while ($mostrar = mysqli_fetch_array($resultado)) {
                $vt  = number_format($mostrar[0],2, '.', ',');
                echo ("$" . $vt.PHP_EOL . "");
              }

              ?>
            </th>
          </tr>
        </tbody>
      </table>
    </section>

    <h2>venta negocio</h2>
    <section class="seccion">


      <table class="table">

        <thead class="thead-light">
          <tr>
            <th>Icon</th>
            <th>Categoria</th>
            <th>Cantidad</th>
          </tr>
        </thead>

        <tbody>

          <tr>
            <th> <i class="fa-solid fa-folder"></i></th>
            <th>
              Papeleria
            </th>
            <th class="cantidad">
              <?php
              $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=5";
              $resultado = mysqli_query($conn, $ventasTotales);
              while ($mostrar = mysqli_fetch_array($resultado)) {
                $vt  = number_format($mostrar[0],2, '.', ',');
                echo ("$" . $vt.PHP_EOL . "");
              }

              ?>
            </th>
          </tr>
          <tr>
            <th> <i class="fa-solid fa-sheet-plastic"></i></th>
            <th>
              Hojas
            </th>
            <th class="cantidad">
              <?php
              $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=6";
              $resultado = mysqli_query($conn, $ventasTotales);
              while ($mostrar = mysqli_fetch_array($resultado)) {
                $vt  = number_format($mostrar[0],2, '.', ',');
                echo ("$" . $vt.PHP_EOL . "");
              }
              ?>
            </th>
          </tr>
          <tr>
            <th> <i class="fa-solid fa-mobile-screen-button"></i></th>
            <th>
              Celular
            </th>
            <th class="cantidad">
              <?php
              $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=4";
              $resultado = mysqli_query($conn, $ventasTotales);
              while ($mostrar = mysqli_fetch_array($resultado)) {
                $vt  = number_format($mostrar[0],2, '.', ',');
                echo ("$" . $vt.PHP_EOL . "");
              }

              ?>
            </th>
          </tr>
          <tr>
            <th> <i class="fa-solid fa-laptop"></i></th>
            <th>
              Negocio
            </th>
            <th class="cantidad">
              <?php
              $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=1";
              $resultado = mysqli_query($conn, $ventasTotales);
              while ($mostrar = mysqli_fetch_array($resultado)) {
                $vt  = number_format($mostrar[0],2, '.', ',');
                echo ("$" . $vt.PHP_EOL . "");
              }

              ?>
            </th>
          </tr>
          <tr>
            <th> <i class="fa-solid fa-cash-register"></i></th>
            <th>
              Total
            </th>
            <th class="cantidad total">
              <?php

              $ventasTotales = "SELECT SUM(costo) FROM ventas";

              $resultado = mysqli_query($conn, $ventasTotales);
              
              while ($mostrar = mysqli_fetch_array($resultado)) {
                $vt  = number_format($mostrar[0],2, '.', ',');
                echo ("$" . $vt.PHP_EOL . "");
              }

              ?>
            </th>
          </tr>
        </tbody>


      </table>
    </section>





    <h2>notas</h2>

    <section class="seccion">
      <div>

        <i class="fa-solid fa-laptop"></i>
        <h5>
          <?php
          $ventasTotales = "SELECT SUM(ganancia) FROM notas";
          $resultado = mysqli_query($conn, $ventasTotales);
          while ($mostrar = mysqli_fetch_array($resultado)) {
            $vt  = number_format($mostrar[0],2, '.', ',');
            echo ("$" . $vt.PHP_EOL . "");
          }

          ?>
        </h5>
        <h3>Servicio</h3>
      </div>

    </section>


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