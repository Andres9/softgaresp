<?php

include "conexion.php";

/* INSERTAR REGISTROS EN LA TABLA */
   $nombre = $_POST["nombre"];
   $apellidos = $_POST["apellidos"];
   $profesionOcupacion = $_POST["profesionocupacion"];
    $calle = $_POST["calle"];
    $domicilio = $_POST["domicilio"];
    $telefono = $_POST["telefono"];
    $fechaRegistro = $_POST["fechaRegistro"];
    $tabla = "clientes";

    $campos = 'nombre,apellidos,profesion,calle,domicilio,telefono,fecha_registro';
    $variables = "'$nombre','$apellidos','$profesionOcupacion','$calle','$domicilio','$telefono','$fechaRegistro'";

    var_dump($variables);

    $_GUARDAR_SQL = "INSERT INTO $tabla ($campos) VALUES ($variables)";  
    mysqli_query($conn,$_GUARDAR_SQL);

    header("Location: clientes.php");
?>