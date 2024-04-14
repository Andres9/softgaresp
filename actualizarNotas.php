<?php

include "conexion.php";

/* EDITAR REGISTROS EN LA TABLA */

   $id = $_REQUEST["id"];
   $cliente = $_REQUEST["cliente"];
   $equipo = $_REQUEST["equipo"];
   $marca = $_REQUEST["marca"];
   $modelo = $_REQUEST["modelo"];
   $servicio = $_REQUEST["servicio"];
   $descripcion = $_REQUEST["descripcionServicio"];
   $estado = $_REQUEST["estado"];
   $costoServicio = $_REQUEST["costoServicio"];
   $ganancia = $_REQUEST["ganancia"];
   $anticipo = $_REQUEST["anticipo"];
   $restante = $_REQUEST["restante"];
   $pagado = $_REQUEST["pagado"];
   $nota = $_REQUEST["nota"];
   $fecha = $_REQUEST["fechaEntrada"];
   
    $tabla = "notas";

    $_GUARDAR_SQL = ("UPDATE $tabla SET 
    id_cliente='".$cliente."',
    equipo='".$equipo."',
    marca='".$marca."',
    modelo='".$modelo."',
    servicio='".$servicio."',
    descripcionServicio='".$descripcion."',
    estado='".$estado."',
    costoServicio='".$costoServicio."',
    ganancia='".$ganancia."',
    anticipo='".$anticipo."',
    restante='".$restante."',
    pagado='".$pagado."',
    notas='".$nota."',
    fecha_alta='".$fecha."'
     WHERE id_nota='".$id."' ");  
    mysqli_query($conn,$_GUARDAR_SQL);

    header("Location: consultas.php");

 
?>