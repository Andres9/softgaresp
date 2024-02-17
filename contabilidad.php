<?php 
include('conexion.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SoftGaresp</title>
    <link rel="icon" href="logotipo.png" />
    <!-- ARCHIVOS PERSONALIZADOS -->
    <script src="jquery.js"></script>
    <script src="index.js"></script>
    <link rel="stylesheet" href="style.css" />
    <!-- BOOTSTRAP -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
      crossorigin="anonymous"
    />
<script src=""></script>

    <!-- DATATABLES -->
    <link
      rel="stylesheet"
      href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.css"
    />
    <link rel="stylesheet" href="responsive.dataTables.min.css" />
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.js"></script>
    <script src="dataTables.responsive.min.js"></script>

    <!-- SELECT2 -->
    <link
      href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"
      rel="stylesheet"
    />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- FUENTES -->
    <link
      rel="stylesheet"
      href="fontawesome-free-6.5.1-web/fontawesome-free-6.5.1-web/css/all.min.css"
    />
  </head>

<body>
    <header>
        <div>
            <img src="logotipo2.png" alt="logotipo">
        </div>
        <nav>
            <ul>
                <!--  <li class="logo"> <img src="logotipo2.png" alt="logotipo"></li> -->
                <li><a href="index.php" > <i class="fa-solid fa-list"></i> Ventas</a></li>
                <li><a href="clientes.php"> <i class="fa-solid fa-list"></i> Clientes</a></li>
                <li><a href="notaCliente.php"> <i class="fa-solid fa-list"></i> Nota</a></li>
                <li><a href="contabilidad.php" class="active"> <i class="fa-solid fa-list"></i> Contabilidad</a></li>
       
            </ul>
        </nav>
    </header>
    <section>

        <section>
       

            <div class="seccion">
          <div>
          <span>
        
        <i class="fa-solid fa-file-circle-check"></i>
        <h5>
          <?php
                  $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=5";  
                      $resultado = mysqli_query($conn,$ventasTotales);
                      while($mostrar = mysqli_fetch_array($resultado)){
                          echo ("$" . $mostrar[0] . "");
                      }
                    
                  ?>
        </h5>
        
                    </span>
      <span>
        <h3>Papeleria</h3>
                    </span>
          </div>
        <div>
          
        </div>
        <div>
       
          <i class="fa-solid fa-mobile-screen-button"></i>
          <h5>
            <?php
                    $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=4";  
                        $resultado = mysqli_query($conn,$ventasTotales);
                        while($mostrar = mysqli_fetch_array($resultado)){
                            echo ("$" . $mostrar[0] . "");
                        }
                      
                    ?>
          </h5>
          <h3>Celular</h3>
        </div>
        <div>
          
          <i class="fa-solid fa-cash-register"></i>
          <h5>
            <?php
                    $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=1";  
                        $resultado = mysqli_query($conn,$ventasTotales);
                        while($mostrar = mysqli_fetch_array($resultado)){
                            echo ("$" . $mostrar[0] . "");
                        }
                      
                    ?>
          </h5>
          <h3>Negocio</h3>
        </div>
        <div>
          
          <i class="fa-solid fa-user"></i>
          <h5>
            <?php
                    $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=2";  
                        $resultado = mysqli_query($conn,$ventasTotales);
                        while($mostrar = mysqli_fetch_array($resultado)){
                            echo ("$" . $mostrar[0] . "");
                        }
                      
                    ?>
          </h5>
          <h3>Patricia</h3>
        </div>
        <div>
       
          <i class="fa-regular fa-user"></i>
          <h5>
            <?php
                    $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion=3";  
                        $resultado = mysqli_query($conn,$ventasTotales);
                        while($mostrar = mysqli_fetch_array($resultado)){
                            echo ("$" . $mostrar[0] . "");
                        }
                      
                    ?>
          </h5>
          <h3>Guadencio</h3>
        </div>
        <div>
      
        <i class="fa-solid fa-laptop"></i>
          <h5>
            <?php
                    $ventasTotales = "SELECT SUM(ganancia) FROM notas";  
                        $resultado = mysqli_query($conn,$ventasTotales);
                        while($mostrar = mysqli_fetch_array($resultado)){
                          echo ("$" . $mostrar[0] . "");
                        }
                      
                    ?>
          </h5>
          <h3>Servicio</h3>
        </div>
        <div>
        
          <i class="fa-solid fa-cash-register"></i>
          <h5>
            <?php
                    $ventasTotales = "SELECT SUM(costo) FROM ventas";  
                        $resultado = mysqli_query($conn,$ventasTotales);
                        while($mostrar = mysqli_fetch_array($resultado)){
                            echo ("$" . $mostrar[0] . "");
                        }
                      
                    ?>
          </h5>
          <h3>Total</h3>
        </div>
      </div>

        </section>
      
      <h2>Notas</h2>
      <div class="seccion">
      <div>
          <h3>Servicio</h3>
          <i class="fa-regular fa-user"></i>
          <h5>
            <?php
                    $ventasTotales = "SELECT SUM(ganancia) FROM notas WHERE YEAR(fecha_alta) = 2024";  
                        $resultado = mysqli_query($conn,$ventasTotales);
                        while($mostrar = mysqli_fetch_array($resultado)){
                          echo ("$" . $mostrar[0] . "");
                        }
                      
                    ?>
          </h5>
        </div>
        </div>
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
                            WHERE YEAR(fecha_alta) = 2024";
                            /* WHERE fecha_alta >= DATE(NOW()) */
                $resultado = mysqli_query($conn,$consulta);
                  while($mostrar = mysqli_fetch_array($resultado)){
                ?>
          <tr>
            <td><?php echo $mostrar['id_nota']?></td>
            <td><?php echo $mostrar['nombre']." ".$mostrar["apellidos"]?></td>
            <td><?php echo $mostrar['fecha_alta']?></td>
            <td><?php echo $mostrar['descripcionEquipo']?></td>
            <td><?php echo $mostrar['marca']?></td>
            <td><?php echo $mostrar['modelo']?></td>
            <td><?php echo $mostrar['tipoServicio']?></td>
            <td><?php echo $mostrar['costoServicio']?></td>
            <td><?php echo $mostrar['descripcionServicio']?></td>
            <td><?php echo $mostrar['notas']?></td>
            <td><?php echo $mostrar['estadoServicio']?></td>
            <td><?php echo $mostrar['anticipo']?></td>
            <td><?php echo $mostrar['ganancia']?></td>
            <td><?php echo $mostrar['restante']?></td>
            <td><?php echo $mostrar['pagado']?></td>
            <td>
              <a href="reportes.php?idNota=<?=$mostrar['id_nota']?>" target="_blank"><i class="fa-solid fa-file-pdf"></i></a>
            </td>
            <td>
              <a href="editarNotas.php?updNotas=<?=$mostrar['id_nota']?>"><i class="fa-solid fa-pen"></i></a>
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
    <footer>
        <div>
            <img src="logotipo-blanco.png" alt="logo">
        </div>
        <p>Todos los derechos reservados - garespservicioinformatico</p>
    </footer>
</body>

</html>
