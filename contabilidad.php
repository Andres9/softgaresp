<?php include_once 'encabezado.php'?>

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
          <tr>
            <th> <i class="fa-solid fa-users"></i></i></th>
            <th>Total</th>
            <th class="cantidad_total">
              <?php
              $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=2 OR seccion=3";
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
            <th class="cantidad_total">
              <?php

              $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=1 or seccion=4 or seccion=5 or seccion = 6";

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



  <?php include_once 'footer.php'; ?>