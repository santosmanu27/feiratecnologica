<?php

require_once 'assets/componentes/sessao.php';

if (empty($_SESSION['carrinho'])) {
    header('Location: carrinho.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar compra | AuraPet</title>

    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/carrinho.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include('assets/componentes/header.php'); ?>

    <main class="pagina-finalizar">
        <div class="finalizar-icone">
            <i class="fa-regular fa-user"></i>
        </div>

        <h1>Entre ou crie sua conta</h1>

        <p>
            Para continuar com a compra, você precisará entrar na sua conta
            ou fazer um cadastro.
        </p>

        <div class="finalizar-botoes">
            <button type="button" disabled>Entrar</button>
            <button type="button" disabled>Criar conta</button>
        </div>

        <p class="finalizar-aviso">
            O sistema de contas e a finalização da compra nn ta pronto ainda
        </p>

        <a href="carrinho.php" class="continuar-comprando">
            <i class="fa-solid fa-arrow-left"></i>
            Voltar ao carrinho
        </a>
    </main>

    <div class="apoiodiv">
        <?php include('assets/componentes/apoio.php'); ?>
    </div>
</body>

</html>