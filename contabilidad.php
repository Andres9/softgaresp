<?php include_once 'encabezado.php' ?>

<section>

  <h2>ventas prestamo</h2>
  <section class="seccion" id="seccionuno">

    <table class="table">
      <thead>
        <tr>
          <th>Categoria</th>
          <th>Cantidad</th>
          <th>INVERTIDO</th>
          <th>GANANCIA</th>
        </tr>
      </thead>
      <tbody>
        <tr>

          <th>Patricia</th>
          <th class="cantidad">
            <?php
            $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=2";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }

            ?>
          </th>
          <th class="cantidad">
            <?php
            $ventasTotales = "SELECT SUM(preciocosto) FROM ventas WHERE seccion=2";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }

            ?>
          </th>
          <th class="cantidad">
            <?php
            $ventasTotales = "SELECT SUM(ganancia) FROM ventas WHERE seccion=2";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }

            ?>
          </th>
        </tr>
        <tr>

          <th>Gaudencio</th>
          <th class="cantidad">
            <?php
            $ventasTotales = "SELECT SUM(ganancia) FROM ventas WHERE seccion=3";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }

            ?>
          </th>
          <th class="cantidad">
            <?php
            $ventasTotales = "SELECT SUM(-preciocosto) FROM ventas WHERE seccion=3";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }

            ?>
          </th>
          <th class="cantidad">
            <?php
            $ventasTotales = "SELECT SUM(ganancia) FROM ventas WHERE seccion=3";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }

            ?>
          </th>
        </tr>
        <tr>

          <th>Total</th>
          <th class="cantidad_total">
            <?php
            $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=2 OR seccion=3";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }

            ?>
          </th>
          <th class="cantidad_total">
            <?php
            $ventasTotales = "SELECT SUM(preciocosto) FROM ventas WHERE seccion=2 OR seccion=3";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }

            ?>
          </th>
          <th class="cantidad_total">
            <?php
            $ventasTotales = "SELECT SUM(ganancia) FROM ventas WHERE seccion=2 OR seccion=3";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
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
          <th>Categoria</th>
          <th>Cantidad</th>
          <th>INVERTIDO</th>
          <th>GANANCIA</th>
        </tr>
      </thead>

      <tbody>
        <tr>
          <th>
            Papeleria
          </th>
          <th class="cantidad">
            <?php
            $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=5";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }
            ?>
          </th>


          <th class="cantidad">
            <?php
            $ventasTotales = "SELECT SUM(preciocosto) FROM ventas WHERE seccion=5";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }

            ?>
          </th>

          <th class="cantidad">
            <?php
            $ventasTotales = "SELECT SUM(ganancia) FROM ventas WHERE seccion=5";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }

            ?>
          </th>
        </tr>

        <tr>
          <th>
            Hojas
          </th>
          <th class="cantidad">
            <?php
            $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=6";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }
            ?>
          </th>
          <th class="cantidad">
            <?php
            $ventasTotales = "SELECT SUM(preciocosto) FROM ventas WHERE seccion=6";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }
            ?>
          </th>
          <th class="cantidad">
            <?php
            $ventasTotales = "SELECT SUM(ganancia) FROM ventas WHERE seccion=6";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }
            ?>
          </th>
        </tr>

        <tr>
          <th>
            Impresion
          </th>
          <th class="cantidad">
            <?php
            $ventasTotales = "SELECT SUM(PRECIO) FROM IMPRESION";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }
            ?>
          </th>
          <th class="cantidad">
            <?php
            $ventasTotales = "SELECT SUM(preciocosto) FROM ventas WHERE seccion=7";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }
            ?>
          </th>
          <th class="cantidad">
            <?php
            $ventasTotales = "SELECT SUM(ganancia) FROM ventas WHERE seccion=7";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }
            ?>
          </th>
        </tr>


        <tr>
          <th>
            Celular
          </th>
          <th class="cantidad">
            <?php
            $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=4";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }

            ?>
          </th>


          <th class="cantidad">
            <?php
            $ventasTotales = "SELECT SUM(preciocosto) FROM ventas WHERE seccion=4";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }

            ?>
          </th>

          <th class="cantidad">
            <?php
            $ventasTotales = "SELECT SUM(ganancia) FROM ventas WHERE seccion=4";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }

            ?>
          </th>
        </tr>
        <tr>
          <th>
            Negocio
          </th>
          <th class="cantidad">
            <?php
            $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=1";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }

            ?>
          </th>


          <th class="cantidad">
            <?php
            $ventasTotales = "SELECT SUM(preciocosto) FROM ventas WHERE seccion=1";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }

            ?>
          </th>


          <th class="cantidad">
            <?php
            $ventasTotales = "SELECT SUM(ganancia) FROM ventas WHERE seccion=1";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }

            ?>
          </th>
        </tr>

        <tr>

          <th>
            Total
          </th>
          <th class="cantidad_total">
            <?php

            $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=1 or seccion=4 or seccion=5 or seccion = 6 or seccion=7" ;

            $resultado = mysqli_query($conn, $ventasTotales);

            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }

            ?>
          </th>
          <th class="cantidad_total">
            <?php

            $ventasTotales = "SELECT SUM(preciocosto) FROM ventas WHERE seccion=1 or seccion=4 or seccion=5 or seccion = 6";

            $resultado = mysqli_query($conn, $ventasTotales);

            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }

            ?>
          </th>
          <th class="cantidad_total">
            <?php

            $ventasTotales = "SELECT SUM(ganancia) FROM ventas WHERE seccion=1 or seccion=4 or seccion=5 or seccion = 6";

            $resultado = mysqli_query($conn, $ventasTotales);

            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
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
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(ganancia) FROM notas WHERE YEAR(fecha_alta) = 2026";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }

        ?>
      </h5>
      <h3>Servicio 2026</h3>
    </div>
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(ganancia) FROM notas WHERE YEAR(fecha_alta) = 2025";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }

        ?>
      </h5>
      <h3>Servicio 2025</h3>
    </div>
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(ganancia) FROM notas WHERE YEAR(fecha_alta) = 2024";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }

        ?>
      </h5>
      <h3>Servicio 2024</h3>
    </div>
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(ganancia) FROM notas WHERE YEAR(fecha_alta) = 2023";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }

        ?>
      </h5>
      <h3>Servicio 2023</h3>
    </div>
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(ganancia) FROM notas WHERE YEAR(fecha_alta) = 2022";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }

        ?>
      </h5>
      <h3>Servicio 2022</h3>
    </div>

  </section>
  


</section>



<?php include_once 'footer.php'; ?>