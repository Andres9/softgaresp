<?php include_once 'encabezado.php'; ?>

  <section>
    <div class="seccion">
      <div>
        <span>
          <h5 class="numerototal">
            <?php
            $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE fecha_venta >= DATE(NOW()) ";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              $vt = number_format($mostrar[0], 2, '.', ',');
              echo ("$" . $vt . PHP_EOL . "");
            }
            ?>
          </h5>
        </span>
        <span>
          <h3>Venta</h3>
        </span>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6">
        <h2>Registro Ventas</h2>
        <div id="registro" class="container">
          <form action="functions.php" method="post">
            <div class="row">
              <div class="col-md-12" style="display: none">
                <div class="row">
                  <label for="">Fecha de venta</label>
                  <input type="datetime" name="fechaVenta" value="<?php echo $fecha_actual ?>"
                    class="form-control datepicker" />
                </div>
              </div>
              <div class="col-md-2 col-xs-12">
                <label for="">Cant.</label>
                <input type="number" value="1" name="cantidad" class="form-control" />
              </div>
              <div class="col-md-10 col-xs-12">
                <label for="">Descripción</label>
                <input type="text" name="descripcion" class="form-control" />
              </div>
              <div class="col-md-4 col-xs-12">
                <label for="">Costo</label>
                <input type="number" name="costo" class="form-control" step="0.01" />
              </div>
              <div class="col-md-4 col-xs-12">
                <label for="">Precio compra</label>
                <input type="number" name="pc" class="form-control" step="0.01" />
              </div>
              <div class="col-md-4 col-xs-12">
                <label for="">Ganancia</label>
                <input type="number" name="g" class="form-control" step="0.01" />
              </div>
              <div class="col-md-8 col-xs-12">
                <label for="">Sección</label>
                <select name="opcionnegocio" class="select">
                  <option value="">Selecciona una opcion</option>
                  <?php
                  $ventasTotales = "SELECT * FROM seccion ORDER BY descripcionSeccion";
                  $resultado = mysqli_query($conn, $ventasTotales);
                  while ($mostrar = mysqli_fetch_array($resultado)) {
                    $id = $mostrar["id_seccion"];
                    $descripcion = $mostrar["descripcionSeccion"];
                    ?>
                    <option value="<?php echo $id; ?>">
                      <?php echo $descripcion ?>
                    </option>
                    <?php
                  }
                  ?>
                </select>
              </div>
              <div class="col-md-3 col-xs-12">
                <label for="">Facturar</label>
                <select name="factura" class="select">
                  <option value="no">No</option>
                  <option value="factura">Si</option>
                </select>
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
              <th>Categoria</th>
              <th>Fecha</th>
              <th>Editar</th>
              <th>Eliminar</th>
            </tr>
          </thead>

          <tbody>
            <?php
            $consulta = "SELECT * FROM ventas 
                WHERE fecha_venta >= DATE(NOW()) 
                ";
            $resultado = mysqli_query($conn, $consulta);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              ?>
              <tr>
                <td><?php echo $mostrar['id_venta'] ?></td>
                <td><?php echo $mostrar['cantidad'] ?></td>
                <td><?php echo $mostrar['descripcion'] ?></td>
                <td><?php echo $mostrar['costo'] ?></td>
                <td><?php echo $mostrar['seccion'] ?></td>
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