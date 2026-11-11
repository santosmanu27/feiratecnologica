<form method="post" action="cartoes.php" class="form-cartao-teste">
    <input
        type="hidden"
        name="csrf_token"
        value="<?= escapar($_SESSION['csrf_token']) ?>"
    >

    <input type="hidden" name="acao" value="salvar">

    <input
        type="hidden"
        name="retorno"
        value="<?= escapar($retornoCartao ?? 'perfil') ?>"
    >

    <label>
        Nome do titular

        <input
            type="text"
            name="nome"
            maxlength="100"
            required
        >
    </label>

    <label>
        Bandeira

        <select name="bandeira">
            <option>Visa</option>
            <option>Mastercard</option>
            <option>Elo</option>
            <option>Outro</option>
        </select>
    </label>

    <label>
        Quatro últimos dígitos

        <input
            type="text"
            name="ultimos4"
            inputmode="numeric"
            pattern="[0-9]{4}"
            maxlength="4"
            placeholder="1234"
            required
        >
    </label>

    <label>
        Validade

        <input
            type="text"
            name="validade"
            pattern="(0[1-9]|1[0-2])/20[0-9]{2}"
            placeholder="MM/AAAA"
            maxlength="7"
            required
        >
    </label>

    <button type="submit" class="botao">
        Salvar cartão 
    </button>
</form>