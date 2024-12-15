      <!-- SECCION DE VENTAS -->
      
    <div class="seccion">
      <div>
        <h5 class="numerototal">
          <?php
          $ventasTotales = "SELECT COUNT(id_venta) FROM ventas WHERE YEAR(fecha_venta) = 2024";
          $resultado = mysqli_query($conn, $ventasTotales);
          while ($mostrar = mysqli_fetch_array($resultado)) {
            echo ($mostrar[0] . "");
          }

          ?>
        </h5>
        <span>
          <h3>Ventas</h3>
        </span>
      </div>
      <div>
        <h5 class="numerototal">
          <?php
          $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE YEAR(fecha_venta) = 2024";
          $resultado = mysqli_query($conn, $ventasTotales);
          while ($mostrar = mysqli_fetch_array($resultado)) {
            $vt  = number_format($mostrar[0],2, '.', ',');
            echo ("$" . $vt.PHP_EOL . "");
          }

          ?>
        </h5>
        <span>
          <h3>Total</h3>
        </span>

      </div>

      <div>
        <h5>
          <a href=""> <i class="fa-solid fa-file-pdf"></i></a>

        </h5>
        <span>
          <h3>GENERAR PDF</h3>
        </span>
      </div>
    </div>

    <h2>Ventas</h2>
    <table id="myTable" class="display responsive nowrap" style="width: 100%">
      <thead>
        <tr>
          <th>ID</th>
          <th>Cantidad</th>
          <th>Descripcion</th>
          <th>Costo</th>
          <th>Seccion</th>
          <th>fecha_venta</th>
          <th>PDF</th>
          <th>Editar</th>
          <th>Eliminar</th>
        </tr>
      </thead>

      <tbody>
        <?php
        $consulta = "select * from ventas v
                            JOIN seccion s ON v.seccion  = s.id_seccion 
                            WHERE YEAR(fecha_venta) = 2024";

        $resultado = mysqli_query($conn, $consulta);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          ?>
          <tr>
            <td>
              <?php echo $mostrar['id_venta'] ?>
            </td>
            <td>
              <?php echo $mostrar['cantidad'] ?>
            </td>
            <td>
              <?php echo $mostrar['descripcion'] ?>
            </td>
            <td>
              <?php echo $mostrar['costo'] ?>
            </td>
            <td>
              <?php echo $mostrar['descripcionSeccion'] ?>
            </td>
            <td>
              <?php echo $mostrar['fecha_venta'] ?>
            </td>
            <td>
              <a href="#" target="_blank"><i class="fa-solid fa-file-pdf"></i></a>
            </td>
            <td>
              <a href="editarVentas.php?updVenta=<?= $mostrar['id_venta'] ?>"><i class="fa-solid fa-pen"></i></a>
            </td>
            <td>
              <a href="#"><i class="fa-solid fa-trash"></i></a>
            </td>
          </tr>
          <?php
        }
        ?>
      </tbody>
    </table>


