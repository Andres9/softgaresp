<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
include __DIR__ . '/conexion.php';

date_default_timezone_set('America/Mexico_City');
$fecha_actual = date("Y-m-d H:i:s");
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SoftGaresp</title>
  <link rel="icon" href="img/logotipo.png" />
  <!-- ARCHIVOS PERSONALIZADOS -->
  <link rel="stylesheet" href="css/style.css" />
  <!-- BOOTSTRAP -->
  <link href="css/bootstrap.min.css" rel="stylesheet" />

  <!-- DATATABLES -->
  <link rel="stylesheet" href="css/datatables.min.css" />
  <link rel="stylesheet" href="css/responsive.dataTables.min.css" />


  <!-- SELECT2 -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />


  <!-- FUENTES -->
  <link rel="stylesheet" href="fontawesome-free-6.5.1-web/fontawesome-free-6.5.1-web/css/all.min.css" />
</head>

<body>
  <header class="site-header">
    <div class="header-top">
      <a class="brand-link" href="<?= pagina_inicio_usuario() ?>">
        <img src="img/logos.jpg" alt="logotipo" id="logo"/>
      </a>
      <div class="user-info">
        <span class="user-avatar"><i class="fa-solid fa-user"></i></span>
        <span><?= htmlspecialchars($_SESSION['usuario'], ENT_QUOTES, 'UTF-8') ?></span>
        <form action="logout.php" method="post">
          <input type="hidden" name="token_csrf" value="<?= htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8') ?>">
          <button type="submit" class="btn btn-outline-primary btn-sm">Cerrar sesión</button>
        </form>
      </div>
    </div>
    <nav class="main-nav" aria-label="Navegación principal">
      <ul class="nav-list">
        <?php if (usuario_es_administrador()) { ?>
        <li>
          <a href="contabilidad.php" class="<?= $pagina_actual === 'contabilidad.php' ? 'active' : '' ?>" aria-current="<?= $pagina_actual === 'contabilidad.php' ? 'page' : 'false' ?>">
            <i class="fa-solid fa-calculator" aria-hidden="true"></i><span>Contabilidad</span></a>
        </li>
        <li>
          <a href="impresion.php" class="<?= $pagina_actual === 'impresion.php' ? 'active' : '' ?>" aria-current="<?= $pagina_actual === 'impresion.php' ? 'page' : 'false' ?>">
            <i class="fa-solid fa-print" aria-hidden="true"></i><span>Impresión</span></a>
        </li>
        <li>
          <a href="ventas.php" class="<?= $pagina_actual === 'ventas.php' ? 'active' : '' ?>" aria-current="<?= $pagina_actual === 'ventas.php' ? 'page' : 'false' ?>">
            <i class="fa-solid fa-cash-register" aria-hidden="true"></i><span>Ventas</span></a>
        </li>
        <?php } ?>
        <li>
          <a href="clientes.php" class="<?= $pagina_actual === 'clientes.php' ? 'active' : '' ?>" aria-current="<?= $pagina_actual === 'clientes.php' ? 'page' : 'false' ?>">
            <i class="fa-solid fa-users" aria-hidden="true"></i><span>Clientes</span></a>
        </li>
        <li>
          <a href="notaCliente.php" class="<?= $pagina_actual === 'notaCliente.php' ? 'active' : '' ?>" aria-current="<?= $pagina_actual === 'notaCliente.php' ? 'page' : 'false' ?>">
            <i class="fa-solid fa-clipboard-list" aria-hidden="true"></i><span>Nota</span></a>
        </li>
        <?php if (usuario_es_administrador()) { ?>
        <li>
          <a href="consultas.php" class="<?= $pagina_actual === 'consultas.php' ? 'active' : '' ?>" aria-current="<?= $pagina_actual === 'consultas.php' ? 'page' : 'false' ?>">
            <i class="fa-solid fa-chart-pie" aria-hidden="true"></i><span>Reportes</span></a>
        </li>
        <?php } ?>
        <li>
          <a href="reportesnotas.php" class="<?= $pagina_actual === 'reportesnotas.php' ? 'active' : '' ?>" aria-current="<?= $pagina_actual === 'reportesnotas.php' ? 'page' : 'false' ?>">
            <i class="fa-solid fa-note-sticky" aria-hidden="true"></i><span>R. notas</span></a>
        </li>
        <?php if (usuario_es_administrador()) { ?>
        <li>
          <a href="reportesventas.php" class="<?= $pagina_actual === 'reportesventas.php' ? 'active' : '' ?>" aria-current="<?= $pagina_actual === 'reportesventas.php' ? 'page' : 'false' ?>">
            <i class="fa-solid fa-chart-line" aria-hidden="true"></i><span>R. ventas</span></a>
        </li>
        <li>
          <a href="configuraciones.php" class="<?= $pagina_actual === 'configuraciones.php' ? 'active' : '' ?>" aria-current="<?= $pagina_actual === 'configuraciones.php' ? 'page' : 'false' ?>">
            <i class="fa-solid fa-gear" aria-hidden="true"></i><span>Configuración</span></a>
        </li>
        <?php } ?>
      </ul>
    </nav>
  </header>
