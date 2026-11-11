<?php
require_once 'assets/componentes/sessao.php';
require 'assets/componentes/dados-produtos.php';

function escapar($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

$id = $_GET['id'] ?? '';

if (!is_string($id) || !isset($produtos[$id])) {
    http_response_code(404);
    $produto = null;
} else {
    $produto = $produtos[$id];
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= $produto ? escapar($produto['nome']) : 'Produto não encontrado' ?>
        | AuraPet
    </title>

    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/conteudo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include('assets/componentes/header.php'); ?>

    <main class="pagina-produto">
        <?php if ($produto): ?>
            <?php
            $imagens = [$produto['imagem']];

            if (!empty($produto['imagens_extras']) && is_array($produto['imagens_extras'])) {
                $imagens = array_merge($imagens, $produto['imagens_extras']);
            }
            ?>

            <section class="detalhe-produto">
                <div class="galeria-produto">
                    <div class="imagem-principal">
                        <img
                            id="imagem-principal"
                            src="assets/images/<?= escapar($produto['imagem']) ?>"
                            alt="<?= escapar($produto['nome']) ?>">
                    </div>

                    <div class="miniaturas">
                        <?php foreach ($imagens as $indice => $imagem): ?>
                            <button
                                type="button"
                                class="miniatura <?= $indice === 0 ? 'ativa' : '' ?>"
                                data-imagem="assets/images/<?= escapar($imagem) ?>"
                                aria-label="Ver imagem <?= $indice + 1 ?>">
                                <img
                                    src="assets/images/<?= escapar($imagem) ?>"
                                    alt="">
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="informacoes-produto">
                    <h1><?= escapar($produto['nome']) ?></h1>

                    <p class="marca-produto">
                        Marca: <?= escapar($produto['marca']) ?>
                    </p>

                    <p class="preco-produto">
                        R$ <?= number_format($produto['preco'], 2, ',', '.') ?>
                    </p>

                    <p class="descricao-produto">
                        <?= escapar($produto['descricao']) ?>
                    </p>

                    <form action="carrinho.php" method="post" class="acoes-produto">
                        <input type="hidden" name="acao" value="adicionar">

                        <input
                            type="hidden"
                            name="id"
                            value="<?= escapar($id) ?>">

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= escapar($_SESSION['csrf_token']) ?>">

                        <div class="seletor-quantidade">
                            <button
                                type="button"
                                id="diminuir"
                                aria-label="Diminuir quantidade">
                                −
                            </button>

                            <input
                                type="number"
                                id="quantidade"
                                name="quantidade"
                                value="1"
                                min="1"
                                max="99"
                                required
                                aria-label="Quantidade">

                            <button
                                type="button"
                                id="aumentar"
                                aria-label="Aumentar quantidade">
                                +
                            </button>
                        </div>

                        <button type="submit" class="adicionar-carrinho">
                            Adicionar ao carrinho
                        </button>
                    </form>

                    <a href="produtos.php" class="voltar-produtos">
                        <i class="fa-solid fa-arrow-left"></i>
                        Voltar aos produtos
                    </a>

                    <img
                        src="assets/images/p2.png"
                        alt=""
                        class="pata-produto">
                </div>
            </section>
        <?php else: ?>
            <div class="produto-nao-encontrado">
                <h1>Produto não encontrado</h1>
                <a href="produtos.php">Voltar aos produtos</a>
            </div>
        <?php endif; ?>
    </main>

    <?php include('assets/componentes/footer.php'); ?>

    <div class="apoiodiv">
        <?php include('assets/componentes/apoio.php'); ?>
    </div>

    <script src="js/conteudo.js" defer></script>
</body>

</html>