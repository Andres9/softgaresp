<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
?>

      <!-- SECCION DE VENTAS -->
      
    <div class="seccion reportes-resumen">
      <article class="reportes-resumen-card">
        <div class="reportes-resumen-cabecera">
          <span class="reportes-resumen-etiqueta">Ventas</span>
          <span class="reportes-resumen-icono" aria-hidden="true"><i class="fa-solid fa-chart-line"></i></span>
        </div>
        <strong class="reportes-resumen-valor">
          <?php
          $ventasTotales = "SELECT COUNT(id_venta) FROM ventas WHERE YEAR(fecha_venta) = 2025";
          $resultado = mysqli_query($conn, $ventasTotales);
          while ($mostrar = mysqli_fetch_array($resultado)) {
            echo ($mostrar[0] . "");
          }

          ?>
        </strong>
        <p class="reportes-resumen-detalle">Registros de 2025</p>
      </article>
      <article class="reportes-resumen-card reportes-resumen-card-total">
        <div class="reportes-resumen-cabecera">
          <span class="reportes-resumen-etiqueta">Total</span>
          <span class="reportes-resumen-icono" aria-hidden="true"><i class="fa-solid fa-sack-dollar"></i></span>
        </div>
        <strong class="reportes-resumen-valor">
          <?php
          $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE YEAR(fecha_venta) = 2026 AND costo >= 0";
          $resultado = mysqli_query($conn, $ventasTotales);
          while ($mostrar = mysqli_fetch_array($resultado)) {
            $vt  = number_format($mostrar[0],2, '.', ',');
            echo ("$" . $vt.PHP_EOL . "");
          }

          ?>
        </strong>
        <p class="reportes-resumen-detalle">Ventas registradas en 2026</p>
      </article>
      <a class="reportes-resumen-pdf" href="ventasMesPDF.php" target="_blank" rel="noopener"
          aria-label="Generar PDF de ventas del mes actual">
        <span class="reportes-resumen-pdf-icono" aria-hidden="true"><i class="fa-solid fa-file-pdf"></i></span>
        <span class="reportes-resumen-pdf-texto">
          <strong>Generar PDF</strong>
          <small>Ventas del mes actual</small>
        </span>
        <i class="fa-solid fa-chevron-right reportes-resumen-pdf-flecha" aria-hidden="true"></i>
      </a>
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
          <th>Factura</th>
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
                            WHERE YEAR(fecha_venta) = 2026";

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
              <?php echo $mostrar['factura'] ?>
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


