<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Início</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <section class="primeiro">
        <?php include('assets/componentes/header.php'); ?>

        <div class="animais">
            <img src="assets/images/inicial.png" alt="Cachorro e gato">
        </div>

        <div class="texto">
            <div class="tit">
                <h1>
                    Tudo o que seu<br>
                    pet precisa,<br>
                    em um só lugar!
                </h1>
            </div>

            <div class="paragrafo">
                <p>
                    Produtos, marcas e atendimento<br>
                    com muito carinho
                    <i class="fa-regular fa-heart"></i>
                </p>
            </div>

            <a href="produtos.php" class="btn-produtos">
                Ver produtos
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </section>

    <section class="beneficios">
        <div class="beneficio">
            <div class="beneficio-icone">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>

            <p>
                Produtos das<br>
                melhores marcas
            </p>
        </div>

        <div class="beneficio">
            <div class="beneficio-icone">
                <i class="fa-regular fa-calendar-days"></i>
            </div>

            <p>
                Agende um<br>
                atendimento
            </p>
        </div>

        <div class="beneficio">
            <div class="beneficio-icone">
                <i class="fa-regular fa-heart"></i>
            </div>

            <p>
                Cuidado e amor<br>
                em cada detalhe
            </p>
        </div>
    </section>

    <section class="dois">
        <div class="categorias">
            <div class="categorias-titulo">
                <img src="assets/images/pata.png" alt="">
                <h2>Categorias</h2>
            </div>

            <div class="categorias-lista">
                <a href="produtos.php?categoria=caes" class="categoria">
                    <div class="categoria-imagem">
                        <img src="assets/images/dog.png" alt="Cães">
                    </div>
                    <div class="categoria-nome">
                        Cães
                    </div>
                </a>

                <a href="produtos.php?categoria=gatos" class="categoria">
                    <div class="categoria-imagem">
                        <img src="assets/images/gato.png" alt="Gatos">
                    </div>
                    <div class="categoria-nome">
                        Gatos
                    </div>
                </a>

                <a href="produtos.php?categoria=higiene" class="categoria">
                    <div class="categoria-imagem">
                        <img src="assets/images/sr.png" alt="Produtos de higiene">
                    </div>
                    <div class="categoria-nome">
                        Higiene
                    </div>
                </a>

                <a href="produtos.php?categoria=brinquedos" class="categoria">
                    <div class="categoria-imagem">
                        <img src="assets/images/br.png" alt="Brinquedos para pets">
                    </div>
                    <div class="categoria-nome">
                        Brinquedos
                    </div>
                </a>
            </div>
        </div>
    </section>
    <div class="novidades">
        <div class="novidades-texto">
            <h2>Novidades na AuraPet!</h2>
            <p>Confira nossas ofertas especiais ♡</p>
        </div>

        <a href="produtos.php" class="btn-ofertas">
            Ver ofertas
            <i class="fa-solid fa-arrow-right"></i>
        </a>

        <div class="novidades-imagem">
            <img src="assets/images/gatin-removebg-preview.png" alt="Animais da AuraPet">
        </div>
    </div>

    <div class="apoiodiv">
        <?php include('assets/componentes/apoio.php'); ?>
    </div>
</body>

</html>