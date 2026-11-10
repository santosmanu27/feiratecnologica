<div class="chat-apoio">
    <button class="icon" type="button" aria-label="Abrir atendimento" aria-expanded="false" aria-controls="janela-chat">
        <i class="fa-solid fa-comment"></i>
    </button>
    <section class="janela-chat" id="janela-chat" aria-label="Atendimento AuraPet" hidden>
        <div class="chat-cabecalho">
            <div><strong>Atendimento AuraPet</strong><span>Como podemos ajudar?</span></div>
            <button type="button" class="chat-fechar" aria-label="Fechar atendimento">&times;</button>
        </div>
        <div class="chat-mensagens" aria-live="polite">
            <div class="mensagem bot">Olá! Sou o assistente da AuraPet. Como posso ajudar com dúvidas sobre a loja?</div>
        </div>
        <form class="chat-form">
            <input name="mensagem" type="text" placeholder="Digite sua dúvida..." maxlength="1000" aria-label="Sua mensagem" required>
            <button type="submit" aria-label="Enviar mensagem"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
    </section>
</div>
<script src="js/chat.js" defer></script>
