<?php

include_once 'conexion.php';

if ($_POST["tabla"] == 'ventas') {
  include_once 'ventasReportes.php';
} else if ($_POST["tabla"] == 'notas') {
  include_once 'notasReportes.php';
}

