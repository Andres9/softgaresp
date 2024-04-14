<?php
include ('conexion.php');
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />

  <!-- DATATABLES -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.css" />
  <link rel="stylesheet" href="css/responsive.dataTables.min.css" />
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.js"></script>
  <script src="css/dataTables.responsive.min.js"></script>

  <!-- SELECT2 -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <!-- FUENTES -->
  <link rel="stylesheet" href="fontawesome-free-6.5.1-web/fontawesome-free-6.5.1-web/css/all.min.css" />
</head>

<body>
  <header>
    <div>
      <img src="logotipo2.png" alt="logotipo" />
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
      <div>
        <i class="fa-solid fa-user"></i>
        <h5>
          <?php
          $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=2";
          $resultado = mysqli_query($conn, $ventasTotales);
          while ($mostrar = mysqli_fetch_array($resultado)) {
            echo ("$" . $mostrar[0] . "");
          }

          ?>
        </h5>
        <h3>Patricia</h3>
      </div>

      <div>
        <i class="fa-regular fa-user"></i>
        <h5>
          <?php
          $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=3";
          $resultado = mysqli_query($conn, $ventasTotales);
          while ($mostrar = mysqli_fetch_array($resultado)) {
            echo ("$" . $mostrar[0] . "");
          }

          ?>
        </h5>
        <h3>Guadencio</h3>
      </div>
    </section>





    <h2>venta negocio</h2>
    <section class="seccion">
      <div>
        <span>
          <i class="fa-solid fa-file-circle-check"></i>
          <h5>
            <?php
            $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=5";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              echo ("$" . $mostrar[0] . "");
            }

            ?>
          </h5>

        </span>
        <span>
          <h3>Papeleria</h3>
        </span>
      </div>



      <div>
        <i class="fa-solid fa-file-circle-check"></i>
        <h5>
          <?php
          $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=6";
          $resultado = mysqli_query($conn, $ventasTotales);
          while ($mostrar = mysqli_fetch_array($resultado)) {
            echo ("$" . $mostrar[0] . "");
          }
          ?>
        </h5>
        <span>
          <h3>Hojas</h3>
        </span>



      </div>


      <div>
        <i class="fa-solid fa-mobile-screen-button"></i>
        <h5>
          <?php
          $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=4";
          $resultado = mysqli_query($conn, $ventasTotales);
          while ($mostrar = mysqli_fetch_array($resultado)) {
            echo ("$" . $mostrar[0] . "");
          }

          ?>
        </h5>
        <h3>Celular</h3>
      </div>

      <div>
        <i class="fa-solid fa-cash-register"></i>
        <h5>
          <?php
          $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=1";
          $resultado = mysqli_query($conn, $ventasTotales);
          while ($mostrar = mysqli_fetch_array($resultado)) {
            echo ("$" . $mostrar[0] . "");
          }

          ?>
        </h5>
        <h3>Negocio</h3>
      </div>


      <div id="secciontotal">
        <i class="fa-solid fa-cash-register"></i>
        <h5>
          <?php
          $ventasTotales = "SELECT SUM(costo) FROM ventas";
          $resultado = mysqli_query($conn, $ventasTotales);
          while ($mostrar = mysqli_fetch_array($resultado)) {
            echo ("$" . $mostrar[0] . "");
          }

          ?>
        </h5>
        <h3>Total</h3>
      </div>


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
            echo ("$" . $mostrar[0] . "");
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
<script src="js/jquery.js"></script>
<script src="js/index.js"></script>

</html>