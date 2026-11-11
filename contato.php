<?php require_once 'assets/componentes/sessao.php'; ?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contato | AuraPet</title>

    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/contato.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include('assets/componentes/header.php'); ?>

    <main class="pagina-contato">
        <div class="titulo-contato">
            <img src="assets/images/pata.png" alt="">

            <div>
                <h1>Fale com a AuraPet</h1>
                <p>Estamos aqui para ajudar você e seu pet!</p>
            </div>
        </div>

        <div class="contato-conteudo">
            <section class="informacoes-contato">
                <h2>Entre em contato</h2>

                <p class="contato-introducao">
                    Ficou com alguma dúvida? Entre em contato pelos nossos canais
                    ou fale com o assistente no canto da tela.
                </p>

                <div class="contato-item">
                    <div class="contato-icone">
                        <i class="fa-solid fa-phone"></i>
                    </div>

                    <div>
                        <h3>Telefone</h3>
                        <p>(18) 12345-6789</p>
                    </div>
                </div>

                <div class="contato-item">
                    <div class="contato-icone">
                        <i class="fa-regular fa-envelope"></i>
                    </div>

                    <div>
                        <h3>E-mail</h3>
                        <p>Aurapet24@gmail.com</p>
                    </div>
                </div>

                <div class="contato-item">
                    <div class="contato-icone">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>

                    <div>
                        <h3>Endereço</h3>
                        <p>Rua não sei oq lá numero 9</p>
                    </div>
                </div>

                <div class="contato-item">
                    <div class="contato-icone">
                        <i class="fa-regular fa-clock"></i>
                    </div>

                    <div>
                        <h3>Horário de atendimento</h3>
                        <p>8:00 - 18:00</p>
                    </div>
                </div>
            </section>

            <section class="contato-destaque">
                <img
                    src="assets/images/cachorroegato.png"
                    alt="Pet da AuraPet"
                    class="imagem-contato"
                >

                <h2>Seu pet merece todo carinho!</h2>

                <p>
                    Conheça nossos produtos ou escolha um horário de atendimento.
                </p>

                <div class="contato-botoes">
                    <a href="produtos.php" class="btn-contato">
                        Ver produtos
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                    <a href="atendimento.php" class="btn-contato btn-secundario">
                        Atendimento
                        <i class="fa-regular fa-calendar-days"></i>
                    </a>
                </div>
            </section>
        </div>
    </main>

    <div class="apoiodiv">
        <?php include('assets/componentes/apoio.php'); ?>
    </div>
</body>

</html>