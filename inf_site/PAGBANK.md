# Integração PagBank Checkout (Sandbox)

Este guia mostra como configurar o fluxo de pagamento com o PagBank usando o ambiente de testes do endpoint sandbox.

## 1. Acesse o portal do PagBank Developers

1. Entre no portal do PagBank Developers.
2. Faça login com a conta do PagBank.
3. Crie um novo aplicativo para o seu projeto.
4. Ative o ambiente de testes/sandbox.
5. Copie o `client_id` e o `client_secret`.

## 2. Configure as URLs de retorno

Defina as URLs que o PagBank vai usar depois do pagamento:

- URL de sucesso: `https://seu-dominio.com/payment_return.php`
- URL de cancelamento: `https://seu-dominio.com/payment_cancel.php`
- Webhook (opcional, mas recomendado): `https://seu-dominio.com/pagbank_webhook.php`

> Se você estiver testando localmente, use um túnel como Ngrok para expor o endereço antes de validar o fluxo.

## 3. Atualize o arquivo de configuração

No arquivo `config.php`, adicione ou ajuste estas variáveis:

```php
$PAGBANK_ENV = 'sandbox';
$PAGBANK_CLIENT_ID = 'SEU_CLIENT_ID';
$PAGBANK_CLIENT_SECRET = 'SEU_CLIENT_SECRET';
$PAGBANK_REDIRECT_URL = 'https://seu-dominio.com/payment_return.php';
$PAGBANK_WEBHOOK_URL = 'https://seu-dominio.com/pagbank_webhook.php';
```

## 4. Obtenha o token de acesso

Antes de criar o checkout, você precisa obter um token de acesso.

### Exemplo em PHP

```php
function getPagbankAccessToken() {
    global $PAGBANK_CLIENT_ID, $PAGBANK_CLIENT_SECRET;

    $url = 'https://sandbox.api.pagseguro.com/oauth2/token';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'client_credentials',
        'client_id' => $PAGBANK_CLIENT_ID,
        'client_secret' => $PAGBANK_CLIENT_SECRET,
    ]));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception('Erro ao obter token do PagBank: ' . $response);
    }

    $data = json_decode($response, true);
    return $data['access_token'] ?? null;
}
```

## 5. Crie o checkout

Depois de obter o token, crie o checkout no endpoint:

```text
https://sandbox.api.pagseguro.com/checkout
```

### Exemplo em PHP

```php
function createPagbankCheckout($orderId, $customerName, $customerEmail, $amountCents) {
    global $PAGBANK_REDIRECT_URL, $PAGBANK_WEBHOOK_URL;

    $token = getPagbankAccessToken();

    $payload = [
        'reference_id' => $orderId,
        'customer' => [
            'name' => $customerName,
            'email' => $customerEmail,
        ],
        'items' => [[
            'reference_id' => 'inscricao-evento',
            'name' => 'Inscrição do evento',
            'quantity' => 1,
            'unit_amount' => $amountCents,
        ]],
        'amount' => [
            'value' => $amountCents,
            'currency' => 'BRL',
        ],
        'redirect_urls' => [
            $PAGBANK_REDIRECT_URL,
        ],
        'notification_urls' => [
            $PAGBANK_WEBHOOK_URL,
        ],
    ];

    $ch = curl_init('https://sandbox.api.pagseguro.com/checkout');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode < 200 || $httpCode >= 300) {
        throw new Exception('Erro ao criar checkout: ' . $response);
    }

    return json_decode($response, true);
}
```

## 6. Redirecione o participante para o checkout

O PagBank vai retornar uma resposta com o checkout criado. Use o valor retornado para encaminhar o usuário ao link de pagamento.

Exemplo:

```php
$checkout = createPagbankCheckout('inscricao-001', 'Nome do Participante', 'email@teste.com', 15000);

if (!empty($checkout['links'][0]['href'])) {
    header('Location: ' . $checkout['links'][0]['href']);
    exit;
}
```

## 7. Valide o fluxo em sandbox

Antes de ir para a produção:

1. Faça uma inscrição de teste.
2. Complete o fluxo até o pagamento.
3. Verifique se o retorno foi recebido corretamente.
4. Atualize o status da inscrição no banco.
5. Teste o webhook se estiver usando.

## 8. Mude para produção somente depois dos testes

Quando tudo estiver certo:

1. Troque `$PAGBANK_ENV` de `sandbox` para `production`.
2. Use as credenciais de produção.
3. Troque o endpoint para o ambiente de produção.
4. Teste novamente antes de liberar para os participantes.

## Dica importante

As respostas e os campos exatos da API podem sofrer pequenas mudanças conforme a documentação do PagBank. Sempre valide o retorno real da API e adapte o código ao seu ambiente.
