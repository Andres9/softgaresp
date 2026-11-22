<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
$notaMensaje = $_SESSION['nota_mensaje'] ?? '';
unset($_SESSION['nota_mensaje']);
$escaparNotaReporte = static function ($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
};
?>

<section>

    <?php if ($notaMensaje !== '') { ?>
      <div class="alert alert-success" role="status"><?= $escaparNotaReporte($notaMensaje) ?></div>
    <?php } ?>

    <!-- SECCION DE NOTAS -->
    <div class="seccion reportes-resumen">
      <article class="reportes-resumen-card">
        <div class="reportes-resumen-cabecera">
          <span class="reportes-resumen-etiqueta">Servicios</span>
          <span class="reportes-resumen-icono" aria-hidden="true"><i class="fa-solid fa-toolbox"></i></span>
        </div>
        <strong class="reportes-resumen-valor">
          <?php
          $ventasTotales = "SELECT COUNT(id_nota) FROM notas WHERE YEAR(fecha_alta) = 2025";
          $resultado = mysqli_query($conn, $ventasTotales);
          while ($mostrar = mysqli_fetch_array($resultado)) {
            echo ($mostrar[0] . "");
          }

          ?>
        </strong>
        <p class="reportes-resumen-detalle">Registros de 2025</p>
      </article>
      <article class="reportes-resumen-card reportes-resumen-card-total">
        <div class="reportes-resumen-cabecera">
          <span class="reportes-resumen-etiqueta">Total</span>
          <span class="reportes-resumen-icono" aria-hidden="true"><i class="fa-solid fa-sack-dollar"></i></span>
        </div>
        <strong class="reportes-resumen-valor">
          <?php
          $ventasTotales = "SELECT SUM(ganancia) FROM notas WHERE YEAR(fecha_alta) = 2026";
          $resultado = mysqli_query($conn, $ventasTotales);
          while ($mostrar = mysqli_fetch_array($resultado)) {
            $vt  = number_format($mostrar[0],2, '.', ',');
            echo ("$" . $vt.PHP_EOL . "");
          }

          ?>
        </strong>
        <p class="reportes-resumen-detalle">Ganancia registrada en 2026</p>
      </article>
      <a class="reportes-resumen-pdf" href="serviciosMesPDF.php" target="_blank" rel="noopener"
          aria-label="Generar reporte PDF de servicios del mes actual">
        <span class="reportes-resumen-pdf-icono" aria-hidden="true"><i class="fa-solid fa-file-pdf"></i></span>
        <span class="reportes-resumen-pdf-texto">
          <strong>Generar PDF</strong>
          <small>Servicios del mes actual</small>
        </span>
        <i class="fa-solid fa-chevron-right reportes-resumen-pdf-flecha" aria-hidden="true"></i>
      </a>
    </div>

    
    <h2>Notas</h2>
    <noscript><p class="alert alert-warning">Activa JavaScript para confirmar la eliminación de notas.</p></noscript>
  
    <table id="myTable2" class="display responsive nowrap" style="width: 100%">
      <thead>
        <tr>
          <th>ID</th>
          <th>Cliente</th>
          <th>Fecha alta</th>
          <th>Equipo</th>
          <th>Marca</th>
          <th>Modelo</th>
          <th>Servicio</th>
          <th>Costo</th>
          <th>Descripcion</th>
          <th>Notas</th>
          <th>Estado</th>
          <th>Anticipo</th>
          <th>Ganancia</th>
          <th>Restante</th>
          <th>Pagado</th>
          <th>PDF</th>
          <th>Editar</th>
          <th>Eliminar</th>
        </tr>
      </thead>

      <tbody>
        <?php
        $consulta = "select * from notas n 
                            JOIN clientes c ON n.id_cliente  = c.id_CLIENTE 
                            JOIN equipo e ON n.equipo =  e.id_equipo 
                            JOIN servicio s ON n.servicio =  s.id_servicio
                            JOIN estadoservicio es ON n.estado =  es.id_estado 
                          ";
                         /*  WHERE YEAR(fecha_alta) = 2024 */
        /* WHERE fecha_alta >= DATE(NOW()) */
        $resultado = mysqli_query($conn, $consulta);
        while ($mostrar = mysqli_fetch_array($resultado)) {
          ?>
          <tr>
            <td><?= $escaparNotaReporte($mostrar['id_nota']) ?></td>
            <td><?= $escaparNotaReporte($mostrar['nombre'] . ' ' . $mostrar['apellidos']) ?></td>
            <td><?= $escaparNotaReporte($mostrar['fecha_alta']) ?></td>
            <td><?= $escaparNotaReporte($mostrar['descripcionEquipo']) ?></td>
            <td><?= $escaparNotaReporte($mostrar['marca']) ?></td>
            <td><?= $escaparNotaReporte($mostrar['modelo']) ?></td>
            <td><?= $escaparNotaReporte($mostrar['tipoServicio']) ?></td>
            <td><?= $escaparNotaReporte($mostrar['costoServicio']) ?></td>
            <td><?= $escaparNotaReporte($mostrar['descripcionServicio']) ?></td>
            <td><?= $escaparNotaReporte($mostrar['notas']) ?></td>
            <td><?= $escaparNotaReporte($mostrar['estadoServicio']) ?></td>
            <td><?= $escaparNotaReporte($mostrar['anticipo']) ?></td>
            <td><?= $escaparNotaReporte($mostrar['ganancia']) ?></td>
            <td><?= $escaparNotaReporte($mostrar['restante']) ?></td>
            <td><?= $escaparNotaReporte($mostrar['pagado']) ?></td>
            <td>
              <a href="reportes.php?idNota=<?= (int) $mostrar['id_nota'] ?>" target="_blank"><i
                  class="fa-solid fa-file-pdf"></i></a>
            </td>
            <td>
              <a href="editarNotas.php?updNotas=<?= (int) $mostrar['id_nota'] ?>"><i class="fa-solid fa-pen"></i></a>
            </td>
            <td>
              <form action="eliminarNota.php" method="post" class="nota-eliminar">
                <input type="hidden" name="token_csrf" value="<?= $escaparNotaReporte(token_csrf()) ?>">
                <input type="hidden" name="id_nota" value="<?= (int) $mostrar['id_nota'] ?>">
                <input type="hidden" name="origen" value="reportesnotas">
                <button type="button" data-bs-toggle="modal" data-bs-target="#confirmar-eliminar-nota" title="Eliminar nota" aria-label="Eliminar nota <?= (int) $mostrar['id_nota'] ?>"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
              </form>
            </td>
          </tr>
          <?php
        }
        ?>
      </tbody>
    </table>
    </section>