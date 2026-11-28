<?php

function iniciar_sesion_app()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    $https = isset($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
    session_set_cookie_params(array(
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax'
    ));

    if (!session_start()) {
        error_log('No fue posible iniciar la sesión de usuario.');
        http_response_code(500);
        exit('No se pudo iniciar la sesión.');
    }
}

function usuario_autenticado()
{
    return isset($_SESSION['usuario']) && is_string($_SESSION['usuario']) && $_SESSION['usuario'] !== '';
}

function usuario_es_administrador()
{
    return usuario_autenticado() && strcasecmp(trim($_SESSION['usuario']), 'andresge') === 0;
}

function pagina_inicio_usuario()
{
    return usuario_es_administrador() ? 'ventas.php' : 'clientes.php';
}

function requerir_usuario_autenticado()
{
    iniciar_sesion_app();

    if (!usuario_autenticado()) {
        header('Location: index.php');
        exit;
    }

    if (usuario_es_administrador()) {
        return;
    }

    $paginasPermitidas = array(
        'clientes.php',
        'editarClientes.php',
        'actualizarClientes.php',
        'registroClientes.php',
        'servicios_notas.php',
        'catalogo_servicios.php',
        'notaCliente.php',
        'editarNotas.php',
        'actualizarNotas.php',
        'registroNotas.php',
        'eliminarNota.php',
        'comercial.php',
        'cotizacion.php',
        'guardarCotizacion.php',
        'cotizacionPDF.php',
        'guardarVentaCotizacion.php',
        'guardarAbonoVenta.php',
        'ventaPDF.php',
        'catalogoPixal.php',
        'importarPixalCSV.php',
        'guardarProductoPixal.php',
        'reportesnotas.php',
        'notasReportes.php',
        'reportes.php',
        'consultaReporte.php',
        'serviciosMesPDF.php'
    );

    $paginaActual = isset($_SERVER['PHP_SELF']) ? basename($_SERVER['PHP_SELF']) : '';
    if (!in_array($paginaActual, $paginasPermitidas, true)) {
        http_response_code(403);
        exit('No tienes permiso para acceder a esta sección.');
    }
}

function token_csrf()
{
    iniciar_sesion_app();

    if (!isset($_SESSION['token_csrf'])) {
        $_SESSION['token_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['token_csrf'];
}

iniciar_sesion_app();
