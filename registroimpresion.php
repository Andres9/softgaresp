<?php

include "conexion.php";

/* INSERTAR REGISTROS EN LA TABLA */
$cantidad = $_POST["cantidad"];
$descripcion = $_POST["descripcion"];
$costo = $_POST["costo"];
$fechaVenta = $_POST["fechaVenta"];
$tabla = "impresion";

$campos = 'cant,concepto,precio,fecha_impresion';
$variables = "$cantidad, '$descripcion',$costo,'$fechaVenta'";
$_GUARDAR_SQL = "INSERT INTO $tabla ($campos) VALUES ($variables)";
mysqli_query($conn, $_GUARDAR_SQL);

header("Location: impresion.php");
?>