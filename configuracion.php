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
          <a href="contabilidad.php">
            <i class="fa-solid fa-file-invoice-dollar"></i> Contabilidad</a>
        </li>
        <li>
          <a href="consultas.php">
            <i class="fa-solid fa-file-invoice-dollar"></i> Reportes</a>
        </li>
        <li>
          <a href="configuracion.php"  class="active">
            <i class="fa-solid fa-file-invoice-dollar icon"></i> Configuración</a>
        </li>
      </ul>
    </nav>
  </header>

  <section>

    <h2>RESPALDO</h2>

    <section>
      <button>
        Respaldar
      </button>
    </section>

    <h2>venta negocio</h2>

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