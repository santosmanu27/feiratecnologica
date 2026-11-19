
(() => {
    const lista = document.querySelector('#produtos-lista');
    const cards = [...document.querySelectorAll('.produto-card')];
    const botoesTipo = document.querySelectorAll('.filtro');
    const botoesCategoria = document.querySelectorAll('.categoria-filtro');
    const ordenar = document.querySelector('#ordenar');
    const busca = document.querySelector('#busca');
    const semProdutos = document.querySelector('#sem-produtos');

    if (!lista || !ordenar || !busca || !semProdutos) return;

    const parametros = new URLSearchParams(window.location.search);

    function normalizar(valor) {
        return (valor || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    function normalizarCategoria(valor) {
        const categoria = normalizar(valor);

        if (categoria === 'gato') return 'gatos';
        if (categoria === 'cao') return 'caes';

        return categoria;
    }

    let tipoAtual = 'todos';
    let categoriaAtual = normalizarCategoria(parametros.get('categoria'));

    busca.value = parametros.get('busca') || '';

    function atualizarProdutos() {
        const termo = normalizar(busca.value);
        let quantidadeVisivel = 0;

        botoesTipo.forEach(botao => {
            botao.classList.toggle(
                'ativo',
                botao.dataset.tipo === tipoAtual
            );
        });

        botoesCategoria.forEach(botao => {
            botao.classList.toggle(
                'ativa',
                normalizarCategoria(botao.dataset.categoria) === categoriaAtual
            );
        });

        cards.forEach(card => {
            const nome = normalizar(card.dataset.nome);
            const marca = normalizar(card.dataset.marca);
            const tipo = normalizar(card.dataset.tipo);

            const categorias = normalizar(card.dataset.categorias)
                .split(/\s+/)
                .filter(Boolean)
                .map(normalizarCategoria);

            const correspondeTipo =
                tipoAtual === 'todos' || tipo === normalizar(tipoAtual);

            const correspondeCategoria =
                !categoriaAtual || categorias.includes(categoriaAtual);

            const correspondeBusca =
                nome.includes(termo) || marca.includes(termo);

            const visivel =
                correspondeTipo &&
                correspondeCategoria &&
                correspondeBusca;

            card.hidden = !visivel;

            if (visivel) quantidadeVisivel++;
        });

        const cardsOrdenados = [...cards].sort((a, b) => {
            const precoA = Number(a.dataset.preco);
            const precoB = Number(b.dataset.preco);

            if (ordenar.value === 'menor-preco') {
                return precoA - precoB;
            }

            if (ordenar.value === 'maior-preco') {
                return precoB - precoA;
            }

            return normalizar(a.dataset.nome).localeCompare(
                normalizar(b.dataset.nome),
                'pt-BR'
            );
        });

        cardsOrdenados.forEach(card => lista.appendChild(card));

        semProdutos.hidden = quantidadeVisivel > 0;
    }

    botoesTipo.forEach(botao => {
        botao.addEventListener('click', () => {
            tipoAtual = botao.dataset.tipo;
            atualizarProdutos();
        });
    });

    botoesCategoria.forEach(botao => {
        botao.addEventListener('click', () => {
            const categoria = normalizarCategoria(botao.dataset.categoria);

            categoriaAtual =
                categoriaAtual === categoria ? '' : categoria;

            atualizarProdutos();
        });
    });

    busca.addEventListener('input', atualizarProdutos);
    ordenar.addEventListener('change', atualizarProdutos);

    atualizarProdutos();
})();
