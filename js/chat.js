(() => {
    const abrir = document.querySelector('.icon');
    const janela = document.querySelector('.janela-chat');
    const fechar = document.querySelector('.chat-fechar');
    const form = document.querySelector('.chat-form');
    const campo = form.elements.mensagem;
    const mensagens = document.querySelector('.chat-mensagens');
    const enviar = form.querySelector('button');
    const historico = [];

    function mostrar(ativo) {
        janela.hidden = !ativo;
        abrir.setAttribute('aria-expanded', String(ativo));
        if (ativo) campo.focus();
        else abrir.focus();
    }

    function adicionar(texto, classe) {
        const elemento = document.createElement('div');
        elemento.className = 'mensagem ' + classe;
        elemento.textContent = texto;
        mensagens.appendChild(elemento);
        mensagens.scrollTop = mensagens.scrollHeight;
        return elemento;
    }

    abrir.addEventListener('click', () => mostrar(janela.hidden));
    fechar.addEventListener('click', () => mostrar(false));
    form.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        const texto = campo.value.trim();
        if (!texto) return;
        adicionar(texto, 'usuario');
        campo.value = '';
        enviar.disabled = true;
        campo.disabled = true;
        const aguardando = adicionar('Digitando...', 'bot');
        try {
            const resposta = await fetch('chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ mensagem: texto, historico: historico.slice(-10) })
            });
            const dados = await resposta.json();
            if (!resposta.ok) throw new Error(dados.erro || 'Não foi possível enviar a mensagem.');
            aguardando.textContent = dados.resposta;
            historico.push({ role: 'user', content: texto }, { role: 'assistant', content: dados.resposta });
        } catch (erro) {
            aguardando.textContent = erro.message || 'Ocorreu um erro. Tente novamente.';
        } finally {
            enviar.disabled = false;
            campo.disabled = false;
            campo.focus();
            mensagens.scrollTop = mensagens.scrollHeight;
        }
    });
})();
