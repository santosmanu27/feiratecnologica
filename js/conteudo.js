(() => {
    const quantidade = document.querySelector('#quantidade');
    const diminuir = document.querySelector('#diminuir');
    const aumentar = document.querySelector('#aumentar');

    if (quantidade && diminuir && aumentar) {
        function ajustarQuantidade(valor) {
            quantidade.value = Math.max(1, Math.min(99, valor));
        }

        diminuir.addEventListener('click', () => {
            ajustarQuantidade((Number(quantidade.value) || 1) - 1);
        });

        aumentar.addEventListener('click', () => {
            ajustarQuantidade((Number(quantidade.value) || 1) + 1);
        });

        quantidade.addEventListener('change', () => {
            ajustarQuantidade(Math.floor(Number(quantidade.value) || 1));
        });
    }

    const imagemPrincipal = document.querySelector('#imagem-principal');
    const miniaturas = document.querySelectorAll('.miniatura');

    miniaturas.forEach((miniatura) => {
        miniatura.addEventListener('click', () => {
            imagemPrincipal.src = miniatura.dataset.imagem;

            miniaturas.forEach((item) => {
                item.classList.remove('ativa');
            });

            miniatura.classList.add('ativa');
        });
    });
})();