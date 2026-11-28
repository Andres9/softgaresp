<?php

if (isset($_SERVER['SCRIPT_FILENAME']) && basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/auth.php';
requerir_usuario_autenticado();
header('Cache-Control: no-store, private');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}
if (!isset($_POST['token_csrf']) || !is_string($_POST['token_csrf']) || !hash_equals(token_csrf(), $_POST['token_csrf'])) {
    http_response_code(403);
    exit('La sesión de seguridad expiró. Recarga la página e inténtalo de nuevo.');
}

function entradaNota($nombre, $maximo, $conservarEspacios = false)
{
    $valor = $_POST[$nombre] ?? '';
    if (!is_string($valor)) {
        http_response_code(400);
        exit('Los datos de la nota no son válidos.');
    }
    if (!$conservarEspacios) {
        $valor = trim($valor);
    }
    if (mb_strlen($valor, 'UTF-8') > $maximo) {
        http_response_code(400);
        exit('Uno de los campos supera la longitud permitida.');
    }
    return $valor;
}

function enteroNota($nombre, $obligatorio = false)
{
    $valor = $_POST[$nombre] ?? '';
    $etiquetas = array('id' => 'la nota', 'cliente' => 'el cliente', 'equipo' => 'el equipo', 'estado' => 'el estado del equipo', 'ganancia' => 'la ganancia', 'anticipo' => 'el anticipo', 'pagado' => 'el pago adicional');
    $mensaje = 'Revisa ' . ($etiquetas[$nombre] ?? 'los datos numéricos') . ': ' . ($obligatorio ? 'selecciona una opción válida.' : 'debe ser un número entero.');
    if (!is_string($valor) && !is_int($valor)) {
        http_response_code(400);
        exit($mensaje);
    }
    if (trim((string) $valor) === '' && !$obligatorio) {
        return 0;
    }
    $numero = filter_var($valor, FILTER_VALIDATE_INT, array('options' => array('min_range' => -2147483648, 'max_range' => 2147483647)));
    if ($numero === false || ($obligatorio && $numero < 1)) {
        http_response_code(400);
        exit($mensaje);
    }
    return $numero;
}

$idNota = $notaEditar ? enteroNota('id', true) : 0;
$cliente = enteroNota('cliente', true);
$estado = enteroNota('estado', true);
$ganancia = enteroNota('ganancia');
$anticipo = enteroNota('anticipo');
$pagado = enteroNota('pagado');
$idsServicios = $_POST['servicios'] ?? null;
$preciosServicios = $_POST['precios'] ?? null;
if (!is_array($idsServicios) || !is_array($preciosServicios) || count($idsServicios) < 1 ||
    count($idsServicios) > 200 || array_keys($idsServicios) !== array_keys($preciosServicios)) {
    http_response_code(400);
    exit('Selecciona entre uno y 200 servicios con sus precios.');
}
$lineasServicios = array();
$costo = 0;
foreach ($idsServicios as $indice => $idServicio) {
    $precioEntrada = $preciosServicios[$indice];
    $idValidado = is_string($idServicio) ? filter_var($idServicio, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 2147483647))) : false;
    $precioValidado = is_string($precioEntrada) ? filter_var($precioEntrada, FILTER_VALIDATE_INT, array('options' => array('min_range' => 0, 'max_range' => 2147483647))) : false;
    if ($idValidado === false || $precioValidado === false || $costo > 2147483647 - $precioValidado) {
        http_response_code(400);
        exit('Revisa los servicios y sus precios enteros; el total no puede superar 2147483647.');
    }
    $lineasServicios[] = array('id' => $idValidado, 'precio' => $precioValidado);
    $costo += $precioValidado;
}
if ($anticipo < 0 || $pagado < 0 || $anticipo > $costo || $pagado > $costo - $anticipo) {
    http_response_code(400);
    exit('El anticipo y el pago adicional deben ser positivos y no superar el total.');
}
$idsEquipos = $_POST['equipos'] ?? null;
$marcasEquipos = $_POST['marcas'] ?? null;
$modelosEquipos = $_POST['modelos'] ?? null;
$seriesEquipos = $_POST['series'] ?? null;
if (!is_array($idsEquipos) || !is_array($marcasEquipos) || !is_array($modelosEquipos) || !is_array($seriesEquipos) ||
    count($idsEquipos) < 1 || count($idsEquipos) > 20 || array_keys($idsEquipos) !== array_keys($marcasEquipos) ||
    array_keys($idsEquipos) !== array_keys($modelosEquipos) || array_keys($idsEquipos) !== array_keys($seriesEquipos)) {
    http_response_code(400);
    exit('Registra entre uno y 20 equipos.');
}
$lineasEquipos = array();
foreach ($idsEquipos as $indice => $idEquipo) {
    $marcaEquipo = $marcasEquipos[$indice];
    $modeloEquipo = $modelosEquipos[$indice];
    $serieEquipo = $seriesEquipos[$indice];
    $idEquipoValidado = is_string($idEquipo) ? filter_var($idEquipo, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 2147483647))) : false;
    if ($idEquipoValidado === false) {
        http_response_code(400);
        exit('Revisa el equipo ' . ((int) $indice + 1) . ': selecciona una opción válida.');
    }
    if (!is_string($marcaEquipo) || !is_string($modeloEquipo) || !is_string($serieEquipo)) {
        http_response_code(400);
        exit('Los datos de los equipos no son válidos.');
    }
    $marcaEquipo = trim($marcaEquipo);
    $modeloEquipo = trim($modeloEquipo);
    $serieEquipo = trim($serieEquipo);
    if (mb_strlen($marcaEquipo, 'UTF-8') > 25 || mb_strlen($modeloEquipo, 'UTF-8') > 25 || mb_strlen($serieEquipo, 'UTF-8') > 100) {
        http_response_code(400);
        exit('Revisa la longitud de marca, modelo o número de serie del equipo ' . ((int) $indice + 1) . '.');
    }
    $lineasEquipos[] = array('id' => $idEquipoValidado, 'marca' => $marcaEquipo, 'modelo' => $modeloEquipo, 'serie' => $serieEquipo);
}
$equipo = $lineasEquipos[0]['id'];
$marca = $lineasEquipos[0]['marca'];
$modelo = $lineasEquipos[0]['modelo'];
$serie = $lineasEquipos[0]['serie'];
$servicio = $lineasServicios[0]['id'];
$restante = $costo - $anticipo - $pagado;
$descripcion = entradaNota($notaEditar ? 'descripcionServicio' : 'descripcion', 150);
$nota = entradaNota('nota', 150);
$fecha = entradaNota('fechaEntrada', 19);
$fechaValidada = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $fecha);
if (!$fechaValidada || $fechaValidada->format('Y-m-d H:i:s') !== $fecha) {
    http_response_code(400);
    exit('La fecha de entrada no es válida.');
}
$cargadorEntrada = entradaNota('cargador', 1);
if (!in_array($cargadorEntrada, array('', '0', '1'), true)) {
    http_response_code(400);
    exit('La información del cargador no es válida.');
}
$cargador = $cargadorEntrada === '' ? null : (int) $cargadorEntrada;
$accesorios = entradaNota('accesorios', 255);
$estadoFisico = entradaNota('estado_fisico', 255);
$claveDispositivo = entradaNota('clave_dispositivo', 256, true);
$patron = entradaNota('patron_dispositivo', 9);

require_once __DIR__ . '/notas_recepcion.php';
if (!canalSeguroNota() && ($claveDispositivo !== '' || $patron !== '')) {
    http_response_code(400);
    exit('Para registrar credenciales usa HTTPS o localhost.');
}
if ($patron !== '' && !patronNotaValido($patron)) {
    http_response_code(400);
    exit('El patrón debe contener entre cuatro y nueve puntos válidos, sin repetir.');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once __DIR__ . '/conexion.php';
prepararRecepcionNotas($conn);
require_once __DIR__ . '/notas_servicios.php';
prepararServiciosNota($conn);

try {
    $conn->begin_transaction();
    $consultaServicio = $conn->prepare('SELECT tipoServicio FROM servicio WHERE id_servicio = ? FOR UPDATE');
    foreach ($lineasServicios as &$lineaServicio) {
        $consultaServicio->bind_param('i', $lineaServicio['id']);
        $consultaServicio->execute();
        $servicioSeleccionado = $consultaServicio->get_result()->fetch_assoc();
        if (!$servicioSeleccionado || trim((string) $servicioSeleccionado['tipoServicio']) === '') {
            $conn->rollback();
            http_response_code(400);
            exit('Selecciona servicios válidos del catálogo.');
        }
        $lineaServicio['nombre'] = $servicioSeleccionado['tipoServicio'];
    }
    unset($lineaServicio);
    $consultaServicio->close();
    $consultaEquipo = $conn->prepare('SELECT descripcionEquipo FROM equipo WHERE id_equipo = ?');
    $admitePatron = false;
    foreach ($lineasEquipos as &$lineaEquipo) {
        $consultaEquipo->bind_param('i', $lineaEquipo['id']);
        $consultaEquipo->execute();
        $equipoSeleccionado = $consultaEquipo->get_result()->fetch_assoc();
        if (!$equipoSeleccionado) {
            $conn->rollback();
            http_response_code(400);
            exit('Selecciona equipos válidos.');
        }
        $lineaEquipo['descripcion'] = $equipoSeleccionado['descripcionEquipo'];
        $admitePatron = $admitePatron || equipoAdmitePatron($equipoSeleccionado['descripcionEquipo']);
    }
    unset($lineaEquipo);
    $consultaEquipo->close();
    if ($patron !== '' && !$admitePatron) {
        $conn->rollback();
        http_response_code(400);
        exit('El patrón solo se puede registrar para un teléfono o una tablet.');
    }

    $claveAnterior = null;
    $patronAnterior = null;
    if ($notaEditar) {
        $consultaAnterior = $conn->prepare('SELECT n.id_nota, r.contrasena_cifrada, r.patron_cifrado FROM notas n LEFT JOIN nota_recepcion r ON r.id_nota = n.id_nota WHERE n.id_nota = ? FOR UPDATE');
        $consultaAnterior->bind_param('i', $idNota);
        $consultaAnterior->execute();
        $anterior = $consultaAnterior->get_result()->fetch_assoc();
        $consultaAnterior->close();
        if (!$anterior) {
            $conn->rollback();
            http_response_code(404);
            exit('No se encontró la nota.');
        }
        $claveAnterior = $anterior['contrasena_cifrada'];
        $patronAnterior = $anterior['patron_cifrado'];
        $consultaDetalles = $conn->prepare('SELECT id_servicio, nombre FROM nota_servicio WHERE id_nota = ? ORDER BY id_detalle');
        $consultaDetalles->bind_param('i', $idNota);
        $consultaDetalles->execute();
        $nombresAnteriores = array();
        foreach ($consultaDetalles->get_result()->fetch_all(MYSQLI_ASSOC) as $detalleAnterior) {
            $nombresAnteriores[$detalleAnterior['id_servicio']] = $detalleAnterior['nombre'];
        }
        $consultaDetalles->close();
        foreach ($lineasServicios as &$lineaServicio) {
            if (isset($nombresAnteriores[$lineaServicio['id']])) {
                $lineaServicio['nombre'] = $nombresAnteriores[$lineaServicio['id']];
            }
        }
        unset($lineaServicio);
    }

    if (canalSeguroNota()) {
        $claveCifrada = cifrarRecepcionNota($claveDispositivo);
        $patronCifrado = $admitePatron ? cifrarRecepcionNota($patron) : null;
    } else {
        $claveCifrada = $claveAnterior;
        $patronCifrado = $admitePatron ? $patronAnterior : null;
    }

    if ($notaEditar) {
        $sentencia = $conn->prepare('UPDATE notas SET id_cliente=?, equipo=?, marca=?, modelo=?, servicio=?, descripcionServicio=?, estado=?, costoServicio=?, ganancia=?, anticipo=?, restante=?, pagado=?, notas=?, fecha_alta=? WHERE id_nota=?');
        $sentencia->bind_param('iissisiiiiiissi', $cliente, $equipo, $marca, $modelo, $servicio, $descripcion, $estado, $costo, $ganancia, $anticipo, $restante, $pagado, $nota, $fecha, $idNota);
    } else {
        $sentencia = $conn->prepare('INSERT INTO notas (id_cliente, equipo, marca, modelo, servicio, descripcionServicio, estado, costoServicio, ganancia, anticipo, restante, pagado, notas, fecha_alta) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $sentencia->bind_param('iissisiiiiiiss', $cliente, $equipo, $marca, $modelo, $servicio, $descripcion, $estado, $costo, $ganancia, $anticipo, $restante, $pagado, $nota, $fecha);
    }
    $sentencia->execute();
    $sentencia->close();
    if (!$notaEditar) {
        $idNota = (int) $conn->insert_id;
        $actualizarCliente = $conn->prepare('UPDATE clientes SET numServicio = COALESCE(numServicio, 0) + 1 WHERE id_cliente = ?');
        $actualizarCliente->bind_param('i', $cliente);
        $actualizarCliente->execute();
        $actualizarCliente->close();
    }

    if ($notaEditar) {
        $eliminarEquipos = $conn->prepare('DELETE FROM nota_equipo WHERE id_nota = ?');
        $eliminarEquipos->bind_param('i', $idNota);
        $eliminarEquipos->execute();
        $eliminarEquipos->close();
        $eliminarDetalles = $conn->prepare('DELETE FROM nota_servicio WHERE id_nota = ?');
        $eliminarDetalles->bind_param('i', $idNota);
        $eliminarDetalles->execute();
        $eliminarDetalles->close();
    }
    $insertarEquipo = $conn->prepare('INSERT INTO nota_equipo (id_nota, id_equipo, descripcion, marca, modelo, numero_serie) VALUES (?, ?, ?, ?, ?, ?)');
    foreach ($lineasEquipos as $lineaEquipo) {
        $insertarEquipo->bind_param('iissss', $idNota, $lineaEquipo['id'], $lineaEquipo['descripcion'], $lineaEquipo['marca'], $lineaEquipo['modelo'], $lineaEquipo['serie']);
        $insertarEquipo->execute();
    }
    $insertarEquipo->close();
    $insertarDetalle = $conn->prepare('INSERT INTO nota_servicio (id_nota, id_servicio, nombre, precio) VALUES (?, ?, ?, ?)');
    foreach ($lineasServicios as $lineaServicio) {
        $insertarDetalle->bind_param('iisi', $idNota, $lineaServicio['id'], $lineaServicio['nombre'], $lineaServicio['precio']);
        $insertarDetalle->execute();
    }
    $insertarDetalle->close();

    $recepcion = $conn->prepare('INSERT INTO nota_recepcion (id_nota, cargador, accesorios, numero_serie, estado_fisico, contrasena_cifrada, patron_cifrado) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE cargador=VALUES(cargador), accesorios=VALUES(accesorios), numero_serie=VALUES(numero_serie), estado_fisico=VALUES(estado_fisico), contrasena_cifrada=VALUES(contrasena_cifrada), patron_cifrado=VALUES(patron_cifrado)');
    $recepcion->bind_param('iisssss', $idNota, $cargador, $accesorios, $serie, $estadoFisico, $claveCifrada, $patronCifrado);
    $recepcion->execute();
    $recepcion->close();
    $conn->commit();
} catch (mysqli_sql_exception $error) {
    $conn->rollback();
    error_log('No fue posible guardar la nota: ' . $error->getMessage());
    http_response_code(500);
    exit('No se pudo guardar la nota. Verifica los datos e inténtalo de nuevo.');
} catch (RuntimeException $error) {
    $conn->rollback();
    error_log('No fue posible proteger el acceso de la nota: ' . $error->getMessage());
    http_response_code(500);
    exit('No se pudo proteger la información de acceso. La nota no se guardó.');
}

header('Location: notaCliente.php');
exit;
