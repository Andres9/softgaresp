<?php

require_once __DIR__ . '/auth.php';
mysqli_report(MYSQLI_REPORT_OFF);
require_once __DIR__ . '/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método no permitido.');
}

$usuario = isset($_POST['usuario']) && is_string($_POST['usuario'])
    ? trim($_POST['usuario'])
    : '';
$password = isset($_POST['password']) && is_string($_POST['password'])
    ? $_POST['password']
    : '';

if ($usuario === '' || $password === '' || strlen($usuario) > 150 || strlen($password) > 4096) {
    $_SESSION['login_error'] = 'Ingresa un usuario y una contraseña válidos.';
    header('Location: index.php');
    exit;
}

if (!$conn) {
    error_log('No fue posible conectar con la base de datos para validar el acceso.');
    $_SESSION['login_error'] = 'No se pudo validar el acceso. Inténtalo más tarde.';
    header('Location: index.php');
    exit;
}

$consulta = mysqli_prepare($conn, 'SELECT nombre, pass FROM users WHERE nombre = ? LIMIT 1');
if ($consulta === false) {
    error_log('No fue posible preparar la consulta de acceso: ' . mysqli_error($conn));
    $_SESSION['login_error'] = 'No se pudo validar el acceso. Inténtalo más tarde.';
    header('Location: index.php');
    exit;
}

if (!mysqli_stmt_bind_param($consulta, 's', $usuario)) {
    error_log('No fue posible asignar el usuario a la consulta de acceso: ' . mysqli_stmt_error($consulta));
    mysqli_stmt_close($consulta);
    $_SESSION['login_error'] = 'No se pudo validar el acceso. Inténtalo más tarde.';
    header('Location: index.php');
    exit;
}

if (!mysqli_stmt_execute($consulta)) {
    error_log('No fue posible consultar las credenciales de acceso: ' . mysqli_stmt_error($consulta));
    mysqli_stmt_close($consulta);
    $_SESSION['login_error'] = 'No se pudo validar el acceso. Inténtalo más tarde.';
    header('Location: index.php');
    exit;
}

if (!mysqli_stmt_bind_result($consulta, $usuarioAlmacenado, $passwordAlmacenado)) {
    error_log('No fue posible leer el resultado de la consulta de acceso: ' . mysqli_stmt_error($consulta));
    mysqli_stmt_close($consulta);
    $_SESSION['login_error'] = 'No se pudo validar el acceso. Inténtalo más tarde.';
    header('Location: index.php');
    exit;
}

$encontrado = mysqli_stmt_fetch($consulta);
mysqli_stmt_close($consulta);

$passwordAlmacenado = $encontrado ? (string) $passwordAlmacenado : '';
$informacionHash = password_get_info($passwordAlmacenado);
$esPasswordHash = isset($informacionHash['algo']) && $informacionHash['algo'] !== 0;
$passwordValido = $encontrado && (
    $esPasswordHash
        ? password_verify($password, $passwordAlmacenado)
        : hash_equals($passwordAlmacenado, $password)
);

if (!$passwordValido) {
    $_SESSION['login_error'] = 'Usuario o contraseña incorrectos.';
    header('Location: index.php');
    exit;
}

if (!session_regenerate_id(true)) {
    error_log('No fue posible renovar el identificador de sesión tras el acceso.');
    $_SESSION['login_error'] = 'No se pudo iniciar la sesión. Inténtalo de nuevo.';
    header('Location: index.php');
    exit;
}

$_SESSION = array();
$_SESSION['usuario'] = (string) $usuarioAlmacenado;
$_SESSION['autenticado_en'] = time();

header('Location: ' . pagina_inicio_usuario());
exit;
