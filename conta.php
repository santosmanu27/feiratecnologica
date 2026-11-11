<?php

require_once 'assets/componentes/sessao.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require 'assets/componentes/conexao.php';

function escapar($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

$consulta = $pdo->prepare(
    'SELECT id, nome, email, telefone, bio, foto, criado_em
     FROM usuarios
     WHERE id = ?
     LIMIT 1'
);

$consulta->execute([
    $_SESSION['usuario_id']
]);

$usuario = $consulta->fetch();

if (!$usuario) {
    unset($_SESSION['usuario_id']);
    header('Location: login.php');
    exit;
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    $nome = trim((string) ($_POST['nome'] ?? ''));
    $telefone = trim((string) ($_POST['telefone'] ?? ''));
    $bio = trim((string) ($_POST['bio'] ?? ''));

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        $erro = 'Solicitação inválida. Atualize a página.';
    } elseif ($nome === '' || mb_strlen($nome) > 100) {
        $erro = 'Informe um nome com até 100 caracteres.';
    } elseif ($telefone === '' || mb_strlen($telefone) > 25) {
        $erro = 'Informe um telefone com até 25 caracteres.';
    } elseif (mb_strlen($bio) > 500) {
        $erro = 'A bio deve ter no máximo 500 caracteres.';
    } else {
        $fotoAtual = $usuario['foto'];
        $novaFoto = null;
        $arquivo = $_FILES['foto'] ?? null;

        if (
            $arquivo &&
            $arquivo['error'] !== UPLOAD_ERR_NO_FILE
        ) {
            if (
                $arquivo['error'] !== UPLOAD_ERR_OK ||
                $arquivo['size'] > 2 * 1024 * 1024
            ) {
                $erro = 'Não foi possível enviar a foto. Limite: 2 MB.';
            } else {
                $tiposPermitidos = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp'
                ];

                $verificador = new finfo(FILEINFO_MIME_TYPE);
                $tipo = $verificador->file($arquivo['tmp_name']);
                $imagemValida = @getimagesize($arquivo['tmp_name']);

                if (
                    !isset($tiposPermitidos[$tipo]) ||
                    $imagemValida === false
                ) {
                    $erro = 'Escolha uma imagem JPG, PNG ou WebP válida.';
                } else {
                    $novaFoto = bin2hex(random_bytes(16))
                        . '.'
                        . $tiposPermitidos[$tipo];

                    $destino = __DIR__ . '/contas/perfis/' . $novaFoto;

                    if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
                        $erro = 'Não foi possível salvar a foto.';
                        $novaFoto = null;
                    }
                }
            }
        }

        if ($erro === '') {
            try {
                $atualizar = $pdo->prepare(
                    'UPDATE usuarios
                     SET nome = ?,
                         telefone = ?,
                         bio = ?,
                         foto = ?
                     WHERE id = ?'
                );

                $atualizar->execute([
                    $nome,
                    $telefone,
                    $bio,
                    $novaFoto ?: $fotoAtual,
                    $usuario['id']
                ]);

                if (
                    $novaFoto &&
                    $fotoAtual &&
                    preg_match(
                        '/^[a-f0-9]{32}\.(jpg|png|webp)$/',
                        $fotoAtual
                    )
                ) {
                    $fotoAntiga = __DIR__ . '/contas/perfis/' . $fotoAtual;

                    if (is_file($fotoAntiga)) {
                        unlink($fotoAntiga);
                    }
                }

                $_SESSION['perfil_atualizado'] = true;

                header('Location: conta.php');
                exit;
            } catch (PDOException $excecao) {
                if ($novaFoto) {
                    $arquivoNovo = __DIR__ . '/contas/perfis/' . $novaFoto;

                    if (is_file($arquivoNovo)) {
                        unlink($arquivoNovo);
                    }
                }

                error_log(
                    'Erro ao atualizar perfil: ' . $excecao->getMessage()
                );

                $erro = 'Não foi possível atualizar o perfil.';
            }
        }

        if ($erro !== '') {
            $usuario['nome'] = $nome;
            $usuario['telefone'] = $telefone;
            $usuario['bio'] = $bio;
        }
    }
}

if (!empty($_SESSION['perfil_atualizado'])) {
    $sucesso = 'Seu perfil foi atualizado com sucesso!';
    unset($_SESSION['perfil_atualizado']);
}

if (!empty($_SESSION['cartao_erro'])) {
    $erro = $_SESSION['cartao_erro'];
    unset($_SESSION['cartao_erro']);
}

$fotoPerfil = null;

if (
    !empty($usuario['foto']) &&
    preg_match(
        '/^[a-f0-9]{32}\.(jpg|png|webp)$/',
        $usuario['foto']
    )
) {
    $fotoPerfil = 'contas/perfis/' . $usuario['foto'];
}

$consultaCartoes = $pdo->prepare(
    'SELECT id, nome, bandeira, ultimos4, validade
     FROM cartoes_teste
     WHERE usuario_id = ?
     ORDER BY id DESC'
);

$consultaCartoes->execute([
    $usuario['id']
]);

$cartoes = $consultaCartoes->fetchAll();

$consultaPedidos = $pdo->prepare(
    'SELECT id, itens_json, metodo, total, criado_em
     FROM pedidos_simulados
     WHERE usuario_id = ?
     ORDER BY id DESC'
);

$consultaPedidos->execute([
    $usuario['id']
]);

$pedidos = $consultaPedidos->fetchAll();

$metodos = [
    'cartao' => 'Cartão de crédito',
    'pix' => 'Pix',
    'boleto' => 'Boleto bancário',
    'loja' => 'Pagamento na loja'
];

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Minha conta | AuraPet</title>

    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/logado.css">
    <link rel="stylesheet" href="css/cartoes-teste.css">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >
</head>

<body>
    <?php include('assets/componentes/header.php'); ?>

    <main class="perfil-pagina">
        <div class="perfil-titulo">
            <div>
                <p class="perfil-boas-vindas">
                    Bem-vindo à sua conta
                </p>

                <h1>
                    Meu perfil
                    <i class="fa-solid fa-paw"></i>
                </h1>

                <p>
                    Cuide dos seus dados e acompanhe suas compras.
                </p>
            </div>
        </div>

        <?php if ($erro !== ''): ?>
            <div class="perfil-mensagem erro" role="alert">
                <?= escapar($erro) ?>
            </div>
        <?php endif; ?>

        <?php if ($sucesso !== ''): ?>
            <div class="perfil-mensagem sucesso" role="status">
                <?= escapar($sucesso) ?>
            </div>
        <?php endif; ?>

        <div class="perfil-layout">
            <aside class="perfil-lateral">
                <div class="perfil-avatar">
                    <?php if ($fotoPerfil): ?>
                        <img
                            src="<?= escapar($fotoPerfil) ?>"
                            alt="Foto de <?= escapar($usuario['nome']) ?>"
                        >
                    <?php else: ?>
                        <i class="fa-solid fa-user"></i>
                    <?php endif; ?>
                </div>

                <h2><?= escapar($usuario['nome']) ?></h2>

                <p class="perfil-email">
                    <?= escapar($usuario['email']) ?>
                </p>

                <p class="perfil-bio">
                    <?php if ($usuario['bio'] !== ''): ?>
                        <?= nl2br(escapar($usuario['bio'])) ?>
                    <?php else: ?>
                        Adicione uma bio para deixar seu perfil com a sua cara.
                    <?php endif; ?>
                </p>

                <nav class="perfil-menu" aria-label="Menu do perfil">
                    <a href="#editar">
                        <i class="fa-solid fa-user-pen"></i>
                        Editar perfil
                    </a>

                    <a href="#cartoes">
                        <i class="fa-regular fa-credit-card"></i>
                        Meus cartões
                    </a>

                    <a href="#pedidos">
                        <i class="fa-solid fa-box"></i>
                        Meus pedidos
                    </a>

                    <a href="#pagamentos">
                        <i class="fa-regular fa-credit-card"></i>
                        Pagamentos
                    </a>
                </nav>

                <form action="sair.php" method="post">
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= escapar($_SESSION['csrf_token']) ?>"
                    >

                    <button type="submit" class="perfil-sair">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        Sair da conta
                    </button>
                </form>
            </aside>

            <div class="perfil-principal">
                <section class="perfil-card" id="editar">
                    <div class="card-cabecalho">
                        <div class="card-icone">
                            <i class="fa-solid fa-user-pen"></i>
                        </div>

                        <div>
                            <h2>Editar perfil</h2>
                            <p>Atualize suas informações e sua foto.</p>
                        </div>
                    </div>

                    <form
                        method="post"
                        enctype="multipart/form-data"
                        class="perfil-form"
                    >
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= escapar($_SESSION['csrf_token']) ?>"
                        >

                        <div class="perfil-campos">
                            <label>
                                Nome completo

                                <input
                                    type="text"
                                    name="nome"
                                    value="<?= escapar($usuario['nome']) ?>"
                                    maxlength="100"
                                    autocomplete="name"
                                    required
                                >
                            </label>

                            <label>
                                Telefone

                                <input
                                    type="tel"
                                    name="telefone"
                                    value="<?= escapar($usuario['telefone']) ?>"
                                    maxlength="25"
                                    autocomplete="tel"
                                    required
                                >
                            </label>
                        </div>

                        <label>
                            E-mail

                            <input
                                type="email"
                                value="<?= escapar($usuario['email']) ?>"
                                disabled
                            >

                            <span class="campo-ajuda">
                                A edição do e-mail ainda não está disponível.
                            </span>
                        </label>

                        <label>
                            Sua bio

                            <textarea
                                name="bio"
                                rows="4"
                                maxlength="500"
                                placeholder="Conte um pouquinho sobre você e seus pets..."
                            ><?= escapar($usuario['bio']) ?></textarea>
                        </label>

                        <label class="campo-foto">
                            Foto de perfil

                            <input
                                type="file"
                                name="foto"
                                accept="image/jpeg,image/png,image/webp"
                            >

                            <span class="campo-ajuda">
                                JPG, PNG ou WebP, com até 2 MB.
                            </span>
                        </label>

                        <button type="submit" class="perfil-botao">
                            Salvar alterações
                            <i class="fa-solid fa-check"></i>
                        </button>
                    </form>
                </section>

                <section class="perfil-card" id="cartoes">
                    <div class="card-cabecalho">
                        <div class="card-icone">
                            <i class="fa-regular fa-credit-card"></i>
                        </div>

                        <div>
                            <h2>Meus cartões</h2>
                        </div>
                    </div>

                    <?php if (empty($cartoes)): ?>
                        <div class="estado-vazio">
                            <p>Você ainda não cadastrou nenhum cartão.</p>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($cartoes as $cartao): ?>
                        <details class="cartao-salvo">
                            <summary>
                                <?= escapar($cartao['bandeira']) ?>
                                •••• <?= escapar($cartao['ultimos4']) ?>
                                — <?= escapar($cartao['nome']) ?>
                            </summary>

                            <form
                                action="cartoes.php"
                                method="post"
                                class="form-cartao-teste"
                            >
                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= escapar($_SESSION['csrf_token']) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="acao"
                                    value="salvar"
                                >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $cartao['id'] ?>"
                                >

                                <label>
                                    Titular

                                    <input
                                        type="text"
                                        name="nome"
                                        maxlength="100"
                                        value="<?= escapar($cartao['nome']) ?>"
                                        required
                                    >
                                </label>

                                <label>
                                    Bandeira

                                    <select name="bandeira">
                                        <?php
                                            $bandeiras = [
                                                'Visa',
                                                'Mastercard',
                                                'Elo',
                                                'Outro'
                                            ];
                                        ?>

                                        <?php foreach ($bandeiras as $bandeira): ?>
                                            <option
                                                value="<?= escapar($bandeira) ?>"
                                                <?= $bandeira === $cartao['bandeira'] ? 'selected' : '' ?>
                                            >
                                                <?= escapar($bandeira) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>

                                <label>
                                    Quatro últimos dígitos

                                    <input
                                        type="text"
                                        name="ultimos4"
                                        value="<?= escapar($cartao['ultimos4']) ?>"
                                        pattern="[0-9]{4}"
                                        maxlength="4"
                                        required
                                    >
                                </label>

                                <label>
                                    Validade

                                    <input
                                        type="text"
                                        name="validade"
                                        value="<?= escapar($cartao['validade']) ?>"
                                        pattern="(0[1-9]|1[0-2])/20[0-9]{2}"
                                        maxlength="7"
                                        placeholder="MM/AAAA"
                                        required
                                    >
                                </label>

                                <button type="submit" class="perfil-botao">
                                    Salvar alterações
                                </button>
                            </form>

                            <form action="cartoes.php" method="post">
                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= escapar($_SESSION['csrf_token']) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="acao"
                                    value="remover"
                                >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $cartao['id'] ?>"
                                >

                                <button type="submit" class="perfil-sair">
                                    Remover cartão
                                </button>
                            </form>
                        </details>
                    <?php endforeach; ?>

                    <details>
                        <summary>Adicionar cartão</summary>

                        <?php
                            $retornoCartao = 'perfil';
                            include('form-cartao-teste.php');
                        ?>
                    </details>
                </section>

                <section class="perfil-card" id="pedidos">
                    <div class="card-cabecalho">
                        <div class="card-icone">
                            <i class="fa-solid fa-box"></i>
                        </div>

                        <div>
                            <h2>Meus pedidos</h2>
                            <p>Acompanhe seus pedidos.</p>
                        </div>
                    </div>

                    <?php if (empty($pedidos)): ?>
                        <div class="estado-vazio">
                            <i class="fa-solid fa-box-open"></i>

                            <h3>Você ainda não possui pedidos.</h3>

                            <a href="produtos.php" class="perfil-botao">
                                Ver produtos
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($pedidos as $pedido): ?>
                        <?php
                            $itensPedido = json_decode(
                                $pedido['itens_json'],
                                true
                            );

                            if (!is_array($itensPedido)) {
                                $itensPedido = [];
                            }
                        ?>

                        <article class="cartao-salvo">
                            <h3>
                                Pedido #<?= (int) $pedido['id'] ?>
                            </h3>

                            <p>
                                <?= date(
                                    'd/m/Y H:i',
                                    strtotime($pedido['criado_em'])
                                ) ?>
                            </p>

                            <?php foreach ($itensPedido as $item): ?>
                                <p>
                                    <?= escapar($item['nome'] ?? 'Produto') ?>
                                    — Qtd:
                                    <?= (int) ($item['quantidade'] ?? 0) ?>
                                </p>
                            <?php endforeach; ?>

                            <strong>
                                Total: R$
                                <?= number_format(
                                    (float) $pedido['total'],
                                    2,
                                    ',',
                                    '.'
                                ) ?>
                            </strong>
                        </article>
                    <?php endforeach; ?>
                </section>

                <section class="perfil-card" id="pagamentos">
                    <div class="card-cabecalho">
                        <div class="card-icone">
                            <i class="fa-regular fa-credit-card"></i>
                        </div>

                        <div>
                            <h2>Pagamentos</h2>
                            <p>
                                Formas selecionadas nos pedidos.
                            </p>
                        </div>
                    </div>

                    <?php if (empty($pedidos)): ?>
                        <div class="estado-vazio">
                            <i class="fa-regular fa-credit-card"></i>
                            <p>Nenhum pagamento registrado.</p>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($pedidos as $pedido): ?>
                        <article class="cartao-salvo">
                            <strong>
                                Pedido #<?= (int) $pedido['id'] ?>
                            </strong>

                            <p>
                                <?= escapar(
                                    $metodos[$pedido['metodo']]
                                    ?? 'Não informado'
                                ) ?>

                                — R$
                                <?= number_format(
                                    (float) $pedido['total'],
                                    2,
                                    ',',
                                    '.'
                                ) ?>
                            </p>
                        </article>
                    <?php endforeach; ?>
                </section>
            </div>
        </div>
    </main>

    <?php include('assets/componentes/footer.php'); ?>

    <div class="apoiodiv">
        <?php include('assets/componentes/apoio.php'); ?>
    </div>
</body>

</html>