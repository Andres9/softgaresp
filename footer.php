<footer class="site-footer">
    <div class="footer-brand">
      <img src="img/logotipo-blanco.png" alt="Garesp Servicio Informático" />
    </div>
    <p>© <?= date('Y') ?> Garesp Servicio Informático. Todos los derechos reservados.</p>
  </footer>

<?php if (in_array($pagina_actual, array('notaCliente.php', 'reportesnotas.php', 'consultas.php'), true)) { ?>
<div class="modal fade" id="confirmar-eliminar-nota" tabindex="-1" aria-labelledby="confirmar-eliminar-nota-titulo" aria-describedby="confirmar-eliminar-nota-descripcion" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content nota-confirmacion">
      <div class="modal-header">
        <h2 class="modal-title" id="confirmar-eliminar-nota-titulo">Eliminar nota</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="confirmar-eliminar-nota-descripcion">
        <p>¿Deseas eliminar la nota <strong id="confirmar-eliminar-nota-folio"></strong>?</p>
        <p class="nota-confirmacion-aviso">También se eliminarán sus datos de acceso. Esta acción no se puede deshacer.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="confirmar-eliminar-nota-accion" disabled>Eliminar nota</button>
      </div>
    </div>
  </div>
</div>
<?php } ?>

</body>

<!-- SCRIPTS -->
<script src="js/jquery-3.7.1.js"></script>

<script src="js/bootstrap.min.js"></script>

<script src="js/datatables.min.js"></script>
<script src="js/dataTables.responsive.js"></script>

<script src="js/select2.min.js"></script>
<script src="js/selectores.js"></script>

<script src="js/index.js"></script>
<?php if ($pagina_actual === 'ventas.php') { ?>
<script src="js/ventas.js"></script>
<?php } ?>
<?php if ($pagina_actual === 'movimiento.php') { ?>
<script src="js/movimiento.js"></script>
<?php } ?>
<?php if ($pagina_actual === 'cierre_mensual.php') { ?>
<script src="js/cierre-mensual.js"></script>
<?php } ?>
<?php if (in_array($pagina_actual, array('notaCliente.php', 'reportesnotas.php', 'consultas.php'), true)) { ?>
<script src="js/nota-eliminar.js"></script>
<?php } ?>


</html>