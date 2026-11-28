<?php
header('Cache-Control: no-store, private');
include_once 'encabezado.php';
require_once __DIR__ . '/notas_recepcion.php';
prepararRecepcionNotas($conn);
require_once __DIR__ . '/notas_servicios.php';
prepararResumenServiciosNota($conn);
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
                  <select id="nota-cliente" name="cliente" class="select" required>
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

            <?php include __DIR__ . '/nota_equipos_formulario.php'; ?>

            <?php include __DIR__ . '/nota_recepcion_formulario.php'; ?>

            <?php include __DIR__ . '/nota_servicios_formulario.php'; ?>

            <fieldset>
              <legend>Estado del equipo</legend>
              <select id="nota-estado" name="estado" class="select" required>
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
                  <label for="">Total de servicios</label>
                  <input type="number" name="costoServicio" value="0" class="form-control" readonly />
                </div>
                <div class="col-md-4 col-xs-12">
                  <label for="">Ganancia</label>
                  <input type="number" name="ganancia" value="0" step="1" class="form-control" />
                </div>
                <div class="col-md-4 col-xs-12">
                  <label for="">Anticipo</label>
                  <input type="number" name="anticipo" value="0" min="0" max="2147483647" step="1" class="form-control" required />
                </div>
                <div class="col-md-4 col-xs-12">
                  <label for="">Restante</label>
                  <input type="number" name="restante" value="0" class="form-control" readonly />
                </div>
                <div class="col-md-4 col-xs-12">
                  <label for="">Pago adicional (sin anticipo)</label>
                  <input type="number" name="pagado" value="0" min="0" max="2147483647" step="1" class="form-control" required />
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
            $consulta = "select n.*, c.*, e.*, es.*, COALESCE(ds.nombres, s.tipoServicio) AS servicios_nombres, COALESCE(de.equipos, e.descripcionEquipo) AS equipos_nombres, COALESCE(de.marcas, n.marca) AS marcas_nombres, COALESCE(de.modelos, n.modelo) AS modelos_nombres from notas n
                            JOIN clientes c ON n.id_cliente  = c.id_CLIENTE 
                            JOIN equipo e ON n.equipo =  e.id_equipo 
                            JOIN servicio s ON n.servicio =  s.id_servicio
                            LEFT JOIN (SELECT id_nota, GROUP_CONCAT(nombre ORDER BY id_detalle SEPARATOR ', ') AS nombres FROM nota_servicio GROUP BY id_nota) ds ON ds.id_nota = n.id_nota
                            LEFT JOIN (SELECT id_nota, GROUP_CONCAT(descripcion ORDER BY id_detalle SEPARATOR ', ') AS equipos, GROUP_CONCAT(NULLIF(marca, '') ORDER BY id_detalle SEPARATOR ', ') AS marcas, GROUP_CONCAT(NULLIF(modelo, '') ORDER BY id_detalle SEPARATOR ', ') AS modelos FROM nota_equipo GROUP BY id_nota) de ON de.id_nota = n.id_nota
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
                <td><?= escaparRecepcionHtml($mostrar['equipos_nombres']) ?></td>
                <td><?= escaparRecepcionHtml($mostrar['marcas_nombres']) ?></td>
                <td><?= escaparRecepcionHtml($mostrar['modelos_nombres']) ?></td>
                <td><?= escaparRecepcionHtml($mostrar['servicios_nombres']) ?></td>
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
