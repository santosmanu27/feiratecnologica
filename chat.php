<?php
header('Content-Type: application/json; charset=utf-8');

function responder($status, $dados) {
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, ['erro' => 'Método não permitido.']);
}

$chave = getenv('GROQ_API_KEY');
if (!$chave) {
    responder(500, ['erro' => 'O atendimento ainda não foi configurado.']);
}
if (!function_exists('curl_init')) {
    responder(500, ['erro' => 'O atendimento está indisponível pq eu sou burro e fiz algo errado.']);
}

$entrada = json_decode(file_get_contents('php://input'), true);
$mensagem = $entrada['mensagem'] ?? null;
if (!is_string($mensagem) || trim($mensagem) === '' || strlen($mensagem) > 4000) {
    responder(400, ['erro' => 'Digite uma mensagem válida.']);
}

$instrucoes = 'Você é o assistente virtual da AuraPet, uma pet shop. Responda em português brasileiro, com cordialidade e objetividade. Ajude apenas com dúvidas sobre a loja, seus produtos e atendimento. Para assuntos fora desse escopo, explique educadamente que só pode ajudar com a AuraPet. Não invente preços, estoque, endereço, horários, políticas, formas de contato ou serviços: se não souber, diga que não tem essa informação e sugira consultar a equipe da loja. Não solicite senhas, dados bancários ou outras informações sensíveis. Não substitua orientação veterinária.';
$mensagens = [['role' => 'system', 'content' => $instrucoes]];
$historico = $entrada['historico'] ?? [];
if (is_array($historico)) {
    foreach (array_slice($historico, -10) as $item) {
        if (is_array($item) && isset($item['role'], $item['content']) && in_array($item['role'], ['user', 'assistant'], true) && is_string($item['content']) && strlen($item['content']) <= 4000) {
            $mensagens[] = ['role' => $item['role'], 'content' => $item['content']];
        }
    }
}
$mensagens[] = ['role' => 'user', 'content' => trim($mensagem)];

$curl = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $chave, 'Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode(['model' => 'llama-3.3-70b-versatile', 'messages' => $mensagens, 'max_tokens' => 300, 'temperature' => 0.3], JSON_UNESCAPED_UNICODE)
]);
$resultado = curl_exec($curl);
$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);
$dados = json_decode($resultado ?: '', true);
$resposta = $dados['choices'][0]['message']['content'] ?? null;
if ($status < 200 || $status >= 300 || !is_string($resposta) || trim($resposta) === '') {
    responder(502, ['erro' => 'Não foi possível obter uma resposta agora. Tente novamente em instantes.']);
}
responder(200, ['resposta' => trim($resposta)]);
