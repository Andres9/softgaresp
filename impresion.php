<?php include_once 'encabezado.php'; ?>

  <section>
    <div class="seccion">
      <div>
        <span>
          <h5 class="numerototal">
            <?php
            $ventasTotales = "SELECT SUM(precio) FROM impresion ";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }
            ?>
          </h5>
        </span>
        <span>
          <h3>Caja Impresion</h3>
        </span>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6">
        <h2>Registro Impresiones</h2>
        <div id="registro" class="container">
          <form action="registroimpresion.php" method="post">
            <div class="row">
              <div class="col-md-12" style="display: none">
                <div class="row">
                  <label for="">Fecha impresion</label>
                  <input type="datetime" name="fechaVenta" value="<?php echo $fecha_actual ?>"
                    class="form-control datepicker" />
                </div>
              </div>
              <div class="col-md-2 col-xs-12">
                <label for="">Cant.</label>
                <input type="number" value="1" name="cantidad" class="form-control" />
              </div>
              <div class="col-md-7 col-xs-12">
                <label for="">Descripción</label>
                <input type="text" name="descripcion" class="form-control" />
              </div>
              <div class="col-md-3 col-xs-12">
                <label for="">Costo</label>
                <input type="number" name="costo" class="form-control" step="0.01" />
              </div>
              
            </div>
            <button type="submit" class="btn btn-primary">
              <i class="fa-solid fa-floppy-disk"></i> Registrar
            </button>
          </form>
        </div>
      </div>

      <div class="col-md-6">
        <h2>Movimientos</h2>
        <table id="tablaventas" class="display responsive nowrap" style="width: 100%">
          <thead>
            <tr>
              <th>ID</th>
              <th>Cantidad</th>
              <th>Descripcion</th>
              <th>Precio</th>
              <th>Fecha</th>
              <th>Editar</th>
              <th>Eliminar</th>
            </tr>
          </thead>

          <tbody>
            <?php
            $consulta = "SELECT * FROM impresion";

            $resultado = mysqli_query($conn, $consulta);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              ?>
              <tr>
                <td><?php echo $mostrar['idImpre'] ?></td>
                <td><?php echo $mostrar['cant'] ?></td>
                <td><?php echo $mostrar['concepto'] ?></td>
                <td><?php echo $mostrar['precio'] ?></td>
                <td><?php echo $mostrar['fecha'] ?></td>
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
      </div>
    </div>

  </section>

  <?php include_once 'footer.php'; ?>
</body>