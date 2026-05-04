<?php include_once 'encabezado.php' ?>

<section>

  <h2>Consultas Personalizadas</h2>

  <form>
    <label>Mostrar</label>
    <select name="" id="tabla" class="select">
      <option value="">Selecciona una opcion</option>
      <option value="notas">Notas</option>
      <option value="ventas">Ventas</option>
    </select>
    <button type="submit" onclick="buscar_filtro($('#tabla').val())">Buscar</button>
  </form>

  <section id="resultado_busqueda"></section>

  <h2>ventas papeleria patricia</h2>

  <section class="seccion">
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(COSTO) FROM VENTAS WHERE SECCION = 2 AND YEAR(fecha_venta) = 2026";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>2026</h3>
    </div>

    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(COSTO) FROM VENTAS WHERE SECCION = 2 AND YEAR(fecha_venta) = 2025";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>2025</h3>
    </div>


    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(COSTO) FROM VENTAS WHERE SECCION = 2 AND YEAR(fecha_venta) = 2024";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>2024</h3>
    </div>

  </section>

  <h2>ventas papeleria patricia</h2>

  <section class="seccion">
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(COSTO) FROM VENTAS WHERE SECCION = 2 AND YEAR(fecha_venta) = 2026 AND COSTO>0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>2026</h3>
    </div>

    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(COSTO) FROM VENTAS WHERE SECCION = 2 AND YEAR(fecha_venta) = 2025 AND COSTO>0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>2025</h3>
    </div>


    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(COSTO) FROM VENTAS WHERE SECCION = 2 AND YEAR(fecha_venta) = 2024 AND COSTO >0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>2024</h3>
    </div>

  </section>
  <h2>ventas accesorio celular</h2>

  <section class="seccion">
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(COSTO) FROM VENTAS WHERE SECCION = 4 AND YEAR(fecha_venta) = 2026 AND COSTO>0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>2026</h3>
    </div>
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(GANANCIA) FROM VENTAS WHERE SECCION = 4 AND YEAR(fecha_venta) = 2026 AND COSTO>0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>2026</h3>
    </div>

    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(COSTO) FROM VENTAS WHERE SECCION = 4 AND YEAR(fecha_venta) = 2025 AND COSTO>0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>2025</h3>
    </div>
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(GANANCIA) FROM VENTAS WHERE SECCION = 4 AND YEAR(fecha_venta) = 2025 AND COSTO>0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>2025</h3>
    </div>


    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(COSTO) FROM VENTAS WHERE SECCION = 4 AND YEAR(fecha_venta) = 2024 AND COSTO >0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>2024</h3>
    </div>
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(GANANCIA) FROM VENTAS WHERE SECCION = 4 AND YEAR(fecha_venta) = 2024 AND COSTO >0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>2024</h3>
    </div>

  </section>
  <h2>ventas accesorio negocio</h2>

  <section class="seccion">
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(COSTO) FROM VENTAS WHERE SECCION = 1 AND YEAR(fecha_venta) = 2026 AND COSTO>0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>2026</h3>
    </div>
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(GANANCIA) FROM VENTAS WHERE SECCION = 1 AND YEAR(fecha_venta) = 2026 AND COSTO>0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>2026</h3>
    </div>

    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(COSTO) FROM VENTAS WHERE SECCION = 1 AND YEAR(fecha_venta) = 2025 AND COSTO>0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>2025</h3>
    </div>
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(GANANCIA) FROM VENTAS WHERE SECCION = 1 AND YEAR(fecha_venta) = 2025 AND COSTO>0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>2025</h3>
    </div>


    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(COSTO) FROM VENTAS WHERE SECCION = 1 AND YEAR(fecha_venta) = 2024 AND COSTO >0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>2024</h3>
    </div>
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(GANANCIA) FROM VENTAS WHERE SECCION = 1 AND YEAR(fecha_venta) = 2024 AND COSTO >0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>2024</h3>
    </div>

  </section>
  <h2>ventas productos papeleria</h2>

  <section class="seccion">
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(COSTO) FROM VENTAS WHERE SECCION = 5 AND YEAR(fecha_venta) = 2026 AND COSTO>0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>2026</h3>
    </div>

    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(COSTO) FROM VENTAS WHERE SECCION = 5 AND YEAR(fecha_venta) = 2025 AND COSTO>0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>2025</h3>
    </div>


    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(COSTO) FROM VENTAS WHERE SECCION = 5 AND YEAR(fecha_venta) = 2024 AND COSTO >0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>2024</h3>
    </div>

  </section>
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

  <h2>Venta Factura - ABRIL 2026</h2>

  <section class="seccion">
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE YEAR(fecha_venta) = 2026 AND MONTH(fecha_venta) = 4 AND FACTURA='FACTURA'";
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
  <h2>Capital Negocio</h2>

  <section class="seccion">
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(preciocosto) FROM ventas WHERE YEAR(fecha_venta) = 2026  AND SECCION= 1  AND preciocosto > 0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>Negocio</h3>
    </div>
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(preciocosto) FROM ventas WHERE YEAR(fecha_venta) = 2026  AND SECCION= 4  AND preciocosto > 0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>Celular</h3>
    </div>
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(preciocosto) FROM ventas WHERE YEAR(fecha_venta) = 2026  AND SECCION= 6  AND preciocosto > 0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>Hojas</h3>
    </div>
    <div>
      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(preciocosto) FROM ventas WHERE YEAR(fecha_venta) = 2026  AND SECCION= 2 AND preciocosto > 0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>PATRICIA</h3>
    </div>
    <div>
      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(preciocosto) FROM ventas WHERE YEAR(fecha_venta) = 2026  AND SECCION= 5 AND preciocosto > 0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>PAPELERIA</h3>
    </div>
    
    <div>
      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(preciocosto) FROM ventas WHERE YEAR(fecha_venta) = 2026  AND preciocosto > 0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>SUMA</h3>
    </div>
  </section>
  <h2>Ganancia Negocio</h2>

  <section class="seccion">
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(ganancia) FROM ventas WHERE YEAR(fecha_venta) = 2026  AND SECCION= 1  AND ganancia > 0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>Negocio</h3>
    </div>
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(ganancia) FROM ventas WHERE YEAR(fecha_venta) = 2026  AND SECCION= 4  AND ganancia > 0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>Celular</h3>
    </div>
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(ganancia) FROM ventas WHERE YEAR(fecha_venta) = 2026  AND SECCION= 6  AND ganancia > 0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>Hojas</h3>
    </div>
    <div>
      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(ganancia) FROM ventas WHERE YEAR(fecha_venta) = 2026  AND SECCION= 2 AND ganancia > 0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>PATRICIA</h3>
    </div>
    <div>
      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(ganancia) FROM ventas WHERE YEAR(fecha_venta) = 2026  AND SECCION= 5 AND ganancia > 0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>PAPELERIA</h3>
    </div>

    <div>
      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(ganancia) FROM ventas WHERE YEAR(fecha_venta) = 2026  AND ganancia > 0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>SUMA</h3>
    </div>
  </section>
  
  <h2>Ventas</h2>
  <section class="seccion">
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE YEAR(fecha_venta) = 2026  AND SECCION= 1 AND costo > 0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>Negocio</h3>
    </div>
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE YEAR(fecha_venta) = 2026  AND SECCION= 4  AND costo > 0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>Celular</h3>
    </div>
    <div>

      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE YEAR(fecha_venta) = 2026  AND SECCION= 6  AND costo > 0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>Hojas</h3>
    </div>
    <div>
      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE YEAR(fecha_venta) = 2026  AND SECCION= 2 AND costo > 0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>PATRICIA</h3>
    </div>
    <div>
      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE YEAR(fecha_venta) = 2026  AND SECCION= 5 AND costo > 0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>PAPELERIA</h3>
    </div>

    <div>
      <i class="fa-solid fa-laptop"></i>
      <h5 id="cantidadS">
        <?php
        $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE YEAR(fecha_venta) = 2026  AND costo > 0";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          $vt  = number_format($mostrar[0], 2, '.', ',');
          echo ("$" . $vt . PHP_EOL . "");
        }
        ?>
      </h5>
      <h3>SUMA</h3>
    </div>
  </section>


</section>



<?php include_once 'footer.php' ?>
<script src="busquedaReporte.js"></script>