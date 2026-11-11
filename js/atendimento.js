(() => {
    const calendario = document.querySelector('#dias-calendario');
    const tituloMes = document.querySelector('#mes-atual');
    const botaoAnterior = document.querySelector('#mes-anterior');
    const botaoProximo = document.querySelector('#proximo-mes');
    const campoData = document.querySelector('#data-selecionada');
    const formulario = document.querySelector('.form-atendimento');

    const hoje = new Date();
    hoje.setHours(0, 0, 0, 0);

    let anoAtual = hoje.getFullYear();
    let mesAtual = hoje.getMonth();
    let dataSelecionada = '';

    function formatarData(ano, mes, dia) {
        const mesFormatado = String(mes + 1).padStart(2, '0');
        const diaFormatado = String(dia).padStart(2, '0');

        return `${ano}-${mesFormatado}-${diaFormatado}`;
    }

    function desenharCalendario() {
        calendario.innerHTML = '';

        const primeiroDia = new Date(anoAtual, mesAtual, 1);
        const quantidadeDias = new Date(anoAtual, mesAtual + 1, 0).getDate();

        tituloMes.textContent = primeiroDia.toLocaleDateString('pt-BR', {
            month: 'long',
            year: 'numeric'
        });

        botaoAnterior.disabled =
            anoAtual === hoje.getFullYear() &&
            mesAtual === hoje.getMonth();

        for (let i = 0; i < primeiroDia.getDay(); i++) {
            const vazio = document.createElement('span');
            calendario.appendChild(vazio);
        }

        for (let dia = 1; dia <= quantidadeDias; dia++) {
            const data = new Date(anoAtual, mesAtual, dia);
            const dataFormatada = formatarData(anoAtual, mesAtual, dia);
            const botao = document.createElement('button');

            botao.type = 'button';
            botao.textContent = dia;
            botao.className = 'dia';
            botao.disabled = data < hoje;
            botao.setAttribute(
                'aria-label',
                `Selecionar ${dia} de ${tituloMes.textContent}`
            );
            botao.setAttribute(
                'aria-pressed',
                String(dataSelecionada === dataFormatada)
            );

            if (dataSelecionada === dataFormatada) {
                botao.classList.add('selecionado');
            }

            botao.addEventListener('click', () => {
                dataSelecionada = dataFormatada;
                campoData.value = dataFormatada;
                desenharCalendario();
            });

            calendario.appendChild(botao);
        }
    }

    botaoAnterior.addEventListener('click', () => {
        mesAtual--;

        if (mesAtual < 0) {
            mesAtual = 11;
            anoAtual--;
        }

        desenharCalendario();
    });

    botaoProximo.addEventListener('click', () => {
        mesAtual++;

        if (mesAtual > 11) {
            mesAtual = 0;
            anoAtual++;
        }

        desenharCalendario();
    });

    formulario.addEventListener('submit', (evento) => {
        if (!campoData.value) {
            evento.preventDefault();
            alert('Selecione uma data no calendário.');
        }
    });

    desenharCalendario();
})();