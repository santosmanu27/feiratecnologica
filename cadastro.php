<?php

require_once 'assets/componentes/sessao.php';

if (!empty($_SESSION['usuario_id'])) {
    if (!empty($_SESSION['voltar_checkout'])) {
        unset($_SESSION['voltar_checkout']);
        header('Location: checkout.php');
    } else {
        header('Location: conta.php');
    }

    exit;
}

function escapar($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

$erro = '';
$nome = '';
$email = '';
$telefone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    $nome = trim((string) ($_POST['nome'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $telefone = trim((string) ($_POST['telefone'] ?? ''));
    $senha = (string) ($_POST['senha'] ?? '');
    $confirmarSenha = (string) ($_POST['confirmar_senha'] ?? '');

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        $erro = 'Não foi possível validar a solicitação. Atualize a página e tente novamente.';
    } elseif (
        $nome === '' ||
        strlen($nome) > 100 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 190 ||
        $telefone === '' ||
        strlen($telefone) > 25
    ) {
        $erro = 'Preencha nome, e-mail e telefone corretamente.';
    } elseif (strlen($senha) < 8 || strlen($senha) > 72) {
        $erro = 'A senha deve ter entre 8 e 72 caracteres.';
    } elseif ($senha !== $confirmarSenha) {
        $erro = 'As senhas não coincidem.';
    } else {
        require 'assets/componentes/conexao.php';

        try {
            $consulta = $pdo->prepare(
                'INSERT INTO usuarios (nome, email, telefone, senha)
                 VALUES (:nome, :email, :telefone, :senha)'
            );

            $consulta->execute([
                ':nome' => $nome,
                ':email' => $email,
                ':telefone' => $telefone,
                ':senha' => password_hash($senha, PASSWORD_DEFAULT)
            ]);

            session_regenerate_id(true);

            $_SESSION['usuario_id'] = (int) $pdo->lastInsertId();
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            if (!empty($_SESSION['voltar_checkout'])) {
                unset($_SESSION['voltar_checkout']);
                header('Location: checkout.php');
            } else {
                header('Location: conta.php');
            }

            exit;
        } catch (PDOException $excecao) {
            if ($excecao->getCode() === '23000') {
                $erro = 'Este e-mail já está cadastrado.';
            } else {
                error_log('Erro no cadastro: ' . $excecao->getMessage());
                $erro = 'Não foi possível criar a conta. Tente novamente.';
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro | AuraPet</title>

    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/conta.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include('assets/componentes/header.php'); ?>

    <main class="pagina-conta">
        <section class="apresentacao-conta">
            <div class="apresentacao-texto">
                <h1>Crie sua conta<br>na AuraPet!</h1>

                <p>
                    Cuide ainda melhor<br>
                    do seu pet com a gente ♡
                </p>
            </div>

            <img
                src="assets/images/inicial.png"
                alt="Cachorro e gato"
                class="animais-conta"
            >
        </section>

        <section class="cartao-conta">
            <h2>
                <i class="fa-solid fa-paw"></i>
                Cadastro
            </h2>

            <?php if ($erro !== ''): ?>
                <p class="mensagem-erro" role="alert">
                    <?= escapar($erro) ?>
                </p>
            <?php endif; ?>

            <form method="post" class="form-conta">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= escapar($_SESSION['csrf_token']) ?>"
                >

                <label>
                    <span>Nome completo</span>

                    <input
                        type="text"
                        name="nome"
                        placeholder="Nome completo"
                        value="<?= escapar($nome) ?>"
                        maxlength="100"
                        autocomplete="name"
                        required
                    >
                </label>

                <label>
                    <span>E-mail</span>

                    <input
                        type="email"
                        name="email"
                        placeholder="E-mail"
                        value="<?= escapar($email) ?>"
                        maxlength="190"
                        autocomplete="email"
                        required
                    >
                </label>

                <label>
                    <span>Telefone</span>

                    <input
                        type="tel"
                        name="telefone"
                        placeholder="Telefone"
                        value="<?= escapar($telefone) ?>"
                        maxlength="25"
                        autocomplete="tel"
                        required
                    >
                </label>

                <label>
                    <span>Senha</span>

                    <input
                        type="password"
                        name="senha"
                        placeholder="Senha: pelo menos 8 caracteres"
                        minlength="8"
                        maxlength="72"
                        autocomplete="new-password"
                        required
                    >
                </label>

                <label>
                    <span>Confirmar senha</span>

                    <input
                        type="password"
                        name="confirmar_senha"
                        placeholder="Confirmar senha"
                        minlength="8"
                        maxlength="72"
                        autocomplete="new-password"
                        required
                    >
                </label>

                <button type="submit" class="btn-conta">
                    Criar conta
                </button>
            </form>

            <p class="alternar-conta">
                Já tem uma conta?
                <a href="login.php">Faça login</a>
            </p>
        </section>
    </main>

    <div class="apoiodiv">
        <?php include('assets/componentes/apoio.php'); ?>
    </div>
</body>

</html>