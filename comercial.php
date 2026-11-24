<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
include __DIR__ . '/encabezado.php';
?>
<main class="comercial-page">
  <section class="comercial-hero">
    <div>
      <span class="comercial-eyebrow">Gestión comercial</span>
      <h1>Comercial</h1>
      <p><?= usuario_es_administrador() ? 'Accede a las herramientas para administrar tus pedidos y cotizaciones.' : 'Accede a las herramientas para administrar tus cotizaciones.' ?></p>
    </div>
    <span class="comercial-hero-icon"><i class="fa-solid fa-briefcase" aria-hidden="true"></i></span>
  </section>

  <?php include __DIR__ . '/app/Views/comercial_subnav.php'; ?>

  <div class="comercial-opciones">
    <?php if (usuario_es_administrador()) { ?>
    <a class="comercial-opcion" href="pedido.php">
      <span class="comercial-opcion-icon"><i class="fa-solid fa-box-open" aria-hidden="true"></i></span>
      <span><strong>Pedidos</strong><small>Registra productos y consulta tus pedidos.</small></span>
      <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
    </a>
    <?php } ?>
    <a class="comercial-opcion" href="cotizacion.php">
      <span class="comercial-opcion-icon"><i class="fa-solid fa-file-invoice-dollar" aria-hidden="true"></i></span>
      <span><strong>Cotizaciones</strong><small>Crea y administra propuestas para tus clientes.</small></span>
      <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
    </a>
  </div>
</main>
<?php include __DIR__ . '/footer.php'; ?>
