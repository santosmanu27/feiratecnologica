<?php
header('Content-Type: application/json; charset=utf-8');

function responder($status, $dados) {
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, [
        'erro' => 'Método não permitido.'
    ]);
}

$chave = getenv('GROQ_API_KEY');

if (!$chave) {
    responder(500, [
        'erro' => 'O atendimento ainda não foi configurado.'
    ]);
}

if (!function_exists('curl_init')) {
    responder(500, [
        'erro' => 'O atendimento está indisponível no momento.'
    ]);
}

$entrada = json_decode(file_get_contents('php://input'), true);

if (!is_array($entrada)) {
    responder(400, [
        'erro' => 'Dados inválidos.'
    ]);
}

$mensagem = $entrada['mensagem'] ?? null;

if (
    !is_string($mensagem) ||
    trim($mensagem) === '' ||
    strlen($mensagem) > 4000
) {
    responder(400, [
        'erro' => 'Digite uma mensagem válida.'
    ]);
}

$instrucoes = '
Você é o assistente virtual oficial da AuraPet, uma pet shop.

REGRAS:
- Responda sempre em português brasileiro.
- Seja cordial, simpático, natural e objetivo.
- Ajude exclusivamente com dúvidas relacionadas à AuraPet, seus produtos e atendimento.
- Se o assunto estiver fora desse escopo, explique educadamente que você só pode ajudar com a AuraPet.
- Nunca invente preços, estoque, endereço, horários, políticas, formas de contato ou serviços.
- Se não souber alguma informação, diga que ela precisa ser confirmada com a equipe da loja.
- Não solicite senhas, dados bancários ou informações pessoais sensíveis.
- Não substitua orientações veterinárias profissionais.
- Não invente informações sobre produtos ou animais.
- Evite respostas excessivamente longas.
';

$mensagens = [
    [
        'role' => 'system',
        'content' => $instrucoes
    ]
];

$historico = $entrada['historico'] ?? [];

if (is_array($historico)) {

    $historico = array_slice($historico, -10);

    foreach ($historico as $item) {

        if (
            !is_array($item) ||
            !isset($item['role'], $item['content'])
        ) {
            continue;
        }

        if (!in_array($item['role'], ['user', 'assistant'], true)) {
            continue;
        }

        if (
            !is_string($item['content']) ||
            trim($item['content']) === '' ||
            strlen($item['content']) > 4000
        ) {
            continue;
        }

        $mensagens[] = [
            'role' => $item['role'],
            'content' => $item['content']
        ];
    }
}

$mensagens[] = [
    'role' => 'user',
    'content' => trim($mensagem)
];

$payload = [
    'model' => 'openai/gpt-oss-120b',
    'messages' => $mensagens,
    'max_tokens' => 300,
    'temperature' => 0.3
];

$curl = curl_init(
    'https://api.groq.com/openai/v1/chat/completions'
);

curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 10,

    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $chave,
        'Content-Type: application/json'
    ],

    CURLOPT_POSTFIELDS => json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE
    )
]);

$resultado = curl_exec($curl);

$erroCurl = curl_error($curl);

$status = curl_getinfo(
    $curl,
    CURLINFO_HTTP_CODE
);

curl_close($curl);

if ($resultado === false) {

    error_log(
        '[AuraPet] Erro cURL: ' . $erroCurl
    );

    responder(502, [
        'erro' => 'Não foi possível conectar ao atendimento.'
    ]);
}

$dados = json_decode($resultado, true);

if ($status < 200 || $status >= 300) {

    error_log(
        '[AuraPet] Erro Groq HTTP ' .
        $status .
        ': ' .
        $resultado
    );

    responder(502, [
        'erro' => 'O atendimento está indisponível no momento.',
        'debug' => $status
    ]);
}

$resposta = $dados['choices'][0]['message']['content'] ?? null;

if (
    !is_string($resposta) ||
    trim($resposta) === ''
) {

    error_log(
        '[AuraPet] Resposta inválida da Groq: ' .
        $resultado
    );

    responder(502, [
        'erro' => 'A IA não retornou uma resposta válida.'
    ]);
}

responder(200, [
    'resposta' => trim($resposta)
]);
