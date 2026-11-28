<?php
require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
header('Cache-Control: no-store, private');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/servicios_catalogo_db.php';
require_once __DIR__ . '/notas_servicios.php';
$conn->set_charset('utf8mb4');
try {
    prepararCatalogoServicios($conn);
    prepararServiciosNota($conn);
} catch (mysqli_sql_exception $e) {
    error_log('No se pudo preparar el catálogo de servicios: ' . $e->getMessage());
    http_response_code(500);
    exit('No se pudo preparar el catálogo de servicios.');
} catch (RuntimeException $e) {
    error_log('No se pudo preparar el catálogo de servicios: ' . $e->getMessage());
    http_response_code(503);
    exit('El catálogo está ocupado. Inténtalo nuevamente.');
}

$errorCatalogo = '';
$nombre = '';
$precio = '';
$categoria = '';
$descripcion = '';
$duracion = '';
$idServicio = '';
$accion = 'crear';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!usuario_es_administrador()) {
        http_response_code(403);
        exit('No tienes permiso para administrar el catálogo.');
    }
    if (!isset($_POST['token_csrf']) || !is_string($_POST['token_csrf']) ||
        !hash_equals(token_csrf(), $_POST['token_csrf'])) {
        http_response_code(403);
        exit('La sesión de seguridad expiró. Recarga la página e inténtalo nuevamente.');
    }
    $accion = $_POST['accion'] ?? 'crear';
    $idServicio = $_POST['id_servicio'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $precio = $_POST['precio'] ?? '';
    $categoria = $_POST['categoria'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $duracion = $_POST['duracion_minutos'] ?? '';
    try {
        if (!is_string($accion) || !in_array($accion, array('crear', 'editar', 'eliminar'), true)) {
            throw new InvalidArgumentException('La acción solicitada no es válida.');
        }
        $idValidado = null;
        if ($accion !== 'crear') {
            $idValidado = is_string($idServicio) ? filter_var($idServicio, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 2147483647))) : false;
            if ($idValidado === false) {
                throw new InvalidArgumentException('El servicio seleccionado no es válido.');
            }
        }
        if ($accion === 'eliminar') {
            eliminarServicioCatalogo($conn, $idValidado);
            $_SESSION['catalogo_servicios_mensaje'] = 'El servicio se eliminó correctamente.';
        } else {
            registrarServicioCatalogo($conn, $nombre, $precio, $categoria, $descripcion, $duracion, $idValidado);
            $_SESSION['catalogo_servicios_mensaje'] = $accion === 'editar'
                ? 'El servicio se actualizó correctamente.'
                : 'El servicio se registró correctamente y ya está disponible en las notas.';
        }
        header('Location: catalogo_servicios.php');
        exit;
    } catch (InvalidArgumentException $e) {
        http_response_code(422);
        $errorCatalogo = $e->getMessage();
        $nombre = is_string($nombre) ? $nombre : '';
        $precio = is_string($precio) ? $precio : '';
        $categoria = is_string($categoria) ? $categoria : '';
        $descripcion = is_string($descripcion) ? $descripcion : '';
        $duracion = is_string($duracion) ? $duracion : '';
        $idServicio = is_string($idServicio) ? $idServicio : '';
        $accion = is_string($accion) ? $accion : '';
    } catch (mysqli_sql_exception $e) {
        error_log('No se pudo guardar el servicio: ' . $e->getMessage());
        if ($accion === 'eliminar' && (int) $e->getCode() === 1451) {
            http_response_code(422);
            $errorCatalogo = 'No se puede eliminar el servicio porque está vinculado a notas.';
        } else {
            http_response_code(500);
            $errorCatalogo = 'No se pudo procesar el servicio. Inténtalo nuevamente.';
        }
    } catch (RuntimeException $e) {
        error_log('No se pudo guardar el servicio: ' . $e->getMessage());
        http_response_code(503);
        $errorCatalogo = 'El catálogo está ocupado. Inténtalo nuevamente.';
    }
} elseif (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    http_response_code(405);
    header('Allow: GET, POST');
    exit('Método no permitido.');
}

try {
    $servicios = $conn->query('SELECT id_servicio, tipoServicio, precio, categoria, descripcion, duracion_minutos FROM servicio ORDER BY tipoServicio, id_servicio')->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $e) {
    error_log('No se pudo consultar el catálogo de servicios: ' . $e->getMessage());
    http_response_code(500);
    exit('No se pudo consultar el catálogo de servicios.');
}
$mensajeCatalogo = $_SESSION['catalogo_servicios_mensaje'] ?? '';
unset($_SESSION['catalogo_servicios_mensaje']);

function escaparCatalogoServicios($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

include __DIR__ . '/encabezado.php';
?>
<main class="pedido-page catalogo-servicios">
  <section class="pedido-hero catalogo-hero">
    <div>
      <span class="pedido-eyebrow"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i> NOTAS DE SERVICIO</span>
      <h1>Catálogo de servicios</h1>
      <p>Organiza tus servicios y consulta sus precios de referencia.</p>
    </div>
    <div class="catalogo-hero-total"><i class="fa-solid fa-layer-group" aria-hidden="true"></i><strong><?= number_format(count($servicios)) ?></strong><span>servicios registrados</span></div>
  </section>

  <?php include __DIR__ . '/app/Views/notas_subnav.php'; ?>

  <?php if ($errorCatalogo !== '') { ?><div class="alert alert-danger" role="alert"><?= escaparCatalogoServicios($errorCatalogo) ?></div><?php } ?>
  <?php if ($mensajeCatalogo !== '') { ?><div class="alert alert-success" role="status"><?= escaparCatalogoServicios($mensajeCatalogo) ?></div><?php } ?>

  <?php if (usuario_es_administrador()) { ?>
  <section class="pedido-panel">
    <div class="pedido-panel-heading">
      <div><span class="pedido-step">01</span><h2>Registrar servicio</h2></div>
      <p>Los campos adicionales son opcionales. El precio se captura por separado en cada nota.</p>
    </div>
    <form action="catalogo_servicios.php" method="post" class="pedido-data-grid">
      <input type="hidden" name="token_csrf" value="<?= escaparCatalogoServicios(token_csrf()) ?>">
      <input type="hidden" name="accion" value="crear">
      <label class="pedido-field">
        <span>Nombre del servicio <b>*</b></span>
        <input type="text" name="nombre" maxlength="50" value="<?= $accion === 'crear' ? escaparCatalogoServicios($nombre) : '' ?>" required>
      </label>
      <label class="pedido-field">
        <span>Precio de referencia en pesos <b>*</b></span>
        <input type="number" name="precio" min="0" max="2147483647" step="1" value="<?= $accion === 'crear' ? escaparCatalogoServicios($precio) : '' ?>" required>
      </label>
      <label class="pedido-field">
        <span>Categoría</span>
        <input type="text" name="categoria" maxlength="50" value="<?= $accion === 'crear' ? escaparCatalogoServicios($categoria) : '' ?>" placeholder="Ej. Mantenimiento">
      </label>
      <label class="pedido-field">
        <span>Duración estimada (minutos)</span>
        <input type="number" name="duracion_minutos" min="1" max="2147483647" step="1" value="<?= $accion === 'crear' ? escaparCatalogoServicios($duracion) : '' ?>" placeholder="Ej. 60">
      </label>
      <label class="pedido-field pedido-field-wide">
        <span>Descripción</span>
        <textarea name="descripcion" maxlength="500" rows="3" placeholder="Describe brevemente el alcance del servicio"><?= $accion === 'crear' ? escaparCatalogoServicios($descripcion) : '' ?></textarea>
      </label>
      <div class="pedido-field-wide">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus" aria-hidden="true"></i> Registrar servicio</button>
      </div>
    </form>
  </section>
  <?php } ?>

  <section class="pedido-panel">
    <div class="pedido-panel-heading">
      <div><span class="pedido-step"><?= usuario_es_administrador() ? '02' : '01' ?></span><h2>Servicios existentes</h2></div>
      <p>Busca, ordena y consulta los servicios registrados.</p>
    </div>
    <div class="catalogo-tabla table-responsive">
      <table id="tabla-catalogo-servicios" class="table table-striped table-hover">
        <thead><tr><th scope="col" class="all">Servicio</th><th scope="col">Categoría</th><th scope="col">Descripción</th><th scope="col">Duración</th><th scope="col" class="all">Precio de referencia</th><?php if (usuario_es_administrador()) { ?><th scope="col" class="all" data-dt-order="disable">Acciones</th><?php } ?></tr></thead>
        <tbody>
          <?php foreach ($servicios as $servicio) { ?>
          <tr>
            <td><strong><?= escaparCatalogoServicios($servicio['tipoServicio']) ?></strong></td>
            <td><?= escaparCatalogoServicios($servicio['categoria'] !== null && $servicio['categoria'] !== '' ? $servicio['categoria'] : 'Sin categoría') ?></td>
            <td class="catalogo-descripcion"><?= $servicio['descripcion'] !== null && $servicio['descripcion'] !== '' ? nl2br(escaparCatalogoServicios($servicio['descripcion'])) : 'Sin descripción' ?></td>
            <td data-order="<?= $servicio['duracion_minutos'] !== null ? (int) $servicio['duracion_minutos'] : -1 ?>"><?= $servicio['duracion_minutos'] !== null ? number_format((int) $servicio['duracion_minutos']) . ' min' : 'Sin indicar' ?></td>
            <td data-order="<?= (int) $servicio['precio'] ?>">$<?= number_format((int) $servicio['precio'], 2) ?></td>
            <?php if (usuario_es_administrador()) { ?>
            <td class="catalogo-acciones">
              <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editar-servicio"
                data-id="<?= (int) $servicio['id_servicio'] ?>" data-nombre="<?= escaparCatalogoServicios($servicio['tipoServicio']) ?>"
                data-precio="<?= (int) $servicio['precio'] ?>" data-categoria="<?= escaparCatalogoServicios($servicio['categoria']) ?>"
                data-descripcion="<?= escaparCatalogoServicios($servicio['descripcion']) ?>" data-duracion="<?= escaparCatalogoServicios($servicio['duracion_minutos']) ?>"
                aria-label="Editar <?= escaparCatalogoServicios($servicio['tipoServicio']) ?>"><i class="fa-solid fa-pen" aria-hidden="true"></i> Editar</button>
              <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#eliminar-servicio"
                data-id="<?= (int) $servicio['id_servicio'] ?>" data-nombre="<?= escaparCatalogoServicios($servicio['tipoServicio']) ?>"
                aria-label="Eliminar <?= escaparCatalogoServicios($servicio['tipoServicio']) ?>"><i class="fa-solid fa-trash" aria-hidden="true"></i> Eliminar</button>
            </td>
            <?php } ?>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </section>
</main>
<?php if (usuario_es_administrador()) { ?>
<div class="modal fade" id="editar-servicio" tabindex="-1" aria-labelledby="editar-servicio-titulo" aria-hidden="true" data-reabrir="<?= $accion === 'editar' && $errorCatalogo !== '' ? '1' : '0' ?>">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="catalogo_servicios.php" method="post">
        <div class="modal-header">
          <h2 class="modal-title fs-5" id="editar-servicio-titulo">Editar servicio</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <?php if ($accion === 'editar' && $errorCatalogo !== '') { ?><div class="alert alert-danger" role="alert"><?= escaparCatalogoServicios($errorCatalogo) ?></div><?php } ?>
          <p class="catalogo-aviso">Cambiar el nombre también lo actualizará en las notas existentes. El precio de referencia no modifica los importes de esas notas.</p>
          <input type="hidden" name="token_csrf" value="<?= escaparCatalogoServicios(token_csrf()) ?>">
          <input type="hidden" name="accion" value="editar">
          <input type="hidden" name="id_servicio" value="<?= $accion === 'editar' ? escaparCatalogoServicios($idServicio) : '' ?>">
          <div class="catalogo-modal-grid">
            <label class="pedido-field"><span>Nombre del servicio <b>*</b></span><input type="text" name="nombre" maxlength="50" value="<?= $accion === 'editar' ? escaparCatalogoServicios($nombre) : '' ?>" required></label>
            <label class="pedido-field"><span>Precio de referencia en pesos <b>*</b></span><input type="number" name="precio" min="0" max="2147483647" step="1" value="<?= $accion === 'editar' ? escaparCatalogoServicios($precio) : '' ?>" required></label>
            <label class="pedido-field"><span>Categoría</span><input type="text" name="categoria" maxlength="50" value="<?= $accion === 'editar' ? escaparCatalogoServicios($categoria) : '' ?>"></label>
            <label class="pedido-field"><span>Duración estimada (minutos)</span><input type="number" name="duracion_minutos" min="1" max="2147483647" step="1" value="<?= $accion === 'editar' ? escaparCatalogoServicios($duracion) : '' ?>"></label>
            <label class="pedido-field catalogo-modal-wide"><span>Descripción</span><textarea name="descripcion" maxlength="500" rows="3"><?= $accion === 'editar' ? escaparCatalogoServicios($descripcion) : '' ?></textarea></label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade" id="eliminar-servicio" tabindex="-1" aria-labelledby="eliminar-servicio-titulo" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="catalogo_servicios.php" method="post">
        <div class="modal-header">
          <h2 class="modal-title fs-5" id="eliminar-servicio-titulo">Eliminar servicio</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <p>¿Deseas eliminar <strong id="eliminar-servicio-nombre"></strong>?</p>
          <p class="catalogo-aviso">Esta acción no se puede deshacer. Los servicios vinculados a notas no se pueden eliminar.</p>
          <input type="hidden" name="token_csrf" value="<?= escaparCatalogoServicios(token_csrf()) ?>">
          <input type="hidden" name="accion" value="eliminar">
          <input type="hidden" name="id_servicio" value="">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger" disabled>Eliminar servicio</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php } ?>
<?php include __DIR__ . '/footer.php'; ?>
