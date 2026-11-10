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
                src="assets/images/p2.png" alt="" class="pata-decorativa">

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
    </main>

    <div class="apoiodiv">
        <?php include('assets/componentes/apoio.php'); ?>
    </div>
</body>
</html>