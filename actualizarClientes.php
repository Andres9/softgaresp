<?php

include "conexion.php";

/* EDITAR REGISTROS EN LA TABLA */

$id = $_REQUEST["id"];
$nombre = $_REQUEST["nombre"];
$apellidos = $_REQUEST["apellidos"];
$profesionOcupacion = $_REQUEST["profesionocupacion"];
 $calle = $_REQUEST["calle"];
 $domicilio = $_REQUEST["domicilio"];
 $telefono = $_REQUEST["telefono"];
 $fechaRegistro = $_REQUEST["fechaRegistro"];
   
    $tabla = "clientes";

    $_GUARDAR_SQL = ("UPDATE $tabla SET 
    nombre='".$nombre."',
    apellidos='".$apellidos."',
    profesion='".$profesionOcupacion."',
    calle='".$calle."',
    domicilio='".$domicilio."',
    telefono='".$telefono."',
    fecha_registro='".$fechaRegistro."'
     WHERE id_cliente='".$id."' ");  
    mysqli_query($conn,$_GUARDAR_SQL);

    header("Location: clientes.php");

?>