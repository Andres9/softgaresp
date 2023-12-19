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
                <li><a href="clientes.php"> <i class="fa-solid fa-list"></i> Clientes</a></li>
                <li><a href="notaCliente.php" class="active"> <i class="fa-solid fa-list"></i> Nota</a></li>
                <li><a href="contabilidad.php"> <i class="fa-solid fa-list"></i> Contabilidad</a></li>
              
            </ul>
        </nav>
    </header>
    <section>
        <div class="seccion">
            <div>
                <h3>Nota</h3>
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
                <h3>Entregado</h3>
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
                <h3>Pendiente</h3>
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
                <h3>Cobrado</h3>
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
        <h2>Registro Notas</h2>
        <div id="registro">
            <form action="registroClientes.php" method="post">
                <label for="">Nombre cliente</label>
                <select name="cliente" id="">
                    <option value="">Selecciona a un cliente</option>
                    <?php
                    $ventasTotales = "SELECT * FROM clientes ORDER BY nombre ASC";  
                        $resultado = mysqli_query($conn,$ventasTotales);
                        while($mostrar = mysqli_fetch_array($resultado)){
                            $id = $mostrar["id_cliente"];
                            $nombre = $mostrar["nombre"];
                            $apellidos = $mostrar["apellidos"];

                            echo "<option='$id'>$nombre $apellidos</option>";
                        }
                    ?>
        
                   
                </select>

                <label for="">Equipo</label>
            <select name="equipo" id="">
                <option value="laptop">Laptop</option>
                <option value="pc">PC</option>
                <option value="impresora">Impresora</option>
                <option value="celular">celular</option>
                <option value="tablet">tablet</option>
            </select>
                <label for="">Servicio</label>
          <select name="servicio" id="">
            <option value="">Seleccione una opcion</option>
            <option value="formateo">Formateo</option>
            <option value="optimizacion">Optimizacion</option>
            <option value="drenadoTinta">Drenado de tinta</option>
          </select>
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
                    <th>Editar</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
          
            <tbody>
            <?php
                $consulta = "SELECT * FROM nota";
                $resultado = mysqli_query($conn,$consulta);
                  while($mostrar = mysqli_fetch_array($resultado)){
                ?>
                <tr>
                    <td><?php echo $mostrar['id_cliente']?></td>

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
