<?php include_once 'encabezado.php' ?>

<section>

  <h2>Consultas Personalizadas</h2>

  <?php
  define('SOFTGARESP_REPORTE_GRAFICOS_VENTAS', true);
  include_once 'ventas.php';
  ?>


  <section id="resultado_busqueda"></section>


  <h2>Total impresiones</h2>

  <section class="seccion reportes-resumen">
    <article class="reportes-resumen-card reportes-resumen-card-total">
      <div class="reportes-resumen-cabecera">
        <span class="reportes-resumen-etiqueta">Impresiones</span>
        <span class="reportes-resumen-icono" aria-hidden="true"><i class="fa-solid fa-print"></i></span>
      </div>
      <strong class="reportes-resumen-valor">
        <?php
        $ventasTotales = "SELECT SUM(precio) FROM impresion WHERE YEAR(fecha_impresion) = 2026 AND precio>0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </strong>
      <p class="reportes-resumen-detalle">Total de 2026</p>
    </article>

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

  <section class="seccion reportes-resumen">
    <article class="reportes-resumen-card reportes-resumen-card-total">
      <div class="reportes-resumen-cabecera">
        <span class="reportes-resumen-etiqueta">Venta con factura</span>
        <span class="reportes-resumen-icono" aria-hidden="true"><i class="fa-solid fa-file-invoice-dollar"></i></span>
      </div>
      <strong class="reportes-resumen-valor">
        <?php
        $mes = date('n'); 
        $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE YEAR(fecha_venta) = 2026 AND MONTH(fecha_venta) = $mes AND FACTURA='FACTURA'";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </strong>
      <p class="reportes-resumen-detalle">Total de <?= htmlspecialchars($mesActual . ' ' . $anioActual, ENT_QUOTES, 'UTF-8') ?></p>
    </article>

  </section>
 

</section>



<?php include_once 'footer.php' ?>
<script src="busquedaReporte.js"></script>