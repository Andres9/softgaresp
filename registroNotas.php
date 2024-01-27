<?php

include "conexion.php";

/* INSERTAR REGISTROS EN LA TABLA */

   $cliente = $_POST["cliente"];
   $equipo = $_POST["equipo"];
   $marca = $_POST["marca"];
   $modelo = $_POST["modelo"];
   $servicio = $_POST["servicio"];
   $descripcion = $_POST["descripcion"];
   $estado = $_POST["estado"];
   $costoServicio = $_POST["costoServicio"];
   $ganancia = $_POST["ganancia"];
   $anticipo = $_POST["anticipo"];
   $restante = $_POST["restante"];
   $pagado = $_POST["pagado"];
   $nota = $_POST["nota"];
   $fecha = $_POST["fechaEntrada"];

   $condicion = true;
   $sqlselect = "SELECT numServicio FROM clientes WHERE id_cliente = $cliente";
   $numservicio=mysqli_query($conn,$sqlselect);

   while($mostrar = mysqli_fetch_array($numservicio)){
    while ($condicion) {
        $contador = $mostrar[0];
        $contador++;
        $numSerUpdate = "UPDATE clientes SET numServicio=$contador WHERE id_cliente = $cliente";
        mysqli_query($conn,$numSerUpdate);
        $condicion = false;
       }
   }


    $tabla = "notas";

    $campos = 'id_cliente,equipo,marca,modelo,servicio,descripcionServicio,estado,costoServicio,ganancia,anticipo,restante,pagado,notas,fecha_alta';
    $variables = "$cliente,$equipo,'$marca','$modelo',$servicio,'$descripcion',$estado,$costoServicio,$ganancia,$anticipo,$restante,
    $pagado,'$nota','$fecha'";

    $_GUARDAR_SQL = "INSERT INTO $tabla ($campos) VALUES ($variables)";  
    mysqli_query($conn,$_GUARDAR_SQL);

    header("Location: notaCliente.php");

 
?>