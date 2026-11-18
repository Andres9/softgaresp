<?php

require_once __DIR__ . '/auth.php';

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !isset($_POST['token_csrf']) ||
    !is_string($_POST['token_csrf']) ||
    !hash_equals(token_csrf(), $_POST['token_csrf'])
) {
    http_response_code(400);
    exit('Solicitud no válida.');
}

$_SESSION = array();
session_destroy();

$cookie = session_get_cookie_params();
setcookie(session_name(), '', time() - 42000, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);

header('Location: index.php');
exit;
