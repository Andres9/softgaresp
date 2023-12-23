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
                <!--  <li class="logo"> <img src="logotipo2.png" alt="logotipo"></li> -->
                <li><a href="index.php" > <i class="fa-solid fa-list"></i> Ventas</a></li>
                <li><a href="clientes.php"> <i class="fa-solid fa-list"></i> Clientes</a></li>
                <li><a href="notaCliente.php"> <i class="fa-solid fa-list"></i> Nota</a></li>
                <li><a href="contabilidad.php" class="active"> <i class="fa-solid fa-list"></i> Contabilidad</a></li>
       
            </ul>
        </nav>
    </header>
    <section>
        <div class="seccion">
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
        <div id="registro">
            <h2>Patricia</h2>
            <form action="functions.php" method="post">
                <label for="">Cantidad Actual</label>
            
                <h3> <?php
                    $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion='patricia'";  
                        $resultado = mysqli_query($conn,$ventasTotales);
                        while($mostrar = mysqli_fetch_array($resultado)){
                            echo ("$" . $mostrar[0] . "");
                        }
                        ?></h3>
                <label for="">Cantidad</label>
                <input type="number" name="costo">
                <label for="">Seccion</label>
                <select name="opcionnegocio" id="">
                    <option value="">Selecciona una opcion</option>
                    <option value="negocio">Reinvertido</option>
                    <option value="patricia">Entregado</option>
                </select>
                <button type="submit" class="btn btn-primary">Agregar</button>
            </form>
        </div>
        <div id="registro">
            <h2>Papeleria</h2>
            <form action="functions.php" method="post">
                <label for="">Cantidad Actual</label>
            
                <h3> <?php
                    $ventasTotales = "SELECT SUM(costo) FROM ventas WHERE seccion='papeleria'";  
                        $resultado = mysqli_query($conn,$ventasTotales);
                        while($mostrar = mysqli_fetch_array($resultado)){
                            echo ("$" . $mostrar[0] . "");
                        }
                        ?></h3>
                <label for="">Cantidad</label>
                <input type="number" name="costo">
                <label for="">Seccion</label>
                <select name="opcionnegocio" id="">
                    <option value="">Selecciona una opcion</option>
                    <option value="negocio">Reinvertido</option>
                    <option value="patricia">Entregado</option>
                </select>
                <button type="submit" class="btn btn-primary">Agregar</button>
            </form>
        </div>
    </section>
    <footer>
        <div>
            <img src="logotipo-blanco.png" alt="logo">
        </div>
        <p>Todos los derechos reservados - garespservicioinformatico</p>
    </footer>
</body>

</html>
