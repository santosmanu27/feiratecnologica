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
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $email = trim((string) ($_POST['email'] ?? ''));
    $senha = (string) ($_POST['senha'] ?? '');

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        $erro = 'Não foi possível validar a solicitação. Atualize a página e tente novamente.';
    } elseif (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 190 ||
        $senha === '' ||
        strlen($senha) > 72
    ) {
        $erro = 'E-mail ou senha inválidos.';
    } else {
        require 'assets/componentes/conexao.php';

        $consulta = $pdo->prepare(
            'SELECT id, senha FROM usuarios WHERE email = :email LIMIT 1'
        );

        $consulta->execute([
            ':email' => $email
        ]);

        $usuario = $consulta->fetch();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            session_regenerate_id(true);

            $_SESSION['usuario_id'] = (int) $usuario['id'];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            if (!empty($_SESSION['voltar_checkout'])) {
                unset($_SESSION['voltar_checkout']);
                header('Location: checkout.php');
            } else {
                header('Location: conta.php');
            }

            exit;
        }

        $erro = 'E-mail ou senha inválidos.';
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | AuraPet</title>

    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/conta.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include('assets/componentes/header.php'); ?>

    <main class="pagina-conta">
        <section class="apresentacao-conta">
            <div class="apresentacao-texto">
                <h1>Que bom ter<br>você de volta!</h1>

                <p>
                    Entre na sua conta<br>
                    e cuide do seu pet com a gente ♡
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
                Login
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
                    <span>Senha</span>

                    <input
                        type="password"
                        name="senha"
                        placeholder="Senha"
                        maxlength="72"
                        autocomplete="current-password"
                        required
                    >
                </label>

                <button type="submit" class="btn-conta">
                    Entrar
                </button>
            </form>

            <p class="alternar-conta">
                Ainda não tem uma conta?
                <a href="cadastro.php">Cadastre-se</a>
            </p>
        </section>
    </main>

    <div class="apoiodiv">
        <?php include('assets/componentes/apoio.php'); ?>
    </div>
</body>

</html>