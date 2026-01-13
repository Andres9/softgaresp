<section>

    <!-- SECCION DE NOTAS -->
    <div class="seccion">
      <div>
        <h5 class="numerototal">
          <?php
          $ventasTotales = "SELECT COUNT(id_nota) FROM notas WHERE YEAR(fecha_alta) = 2025";
          $resultado = mysqli_query($conn, $ventasTotales);
          while ($mostrar = mysqli_fetch_array($resultado)) {
            echo ($mostrar[0] . "");
          }

          ?>
        </h5>
        <span>
          <h3>Servicios</h3>
        </span>
      </div>
      <div>
        <h5 class="numerototal">
          <?php
          $ventasTotales = "SELECT SUM(ganancia) FROM notas WHERE YEAR(fecha_alta) = 2026";
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