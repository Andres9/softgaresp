<?php

include "conexion.php";

/* INSERTAR REGISTROS EN LA TABLA */
   $cantidad = $_POST["cantidad"];
   $descripcion = $_POST["descripcion"];
    $costo = $_POST["costo"];
    $opcionNegocio = $_POST["opcionnegocio"];
    $tabla = "ventas";


    $campos = 'cantidad,descripcion,costo,seccion';
    $variables = "$cantidad, '$descripcion',$costo,'$opcionNegocio'";
    $_GUARDAR_SQL = "INSERT INTO $tabla ($campos) VALUES ($variables)";  
    mysqli_query($conn,$_GUARDAR_SQL);

    header("Location: index.php");
?>