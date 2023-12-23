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
   $anticipo = $_POST["anticipo"];
   $restante = $_POST["restante"];
   $pagado = $_POST["pagado"];
   $nota = $_POST["nota"];
   $fecha = $_POST["fechaEntrada"];
   var_dump($fecha);

    $tabla = "notas";

    $campos = 'id_cliente,equipo,marca,modelo,servicio,descripcionServicio,estado,costoServicio,anticipo,restante,pagado,notas,fecha_alta';
    $variables = "$cliente,'$equipo','$marca','$modelo','$servicio','$descripcion','$estado',$costoServicio,$anticipo,$restante,
    $pagado,'$nota','$fecha'";

    $_GUARDAR_SQL = "INSERT INTO $tabla ($campos) VALUES ($variables)";  
    mysqli_query($conn,$_GUARDAR_SQL);

    header("Location: notaCliente.php");

 
?>