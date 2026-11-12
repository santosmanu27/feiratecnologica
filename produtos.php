<?php
require_once 'assets/componentes/sessao.php';
require 'assets/componentes/dados-produtos.php';

function escapar($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos | AuraPet</title>

    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/produtos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include('assets/componentes/header.php'); ?>

    <main>
        <section class="inicio-produtos">
            <div class="titulo-produtos">
                <img src="assets/images/pata.png" alt="">

                <div>
                    <h1>Produtos e Marcas</h1>
                    <p>Tudo o que seu pet precisa, com as melhores marcas!</p>
                </div>
            </div>

            <img
                src="assets/images/p2.png"
                alt=""
                class="pata-decorativa"
            >

            <form action="produtos.php" method="get" class="busca-produtos">
                <input
                    type="search"
                    id="busca"
                    name="busca"
                    placeholder="Pesquisar produtos ou marcas..."
                    aria-label="Pesquisar produtos ou marcas"
                >

                <button type="submit" aria-label="Pesquisar">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>
        </section>

        <section class="catalogo">
            <div class="catalogo-controles">
                <div class="filtros" aria-label="Filtrar por tipo">
                    <button type="button" class="filtro ativo" data-tipo="todos">
                        Todos
                    </button>

                    <button type="button" class="filtro" data-tipo="racao">
                        Ração
                    </button>

                    <button type="button" class="filtro" data-tipo="brinquedos">
                        Brinquedos
                    </button>

                    <button type="button" class="filtro" data-tipo="higiene">
                        Higiene
                    </button>

                    <button type="button" class="filtro" data-tipo="acessorios">
                        Acessórios
                    </button>
                </div>

                <label class="ordenacao">
                    Ordenar por:

                    <select id="ordenar">
                        <option value="nome">Nome</option>
                        <option value="menor-preco">Menor preço</option>
                        <option value="maior-preco">Maior preço</option>
                    </select>
                </label>
            </div>

            <div class="catalogo-conteudo">
                <aside class="categorias-produtos">
                    <h2>Categorias</h2>

                    <button type="button" class="categoria-filtro" data-categoria="caes">
                        <i class="fa-solid fa-paw"></i>
                        Cães
                    </button>

                    <button type="button" class="categoria-filtro" data-categoria="gatos">
                        <i class="fa-solid fa-paw"></i>
                        Gatos
                    </button>

                    <button type="button" class="categoria-filtro" data-categoria="higiene">
                        <i class="fa-solid fa-paw"></i>
                        Higiene
                    </button>

                    <button type="button" class="categoria-filtro" data-categoria="brinquedos">
                        <i class="fa-solid fa-paw"></i>
                        Brinquedos
                    </button>

                    <button type="button" class="categoria-filtro" data-categoria="acessorios">
                        <i class="fa-solid fa-paw"></i>
                        Acessórios
                    </button>

                    <button type="button" class="categoria-filtro" data-categoria="nossos produtos">
                        <i class="fa-solid fa-paw"></i>
                        Nossos produtos
                    </button>

                </aside>

                <div class="produtos-lista" id="produtos-lista">
                    <?php foreach ($produtos as $id => $produto): ?>
                        <article
                            class="produto-card"
                            data-nome="<?= escapar($produto['nome']) ?>"
                            data-marca="<?= escapar($produto['marca']) ?>"
                            data-preco="<?= escapar($produto['preco']) ?>"
                            data-tipo="<?= escapar($produto['tipo']) ?>"
                            data-categorias="<?= escapar($produto['categorias']) ?>"
                        >
                            <a
                                href="conteudo.php?id=<?= urlencode($id) ?>"
                                class="produto-link"
                            >
                                <div class="produto-imagem">
                                    <img
                                        src="assets/images/<?= escapar($produto['imagem']) ?>"
                                        alt="<?= escapar($produto['nome']) ?>"
                                    >
                                </div>

                                <h3><?= escapar($produto['nome']) ?></h3>
                            </a>

                            <p class="produto-preco">
                                R$ <?= number_format($produto['preco'], 2, ',', '.') ?>
                            </p>

                            <a
                                href="conteudo.php?id=<?= urlencode($id) ?>"
                                class="btn-carrinho"
                            >
                                Ver mais
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <p class="sem-produtos" id="sem-produtos" hidden>
                Nenhum produto encontrado.
            </p>
        </section>
    </main>

    <?php include('assets/componentes/footer.php'); ?>

    <div class="apoiodiv">
        <?php include('assets/componentes/apoio.php'); ?>
    </div>

    <script src="js/produtos.js" defer></script>
</body>

</html>