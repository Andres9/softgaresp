<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
include __DIR__ . '/encabezado.php';
?>
<main class="comercial-page">
  <section class="comercial-hero">
    <div>
      <span class="comercial-eyebrow">Gestión de servicios</span>
      <h1>Notas de servicio</h1>
      <p>Registra notas de servicio y consulta sus reportes.</p>
    </div>
    <span class="comercial-hero-icon"><i class="fa-solid fa-clipboard-list" aria-hidden="true"></i></span>
  </section>

  <?php include __DIR__ . '/app/Views/notas_subnav.php'; ?>

  <div class="comercial-opciones">
    <a class="comercial-opcion" href="notaCliente.php">
      <span class="comercial-opcion-icon"><i class="fa-solid fa-clipboard-list" aria-hidden="true"></i></span>
      <span><strong>Nota</strong><small>Registra y consulta notas de servicio.</small></span>
      <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
    </a>
    <a class="comercial-opcion" href="reportesnotas.php">
      <span class="comercial-opcion-icon"><i class="fa-solid fa-note-sticky" aria-hidden="true"></i></span>
      <span><strong>Reportes de notas</strong><small>Consulta los reportes de servicios registrados.</small></span>
      <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
    </a>
  </div>
</main>
<?php include __DIR__ . '/footer.php'; ?>
