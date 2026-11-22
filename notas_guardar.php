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
    if (!is_string($valor) && !is_int($valor)) {
        http_response_code(400);
        exit('Los datos numéricos de la nota no son válidos.');
    }
    if ((string) $valor === '' && !$obligatorio) {
        return 0;
    }
    $numero = filter_var($valor, FILTER_VALIDATE_INT, array('options' => array('min_range' => -2147483648, 'max_range' => 2147483647)));
    if ($numero === false || ($obligatorio && $numero < 1)) {
        http_response_code(400);
        exit('Los datos numéricos de la nota no son válidos.');
    }
    return $numero;
}

$idNota = $notaEditar ? enteroNota('id', true) : 0;
$cliente = enteroNota('cliente', true);
$equipo = enteroNota('equipo', true);
$servicio = enteroNota('servicio', true);
$estado = enteroNota('estado', true);
$costo = enteroNota('costoServicio');
$ganancia = enteroNota('ganancia');
$anticipo = enteroNota('anticipo');
$restante = enteroNota('restante');
$pagado = enteroNota('pagado');
$marca = entradaNota('marca', 25);
$modelo = entradaNota('modelo', 25);
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
$serie = entradaNota('numero_serie', 100);
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

try {
    $conn->begin_transaction();
    $consultaEquipo = $conn->prepare('SELECT descripcionEquipo FROM equipo WHERE id_equipo = ?');
    $consultaEquipo->bind_param('i', $equipo);
    $consultaEquipo->execute();
    $equipoSeleccionado = $consultaEquipo->get_result()->fetch_assoc();
    $consultaEquipo->close();
    if (!$equipoSeleccionado) {
        $conn->rollback();
        http_response_code(400);
        exit('Selecciona un equipo válido.');
    }
    $admitePatron = equipoAdmitePatron($equipoSeleccionado['descripcionEquipo']);
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
