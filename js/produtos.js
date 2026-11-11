(() => {
    const lista = document.querySelector('#produtos-lista');
    const cards = [...document.querySelectorAll('.produto-card')];
    const botoesTipo = document.querySelectorAll('.filtro');
    const botoesCategoria = document.querySelectorAll('.categoria-filtro');
    const ordenar = document.querySelector('#ordenar');
    const busca = document.querySelector('#busca');
    const semProdutos = document.querySelector('#sem-produtos');

    const parametros = new URLSearchParams(window.location.search);

    const categoriasValidas = [
        'caes',
        'gatos',
        'higiene',
        'brinquedos',
        'acessorios'
    ];

    let tipoAtual = 'todos';
    let categoriaAtual = parametros.get('categoria') || '';

    if (!categoriasValidas.includes(categoriaAtual)) {
        categoriaAtual = '';
    }

    busca.value = parametros.get('busca') || '';

    function atualizarProdutos() {
        const termo = busca.value.trim().toLocaleLowerCase('pt-BR');
        let quantidadeVisivel = 0;

        botoesTipo.forEach((botao) => {
            botao.classList.toggle(
                'ativo',
                botao.dataset.tipo === tipoAtual
            );
        });

        botoesCategoria.forEach((botao) => {
            botao.classList.toggle(
                'ativa',
                botao.dataset.categoria === categoriaAtual
            );
        });

        cards.forEach((card) => {
            const nome = card.dataset.nome.toLocaleLowerCase('pt-BR');
            const marca = card.dataset.marca.toLocaleLowerCase('pt-BR');
            const categorias = card.dataset.categorias.split(' ');

            const correspondeTipo =
                tipoAtual === 'todos' || card.dataset.tipo === tipoAtual;

            const correspondeCategoria =
                !categoriaAtual || categorias.includes(categoriaAtual);

            const correspondeBusca =
                nome.includes(termo) || marca.includes(termo);

            const visivel =
                correspondeTipo &&
                correspondeCategoria &&
                correspondeBusca;

            card.hidden = !visivel;

            if (visivel) {
                quantidadeVisivel++;
            }
        });

        cards.sort((a, b) => {
            if (ordenar.value === 'menor-preco') {
                return Number(a.dataset.preco) - Number(b.dataset.preco);
            }

            if (ordenar.value === 'maior-preco') {
                return Number(b.dataset.preco) - Number(a.dataset.preco);
            }

            return a.dataset.nome.localeCompare(b.dataset.nome, 'pt-BR');
        });

        cards.forEach((card) => lista.appendChild(card));

        semProdutos.hidden = quantidadeVisivel > 0;
    }

    botoesTipo.forEach((botao) => {
        botao.addEventListener('click', () => {
            tipoAtual = botao.dataset.tipo;

            if (tipoAtual === 'todos') {
                categoriaAtual = '';
                busca.value = '';
            }

            atualizarProdutos();
        });
    });

    botoesCategoria.forEach((botao) => {
        botao.addEventListener('click', () => {
            categoriaAtual = botao.dataset.categoria;
            tipoAtual = 'todos';
            atualizarProdutos();
        });
    });

    busca.addEventListener('input', atualizarProdutos);
    ordenar.addEventListener('change', atualizarProdutos);

    atualizarProdutos();
})();