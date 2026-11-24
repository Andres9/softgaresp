<nav class="comercial-subnav" aria-label="Secciones comerciales">
  <a href="comercial.php" class="<?= $pagina_actual === 'comercial.php' ? 'active' : '' ?>" <?= $pagina_actual === 'comercial.php' ? 'aria-current="page"' : '' ?>>
    <i class="fa-solid fa-house" aria-hidden="true"></i> Inicio
  </a>
  <?php if (usuario_es_administrador()) { ?>
  <a href="pedido.php" class="<?= in_array($pagina_actual, array('pedido.php', 'catalogoPixal.php'), true) ? 'active' : '' ?>" <?= $pagina_actual === 'pedido.php' ? 'aria-current="page"' : '' ?>>
    <i class="fa-solid fa-box-open" aria-hidden="true"></i> Pedidos
  </a>
  <?php } ?>
  <a href="cotizacion.php" class="<?= $pagina_actual === 'cotizacion.php' ? 'active' : '' ?>" <?= $pagina_actual === 'cotizacion.php' ? 'aria-current="page"' : '' ?>>
    <i class="fa-solid fa-file-invoice-dollar" aria-hidden="true"></i> Cotizaciones
  </a>
</nav>
