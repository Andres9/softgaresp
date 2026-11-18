<?php

require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Método no permitido.');
}

if (!isset($_POST['tabla']) || !in_array($_POST['tabla'], ['ventas', 'notas'], true)) {
  http_response_code(400);
  exit('Selecciona un reporte válido.');
}

if (!usuario_es_administrador() && $_POST['tabla'] !== 'notas') {
  http_response_code(403);
  exit('No tienes permiso para consultar este reporte.');
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
