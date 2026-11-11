<?php
require_once 'assets/componentes/sessao.php';
$data = $_POST['data'] ?? '';
$horario = $_POST['horario'] ?? '';

$horariosValidos = [
    '09:00',
    '10:00',
    '11:00',
    '14:00',
    '15:00',
    '16:00'
];

$dataValida = is_string($data)
    ? DateTime::createFromFormat('!Y-m-d', $data)
    : false;

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !$dataValida ||
    $dataValida->format('Y-m-d') !== $data ||
    $data < date('Y-m-d') ||
    !is_string($horario) ||
    !in_array($horario, $horariosValidos, true)
) {
    header('Location: atendimento.php');
    exit;
}

$dataFormatada = $dataValida->format('d/m/Y');

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horário escolhido | AuraPet</title>

    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/atendimento.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include('assets/componentes/header.php'); ?>

    <main class="pagina-confirmacao">
        <div class="icone-confirmacao">
            <i class="fa-solid fa-check"></i>
        </div>

        <h1>Horário escolhido!</h1>

        <p>
            Por favor, compareça no horário marcado
        </p>

        <div class="resumo-atendimento">
            <span>
                <i class="fa-regular fa-calendar-days"></i>
                Data: <?= htmlspecialchars($dataFormatada, ENT_QUOTES, 'UTF-8') ?>
            </span>

            <span>
                <i class="fa-regular fa-clock"></i>
                Horário: <?= htmlspecialchars($horario, ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>

        <a href="index.php" class="btn-confirmar">
            Voltar para o início
            <i class="fa-solid fa-arrow-right"></i>
        </a>
    </main>

    <?php include('assets/componentes/footer.php'); ?>

    <div class="apoiodiv">
        <?php include('assets/componentes/apoio.php'); ?>
    </div>
</body>

</html>