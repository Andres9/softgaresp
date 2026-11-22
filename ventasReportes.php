<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
require_once __DIR__ . '/ventas_db.php';
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
          $ventasTotales = "SELECT COUNT(DISTINCT id_grupo) + COALESCE(SUM(id_grupo IS NULL), 0) FROM ventas WHERE YEAR(fecha_venta) = 2025";
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
          $ventasTotales = "SELECT SUM(v.costo) FROM ventas v
            LEFT JOIN (SELECT id_grupo, SUM(costo) AS total_grupo FROM ventas WHERE id_grupo IS NOT NULL GROUP BY id_grupo) g
              ON g.id_grupo = v.id_grupo
            WHERE YEAR(v.fecha_venta) = 2026
              AND ((v.id_grupo IS NULL AND v.costo >= 0) OR (v.id_grupo IS NOT NULL AND g.total_grupo >= 0))";
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
          <th>Venta</th>
          <th>Cantidad</th>
          <th>Descripcion</th>
          <th>Costo</th>
          <th>Total venta</th>
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
        $consulta = "SELECT v.*, s.descripcionSeccion, COALESCE(g.total_grupo, v.costo) AS total_grupo
                            FROM ventas v
                            JOIN seccion s ON v.seccion = s.id_seccion
                            LEFT JOIN (SELECT id_grupo, SUM(costo) AS total_grupo FROM ventas WHERE id_grupo IS NOT NULL GROUP BY id_grupo) g
                              ON g.id_grupo = v.id_grupo
                            WHERE YEAR(v.fecha_venta) = 2026";

        $resultado = mysqli_query($conn, $consulta);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          ?>
          <tr>
            <td>
              <?php echo $mostrar['id_venta'] ?>
            </td>
            <td><?= $mostrar['id_grupo'] === null ? 'Individual' : 'Grupo ' . (int) $mostrar['id_grupo'] ?></td>
            <td>
              <?php echo $mostrar['cantidad'] ?>
            </td>
            <td>
              <?= htmlspecialchars((string) $mostrar['descripcion'], ENT_QUOTES, 'UTF-8') ?>
            </td>
            <td>
              <?php echo $mostrar['costo'] ?>
            </td>
            <td><?= number_format((float) $mostrar['total_grupo'], 2, '.', ',') ?></td>
            <td>
              <?= htmlspecialchars((string) $mostrar['descripcionSeccion'], ENT_QUOTES, 'UTF-8') ?>
            </td>
            <td>
              <?= htmlspecialchars((string) $mostrar['factura'], ENT_QUOTES, 'UTF-8') ?>
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


