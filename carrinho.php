<?php

require_once 'assets/componentes/sessao.php';
require 'assets/componentes/dados-produtos.php';

function escapar($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('Não foi possível validar a solicitação. Atualize a página e tente novamente.');
    }

    $acao = $_POST['acao'] ?? '';
    $id = $_POST['id'] ?? '';

    if (
        is_string($acao) &&
        is_string($id) &&
        isset($produtos[$id])
    ) {
        if ($acao === 'adicionar' || $acao === 'atualizar') {
            $quantidade = filter_var(
                $_POST['quantidade'] ?? null,
                FILTER_VALIDATE_INT
            );

            if ($quantidade !== false && $quantidade >= 1 && $quantidade <= 99) {
                if ($acao === 'adicionar') {
                    $atual = (int) ($_SESSION['carrinho'][$id] ?? 0);
                    $_SESSION['carrinho'][$id] = min(99, $atual + $quantidade);
                } elseif (isset($_SESSION['carrinho'][$id])) {
                    $_SESSION['carrinho'][$id] = $quantidade;
                }
            }
        }

        if ($acao === 'remover') {
            unset($_SESSION['carrinho'][$id]);
        }
    }

    header('Location: carrinho.php');
    exit;
}

$itensCarrinho = [];
$total = 0;

foreach ($_SESSION['carrinho'] as $id => $quantidade) {
    if (!isset($produtos[$id])) {
        unset($_SESSION['carrinho'][$id]);
        continue;
    }

    $quantidade = (int) $quantidade;
    $produto = $produtos[$id];
    $subtotal = $produto['preco'] * $quantidade;

    $itensCarrinho[] = [
        'id' => $id,
        'produto' => $produto,
        'quantidade' => $quantidade,
        'subtotal' => $subtotal
    ];

    $total += $subtotal;
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho | AuraPet</title>

    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/carrinho.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include('assets/componentes/header.php'); ?>

    <main class="pagina-carrinho">
        <div class="titulo-carrinho">
            <i class="fa-solid fa-cart-shopping"></i>

            <div>
                <h1>Meu carrinho</h1>
                <p>Confira os produtos escolhidos para seu pet.</p>
            </div>
        </div>

        <?php if (empty($itensCarrinho)): ?>
            <div class="carrinho-vazio">
                <i class="fa-solid fa-bag-shopping"></i>

                <h2>Seu carrinho está vazio</h2>
                <p>Que tal encontrar algo especial para seu pet?</p>

                <a href="produtos.php" class="btn-carrinho-principal">
                    Ver produtos
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        <?php else: ?>
            <div class="carrinho-conteudo">
                <section class="lista-carrinho">
                    <?php foreach ($itensCarrinho as $item): ?>
                        <article class="item-carrinho">
                            <a
                                href="conteudo.php?id=<?= urlencode($item['id']) ?>"
                                class="item-imagem"
                            >
                                <img
                                    src="assets/images/<?= escapar($item['produto']['imagem']) ?>"
                                    alt="<?= escapar($item['produto']['nome']) ?>"
                                >
                            </a>

                            <div class="item-informacoes">
                                <a
                                    href="conteudo.php?id=<?= urlencode($item['id']) ?>"
                                    class="item-nome"
                                >
                                    <?= escapar($item['produto']['nome']) ?>
                                </a>

                                <p class="item-marca">
                                    Marca: <?= escapar($item['produto']['marca']) ?>
                                </p>

                                <p class="item-preco">
                                    R$ <?= number_format($item['produto']['preco'], 2, ',', '.') ?>
                                    <span>por unidade</span>
                                </p>

                                <div class="item-acoes">
                                    <form method="post" class="form-quantidade">
                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= escapar($_SESSION['csrf_token']) ?>"
                                        >

                                        <input type="hidden" name="acao" value="atualizar">

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= escapar($item['id']) ?>"
                                        >

                                        <label>
                                            Quantidade

                                            <input
                                                type="number"
                                                name="quantidade"
                                                min="1"
                                                max="99"
                                                value="<?= $item['quantidade'] ?>"
                                                required
                                            >
                                        </label>

                                        <button type="submit" class="btn-atualizar">
                                            Atualizar
                                        </button>
                                    </form>

                                    <form method="post">
                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= escapar($_SESSION['csrf_token']) ?>"
                                        >

                                        <input type="hidden" name="acao" value="remover">

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= escapar($item['id']) ?>"
                                        >

                                        <button type="submit" class="btn-remover">
                                            <i class="fa-regular fa-trash-can"></i>
                                            Remover
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="item-subtotal">
                                <span>Subtotal</span>

                                <strong>
                                    R$ <?= number_format($item['subtotal'], 2, ',', '.') ?>
                                </strong>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>

                <aside class="resumo-carrinho">
                    <h2>Resumo</h2>

                    <div class="resumo-linha">
                        <span>Total dos produtos</span>

                        <strong>
                            R$ <?= number_format($total, 2, ',', '.') ?>
                        </strong>
                    </div>

                    <p class="resumo-aviso">
                        Frete e pagamento ainda não estão disponíveis.
                    </p>

                    <a href="finalizar.php" class="btn-carrinho-principal">
                        Finalizar compra
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                    <a href="produtos.php" class="continuar-comprando">
                        Continuar comprando
                    </a>
                </aside>
            </div>
        <?php endif; ?>
    </main>

    <?php include('assets/componentes/footer.php'); ?>

    <div class="apoiodiv">
        <?php include('assets/componentes/apoio.php'); ?>
    </div>
</body>

</html>