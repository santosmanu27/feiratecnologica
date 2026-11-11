<?php

require_once 'assets/componentes/sessao.php';

$token = $_POST['csrf_token'] ?? '';

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !is_string($token) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
    http_response_code(403);
    exit('Solicitação inválida.');
}

unset($_SESSION['usuario_id']);

session_regenerate_id(true);
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

header('Location: login.php');
exit;