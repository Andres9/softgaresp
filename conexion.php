<?php
/* $servidor = 'mysql:host=localhost;dbname=garesp';
$usuario = 'root';
$password = '';

try {
    $conn = new PDO($servidor, $usuario, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   echo "Conexión OK con PDO"; 
} catch (PDOException $e) {
    echo "Conexión fallida - ERROR de conexión: " . $e->getMessage();
} */

	
$conn = mysqli_connect("localhost","root","","garesp");

?>