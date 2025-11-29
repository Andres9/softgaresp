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
      <a href="ventas.php">
        <img src="img/logos.jpg" alt="logotipo" id="logo"/>
      </a>
      <div>
        <a href="">
        <i class="fa-solid fa-user userSystem"></i>
        </a>
        <span>Andres GE</span>
      </div>
    </div>
    <nav>
      <ul>
          <li>
          <a href="contabilidad.php">
            <i class="fa-solid fa-file-invoice-dollar"></i> Contabilidad</a>
        </li>
        <li>
          <a href="ventas.php" class="active">
            <i class="fa-solid fa-cash-register"></i> Ventas</a>
        </li>
        <li>
          <a href="clientes.php">
            <i class="fa-solid fa-users"></i> Clientes</a>
        </li>
        <li>
          <a href="notaCliente.php"> <i class="fa-solid fa-clipboard-user"></i> Nota</a>
        </li>
        <li>
          <a href="consultas.php">
            <i class="fa-solid fa-file-invoice-dollar icon"></i> Reportes</a>
        </li>
        <li>
          <a href="reportesnotas.php">
            <i class="fa-solid fa-file-invoice-dollar icon"></i> R. notas</a>
        </li>
        <li>
          <a href="reportesventas.php">
            <i class="fa-solid fa-file-invoice-dollar icon"></i> R. ventas</a>
        </li>
        <li>
          <a href="configuraciones.php">
            <i class="fa-solid fa-gear"></i></i> Conf.</a>
        </li>
      </ul>
    </nav>
  </header>
