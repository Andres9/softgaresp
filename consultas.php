<?php include_once 'encabezado.php' ?>

<section>

  <h2>Consultas Personalizadas</h2>

  <?php
  define('SOFTGARESP_REPORTE_GRAFICOS_VENTAS', true);
  include_once 'ventas.php';
  ?>


  <section id="resultado_busqueda"></section>


  <h2>Total impresiones</h2>

  <section class="seccion">
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(precio) FROM impresion WHERE YEAR(fecha_impresion) = 2026 AND precio>0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>2026</h3>
    </div>

  </section>
  <?php

// Establecer la zona horaria (opcional pero recomendado)
date_default_timezone_set('America/Mexico_City');

$meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

  $mesActual = $meses[(int) date('n')];
  $anioActual = date('Y');

  echo ("<h2>Venta Factura - " . $mesActual . " " . $anioActual . "</h2>");
  ?>

  <!-- <h2>Venta Factura - NOVIEMBRE 2026</h2> -->

  <section class="seccion">
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $mes = date('n'); 
        $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE YEAR(fecha_venta) = 2026 AND MONTH(fecha_venta) = $mes AND FACTURA='FACTURA'";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>2026</h3>
    </div>

  </section>
 

</section>



<?php include_once 'footer.php' ?>
<script src="busquedaReporte.js"></script>