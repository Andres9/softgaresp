<?php
header('Cache-Control: no-store, private');
include_once 'encabezado.php';
require_once __DIR__ . '/notas_recepcion.php';
prepararRecepcionNotas($conn);
$notaMensaje = $_SESSION['nota_mensaje'] ?? '';
unset($_SESSION['nota_mensaje']);
?>

<section>

  <?php include __DIR__ . '/app/Views/notas_subnav.php'; ?>

  <?php if ($notaMensaje !== '') { ?>
    <div class="alert alert-success" role="status"><?= escaparRecepcionHtml($notaMensaje) ?></div>
  <?php } ?>

  <div class="seccion reportes-resumen">
    <article class="reportes-resumen-card">
      <div class="reportes-resumen-cabecera">
        <span class="reportes-resumen-etiqueta">Notas</span>
        <span class="reportes-resumen-icono" aria-hidden="true"><i class="fa-solid fa-clipboard-list"></i></span>
      </div>
      <strong class="reportes-resumen-valor">
        <?php
        $ventasTotales = "SELECT COUNT(id_nota) FROM notas";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          echo ($mostrar[0]);
        }

        ?>
      </strong>
      <p class="reportes-resumen-detalle">Notas registradas</p>
    </article>
    <article class="reportes-resumen-card">
      <div class="reportes-resumen-cabecera">
        <span class="reportes-resumen-etiqueta">Entrada</span>
        <span class="reportes-resumen-icono" aria-hidden="true"><i class="fa-solid fa-inbox"></i></span>
      </div>
      <strong class="reportes-resumen-valor">
          <?php
          $ventasTotales = "SELECT COUNT(estado) FROM notas WHERE estado='1'";
          $resultado = mysqli_query($conn, $ventasTotales);
          while ($mostrar = mysqli_fetch_array($resultado)) {
            echo ($mostrar[0]);
          }

          ?>
      </strong>
      <p class="reportes-resumen-detalle">Esperando atención</p>
    </article>
    <article class="reportes-resumen-card">
      <div class="reportes-resumen-cabecera">
        <span class="reportes-resumen-etiqueta">Revisión</span>
        <span class="reportes-resumen-icono" aria-hidden="true"><i class="fa-solid fa-magnifying-glass"></i></span>
      </div>
      <strong class="reportes-resumen-valor">
        <?php
        $ventasTotales = "SELECT COUNT(estado) FROM notas WHERE estado='2'";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          echo ($mostrar[0]);
        }

        ?>
      </strong>
      <p class="reportes-resumen-detalle">En proceso de revisión</p>
    </article>
    <article class="reportes-resumen-card">
      <div class="reportes-resumen-cabecera">
        <span class="reportes-resumen-etiqueta">Entregado</span>
        <span class="reportes-resumen-icono" aria-hidden="true"><i class="fa-solid fa-circle-check"></i></span>
      </div>
      <strong class="reportes-resumen-valor">
          <?php
          $ventasTotales = "SELECT COUNT(estado) FROM notas WHERE estado='3'";
          $resultado = mysqli_query($conn, $ventasTotales);
          while ($mostrar = mysqli_fetch_array($resultado)) {
            echo ($mostrar[0]);
          }

          ?>
      </strong>
      <p class="reportes-resumen-detalle">Servicio finalizado</p>
    </article>
    <article class="reportes-resumen-card">
      <div class="reportes-resumen-cabecera">
        <span class="reportes-resumen-etiqueta">Solución</span>
        <span class="reportes-resumen-icono" aria-hidden="true"><i class="fa-solid fa-gear"></i></span>
      </div>
      <strong class="reportes-resumen-valor">
        <?php
        $ventasTotales = "SELECT COUNT(estado) FROM notas WHERE estado='4'";
        $resultado = mysqli_query($conn, $ventasTotales);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          echo ($mostrar[0]);
        }

        ?>
      </strong>
      <p class="reportes-resumen-detalle">Solución registrada</p>
    </article>
  </div>


  <div class="container-fluid">
    <div class="row">
      <div class="col-md-8">
        <h2>Registro Notas</h2>
        <div id="registro2" class="container">
          <form action="registroNotas.php" method="post" class="nota-formulario">
            <input type="hidden" name="token_csrf" value="<?= htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8') ?>">
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
                  <label for="nota-cliente">Nombre cliente</label>
                  <select id="nota-cliente" name="cliente" class="select">
                    <option value="">Selecciona a un cliente</option>
                    <?php
                    $totalClientes = "SELECT * FROM clientes";
                    $resultado = mysqli_query($conn, $totalClientes);
                    while ($mostrarCliente = mysqli_fetch_array($resultado)) {
                      $id = $mostrarCliente["id_cliente"];
                      $nombre = $mostrarCliente["nombre"];
                      $apellidos = $mostrarCliente["apellidos"];
                      ?>
                      <option value="<?= (int) $id ?>">
                        <?= escaparRecepcionHtml($nombre . ' ' . $apellidos) ?>
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
                <select id="nota-equipo" name="equipo" class="select">
                  <option value="">Selecciona un equipo</option>
                  <?php
                  $datosEquipo = "SELECT * FROM equipo";
                  $resultado = mysqli_query($conn, $datosEquipo);

                  while ($mostrarEquipo = mysqli_fetch_array($resultado)) {
                    $id = $mostrarEquipo["id_equipo"];
                    $descripcion = $mostrarEquipo["descripcionEquipo"];
                    ?>
                    <option value="<?= (int) $id ?>" data-patron="<?= equipoAdmitePatron($descripcion) ? '1' : '0' ?>">
                      <?= htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') ?>
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

            <?php include __DIR__ . '/nota_recepcion_formulario.php'; ?>

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
                  <option value="<?= (int) $id ?>">
                    <?= escaparRecepcionHtml($descripcion) ?>
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
              <select id="nota-estado" name="estado" class="select">
                <option value="">Seleccione una opcion</option>
                <?php
                $ventasTotales = "SELECT * FROM estadoservicio";
                $resultado = mysqli_query($conn, $ventasTotales);
                while ($mostrar = mysqli_fetch_array($resultado)) {
                  $id = $mostrar['id_estado'];
                  $descripcion = $mostrar["estadoServicio"];
                  ?>
                  <option value="<?= (int) $id ?>">
                    <?= escaparRecepcionHtml($descripcion) ?>
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

      </div>

      <div class="col-md-4">
        <h2>Movimientos</h2>
        <noscript><p class="alert alert-warning">Activa JavaScript para confirmar la eliminación de notas.</p></noscript>
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
                <td><?= escaparRecepcionHtml($mostrar['id_nota']) ?></td>
                <td><?= escaparRecepcionHtml($mostrar['nombre'] . ' ' . $mostrar['apellidos']) ?></td>
                <td><?= escaparRecepcionHtml($mostrar['fecha_alta']) ?></td>
                <td><?= escaparRecepcionHtml($mostrar['descripcionEquipo']) ?></td>
                <td><?= escaparRecepcionHtml($mostrar['marca']) ?></td>
                <td><?= escaparRecepcionHtml($mostrar['modelo']) ?></td>
                <td><?= escaparRecepcionHtml($mostrar['tipoServicio']) ?></td>
                <td><?= escaparRecepcionHtml($mostrar['costoServicio']) ?></td>
                <td><?= escaparRecepcionHtml($mostrar['descripcionServicio']) ?></td>
                <td><?= escaparRecepcionHtml($mostrar['notas']) ?></td>
                <td><?= escaparRecepcionHtml($mostrar['estadoServicio']) ?></td>
                <td><?= escaparRecepcionHtml($mostrar['anticipo']) ?></td>
                <td><?= escaparRecepcionHtml($mostrar['ganancia']) ?></td>
                <td><?= escaparRecepcionHtml($mostrar['restante']) ?></td>
                <td><?= escaparRecepcionHtml($mostrar['pagado']) ?></td>
                <td>
                  <a href="reportes.php?idNota=<?= (int) $mostrar['id_nota'] ?>" target="_blank"><i
                      class="fa-solid fa-file-pdf"></i></a>
                </td>
                <td>
                  <a href="editarNotas.php?updNotas=<?= (int) $mostrar['id_nota'] ?>"><i class="fa-solid fa-pen"></i></a>
                </td>
                <td>
                  <form action="eliminarNota.php" method="post" class="nota-eliminar">
                    <input type="hidden" name="token_csrf" value="<?= escaparRecepcionHtml(token_csrf()) ?>">
                    <input type="hidden" name="id_nota" value="<?= (int) $mostrar['id_nota'] ?>">
                    <input type="hidden" name="origen" value="notaCliente">
                    <button type="button" data-bs-toggle="modal" data-bs-target="#confirmar-eliminar-nota" title="Eliminar nota" aria-label="Eliminar nota <?= (int) $mostrar['id_nota'] ?>"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                  </form>
                </td>
              </tr>
              <?php
            }
            ?>
          </tbody>
        </table>
</section>
</div>
</div>
</div>




<?php include_once 'footer.php'; ?>