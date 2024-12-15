<?php include_once 'encabezado.php'?>

  <section>
    <div class="seccion">
      <div>
        <span>

          <h5 class="numerototal">
            <?php
            $ventasTotales = "SELECT COUNT(id_cliente) FROM clientes";
            $resultado = mysqli_query($conn, $ventasTotales);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              echo ($mostrar[0]);
            }

            ?>
          </h5>
        </span>
        <span>

          <h3>Clientes</h3>
        </span>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <h2>Registro Cliente</h2>
        <div id="registro" class="container">
          <form action="registroClientes.php" method="post">
            <div class="row" style="display:none">
              <label for="">Fecha Registro</label>
              <input type="datetime" name="fechaRegistro" value="<?php echo $fecha_actual ?>" class="form-control">
            </div>
            <div class="row">
              <div class="col-md-6 col-xs-12">
                <label for="">Nombre</label>
                <input type="text" name="nombre" class="form-control">
              </div>
              <div class="col-md-6 col-xs-12">
                <label for="">Apellidos</label>
                <input type="text" name="apellidos" class="form-control">
              </div>
            </div>

            <label for="">Profesion</label>
            <select name="profesionocupacion" class="select">
              <option value="">Selecciona una profesion o ocupacion</option>
              <?php
              $ventasTotales = "SELECT * FROM profesion";
              $resultado = mysqli_query($conn, $ventasTotales);
              while ($mostrar = mysqli_fetch_array($resultado)) {
                $id = $mostrar["id_profesion"];
                $descripcion = $mostrar["descripcionProfesion"];
                ?>
                <option value="<?php echo $id; ?>">
                  <?php echo $descripcion ?>
                </option>
                <?php
              }
              ?>
            </select>
            <label for="">Calle</label>
            <input type="text" name="calle" class="form-control">

            <label for="">Colonia - Localidad</label>
            <input type="text" name="domicilio" class="form-control">
            <label for="">Telefono</label>
            <input type="text" name="telefono" class="form-control">

            <button type="submit" class="btn btn-primary">Agregar</button>
          </form>
        </div>
      </div>
      <div class="col-md-6">
        <h2>Movimientos</h2>
        <table id="tablaclientes" class="display responsive nowrap" style="width: 100%">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Apellidos</th>
              <th>Servicios</th>
              <th>Descripcion</th>
              <th>Calle</th>
              <th>Domicilio</th>
              <th>Telefono</th>
              <th>Editar</th>
              <th>Eliminar</th>
            </tr>
          </thead>

          <tbody>
            <?php
            $consulta = "SELECT * FROM clientes c 
                JOIN profesion p ON c.profesion  = p.id_profesion ";
            $resultado = mysqli_query($conn, $consulta);
            while ($mostrar = mysqli_fetch_array($resultado)) {
              ?>
              <tr>
                <td><?php echo $mostrar['id_cliente'] ?></td>
                <td><?php echo $mostrar['nombre'] ?></td>
                <td><?php echo $mostrar['apellidos'] ?></td>
                <td><?php echo $mostrar['numServicio'] ?></td>
                <td><?php echo $mostrar['descripcionProfesion'] ?></td>
                <td><?php echo $mostrar['calle'] ?></td>
                <td><?php echo $mostrar['domicilio'] ?></td>
                <td><?php echo $mostrar['telefono'] ?></td>

                <td>
                  <a href="editarClientes.php?updCliente=<?= $mostrar['id_cliente'] ?>"><i
                      class="fa-solid fa-pen"></i></a>
                </td>
                <td><a href="#"><i class="fa-solid fa-trash"></i></a></td>
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