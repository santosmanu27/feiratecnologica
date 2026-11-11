<?php

require_once 'assets/componentes/sessao.php';
require 'assets/componentes/dados-produtos.php';

if (empty($_SESSION['usuario_id'])) {
    $_SESSION['voltar_checkout'] = true;
    header('Location: login.php');
    exit;
}

function escapar($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

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
        'produto' => $produto,
        'quantidade' => $quantidade,
        'subtotal_centavos' => $subtotalCentavos
    ];

    $totalCentavos += $subtotalCentavos;
}

if (!$itens) {
    header('Location: carrinho.php');
    exit;
}

$erro = '';
$endereco = $_SESSION['checkout_endereco'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    $campos = [
        'nome',
        'cep',
        'rua',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado'
    ];

    $dados = [];

    foreach ($campos as $campo) {
        $valor = $_POST[$campo] ?? '';
        $dados[$campo] = is_string($valor) ? trim($valor) : '';
    }

    $metodo = $_POST['metodo'] ?? '';

    $metodosValidos = [
        'cartao',
        'pix',
        'boleto',
        'loja'
    ];

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        $erro = 'Não foi possível validar a solicitação. Atualize a página.';
    } elseif (
        $dados['nome'] === '' ||
        $dados['rua'] === '' ||
        $dados['numero'] === '' ||
        $dados['bairro'] === '' ||
        $dados['cidade'] === '' ||
        !preg_match('/^[0-9]{5}-?[0-9]{3}$/', $dados['cep']) ||
        !preg_match('/^[A-Za-z]{2}$/', $dados['estado'])
    ) {
        $erro = 'Preencha o endereço corretamente, com CEP e UF válidos.';
    } elseif (
        array_filter($dados, function ($valor) {
            return strlen($valor) > 200;
        })
    ) {
        $erro = 'Um dos campos do endereço está muito longo.';
    } elseif (
        !is_string($metodo) ||
        !in_array($metodo, $metodosValidos, true)
    ) {
        $erro = 'Escolha uma forma de pagamento.';
    } else {
        $_SESSION['checkout_endereco'] = $dados;
        $_SESSION['checkout_metodo'] = $metodo;

        header('Location: pagamento-demo.php');
        exit;
    }

    $endereco = $dados;
}

$enderecoCompleto =
    !empty($endereco['nome']) &&
    !empty($endereco['cep']) &&
    !empty($endereco['rua']) &&
    !empty($endereco['numero']) &&
    !empty($endereco['bairro']) &&
    !empty($endereco['cidade']) &&
    !empty($endereco['estado']);

$metodos = [
    'cartao' => 'Cartão de crédito',
    'pix' => 'Pix',
    'boleto' => 'Boleto bancário',
    'loja' => 'Pagamento na loja'
];

$metodoEscolhido = $_POST['metodo']
    ?? $_SESSION['checkout_metodo']
    ?? 'cartao';

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Finalizar compra | AuraPet</title>

    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/checkout.css">
    <link rel="stylesheet" href="css/checkout-layout.css">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >
</head>

<body class="fundo-cadastro">
    <?php include('assets/componentes/header.php'); ?>

    <main class="checkout-pagina">
        <nav class="checkout-etapas" aria-label="Etapas da compra">
            <div class="etapa concluida">
                <span class="etapa-circulo">1</span>
                <span>Carrinho</span>
            </div>

            <div class="etapa concluida">
                <span class="etapa-circulo">2</span>
                <span>Endereço</span>
            </div>

            <div class="etapa atual" aria-current="step">
                <span class="etapa-circulo">3</span>
                <span>Pagamento</span>
            </div>

            <div class="etapa">
                <span class="etapa-circulo">
                    <i class="fa-solid fa-check"></i>
                </span>
                <span>Confirmação</span>
            </div>
        </nav>

        <?php if ($erro !== ''): ?>
            <div class="checkout-erro" role="alert">
                <?= escapar($erro) ?>
            </div>
        <?php endif; ?>

        <form method="post" class="checkout-conteudo" id="form-checkout">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= escapar($_SESSION['csrf_token']) ?>"
            >

            <div class="checkout-esquerda">
                <section class="checkout-endereco">
                    <h2>
                        <i class="fa-solid fa-location-dot"></i>
                        Endereço de entrega
                    </h2>

                    <?php if ($enderecoCompleto): ?>
                        <div class="endereco-resumo">
                            <strong>
                                <?= escapar($endereco['nome']) ?>
                            </strong>

                            <p>
                                <?= escapar($endereco['rua']) ?>,
                                <?= escapar($endereco['numero']) ?>

                                <?php if (!empty($endereco['complemento'])): ?>
                                    — <?= escapar($endereco['complemento']) ?>
                                <?php endif; ?>
                            </p>

                            <p>
                                <?= escapar($endereco['bairro']) ?> —
                                <?= escapar($endereco['cidade']) ?>,
                                <?= escapar($endereco['estado']) ?>
                            </p>

                            <p>
                                CEP: <?= escapar($endereco['cep']) ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <details
                        class="endereco-edicao"
                        id="endereco-edicao"
                        <?= !$enderecoCompleto || $erro !== '' ? 'open' : '' ?>
                    >
                        <summary>
                            <?= $enderecoCompleto
                                ? 'Alterar endereço'
                                : 'Preencher endereço' ?>
                        </summary>

                        <div class="endereco-campos">
                            <label>
                                Nome completo

                                <input
                                    type="text"
                                    name="nome"
                                    value="<?= escapar($endereco['nome'] ?? '') ?>"
                                    maxlength="120"
                                    autocomplete="name"
                                    required
                                >
                            </label>

                            <label>
                                CEP

                                <input
                                    type="text"
                                    name="cep"
                                    value="<?= escapar($endereco['cep'] ?? '') ?>"
                                    placeholder="00000-000"
                                    maxlength="9"
                                    autocomplete="postal-code"
                                    required
                                >
                            </label>

                            <label>
                                Rua

                                <input
                                    type="text"
                                    name="rua"
                                    value="<?= escapar($endereco['rua'] ?? '') ?>"
                                    maxlength="120"
                                    autocomplete="address-line1"
                                    required
                                >
                            </label>

                            <label>
                                Número

                                <input
                                    type="text"
                                    name="numero"
                                    value="<?= escapar($endereco['numero'] ?? '') ?>"
                                    maxlength="120"
                                    required
                                >
                            </label>

                            <label>
                                Complemento

                                <input
                                    type="text"
                                    name="complemento"
                                    value="<?= escapar($endereco['complemento'] ?? '') ?>"
                                    maxlength="120"
                                    autocomplete="address-line2"
                                >
                            </label>

                            <label>
                                Bairro

                                <input
                                    type="text"
                                    name="bairro"
                                    value="<?= escapar($endereco['bairro'] ?? '') ?>"
                                    maxlength="120"
                                    required
                                >
                            </label>

                            <label>
                                Cidade

                                <input
                                    type="text"
                                    name="cidade"
                                    value="<?= escapar($endereco['cidade'] ?? '') ?>"
                                    maxlength="120"
                                    autocomplete="address-level2"
                                    required
                                >
                            </label>

                            <label>
                                UF

                                <input
                                    type="text"
                                    name="estado"
                                    value="<?= escapar($endereco['estado'] ?? '') ?>"
                                    placeholder="SP"
                                    maxlength="2"
                                    autocomplete="address-level1"
                                    required
                                >
                            </label>
                        </div>
                    </details>
                </section>

                <section class="checkout-resumo">
                    <h2>
                        <i class="fa-solid fa-credit-card"></i>
                        Resumo do pedido
                    </h2>

                    <div class="resumo-produtos">
                        <?php foreach ($itens as $item): ?>
                            <article class="resumo-produto">
                                <img
                                    src="assets/images/<?= escapar($item['produto']['imagem']) ?>"
                                    alt="<?= escapar($item['produto']['nome']) ?>"
                                >

                                <div class="resumo-produto-texto">
                                    <strong>
                                        <?= escapar($item['produto']['nome']) ?>
                                    </strong>

                                    <span>
                                        Qtd: <?= $item['quantidade'] ?>
                                    </span>
                                </div>

                                <strong class="resumo-produto-preco">
                                    R$ <?= number_format(
                                        $item['subtotal_centavos'] / 100,
                                        2,
                                        ',',
                                        '.'
                                    ) ?>
                                </strong>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div class="resumo-valores">
                        <div>
                            <span>Subtotal</span>

                            <span>
                                R$ <?= number_format(
                                    $totalCentavos / 100,
                                    2,
                                    ',',
                                    '.'
                                ) ?>
                            </span>
                        </div>

                        <div>
                            <span>Frete</span>
                            <span>Não calculado</span>
                        </div>

                        <div class="resumo-total">
                            <strong>Total dos produtos</strong>

                            <strong>
                                R$ <?= number_format(
                                    $totalCentavos / 100,
                                    2,
                                    ',',
                                    '.'
                                ) ?>
                            </strong>
                        </div>
                    </div>
                </section>
            </div>

            <section class="checkout-pagamento">
                <h2>
                    <i class="fa-solid fa-credit-card"></i>
                    Forma de pagamento
                </h2>

                <div class="pagamento-opcoes">
                    <?php foreach ($metodos as $valor => $nome): ?>
                        <label class="pagamento-opcao">
                            <input
                                type="radio"
                                name="metodo"
                                value="<?= escapar($valor) ?>"
                                <?= $metodoEscolhido === $valor ? 'checked' : '' ?>
                                required
                            >

                            <span class="pagamento-opcao-conteudo">
                                <strong><?= escapar($nome) ?></strong>

                                <?php if ($valor === 'cartao'): ?>
                                    <span class="pagamento-bandeiras">
                                        <img
                                            src="assets/images/visa.png"
                                            alt="Visa"
                                        >

                                        <img
                                            src="assets/images/mastercard.webp"
                                            alt="Mastercard"
                                        >

                                        <img
                                            src="assets/images/elo.png"
                                            alt="Elo"
                                        >

                                        <img
                                            src="assets/images/amex.png"
                                            alt="American Express"
                                        >
                                    </span>

                                <?php elseif ($valor === 'pix'): ?>
                                    <img
                                        src="assets/images/pix.png"
                                        alt="Pix"
                                        class="pagamento-logo"
                                    >

                                <?php elseif ($valor === 'boleto'): ?>
                                    <i class="fa-solid fa-barcode pagamento-icone"></i>

                                <?php else: ?>
                                    <i class="fa-solid fa-store pagamento-icone"></i>
                                <?php endif; ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="pagamento-rodape">
                    <button type="submit" class="checkout-botao">
                        Finalizar compra
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </section>
        </form>
    </main>

    <?php include('assets/componentes/footer.php'); ?>

    <div class="apoiodiv">
        <?php include('assets/componentes/apoio.php'); ?>
    </div>

    <script>
        const formulario = document.querySelector('#form-checkout');
        const enderecoEdicao = document.querySelector('#endereco-edicao');

        formulario.addEventListener('invalid', function (evento) {
            if (enderecoEdicao.contains(evento.target)) {
                enderecoEdicao.open = true;
            }
        }, true);
    </script>
</body>

</html>