<?php 
include('conexion.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SoftGaresp</title>
    <link rel="icon" href="logotipo.png">

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
                <li><a href="index.php"> <i class="fa-solid fa-list"></i> Ventas</a></li>
                <li><a href="clientes.php" class="active"> <i class="fa-solid fa-list"></i> Clientes</a></li>
                <li><a href="notaCliente.php"> <i class="fa-solid fa-list"></i> Nota</a></li>
                <li><a href="contabilidad.php"> <i class="fa-solid fa-list"></i> Contabilidad</a></li>
    
            </ul>
        </nav>
    </header>
    <section>
        <div class="seccion">
            <div>
                <h3>Clientes</h3>
                <h5 id="total">
                    <?php
                        $ventasTotales = "SELECT COUNT(id_cliente) FROM clientes";  
                        $resultado = mysqli_query($conn,$ventasTotales);
                        while($mostrar = mysqli_fetch_array($resultado)){
                            echo ($mostrar[0]);
                        }
                      
                    ?>
                </h5>
            </div>
            <div>
                <h3>Papeleria</h3>
                <i class="fa-solid fa-file-circle-check"></i>
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
                <i class="fa-solid fa-mobile-screen-button"></i>
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
                <i class="fa-solid fa-user"></i>
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
                <i class="fa-regular fa-user"></i>
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
            <div>
                <h3>Total</h3>
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
            </div>
        </div>
        <h2>Registro Cliente</h2>
        <div id="registro">
            <form action="registroClientes.php" method="post">
                <label for="">Nombre</label>
                <input type="text" name="nombre">
                <label for="">Apellidos</label>
                <input type="text" name="apellidos">
         
                <label for="">Profesion</label>
                <select name="profesionocupacion" id="">
                    <option value="">Selecciona una profesion o ocupacion</option>
                    <option value="profesor(a)">Profesor(a)</option>
                    <option value="ingcivil">Ing. civil</option>
                    <option value="ingsistemas">Ing. sistemas</option>
                    <option value="estudiante">Estudiante</option>
                    <option value="arquitecto">Arquitecto</option>
                    <option value="cliente">Cliente</option>
                </select>
                <label for="">Calle</label>
                <input type="text" name="calle">
                
                <label for="">Colonia - Localidad</label>
                <input type="text" name="domicilio">
                <label for="">Telefono</label>
                <input type="text" name="telefono">
              
                <button type="submit" class="btn btn-primary">Agregar</button>
            </form>
        </div>
        <h2>Movimientos</h2>
        <table id="myTable" class="display nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellidos</th>
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
                $consulta = "SELECT * FROM clientes";
                $resultado = mysqli_query($conn,$consulta);
                  while($mostrar = mysqli_fetch_array($resultado)){
                ?>
                <tr>
                    <td><?php echo $mostrar['id_cliente']?></td>
                    <td><?php echo $mostrar['nombre']?></td>
                    <td><?php echo $mostrar['apellidos']?></td>
                    <td><?php echo $mostrar['profesion']?></td>
                    <td><?php echo $mostrar['calle']?></td>
                    <td><?php echo $mostrar['domicilio']?></td>
                    <td><?php echo $mostrar['telefono']?></td>
                    <td><a href="#"><i class="fa-solid fa-pen "></i></a></td>
                    <td><a href="#"><i class="fa-solid fa-trash"></i></a></td>
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
