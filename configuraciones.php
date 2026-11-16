<?php include_once 'encabezado.php' ?>

<section>
  <h2>RESPALDO</h2>

  <p>Descarga un respaldo de la estructura y los datos de la base de datos.</p>
  <form action="respaldo.php" method="post">
    <button id="btnRespaldo" class="btn btn-primary" type="submit">
      <i class="fa-solid fa-download"></i> Descargar respaldo SQL
    </button>
  </form>

</section>

<?php include_once 'footer.php'; ?>