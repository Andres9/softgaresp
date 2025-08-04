<?php

include "conexion.php";

/* INSERTAR REGISTROS EN LA TABLA */
$cantidad = $_POST["cantidad"];
$descripcion = $_POST["descripcion"];
$costo = $_POST["costo"];
$pc = $_POST["pc"];
$ganancia = $_POST["g"];
$opcionNegocio = $_POST["opcionnegocio"];
$factura = $_POST["factura"];
$fechaVenta = $_POST["fechaVenta"];
$tabla = "ventas";

$campos = 'cantidad,descripcion,costo,preciocosto,ganancia,seccion,factura,fecha_venta';
$variables = "$cantidad, '$descripcion',$costo,$pc,$ganancia,'$opcionNegocio','$factura','$fechaVenta'";
$_GUARDAR_SQL = "INSERT INTO $tabla ($campos) VALUES ($variables)";
mysqli_query($conn, $_GUARDAR_SQL);

header("Location: ventas.php");
?>