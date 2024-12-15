<?php include_once 'encabezado.php'?>

  <section>
    <div class="seccion">
      <div>

        <h5 class="numerototal">
          <?php
          $ventasTotales = "SELECT COUNT(id_nota) FROM notas";
          $resultado = mysqli_query($conn, $ventasTotales);
          while ($mostrar = mysqli_fetch_array($resultado)) {
            echo ($mostrar[0]);
          }

          ?>
        </h5>
        <span>
          <h3>Notas</h3>
        </span>
      </div>
      <div>
        <span>


          <h5 class="numerototal">
            <?php
            $ventasTotales = "SELECT COUNT(estado) FROM notas WHERE estado='1'";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              echo ($mostrar[0]);
            }

            ?>
          </h5>
        </span>
        <span>

          <h3>Entrada</h3>
        </span>
      </div>
      <div>


        <h5 class="numerototal">
          <?php
          $ventasTotales = "SELECT COUNT(estado) FROM notas WHERE estado='2'";
          $resultado = mysqli_query($conn, $ventasTotales);
          while ($mostrar = mysqli_fetch_array($resultado)) {
            echo ($mostrar[0]);
          }

          ?>
        </h5>

        <span>

          <h3>Revision</h3>
        </span>
      </div>
      <div>
        <span>

          <h5 class="numerototal">
            <?php
            $ventasTotales = "SELECT COUNT(estado) FROM notas WHERE estado='3'";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              echo ($mostrar[0]);
            }

            ?>
          </h5>
        </span>
        <span>

          <h3>Entregado</h3>
        </span>


      </div>
      <div>

        <h5 class="numerototal">
          <?php
          $ventasTotales = "SELECT COUNT(estado) FROM notas WHERE estado='4'";
          $resultado = mysqli_query($conn, $ventasTotales);
          while ($mostrar = mysqli_fetch_array($resultado)) {
            echo ($mostrar[0]);
          }

          ?>
        </h5>

        <span>
          <h3>Ssolucion</h3>
        </span>

      </div>
    </div>

    <h2>Registro Notas</h2>

    <div id="registro2" class="container">
      <form action="registroNotas.php" method="post">
        <div class="row" style="display: none">
          <div class="col-12">
            <fieldset>
              <legend>Fecha</legend>
              <label for="">Fecha de entrada</label>
              <input class="form-control" type="datetime" name="fechaEntrada" value="<?php echo $fecha_actual ?>" />
            </fieldset>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <fieldset>
              <legend>Datos del cliente</legend>
              <label for="">Nombre cliente</label>
              <select name="cliente" class="select">
                <option value="">Selecciona a un cliente</option>
                <?php
                $totalClientes = "SELECT * FROM clientes";
                $resultado = mysqli_query($conn, $totalClientes);
                while ($mostrarCliente = mysqli_fetch_array($resultado)) {
                  $id = $mostrarCliente["id_cliente"];
                  $nombre = $mostrarCliente["nombre"];
                  $apellidos = $mostrarCliente["apellidos"];
                  ?>
                  <option value="<?php echo $id; ?>">
                    <?php echo $nombre . " " . $apellidos; ?>
                  </option>
                  <?php
                }
                ?>
              </select>
            </fieldset>
          </div>
        </div>

        <fieldset>
          <legend>Datos del equipo</legend>
          <div class="col-md-12 col-xs-12">
            <label for="">Equipo</label>
            <select name="equipo" class="select">
              <option value="">Selecciona un equipo</option>
              <?php
              $datosEquipo = "SELECT * FROM equipo";
              $resultado = mysqli_query($conn, $datosEquipo);

              while ($mostrarEquipo = mysqli_fetch_array($resultado)) {
                $id = $mostrarEquipo["id_equipo"];
                $descripcion = $mostrarEquipo["descripcionEquipo"];
                ?>
                <option value="<?php echo $id; ?>">
                  <?php echo $descripcion ?>
                </option>
                <?php
              }
              ?>
            </select>
          </div>
          <div class="row">
            <div class="col-md-6 col-xs-12">
              <label for="marca">Marca</label>
              <input type="text" name="marca" class="form-control" />
            </div>
            <div class="col-md-6 col-xs-12">
              <label for="modelo">Modelo</label>
              <input type="text" name="modelo" class="form-control" />
            </div>
          </div>
        </fieldset>

        <fieldset>
          <legend>Servicio</legend>
          <label for="">Servicio</label>
          <select name="servicio" class="select">
            <option value="">Seleccione una opcion</option>
            <?php
            $ventasTotales = "SELECT * FROM servicio";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $id = $mostrar['id_servicio'];
              $descripcion = $mostrar["tipoServicio"];
              ?>
              <option value="<?php echo $id; ?>">
                <?php echo $descripcion ?>
              </option>
              <?php
            }
            ?>
          </select>
          <label for="">Descripcion</label>
          <textarea id="" cols="45" rows="3" name="descripcion" class="form-control"></textarea>
        </fieldset>

        <fieldset>
          <legend>Estado del equipo</legend>
          <select name="estado" class="select">
            <option value="">Seleccione una opcion</option>
            <?php
            $ventasTotales = "SELECT * FROM estadoservicio";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $id = $mostrar['id_estado'];
              $descripcion = $mostrar["estadoServicio"];
              ?>
              <option value="<?php echo $id; ?>">
                <?php echo $descripcion ?>
              </option>
              <?php
            }
            ?>
          </select>
        </fieldset>
        <fieldset>
          <legend>Costo</legend>
          <div class="row">
            <div class="col-md-4 col-xs-12">
              <label for="">Costo</label>
              <input type="number" name="costoServicio" value="0" class="form-control" />
            </div>
            <div class="col-md-4 col-xs-12">
              <label for="">Ganancia</label>
              <input type="number" name="ganancia" value="0" class="form-control" />
            </div>
            <div class="col-md-4 col-xs-12">
              <label for="">Anticipo</label>
              <input type="number" name="anticipo" value="0" class="form-control" />
            </div>
            <div class="col-md-4 col-xs-12">
              <label for="">Restante</label>
              <input type="number" name="restante" value="0" class="form-control" />
            </div>
            <div class="col-md-4 col-xs-12">
              <label for="">Pagado</label>
              <input type="number" name="pagado" value="0" class="form-control" />
            </div>
          </div>
        </fieldset>

        <fieldset>
          <legend>Notas</legend>
          <label for="">Notas adicionales</label>
          <textarea name="nota" id="" cols="45" rows="3" class="form-control"></textarea>
        </fieldset>

        <button type="submit" class="btn btn-primary">Agregar</button>
      </form>
    </div>

    <h2>Movimientos</h2>
    <table id="myTable" class="display responsive nowrap" style="width: 100%">
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
                            WHERE fecha_alta >= DATE(NOW())";
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
  
  <?php include_once 'footer.php'; ?>