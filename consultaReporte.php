<?php

include_once 'conexion.php';

if ($_POST["tabla"] == "ventas") {
  include 'ventasReportes.php';
}

if ($_POST["tabla"] == "notas") {
  include 'notasReportes.php';

}

