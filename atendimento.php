<?php require_once 'assets/componentes/sessao.php'; ?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atendimento | AuraPet</title>

    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/atendimento.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include('assets/componentes/header.php'); ?>

    <main class="pagina-atendimento">
        <div class="titulo-atendimento">
            <i class="fa-regular fa-calendar-days"></i>

            <div>
                <h1>Agende um atendimento</h1>
                <p>Escolha a data e o horário que preferir:</p>
            </div>
        </div>

        <form action="confirmacao.php" method="post" class="form-atendimento">
            <div class="calendario">
                <div class="calendario-topo">
                    <button type="button" id="mes-anterior" aria-label="Mês anterior">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>

                    <h2 id="mes-atual"></h2>

                    <button type="button" id="proximo-mes" aria-label="Próximo mês">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>

                <div class="dias-semana">
                    <span>D</span>
                    <span>S</span>
                    <span>T</span>
                    <span>Q</span>
                    <span>Q</span>
                    <span>S</span>
                    <span>S</span>
                </div>

                <div class="dias-calendario" id="dias-calendario"></div>

                <input type="hidden" name="data" id="data-selecionada">
            </div>

            <div class="escolha-horario">
                <h2>Horários disponíveis</h2>

                <div class="horarios">
                    <label>
                        <input type="radio" name="horario" value="09:00" required>
                        <span>09:00</span>
                    </label>

                    <label>
                        <input type="radio" name="horario" value="10:00">
                        <span>10:00</span>
                    </label>

                    <label>
                        <input type="radio" name="horario" value="11:00">
                        <span>11:00</span>
                    </label>

                    <label>
                        <input type="radio" name="horario" value="14:00">
                        <span>14:00</span>
                    </label>

                    <label>
                        <input type="radio" name="horario" value="15:00">
                        <span>15:00</span>
                    </label>

                    <label>
                        <input type="radio" name="horario" value="16:00">
                        <span>16:00</span>
                    </label>
                </div>

                <button type="submit" class="btn-confirmar">
                    Confirmar horário
                    <i class="fa-solid fa-arrow-right"></i>
                </button>

                <p class="aviso-agendamento">
                    Demonstração: o horário ainda não será reservado.
                </p>
            </div>

            <div class="imagem-atendimento">
                <img src="assets/images/fofo.png" alt="Cachorro">
            </div>
        </form>
    </main>

    <?php include('assets/componentes/footer.php'); ?>

    <div class="apoiodiv">
        <?php include('assets/componentes/apoio.php'); ?>
    </div>

    <script src="js/atendimento.js" defer></script>
</body>

</html>