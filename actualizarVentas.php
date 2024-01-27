<?php

include "conexion.php";

/* EDITAR REGISTROS EN LA TABLA */

$id = $_REQUEST["id"];
$cantidad = $_REQUEST["cantidad"];
$descripcion = $_REQUEST["descripcion"];
 $costo = $_REQUEST["costo"];
 $opcionNegocio = $_REQUEST["opcionnegocio"];
 $fechaVenta = $_REQUEST["fechaVenta"];
 $tabla = "ventas";

    $_GUARDAR_SQL = ("UPDATE $tabla SET 
    cantidad='".$cantidad."',
    descripcion='".$descripcion."',
    costo='".$costo."',
    seccion='".$opcionNegocio."',
    fecha_venta='".$fechaVenta."'
     WHERE id_venta='".$id."' ");  
    mysqli_query($conn,$_GUARDAR_SQL);

    header("Location: index.php");

?>