<?php

require_once 'assets/componentes/sessao.php';
require 'assets/componentes/dados-produtos.php';

if (empty($_SESSION['usuario_id'])) {
    $_SESSION['voltar_checkout'] = true;
    header('Location: login.php');
    exit;
}

if (
    empty($_SESSION['carrinho']) ||
    empty($_SESSION['checkout_endereco']) ||
    empty($_SESSION['checkout_metodo'])
) {
    header('Location: checkout.php');
    exit;
}

require 'assets/componentes/conexao.php';

$metodos = [
    'cartao' => 'Cartão de crédito',
    'pix' => 'Pix',
    'boleto' => 'Boleto bancário',
    'loja' => 'Pagamento na loja'
];

$metodo = $_SESSION['checkout_metodo'];

if (!is_string($metodo) || !isset($metodos[$metodo])) {
    header('Location: checkout.php');
    exit;
}

function escapar($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

$consulta = $pdo->prepare(
    'SELECT id, nome, bandeira, ultimos4, validade
     FROM cartoes_teste
     WHERE usuario_id = ?
     ORDER BY id DESC'
);

$consulta->execute([
    $_SESSION['usuario_id']
]);

$cartoes = $consulta->fetchAll();

$erro = $_SESSION['cartao_erro'] ?? '';
unset($_SESSION['cartao_erro']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        http_response_code(403);
        exit('Solicitação inválida.');
    }

    if ($metodo === 'cartao') {
        $idCartao = filter_var(
            $_POST['cartao_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        $consulta = $pdo->prepare(
            'SELECT id
             FROM cartoes_teste
             WHERE id = ? AND usuario_id = ?
             LIMIT 1'
        );

        $consulta->execute([
            $idCartao ?: 0,
            $_SESSION['usuario_id']
        ]);

        if (!$consulta->fetch()) {
            $erro = 'Selecione um cartão cadastrado.';
        }
    }

    if ($erro === '') {
        $itens = [];
        $totalCentavos = 0;

        foreach ($_SESSION['carrinho'] as $id => $quantidade) {
            if (
                !isset($produtos[$id]) ||
                !is_numeric($quantidade) ||
                (int) $quantidade < 1
            ) {
                continue;
            }

            $quantidade = min(99, (int) $quantidade);
            $produto = $produtos[$id];

            $precoCentavos = (int) round(
                (float) $produto['preco'] * 100
            );

            $subtotalCentavos = $precoCentavos * $quantidade;

            $itens[] = [
                'id' => $id,
                'nome' => $produto['nome'],
                'imagem' => $produto['imagem'],
                'quantidade' => $quantidade,
                'preco_centavos' => $precoCentavos,
                'subtotal_centavos' => $subtotalCentavos
            ];

            $totalCentavos += $subtotalCentavos;
        }

        if (!$itens) {
            header('Location: carrinho.php');
            exit;
        }

        try {
            $consulta = $pdo->prepare(
                'INSERT INTO pedidos_simulados
                    (usuario_id, itens_json, endereco_json, metodo, total)
                 VALUES (?, ?, ?, ?, ?)'
            );

            $consulta->execute([
                $_SESSION['usuario_id'],

                json_encode(
                    $itens,
                    JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
                ),

                json_encode(
                    $_SESSION['checkout_endereco'],
                    JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
                ),

                $metodo,

                number_format(
                    $totalCentavos / 100,
                    2,
                    '.',
                    ''
                )
            ]);

            $_SESSION['ultima_simulacao_id'] = (int) $pdo->lastInsertId();

            unset(
                $_SESSION['checkout_endereco'],
                $_SESSION['checkout_metodo'],
                $_SESSION['qr_teste']
            );

            $_SESSION['carrinho'] = [];

            header('Location: compra-demo.php');
            exit;
        } catch (Throwable $excecao) {
            error_log('Erro na simulação: ' . $excecao->getMessage());
            $erro = 'Não foi possível registrar. Tente novamente.';
        }
    }
}

if ($metodo === 'pix' && empty($_SESSION['qr_teste'])) {
    $_SESSION['qr_teste'] = bin2hex(random_bytes(16));
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pagamento | AuraPet</title>

    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/checkout.css">
    <link rel="stylesheet" href="css/cartoes-teste.css">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >
</head>

<body class="fundo-pagamento">
    <?php include('assets/componentes/header.php'); ?>

    <main class="checkout">
        <a href="checkout.php" class="voltar">
            <i class="fa-solid fa-arrow-left"></i>
            Voltar
        </a>

        <section class="cartao cartao-demo">
            <?php if ($erro !== ''): ?>
                <p class="erro" role="alert">
                    <?= escapar($erro) ?>
                </p>
            <?php endif; ?>

            <?php if ($metodo === 'cartao'): ?>
                <div class="pagamento-cartao-visual">
                    <div class="pagamento-cartao-titulo">
                        <i class="fa-solid fa-credit-card"></i>
                        <h2>Pagamento com cartão de crédito</h2>
                    </div>

                    <?php if (!empty($cartoes)): ?>
                        <form method="post" class="selecionar-cartao">
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= escapar($_SESSION['csrf_token']) ?>"
                            >

                            <label for="cartao_id">
                                Escolha um cartão
                            </label>

                            <select
                                name="cartao_id"
                                id="cartao_id"
                                required
                            >
                                <option value="">Selecione um cartão</option>

                                <?php foreach ($cartoes as $cartao): ?>
                                    <option value="<?= (int) $cartao['id'] ?>">
                                        <?= escapar($cartao['bandeira']) ?>
                                        •••• <?= escapar($cartao['ultimos4']) ?>
                                        — <?= escapar($cartao['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <button
                                class="botao confirmar-pagamento"
                                type="submit"
                            >
                                <i class="fa-solid fa-lock"></i>
                                Confirmar pagamento
                            </button>
                        </form>
                    <?php else: ?>
                        <p>
                            Você ainda não tem um cartão cadastrado.
                            Adicione um abaixo para continuar.
                        </p>
                    <?php endif; ?>

                    <details
                        class="adicionar-cartao"
                        <?= empty($cartoes) ? 'open' : '' ?>
                    >
                        <summary>
                            <?= empty($cartoes)
                                ? 'Adicionar cartão'
                                : 'Adicionar outro cartão' ?>
                        </summary>

                        <?php
                            $retornoCartao = 'pagamento';
                            include('form-cartao-teste.php');
                        ?>
                    </details>

                    <a href="conta.php#cartoes" class="gerenciar-cartoes">
                        Gerenciar cartões no perfil
                    </a>
                </div>

            <?php elseif ($metodo === 'pix'): ?>
                <h1>Pagamento com Pix</h1>

                <p>Use o QR Code abaixo para a apresentação.</p>

                <div id="qrcode"></div>

                <p>
                    Código:
                    <strong><?= escapar($_SESSION['qr_teste']) ?></strong>
                </p>

                <form method="post">
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= escapar($_SESSION['csrf_token']) ?>"
                    >

                    <button class="botao" type="submit">
                        Continuar
                    </button>
                </form>

            <?php else: ?>
                <h1><?= escapar($metodos[$metodo]) ?></h1>

                <p>
                    Forma escolhida:
                    <strong><?= escapar($metodos[$metodo]) ?></strong>
                </p>

                <form method="post">
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= escapar($_SESSION['csrf_token']) ?>"
                    >

                    <button class="botao" type="submit">
                        Continuar
                    </button>
                </form>
            <?php endif; ?>
        </section>
    </main>

    <div class="apoiodiv">
        <?php include('assets/componentes/apoio.php'); ?>
    </div>

    <?php if ($metodo === 'pix'): ?>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

        <script>
            if (window.QRCode) {
                new QRCode(
                    document.getElementById('qrcode'),
                    {
                        text: <?= json_encode(
                            'AURAPET-TESTE-' . $_SESSION['qr_teste'],
                            JSON_HEX_TAG |
                            JSON_HEX_AMP |
                            JSON_HEX_APOS |
                            JSON_HEX_QUOT
                        ) ?>,
                        width: 200,
                        height: 200
                    }
                );
            } else {
                document.getElementById('qrcode').textContent =
                    'Não foi possível carregar o QR Code. Verifique a internet.';
            }
        </script>
    <?php endif; ?>
</body>

</html>