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

            $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=1 or seccion=4 or seccion=5 or seccion = 6";

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
  <h2>otros movimientos</h2>
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
            Total
          </th>
          <th class="cantidad_total">
            <?php

            $ventasTotales = "SELECT SUM(precio) FROM impresion";

            $resultado = mysqli_query($conn, $ventasTotales);

            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }

            ?>
          </th>
          <th class="cantidad_total">
            <?php

            $ventasTotales = "SELECT SUM(preciocosto) FROM ventas WHERE seccion = 7";

            $resultado = mysqli_query($conn, $ventasTotales);

            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt  = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }

            ?>
          </th>
          <th class="cantidad_total">
            <?php

            $ventasTotales = "SELECT SUM(ganancia) FROM ventas WHERE  seccion = 7";

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

  <section class="seccion reportes-resumen">
    <article class="reportes-resumen-card reportes-resumen-card-total">
      <div class="reportes-resumen-cabecera">
        <span class="reportes-resumen-etiqueta">Servicio 2026</span>
        <span class="reportes-resumen-icono" aria-hidden="true"><i class="fa-solid fa-toolbox"></i></span>
      </div>
      <strong class="reportes-resumen-valor">
        <?php
        $ventasTotales = "SELECT SUM(ganancia) FROM notas WHERE YEAR(fecha_alta) = 2026";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }

        ?>
      </strong>
      <p class="reportes-resumen-detalle">Ganancia anual</p>
    </article>
    <article class="reportes-resumen-card reportes-resumen-card-total">
      <div class="reportes-resumen-cabecera">
        <span class="reportes-resumen-etiqueta">Servicio 2025</span>
        <span class="reportes-resumen-icono" aria-hidden="true"><i class="fa-solid fa-toolbox"></i></span>
      </div>
      <strong class="reportes-resumen-valor">
        <?php
        $ventasTotales = "SELECT SUM(ganancia) FROM notas WHERE YEAR(fecha_alta) = 2025";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </strong>
      <p class="reportes-resumen-detalle">Ganancia anual</p>
    </article>
    <article class="reportes-resumen-card reportes-resumen-card-total">
      <div class="reportes-resumen-cabecera">
        <span class="reportes-resumen-etiqueta">Servicio 2024</span>
        <span class="reportes-resumen-icono" aria-hidden="true"><i class="fa-solid fa-toolbox"></i></span>
      </div>
      <strong class="reportes-resumen-valor">
        <?php
        $ventasTotales = "SELECT SUM(ganancia) FROM notas WHERE YEAR(fecha_alta) = 2024";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </strong>
      <p class="reportes-resumen-detalle">Ganancia anual</p>
    </article>
    <article class="reportes-resumen-card reportes-resumen-card-total">
      <div class="reportes-resumen-cabecera">
        <span class="reportes-resumen-etiqueta">Servicio 2023</span>
        <span class="reportes-resumen-icono" aria-hidden="true"><i class="fa-solid fa-toolbox"></i></span>
      </div>
      <strong class="reportes-resumen-valor">
        <?php
        $ventasTotales = "SELECT SUM(ganancia) FROM notas WHERE YEAR(fecha_alta) = 2023";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </strong>
      <p class="reportes-resumen-detalle">Ganancia anual</p>
    </article>
    <article class="reportes-resumen-card reportes-resumen-card-total">
      <div class="reportes-resumen-cabecera">
        <span class="reportes-resumen-etiqueta">Servicio 2022</span>
        <span class="reportes-resumen-icono" aria-hidden="true"><i class="fa-solid fa-toolbox"></i></span>
      </div>
      <strong class="reportes-resumen-valor">
        <?php
        $ventasTotales = "SELECT SUM(ganancia) FROM notas WHERE YEAR(fecha_alta) = 2022";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </strong>
      <p class="reportes-resumen-detalle">Ganancia anual</p>
    </article>

  </section>



</section>



<?php include_once 'footer.php'; ?>