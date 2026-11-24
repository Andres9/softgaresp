<nav class="comercial-subnav" aria-label="Secciones de notas de servicio">
  <a href="servicios_notas.php" class="<?= $pagina_actual === 'servicios_notas.php' ? 'active' : '' ?>" <?= $pagina_actual === 'servicios_notas.php' ? 'aria-current="page"' : '' ?>>
    <i class="fa-solid fa-house" aria-hidden="true"></i> Inicio
  </a>
  <a href="notaCliente.php" class="<?= $pagina_actual === 'notaCliente.php' ? 'active' : '' ?>" <?= $pagina_actual === 'notaCliente.php' ? 'aria-current="page"' : '' ?>>
    <i class="fa-solid fa-clipboard-list" aria-hidden="true"></i> Nota
  </a>
  <a href="reportesnotas.php" class="<?= $pagina_actual === 'reportesnotas.php' ? 'active' : '' ?>" <?= $pagina_actual === 'reportesnotas.php' ? 'aria-current="page"' : '' ?>>
    <i class="fa-solid fa-note-sticky" aria-hidden="true"></i> Reportes de notas
  </a>
</nav>
