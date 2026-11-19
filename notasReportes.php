<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
?>

<section>

    <!-- SECCION DE NOTAS -->
    <div class="seccion reportes-resumen">
      <article class="reportes-resumen-card">
        <div class="reportes-resumen-cabecera">
          <span class="reportes-resumen-etiqueta">Servicios</span>
          <span class="reportes-resumen-icono" aria-hidden="true"><i class="fa-solid fa-toolbox"></i></span>
        </div>
        <strong class="reportes-resumen-valor">
          <?php
          $ventasTotales = "SELECT COUNT(id_nota) FROM notas WHERE YEAR(fecha_alta) = 2025";
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
          $ventasTotales = "SELECT SUM(ganancia) FROM notas WHERE YEAR(fecha_alta) = 2026";
          $resultado = mysqli_query($conn, $ventasTotales);
          while ($mostrar = mysqli_fetch_array($resultado)) {
            $vt  = number_format($mostrar[0],2, '.', ',');
            echo ("$" . $vt.PHP_EOL . "");
          }

          ?>
        </strong>
        <p class="reportes-resumen-detalle">Ganancia registrada en 2026</p>
      </article>
      <a class="reportes-resumen-pdf" href="serviciosMesPDF.php" target="_blank" rel="noopener"
          aria-label="Generar reporte PDF de servicios del mes actual">
        <span class="reportes-resumen-pdf-icono" aria-hidden="true"><i class="fa-solid fa-file-pdf"></i></span>
        <span class="reportes-resumen-pdf-texto">
          <strong>Generar PDF</strong>
          <small>Servicios del mes actual</small>
        </span>
        <i class="fa-solid fa-chevron-right reportes-resumen-pdf-flecha" aria-hidden="true"></i>
      </a>
    </div>

    
    <h2>Notas</h2>
  
    <table id="myTable2" class="display responsive nowrap" style="width: 100%">
      <thead>
        <tr>
          <th>ID</th>
          <th>Cliente</th>
          <th>Fecha alta</th>
          <th>Equipo</th>
          <th>Marca</th>
          <th>Modelo</th>
          <th>Servicio</th>
          <th>Costo</th>
          <th>Descripcion</th>
          <th>Notas</th>
          <th>Estado</th>
          <th>Anticipo</th>
          <th>Ganancia</th>
          <th>Restante</th>
          <th>Pagado</th>
          <th>PDF</th>
          <th>Editar</th>
          <th>Eliminar</th>
        </tr>
      </thead>

      <tbody>
        <?php
        $consulta = "select * from notas n 
                            JOIN clientes c ON n.id_cliente  = c.id_CLIENTE 
                            JOIN equipo e ON n.equipo =  e.id_equipo 
                            JOIN servicio s ON n.servicio =  s.id_servicio
                            JOIN estadoservicio es ON n.estado =  es.id_estado 
                          ";
                         /*  WHERE YEAR(fecha_alta) = 2024 */
        /* WHERE fecha_alta >= DATE(NOW()) */
        $resultado = mysqli_query($conn, $consulta);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          ?>
          <tr>
            <td><?php echo $mostrar['id_nota'] ?></td>
            <td><?php echo $mostrar['nombre'] . " " . $mostrar["apellidos"] ?></td>
            <td><?php echo $mostrar['fecha_alta'] ?></td>
            <td><?php echo $mostrar['descripcionEquipo'] ?></td>
            <td><?php echo $mostrar['marca'] ?></td>
            <td><?php echo $mostrar['modelo'] ?></td>
            <td><?php echo $mostrar['tipoServicio'] ?></td>
            <td><?php echo $mostrar['costoServicio'] ?></td>
            <td><?php echo $mostrar['descripcionServicio'] ?></td>
            <td><?php echo $mostrar['notas'] ?></td>
            <td><?php echo $mostrar['estadoServicio'] ?></td>
            <td><?php echo $mostrar['anticipo'] ?></td>
            <td><?php echo $mostrar['ganancia'] ?></td>
            <td><?php echo $mostrar['restante'] ?></td>
            <td><?php echo $mostrar['pagado'] ?></td>
            <td>
              <a href="reportes.php?idNota=<?= $mostrar['id_nota'] ?>" target="_blank"><i
                  class="fa-solid fa-file-pdf"></i></a>
            </td>
            <td>
              <a href="editarNotas.php?updNotas=<?= $mostrar['id_nota'] ?>"><i class="fa-solid fa-pen"></i></a>
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
    </section>