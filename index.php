<?php 
include('conexion.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SoftGaresp</title>

    <script src="jquery.js"></script>
    <script src="index.js"></script>
    <link rel="stylesheet" href="style.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.css" />
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.js"></script>
    
    <script src="dataTables.responsive.min.js"></script>

    <link rel="stylesheet" href="fontawesome-free-6.5.1-web/fontawesome-free-6.5.1-web/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

</head>

<body>
    <header>
        <div>
            <img src="logotipo2.png" alt="logotipo">
        </div>
        <nav>
            <ul>
                <!--  <li class="logo"> <img src="logotipo2.png" alt="logotipo"></li> -->
                <li><a href="index.php" class="active"> <i class="fa-solid fa-list"></i> Ventas</a></li>
                <li><a href="clientes.php"> <i class="fa-solid fa-list"></i> Clientes</a></li>
                <li><a href="patricia.html">Patricia</a></li>
                <li><a href="desarrollo.html">Desarrollos</a></li>
                <li><a href="acerca.html">Acerca de</a></li>
            </ul>
        </nav>
    </header>
    <section>
        <div class="seccion">
            <div>
                <h3>Ventas</h3>
              
                <h5 id="total">
                    <?php
                        $ventasTotales = "SELECT COUNT(id_venta) FROM ventas";  
                        $resultado = mysqli_query($conn,$ventasTotales);
                        while($mostrar = mysqli_fetch_array($resultado)){
                            echo ($mostrar[0]);
                        }
                      
                    ?>
                </h5>
            </div>
            <div>
                <h3>Papeleria</h3>
                <i class="fa-solid fa-cash-register"></i>
                <h5>
                <?php
                    $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion='papeleria'";  
                        $resultado = mysqli_query($conn,$ventasTotales);
                        while($mostrar = mysqli_fetch_array($resultado)){
                            echo ("$" . $mostrar[0] . "");
                        }
                      
                    ?>
                </h5>
            </div>
            <div>
                <h3>Celular</h3>
                <i class="fa-solid fa-cash-register"></i>
                <h5>
                <?php
                    $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion='celular'";  
                        $resultado = mysqli_query($conn,$ventasTotales);
                        while($mostrar = mysqli_fetch_array($resultado)){
                            echo ("$" . $mostrar[0] . "");
                        }
                      
                    ?>
                </h5>
            </div>
            <div>
                <h3>Negocio</h3>
                <i class="fa-solid fa-cash-register"></i>
                <h5>
                <?php
                    $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion='negocio'";  
                        $resultado = mysqli_query($conn,$ventasTotales);
                        while($mostrar = mysqli_fetch_array($resultado)){
                            echo ("$" . $mostrar[0] . "");
                        }
                      
                    ?>
                </h5>
            </div>
            <div>
                <h3>Patricia</h3>
                <i class="fa-solid fa-cash-register"></i>
                <h5>
                <?php
                    $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion='patricia'";  
                        $resultado = mysqli_query($conn,$ventasTotales);
                        while($mostrar = mysqli_fetch_array($resultado)){
                            echo ("$" . $mostrar[0] . "");
                        }
                      
                    ?>
                </h5>
            </div>
            <div>
                <h3>Guadencio</h3>
                <i class="fa-solid fa-cash-register"></i>
                <h5>
                <?php
                    $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion='guadencio'";  
                        $resultado = mysqli_query($conn,$ventasTotales);
                        while($mostrar = mysqli_fetch_array($resultado)){
                            echo ("$" . $mostrar[0] . "");
                        }
                      
                    ?>
                </h5>
            </div>
        </div>
        <h2>Registro</h2>
        <div id="registro">
            <form action="functions.php" method="post">
                <label for="">Cantidad</label>
                <input type="number" value="1" name="cantidad">
                <label for="">Descripcion</label>
                <input type="text" name="descripcion">
                <label for="">Costo</label>
                <input type="number" name="costo">
                <label for="">Seccion</label>
                <select name="opcionnegocio" id="">
                    <option value="">Selecciona una opcion</option>
                    <option value="negocio">Negocio</option>
                    <option value="patricia">Patricia</option>
                    <option value="gaudencio">Guadencio</option>
                    <option value="celular">Celular</option>
                    <option value="papeleria">Papeleria</option>
                </select>
                <button type="submit" class="btn btn-primary">Agregar</button>
            </form>
        </div>
        <h2>Movimientos</h2>
        <table id="myTable" class="display nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cantidad</th>
                    <th>Descripcion</th>
                    <th>Precio</th>
                    <th>Categoria</th>
                    <th>Fecha</th>
                </tr>
            </thead>
          
            <tbody>
            <?php
                $consulta = "SELECT * FROM ventas ORDER BY id_venta DESC";
                $resultado = mysqli_query($conn,$consulta);
                  while($mostrar = mysqli_fetch_array($resultado)){
                ?>
                <tr>
                    <td><?php echo $mostrar['id_venta']?></td>
                    <td><?php echo $mostrar['cantidad']?></td>
                    <td><?php echo $mostrar['descripcion']?></td>
                    <td><?php echo $mostrar['costo']?></td>
                    <td><?php echo $mostrar['seccion']?></td>
                    <td><?php echo $mostrar['fecha']?></td>
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
