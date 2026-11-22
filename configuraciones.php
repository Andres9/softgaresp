<?php include_once 'encabezado.php' ?>

<section>
  <h2>RESPALDO</h2>

  <p>Descarga un respaldo de la estructura y los datos de la base de datos.</p>
  <form action="respaldo.php" method="post">
    <button id="btnRespaldo" class="btn btn-primary" type="submit">
      <i class="fa-solid fa-download"></i> Descargar respaldo SQL
    </button>
  </form>

  <h2>Datos de acceso de equipos</h2>
  <p>Las contraseñas y patrones de las notas se guardan cifrados, no se incluyen en el PDF y se conservan tras la entrega. Los respaldos pueden contener copias cifradas.</p>
  <p>Respalda por separado la clave <code>C:\xampp\softgaresp-secrets\notas.key</code> en un lugar seguro. Sin ella no podrán recuperarse las credenciales guardadas. Si se usa otra ubicación, configura la variable <code>SOFTGARESP_NOTAS_KEY_FILE</code> para el servidor.</p>
  <p>Las credenciales solo se pueden consultar o modificar desde este equipo mediante localhost o mediante una conexión HTTPS.</p>

</section>

<?php include_once 'footer.php'; ?>