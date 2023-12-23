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
                        $ventasTotales = "SELECT COUNT(id_nota) FROM notas";  
                        $resultado = mysqli_query($conn,$ventasTotales);
                        while($mostrar = mysqli_fetch_array($resultado)){
                            echo ($mostrar[0]);
                        }
                    
                    ?>
                </h5>
            </div>
            <div>
                <h3>Entrada</h3>
                <i class="fa-solid fa-file-circle-check"></i>
                <h5>
                <?php
                    $ventasTotales = "SELECT COUNT(estado) FROM notas WHERE estado='entrada'";  
                        $resultado = mysqli_query($conn,$ventasTotales);
                        while($mostrar = mysqli_fetch_array($resultado)){
                            echo ( $mostrar[0] );
                        }
                      
                    ?>
                </h5>
            </div>
            <div>
                <h3>Revision</h3>
                <i class="fa-solid fa-file-circle-check"></i>
                <h5>
                <?php
                    $ventasTotales = "SELECT COUNT(estado) FROM notas WHERE estado='revision'";  
                        $resultado = mysqli_query($conn,$ventasTotales);
                        while($mostrar = mysqli_fetch_array($resultado)){
                            echo ( $mostrar[0] );
                        }
                      
                    ?>
                </h5>
            </div>
            <div>
                <h3>Entregado</h3>
                <i class="fa-solid fa-file-circle-check"></i>
                <h5>
                <?php
                    $ventasTotales = "SELECT COUNT(estado) FROM notas WHERE estado='entregado'";  
                        $resultado = mysqli_query($conn,$ventasTotales);
                        while($mostrar = mysqli_fetch_array($resultado)){
                            echo ( $mostrar[0] );
                        }
                      
                    ?>
                </h5>
            </div>
            <div>
                <h3>Ssolucion</h3>
                <i class="fa-solid fa-file-circle-check"></i>
                <h5>
                <?php
                    $ventasTotales = "SELECT COUNT(estado) FROM notas WHERE estado='sin reparacion'";  
                        $resultado = mysqli_query($conn,$ventasTotales);
                        while($mostrar = mysqli_fetch_array($resultado)){
                            echo ( $mostrar[0] );
                        }
                      
                    ?>
                </h5>
            </div>

    
            <div>
                <h3>Total</h3>
                <i class="fa-regular fa-user"></i>
                <h5>
                <?php
                    $ventasTotales = "SELECT SUM(costoServicio) FROM notas";  
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
            <form action="registroNotas.php" method="post">
            <fieldset>
            <legend>Fecha</legend>
            <label for="">Fecha de entrada</label>
            <input type="datetime" name="fechaEntrada" value="<?php echo $fecha_actual?>">
           </fieldset>
                <fieldset>
                <legend>
                    Datos del cliente
                </legend>
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
                            ?>
                             <option value="<?php echo $id; ?>"><?php echo $nombre." ".$apellidos; ?></option>
<?php
                        }
                    ?>
        
                   
                </select>
                    </fieldset>

                
                <fieldset>
                    <legend>Datos del equipo</legend>
                    <label for="">Equipo</label>
            <select name="equipo" id="">
                <option value="">Selecciona un equipo</option>
                <option value="laptop">Laptop</option>
                <option value="pc">PC</option>
                <option value="impresora">Impresora</option>
                <option value="celular">celular</option>
                <option value="tablet">tablet</option>
            </select>
            <label for="marca">Marca</label>
            <input type="text" name="marca">
            <label for="modelo">Modelo</label>
            <input type="text" name="modelo">
                </fieldset>


                <fieldset>
                    <legend>Servicio</legend>
                    <label for="">Servicio</label>
          <select name="servicio" id="">
            <option value="">Seleccione una opcion</option>
            <option value="Hardware">Hardware</option>
            <option value="Software">Software</option>
          </select>
                <label for="">Descripcion</label>
                <textarea id="" cols="45" rows="3" name="descripcion"></textarea>
                </fieldset>
              
   <fieldset>
    <legend>Estado del equipo</legend>
    <select name="estado" id="">
        <option value="">Seleccione una opcion</option>
        <option value="entrada">Entrada</option>
        <option value="revision">En revision</option>
        <option value="entregado">Entregado</option>
        <option value="sin reparacion">Sin reparacion</option>
    </select>
   </fieldset>
                <fieldset>
                    <legend>Costo</legend>
                    <label for="">Costo del servicio</label>
                    <input type="number" name="costoServicio" value="0">
                    <label for="">Anticipo</label>
                    <input type="number" name="anticipo" value="0">
                    <label for="">Restante</label>
                    <input type="number" name="restante" value="0">
                    <label for="">Pagado</label>
                    <input type="number" name="pagado" value="0">
                </fieldset>

                <fieldset>
                    <legend>Notas</legend>
                    <label for="">Ingrese las notas adicionales</label>
                 <textarea name="nota" id="" cols="45" rows="5" ></textarea>
                </fieldset>
              
                <button type="submit" class="btn btn-primary">Agregar</button>
            </form>
        </div>
        <h2>Movimientos</h2>
        <table id="myTable" class="display responsive nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Equipo</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Servicio</th>
                    <th>Descripcion</th>
                    <th>Estado</th>
                    <th>Costo</th>
                    <th>Anticipo</th>
                    <th>Restante</th>
                    <th>Pagado</th>
                    <th>Notas</th>
                    <th>Editar</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
          
            <tbody>
            <?php
                $consulta = "select * from notas n JOIN clientes c ON n.id_cliente  = c.id_CLIENTE";
                $resultado = mysqli_query($conn,$consulta);
                  while($mostrar = mysqli_fetch_array($resultado)){
                ?>
                <tr>
                    <td><?php echo $mostrar['id_nota']?></td>
                    <td><?php echo $mostrar['nombre']." ".$mostrar["apellidos"]?></td>
                    <td><?php echo $mostrar['equipo']?></td>
                    <td><?php echo $mostrar['marca']?></td>
                    <td><?php echo $mostrar['modelo']?></td>
                    <td><?php echo $mostrar['servicio']?></td>
                    <td><?php echo $mostrar['descripcionServicio']?></td>
                    <td><?php echo $mostrar['estado']?></td>
                    <td><?php echo $mostrar['costoServicio']?></td>
                    <td><?php echo $mostrar['anticipo']?></td>
                    <td><?php echo $mostrar['restante']?></td>
                    <td><?php echo $mostrar['pagado']?></td>
                    <td><?php echo $mostrar['notas']?></td>

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
