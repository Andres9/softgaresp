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


</section>



<?php include_once 'footer.php' ?>
<script src="busquedaReporte.js"></script>