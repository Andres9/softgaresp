<select name="tablaBusqueda" id="tabla" class="select">
        <option value="">Selecciona una opcion</option>
        <?php
                $idMR = "SELECT * FROM menureporte";
                $resultado = mysqli_query($conn, $idMR);
                while ($mostrarid = mysqli_fetch_array($resultado)) {

                  $id = $mostrarid["id_menrep"];
                  $nombre = $mostrarid["nombre"];
                  ?>
                  <option value="<?php echo $nombre; ?>">
                    <?php echo $id ." ". $nombre?>
                  </option>
                  <?php
                }
                ?>
      </select>
