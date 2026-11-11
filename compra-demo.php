<?php

require_once 'assets/componentes/sessao.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$id = $_SESSION['ultima_simulacao_id'] ?? null;

if (!$id) {
    header('Location: checkout.php');
    exit;
}

require 'assets/componentes/conexao.php';

$consulta = $pdo->prepare(
    'SELECT id FROM pedidos_simulados
     WHERE id = ? AND usuario_id = ?
     LIMIT 1'
);

$consulta->execute([
    $id,
    $_SESSION['usuario_id']
]);

$pedido = $consulta->fetch();

if (!$pedido) {
    header('Location: checkout.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido | AuraPet</title>

    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/checkout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="fundo-obrigado">
    <?php include('assets/componentes/header.php'); ?>

    <main class="checkout conclusao">
        <div class="check">
            <i class="fa-solid fa-check"></i>
        </div>

        <h1>Compra realizada com sucesso</h1>

        <p>Obrigada por conhecer a AuraPet ♡</p>

        <p>
            Número:
            <strong>#<?= (int) $pedido['id'] ?></strong>
        </p>

        <div class="botoes">
            <a class="botao" href="index.php">
                Voltar para o início
            </a>

            <a class="botao secundario" href="conta.php#pedidos">
                Acompanhar pedido
            </a>
        </div>
    </main>
    <div class="apoiodiv">
        <?php include('assets/componentes/apoio.php'); ?>
    </div>
</body>

</html>