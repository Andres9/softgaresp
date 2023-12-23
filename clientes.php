<?php 
include('conexion.php');

date_default_timezone_set('America/Mexico_City');
$fecha_actual=date("Y-m-d H:i:s");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SoftGaresp</title>
    <link rel="icon" href="logotipo.png">

    <!-- ARCHIVOS PERSONALIZADOS -->
    <script src="jquery.js"></script>
    <script src="index.js"></script>
    <link rel="stylesheet" href="style.css">
    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <!-- DATATABLES -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.css" />
    <link rel="stylesheet" href="responsive.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.js"></script>
    <script src="dataTables.responsive.min.js"></script> 

    <!-- FUENTES -->
    <link rel="stylesheet" href="fontawesome-free-6.5.1-web/fontawesome-free-6.5.1-web/css/all.min.css">
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
                <h3>Total Clientes</h3>
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
        </div>
        
        <h2>Registro Cliente</h2>
        <div id="registro">
            <form action="registroClientes.php" method="post">
                <label for="">Fecha Registro</label>
                <input type="datetime" name="fechaRegistro" value="<?php echo $fecha_actual?>">
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
        <table id="myTable" class="display responsive nowrap" style="width:100%">
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
