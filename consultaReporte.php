<?php

include_once 'conexion.php';

  if ($_POST["tabla"] == 'ventas') {
 /*   include_once 'ventasReportes.php';  */
    echo "<h2>Ventas</h2>";
    
  }
  if ($_POST["tabla"] == 'notas') {
 /* include_once 'notasReportes.php';  */
    echo "<h2>Notas</h2>";
  
  }
  

  ?>
