<?php

include "conexion.php";
session_start();


/* DATOS FORMULARIO */
$usuario = $_POST["usuario"];
$password = $_POST["password"];


$user = "SELECT * FROM users WHERE nombre='$usuario' AND pass = '$password'";

$consulta = mysqli_query($conn, $user);

if (!$consulta) {
    echo "Usuario no existe";
} else {
    /*    header("Location: ventas.php"); */
    if ($valido = mysqli_fetch_assoc($consulta)) {
        header("Location: ventas.php");
        
    } else {
        header("Location: index.php");

    }
}


?>