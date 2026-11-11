<?php

require_once 'assets/componentes/sessao.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !is_string($_POST['csrf_token'] ?? null) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);
    exit('Solicitação inválida.');
}

require 'assets/componentes/conexao.php';

$retorno = ($_POST['retorno'] ?? '') === 'pagamento'
    ? 'pagamento-demo.php'
    : 'conta.php#cartoes';

$id = filter_var(
    $_POST['id'] ?? 0,
    FILTER_VALIDATE_INT
) ?: 0;

$acao = $_POST['acao'] ?? '';

if ($acao === 'remover' && $id > 0) {
    $consulta = $pdo->prepare(
        'DELETE FROM cartoes_teste
         WHERE id = ? AND usuario_id = ?'
    );

    $consulta->execute([
        $id,
        $_SESSION['usuario_id']
    ]);
} elseif ($acao === 'salvar') {
    $nome = trim(
        is_string($_POST['nome'] ?? null)
            ? $_POST['nome']
            : ''
    );

    $bandeira = $_POST['bandeira'] ?? '';
    $ultimos = $_POST['ultimos4'] ?? '';
    $validade = $_POST['validade'] ?? '';

    if (
        $nome === '' ||
        mb_strlen($nome) > 100 ||
        !is_string($bandeira) ||
        !in_array($bandeira, ['Visa', 'Mastercard', 'Elo', 'Outro'], true) ||
        !is_string($ultimos) ||
        !preg_match('/^[0-9]{4}$/', $ultimos) ||
        !is_string($validade) ||
        !preg_match('/^(0[1-9]|1[0-2])\/20[0-9]{2}$/', $validade)
    ) {
        $_SESSION['cartao_erro'] =
            'Confira o nome, bandeira, quatro dígitos e validade no formato MM/AAAA.';
    } elseif ($id > 0) {
        $consulta = $pdo->prepare(
            'UPDATE cartoes_teste
             SET nome = ?, bandeira = ?, ultimos4 = ?, validade = ?
             WHERE id = ? AND usuario_id = ?'
        );

        $consulta->execute([
            $nome,
            $bandeira,
            $ultimos,
            $validade,
            $id,
            $_SESSION['usuario_id']
        ]);
    } else {
        $consulta = $pdo->prepare(
            'INSERT INTO cartoes_teste
                (usuario_id, nome, bandeira, ultimos4, validade)
             VALUES (?, ?, ?, ?, ?)'
        );

        $consulta->execute([
            $_SESSION['usuario_id'],
            $nome,
            $bandeira,
            $ultimos,
            $validade
        ]);
    }
}

header('Location: ' . $retorno);
exit;