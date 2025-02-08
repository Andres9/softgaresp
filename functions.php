<?php

include "conexion.php";

/* INSERTAR REGISTROS EN LA TABLA */
$cantidad = $_POST["cantidad"];
$descripcion = $_POST["descripcion"];
$costo = $_POST["costo"];
$opcionNegocio = $_POST["opcionnegocio"];
$factura = $_POST["factura"];
$fechaVenta = $_POST["fechaVenta"];
$tabla = "ventas";


$campos = 'cantidad,descripcion,costo,seccion,factura,fecha_venta';
$variables = "$cantidad, '$descripcion',$costo,'$opcionNegocio','$factura','$fechaVenta'";
$_GUARDAR_SQL = "INSERT INTO $tabla ($campos) VALUES ($variables)";
mysqli_query($conn, $_GUARDAR_SQL);

header("Location: ventas.php");
?>