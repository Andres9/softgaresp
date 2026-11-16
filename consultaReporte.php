<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Método no permitido.');
}

if (!isset($_POST['tabla']) || !in_array($_POST['tabla'], ['ventas', 'notas'], true)) {
  http_response_code(400);
  exit('Selecciona un reporte válido.');
}

include_once 'conexion.php';

if (!$conn) {
  http_response_code(500);
  exit('No fue posible conectar con la base de datos.');
}

if ($_POST["tabla"] === 'ventas') {
  include_once 'ventasReportes.php';
} else {
  include_once 'notasReportes.php';
}
