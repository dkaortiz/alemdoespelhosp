<?php
// === CACHE HEADERS - Forçar revalidação em cada acesso ===
if (!headers_sent()) {
    // Desabilitar cache para páginas HTML/PHP
    header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
}

// === SESSION CONFIGURATION ===
$session_dir = __DIR__ . '/sessions';
if (!is_dir($session_dir)) {
    @mkdir($session_dir, 0755, true);
}
if (!session_id()) {
    @session_save_path($session_dir);
}

function loadEnvFile($path) {
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        [$name, $value] = array_map('trim', $parts);
        if ($name === '' || getenv($name) !== false) {
            continue;
        }

        putenv($name . '=' . $value);
    }
}

loadEnvFile(__DIR__ . '/.env');

// Configuração do banco de dados
$DB_HOST = trim((string) getenv('DB_HOST'));
$DB_USER = trim((string) getenv('DB_USER'));
$DB_PASS = trim((string) getenv('DB_PASS'));
$DB_NAME = trim((string) getenv('DB_NAME'));

if ($DB_HOST === '' || $DB_USER === '' || $DB_PASS === '' || $DB_NAME === '') {
    die('Configuração do banco ausente. Defina DB_HOST, DB_USER, DB_PASS e DB_NAME no .env.');
}

// Configuração de pagamento
$PIX_PHONE = '11993813374';
$PAYMENT_BASE = 150.00;
$PAYMENT_AMOUNT = 150.00;

// Configuração PagBank Checkout
$PAGBANK_ENV = trim((string) getenv('PAGBANK_ENV'));
$PAGBANK_ACCESS_TOKEN = trim((string) getenv('PAGBANK_ACCESS_TOKEN'));
$PAGBANK_REDIRECT_URL = trim((string) getenv('PAGBANK_REDIRECT_URL'));
$PAGBANK_WEBHOOK_URL = trim((string) getenv('PAGBANK_WEBHOOK_URL'));
$PAGBANK_DEFAULT_EMAIL = trim((string) getenv('PAGBANK_DEFAULT_EMAIL'));

if ($PAGBANK_ENV === '') {
    die('PAGBANK_ENV não configurado. Defina a variável de ambiente no .env.');
}
if ($PAGBANK_ACCESS_TOKEN === '') {
    die('PAGBANK_ACCESS_TOKEN não configurado. Defina a variável de ambiente no .env.');
}
if ($PAGBANK_REDIRECT_URL === '') {
    die('PAGBANK_REDIRECT_URL não configurado. Defina a variável de ambiente no .env.');
}
if ($PAGBANK_WEBHOOK_URL === '') {
    die('PAGBANK_WEBHOOK_URL não configurado. Defina a variável de ambiente no .env.');
}
if ($PAGBANK_DEFAULT_EMAIL === '') {
    die('PAGBANK_DEFAULT_EMAIL não configurado. Defina a variável de ambiente no .env.');
}

// Configuração admin: autenticação exclusivamente via tabela `admins`

// Diretório de uploads
$UPLOAD_DIR = __DIR__ . '/uploads';
if (!is_dir($UPLOAD_DIR)) {
    mkdir($UPLOAD_DIR, 0755, true);
}

// Conexão com banco de dados
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    die('Falha ao conectar ao banco de dados: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

function normalizeRegistrationType($type): string {
    $normalized = strtolower(trim((string) $type));
    if ($normalized === '' || strpos($normalized, 'pere') !== false) {
        return 'peregrino';
    }

    return 'anfitriao';
}

function sendEmailMessage(string $to, string $subject, string $htmlBody, string $altBody = '', array $inlineImages = []): bool {
    $host = trim((string) getenv('SMTP_HOST')) ?: 'email-ssl.com.br';
    $port = (int) (trim((string) getenv('SMTP_PORT')) ?: '465');
    $username = trim((string) getenv('SMTP_USERNAME')) ?: 'contato@alemdoespelhosp.com.br';
    $password = trim((string) getenv('SMTP_PASSWORD')) ?: '01062021Midi@';
    $from = trim((string) getenv('SMTP_FROM')) ?: 'contato@alemdoespelhosp.com.br';
    $fromName = trim((string) getenv('SMTP_FROM_NAME')) ?: 'Alem do Espelho';
    $secure = strtolower(trim((string) getenv('SMTP_SECURE')) ?: 'ssl');

    $logPath = __DIR__ . '/uploads/email-errors.log';
    $logError = function (string $message) use ($logPath): void {
        error_log($message);
        @file_put_contents($logPath, date('Y-m-d H:i:s') . ' ' . $message . PHP_EOL, FILE_APPEND);
    };

    $connectHost = ($secure === 'ssl' || $port === 465) ? 'ssl://' . $host : $host;
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ],
    ]);

    $socket = @stream_socket_client($connectHost . ':' . $port, $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $context);
    if (!$socket) {
        $logError('SMTP connect failed: ' . $errstr);
    } else {
        stream_set_timeout($socket, 20);

        $readResponse = function ($socket) {
            $buffer = '';
            while (true) {
                $line = fgets($socket, 515);
                if ($line === false) {
                    return trim($buffer);
                }

                $buffer .= $line;
                if (preg_match('/^\d{3}\s/', $line) === 1) {
                    return trim($buffer);
                }
            }
        };

        $sendCommand = function ($socket, $command) use ($readResponse) {
            $written = fwrite($socket, $command . "\r\n");
            if ($written === false) {
                return '';
            }

            return $readResponse($socket);
        };

        $response = $readResponse($socket);
        if ($response === '' || strpos($response, '220') !== 0) {
            $logError('SMTP greeting failed: ' . $response);
            fclose($socket);
            return false;
        }

        $ehlo = $sendCommand($socket, 'EHLO localhost');
        if (strpos($ehlo, '250') !== 0) {
            $logError('SMTP EHLO failed: ' . $ehlo);
            fclose($socket);
            return false;
        }

        if ($port === 587 && $secure !== 'ssl') {
            $starttls = $sendCommand($socket, 'STARTTLS');
            if (strpos($starttls, '220') !== 0) {
                $logError('SMTP STARTTLS failed: ' . $starttls);
                fclose($socket);
                return false;
            }
            $crypto = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if ($crypto !== true) {
                $logError('SMTP crypto handshake failed');
                fclose($socket);
                return false;
            }
            $sendCommand($socket, 'EHLO localhost');
        }

        $authResponse = $sendCommand($socket, 'AUTH LOGIN');
        if (strpos($authResponse, '334') === 0) {
            $userResponse = $sendCommand($socket, base64_encode($username));
            if (strpos($userResponse, '334') !== 0) {
                $logError('SMTP username challenge failed: ' . $userResponse);
                fclose($socket);
                return false;
            }

            $passResponse = $sendCommand($socket, base64_encode($password));
            if (strpos($passResponse, '235') !== 0) {
                $logError('SMTP password challenge failed: ' . $passResponse);
                fclose($socket);
                return false;
            }
        } else {
            $authPlain = base64_encode("\0" . $username . "\0" . $password);
            $plainResponse = $sendCommand($socket, 'AUTH PLAIN ' . $authPlain);
            if (strpos($plainResponse, '235') !== 0) {
                $logError('SMTP AUTH PLAIN failed: ' . $plainResponse);
                fclose($socket);
                return false;
            }
        }

        $mailFromResponse = $sendCommand($socket, 'MAIL FROM:<'.$from.'>');
        if (strpos($mailFromResponse, '250') !== 0) {
            $logError('SMTP MAIL FROM failed: ' . $mailFromResponse);
            fclose($socket);
            return false;
        }

        $rcptResponse = $sendCommand($socket, 'RCPT TO:<'.$to.'>');
        if (strpos($rcptResponse, '250') !== 0 && strpos($rcptResponse, '251') !== 0) {
            $logError('SMTP RCPT TO failed: ' . $rcptResponse);
            fclose($socket);
            return false;
        }

        $dataResponse = $sendCommand($socket, 'DATA');
        if (strpos($dataResponse, '354') !== 0) {
            $logError('SMTP DATA failed: ' . $dataResponse);
            fclose($socket);
            return false;
        }

        $boundary = '----=_Part_' . md5(uniqid('', true));
        $encodedFromName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
        $message = "From: {$encodedFromName} <{$from}>\r\n";
        $message .= "To: {$to}\r\n";
        $message .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $message .= "MIME-Version: 1.0\r\n";
        $message .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
        $message .= "\r\n";
        $message .= "--{$boundary}\r\n";
        $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $message .= ($altBody !== '' ? $altBody : strip_tags($htmlBody)) . "\r\n\r\n";
        $message .= "--{$boundary}\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $message .= $htmlBody . "\r\n\r\n";

        foreach ($inlineImages as $cid => $imagePath) {
            if (!is_string($imagePath) || $imagePath === '' || !is_file($imagePath)) {
                continue;
            }

            $imageMime = mime_content_type($imagePath) ?: 'image/png';
            $imageData = file_get_contents($imagePath);
            $message .= "--{$boundary}\r\n";
            $message .= "Content-Type: {$imageMime}; name=\"" . basename($imagePath) . "\"\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n";
            $message .= "Content-ID: <{$cid}>\r\n";
            $message .= "Content-Disposition: inline; filename=\"" . basename($imagePath) . "\"\r\n\r\n";
            $message .= chunk_split(base64_encode($imageData), 76, "\r\n") . "\r\n";
        }

        $message .= "--{$boundary}--\r\n";
        $message .= ".\r\n";

        fwrite($socket, $message);
        $finishResponse = $readResponse($socket);
        fclose($socket);

        if (strpos($finishResponse, '250') === 0) {
            return true;
        }

        $logError('SMTP DATA final response failed: ' . $finishResponse);
    }

    $headers = [];
    $headers[] = 'From: ' . $fromName . ' <' . $from . '>';
    $headers[] = 'Reply-To: ' . $from;
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = 'X-Mailer: PHP/' . phpversion();

    $subjectEncoded = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $mailResult = @mail($to, $subjectEncoded, $htmlBody, implode("\r\n", $headers), '-f' . $from);
    if ($mailResult) {
        return true;
    }

    $logError('Fallback mail() failed for ' . $to);
    return false;
}

function sendRegistrationEmail(string $to, string $name, string $type, bool $isApproval = false): bool {
    $normalizedType = normalizeRegistrationType($type);
    $displayType = $normalizedType === 'anfitriao' ? 'Anfitrião' : 'Peregrino';
    $subject = $isApproval
        ? ($normalizedType === 'anfitriao' ? 'Anfitrião Parabens Pagamento Aprovado' : 'Peregrino Parabens Pagamento Aprovado')
        : ($normalizedType === 'anfitriao' ? 'Bem vindo Anfitrião - Cadastro @alemdoespelhosp' : 'Bem vindo Peregrino - Cadastro @alemdoespelhosp');

    $logoPath = __DIR__ . '/public/Logosemfundo.png';
    $approvalImagePath = __DIR__ . '/public/aprovado.png';
    $registrationImagePath = __DIR__ . '/public/email criado.png';
    $footerImagePath = $isApproval ? $approvalImagePath : $registrationImagePath;
    $title = $isApproval ? 'SEU PAGAMENTO FOI APROVADO!' : 'SEU CADASTRO FOI REALIZADO!';
    $bodyText = $isApproval
        ? 'Seu pagamento foi aprovado e você já pode participar do evento com tranquilidade.'
        : 'Obrigado por se inscrever. Estamos ansiosos para recebê-lo(a) no evento.';

    $templatePath = $isApproval ? __DIR__ . '/email/Aprovado.html' : __DIR__ . '/email/cadastro.html';
    $templateHtml = '';

    if (is_file($templatePath)) {
        $templateHtml = file_get_contents($templatePath);
        if ($templateHtml !== false) {
            $templateHtml = str_replace('{{NOME}}', htmlspecialchars($name, ENT_QUOTES, 'UTF-8'), $templateHtml);
            $templateHtml = str_replace('{{TITULO}}', htmlspecialchars($title, ENT_QUOTES, 'UTF-8'), $templateHtml);
            $templateHtml = str_replace('{{SUBTITULO}}', htmlspecialchars($bodyText, ENT_QUOTES, 'UTF-8'), $templateHtml);
            $templateHtml = str_replace('{{LINHA2}}', $isApproval ? 'ESPERAMOS VOCÊ NO DIA' : 'SEU CADASTRO ESTÁ PRONTO', $templateHtml);
            $templateHtml = str_replace('{{DATA}}', $isApproval ? '7 DE AGOSTO!' : 'AGUARDE OS DETALHES!', $templateHtml);
        } else {
            $templateHtml = '';
        }
    }

    $htmlBody = $templateHtml !== ''
        ? $templateHtml
        : '<!DOCTYPE html><html><body style="margin:0;padding:0;background:#111827;font-family:Arial,sans-serif;color:#fef3c7;"><div style="max-width:680px;margin:0 auto;padding:24px;background:linear-gradient(135deg,#1f2937,#111827);border-radius:16px;overflow:hidden;"><div style="text-align:center;padding:20px 0 8px;"><img src="cid:email-image" alt="Imagem do e-mail" style="max-width:100%;border-radius:12px;display:block;margin:0 auto;" /></div><div style="padding:24px 20px 12px;"><h1 style="margin:0 0 12px;font-size:28px;color:#fbbf24;text-align:center;">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1><p style="margin:0 0 10px;font-size:16px;line-height:1.6;color:#fef3c7;">Olá, ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '!</p><p style="margin:0 0 10px;font-size:16px;line-height:1.6;color:#fef3c7;">' . htmlspecialchars($bodyText, ENT_QUOTES, 'UTF-8') . '</p><p style="margin:0 0 10px;font-size:16px;line-height:1.6;color:#fef3c7;">Tipo de inscrição: ' . htmlspecialchars($displayType, ENT_QUOTES, 'UTF-8') . '</p><p style="margin:12px 0 0;font-size:14px;color:#d1d5db;">Atenciosamente,<br/>Equipe @alemdoespelhosp</p></div></div></body></html>';

    $altBody = 'Olá, ' . $name . '! ' . $bodyText . ' Tipo de inscrição: ' . $displayType;

    $inlineImages = [
        'logo-image' => $logoPath,
        'approval-image' => $footerImagePath,
    ];

    return sendEmailMessage($to, $subject, $htmlBody, $altBody, $inlineImages);
}

function ensureRegistrationSchema($mysqli) {
    $tables = [
        'edicoes' => [
            ['hora_inicio', 'TIME NULL'],
            ['hora_fim', 'TIME NULL'],
            ['hora_inscricao_inicio', 'TIME NULL'],
            ['hora_inscricao_fim', 'TIME NULL'],
        ],
        'peregrinos' => [
            ['endereco', 'VARCHAR(255) NULL'],
            ['problema_saude', 'VARCHAR(10) NULL'],
            ['problema_saude_descricao', 'TEXT NULL'],
            ['usa_remedio', 'VARCHAR(10) NULL'],
            ['remedio_descricao', 'TEXT NULL'],
            ['pagbank_reference_id', 'VARCHAR(100) NULL'],
            ['pagbank_checkout_id', 'VARCHAR(100) NULL'],
            ['pagbank_status', 'VARCHAR(50) NULL'],
            ['pagbank_last_event', 'VARCHAR(100) NULL'],
            ['pagbank_payment_id', 'VARCHAR(100) NULL'],
            ['pagbank_payload', 'TEXT NULL'],
        ],
        'anfitrioes' => [
            ['endereco', 'VARCHAR(255) NULL'],
            ['problema_saude', 'VARCHAR(10) NULL'],
            ['problema_saude_descricao', 'TEXT NULL'],
            ['usa_remedio', 'VARCHAR(10) NULL'],
            ['remedio_descricao', 'TEXT NULL'],
            ['pagbank_reference_id', 'VARCHAR(100) NULL'],
            ['pagbank_checkout_id', 'VARCHAR(100) NULL'],
            ['pagbank_status', 'VARCHAR(50) NULL'],
            ['pagbank_last_event', 'VARCHAR(100) NULL'],
            ['pagbank_payment_id', 'VARCHAR(100) NULL'],
            ['pagbank_payload', 'TEXT NULL'],
        ],
    ];

    foreach ($tables as $table => $columns) {
        foreach ($columns as [$column, $definition]) {
            $check = $mysqli->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
            if ($check && $check->num_rows === 0) {
                $mysqli->query("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
            }
        }
    }

    $mysqli->query("ALTER TABLE `peregrinos` MODIFY `payment_method` ENUM('pix','cartao','pagbank') NOT NULL DEFAULT 'pagbank'");
    $mysqli->query("ALTER TABLE `anfitrioes` MODIFY `payment_method` ENUM('pix','cartao','pagbank') NOT NULL DEFAULT 'pagbank'");
}

ensureRegistrationSchema($mysqli);

function getPagbankApiBaseUrl() {
    global $PAGBANK_ENV;
    if ($PAGBANK_ENV === 'production') {
        return 'https://api.pagseguro.com';
    }
    if ($PAGBANK_ENV === 'sandbox') {
        return 'https://sandbox.api.pagseguro.com';
    }

    throw new RuntimeException('PAGBANK_ENV inválido. Use sandbox ou production.');
}

function pagbankApiRequest($path, $payload = null, $method = 'POST') {
    global $PAGBANK_ACCESS_TOKEN;

    $token = trim((string) $PAGBANK_ACCESS_TOKEN);
    if ($token === '') {
        return [
            'ok' => false,
            'message' => 'Token do PagBank não configurado. Defina a variável de ambiente PAGBANK_ACCESS_TOKEN com um token válido do ambiente correto (sandbox ou production).',
        ];
    }

    $authorizationHeader = stripos($token, 'Bearer ') === 0 ? $token : 'Bearer ' . $token;

    $url = getPagbankApiBaseUrl() . $path;
    $headers = [
        'Authorization: ' . $authorizationHeader,
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if ($payload !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['ok' => false, 'message' => $error];
    }

    $decoded = json_decode($response, true);
    if ($httpCode === 401 || $httpCode === 403) {
        return [
            'ok' => false,
            'message' => 'Credencial inválida do PagBank. Gere um novo access token via Connect e confirme se o header Authorization está no formato Bearer <token>.',
            'http_code' => $httpCode,
            'body' => $decoded,
            'raw' => $response,
        ];
    }

    return [
        'ok' => $httpCode >= 200 && $httpCode < 300,
        'http_code' => $httpCode,
        'body' => $decoded,
        'raw' => $response,
    ];
}

function createPagbankCheckout($registrationId, $registrationType, $customerName, $customerEmail, $amountCents, $redirectUrl = null, $webhookUrl = null) {
    global $PAGBANK_REDIRECT_URL, $PAGBANK_WEBHOOK_URL, $PAGBANK_DEFAULT_EMAIL;

    $referenceId = 'inscricao-' . $registrationType . '-' . $registrationId;
    $redirectUrl = trim((string) ($redirectUrl ?: $PAGBANK_REDIRECT_URL));
    $webhookUrl = trim((string) ($webhookUrl ?: $PAGBANK_WEBHOOK_URL));
    $customerEmail = trim((string) ($customerEmail ?: $PAGBANK_DEFAULT_EMAIL));

    if ($redirectUrl === '') {
        return ['success' => false, 'message' => 'PAGBANK_REDIRECT_URL não configurado'];
    }
    if ($webhookUrl === '') {
        return ['success' => false, 'message' => 'PAGBANK_WEBHOOK_URL não configurado'];
    }
    if ($customerEmail === '') {
        return ['success' => false, 'message' => 'PAGBANK_DEFAULT_EMAIL não configurado'];
    }

    $payload = [
        'reference_id' => $referenceId,
        'customer' => [
            'name' => $customerName,
            'email' => $customerEmail,
        ],
        'items' => [[
            'reference_id' => $referenceId,
            'name' => 'Inscrição ' . ucfirst($registrationType),
            'quantity' => 1,
            'unit_amount' => $amountCents,
        ]],
        'amount' => [
            'value' => $amountCents,
            'currency' => 'BRL',
        ],
        'redirect_url' => $redirectUrl,
        'return_url' => $redirectUrl,
        'notification_urls' => array_filter([$webhookUrl]),
    ];

    $result = pagbankApiRequest('/checkouts', $payload, 'POST');
    if (!$result['ok']) {
        return ['success' => false, 'message' => $result['message'] ?? 'Erro ao criar checkout'];
    }

    $body = $result['body'] ?? [];
    $checkoutUrl = extractPagbankCheckoutUrl($body);

    return [
        'success' => true,
        'checkout_id' => $body['id'] ?? null,
        'reference_id' => $referenceId,
        'checkout_url' => $checkoutUrl,
        'body' => $body,
    ];
}

function savePagbankCheckoutData($mysqli, $table, $id, $checkoutData) {
    if (empty($checkoutData['checkout_id']) && empty($checkoutData['reference_id']) && empty($checkoutData['checkout_url'])) {
        return false;
    }

    $stmt = $mysqli->prepare("UPDATE `$table` SET pagbank_reference_id = ?, pagbank_checkout_id = ?, pagbank_status = ?, pagbank_payload = ? WHERE id = ?");
    if (!$stmt) {
        return false;
    }

    $payload = json_encode($checkoutData['body'] ?? []);
    $status = $checkoutData['success'] ? 'created' : 'error';
    $stmt->bind_param('ssssi', $checkoutData['reference_id'], $checkoutData['checkout_id'], $status, $payload, $id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function updateRegistrationPaymentStatus($mysqli, $table, $id, $status, $event = null, $paymentId = null, $payload = null) {
    try {
        $stmt = $mysqli->prepare("UPDATE `$table` SET payment_status = ?, pagbank_status = ?, pagbank_last_event = ?, pagbank_payment_id = ?, pagbank_payload = ? WHERE id = ?");
        if (!$stmt) {
            error_log('updateRegistrationPaymentStatus prepare failed: ' . $mysqli->error);
            return false;
        }

        $payloadJson = is_array($payload) ? json_encode($payload) : $payload;
        $stmt->bind_param('sssssi', $status, $status, $event, $paymentId, $payloadJson, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    } catch (Throwable $e) {
        error_log('updateRegistrationPaymentStatus exception: ' . $e->getMessage());
        return false;
    }
}

function normalizePhoneForLookup($value) {
    return preg_replace('/\D+/', '', (string) $value);
}

function extractPagbankCheckoutUrl($body) {
    if (!is_array($body)) {
        return null;
    }

    // Prefer the user-facing payment URL (rel=PAY) when available.
    if (!empty($body['links']) && is_array($body['links'])) {
        // First pass: look for PAY
        foreach ($body['links'] as $link) {
            if (is_array($link) && isset($link['rel']) && strtoupper($link['rel']) === 'PAY') {
                return $link['href'] ?? null;
            }
        }

        // Second pass: look for explicit payment_url or checkout_url links
        foreach ($body['links'] as $link) {
            if (is_array($link) && isset($link['rel'])) {
                $rel = strtoupper($link['rel']);
                if ($rel === 'PAYMENT' || $rel === 'CHECKOUT' || $rel === 'SELF') {
                    return $link['href'] ?? null;
                }
            }
        }
    }

    if (!empty($body['payment_url'])) {
        return $body['payment_url'];
    }

    if (!empty($body['checkout_url'])) {
        return $body['checkout_url'];
    }

    return null;
}

function extractPagbankStatusValue(array $body): string {
    $candidates = [
        $body['status'] ?? null,
        $body['payment_status'] ?? null,
        $body['payment_response']['status'] ?? null,
        $body['data']['status'] ?? null,
        $body['data']['object']['status'] ?? null,
        $body['data']['charges'][0]['status'] ?? null,
        $body['data']['payments'][0]['status'] ?? null,
        $body['data']['object']['charges'][0]['status'] ?? null,
    ];

    foreach ($candidates as $value) {
        if (!empty($value)) {
            return strtolower((string) $value);
        }
    }
    return '';
}

function extractPagbankPaymentId(array $body): ?string {
    $candidates = [
        $body['payment_id'] ?? null,
        $body['id'] ?? null,
        $body['data']['payment_id'] ?? null,
        $body['data']['id'] ?? null,
        $body['data']['object']['id'] ?? null,
        $body['data']['charges'][0]['id'] ?? null,
        $body['data']['payments'][0]['id'] ?? null,
        $body['data']['object']['charges'][0]['id'] ?? null,
    ];

    foreach ($candidates as $value) {
        if (!empty($value)) {
            return (string) $value;
        }
    }
    return null;
}

function mapPagbankStatusToRegistrationStatus($body) {
    $rawStatus = '';
    if (is_array($body)) {
        $rawStatus = extractPagbankStatusValue($body);
    }

    $status = strtolower(trim($rawStatus));
    if (in_array($status, ['paid', 'approved', 'authorized', 'confirmed', 'settled', 'success', 'succeeded'], true)) {
        return 'confirmado';
    }

    if (in_array($status, ['canceled', 'cancelled', 'expired', 'failed', 'declined', 'rejected'], true)) {
        return 'cancelado';
    }

    if (in_array($status, ['active', 'created', 'pending', 'processing', 'in_progress', 'inreview', 'waiting'], true)) {
        return 'pendente';
    }

    return 'pendente';
}

function refreshPagbankRegistrationStatus($mysqli, $table, $id, $checkoutId = null) {
    if (empty($checkoutId)) {
        return ['ok' => false, 'message' => 'Checkout do PagBank não encontrado.'];
    }

    $result = pagbankApiRequest('/checkouts/' . rawurlencode($checkoutId), null, 'GET');
    if (!$result['ok']) {
        return [
            'ok' => false,
            'message' => $result['message'] ?? 'Não foi possível consultar o status no PagBank.',
            'http_code' => $result['http_code'] ?? null,
        ];
    }

    $body = $result['body'] ?? [];
    $pagbankStatus = $body['status'] ?? $body['payment_status'] ?? null;
    $paymentId = $body['payment_response']['id'] ?? $body['payment_id'] ?? null;
    $mappedStatus = mapPagbankStatusToRegistrationStatus($body);
    $checkoutUrl = extractPagbankCheckoutUrl($body);

    updateRegistrationPaymentStatus($mysqli, $table, $id, $mappedStatus, $pagbankStatus, $paymentId, $body);

    return [
        'ok' => true,
        'status' => $mappedStatus,
        'pagbank_status' => $pagbankStatus,
        'payment_id' => $paymentId,
        'checkout_url' => $checkoutUrl,
        'body' => $body,
    ];
}

function activatePagbankCheckout($checkoutId) {
    if (empty($checkoutId)) {
        return [
            'ok' => false,
            'message' => 'Checkout do PagBank não informado.',
        ];
    }

    $result = pagbankApiRequest('/checkouts/' . rawurlencode($checkoutId) . '/activate', null, 'POST');
    if (!$result['ok']) {
        return [
            'ok' => false,
            'message' => $result['message'] ?? 'Não foi possível ativar o checkout no PagBank.',
            'http_code' => $result['http_code'] ?? null,
            'body' => $result['body'] ?? null,
        ];
    }

    return [
        'ok' => true,
        'body' => $result['body'] ?? [],
        'http_code' => $result['http_code'] ?? null,
    ];
}

// === Função de versioning para assets (CSS/JS) ===
function assetVersion($file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $mtime = filemtime($path);
        return $file . '?v=' . date('YmdHis', $mtime);
    }
    return $file . '?v=' . time();
}

// Constantes de sessão
define('SESSION_ADMIN_AUTH', 'admin_authenticated');

// Função: Calcular centavos para PIX baseado no ID (para identificação)
function calculatePixCents($id) {
    return ($id % 99) + 1; // Resultado: 1 a 99 centavos
}

// Função: Calcular valor do PIX com centavos
function calculatePixAmount($id) {
    $cents = calculatePixCents($id);
    return 150.00 + ($cents / 100);
}

// Função: Formatar valor para exibição
function formatPrice($amount) {
    return 'R$ ' . number_format($amount, 2, ',', '.');
}

// Função: Formatar data em português (DD de MÊÊS de YYYY)
function formatDatePT($date_string, $format = 'completo') {
    if (!$date_string) return 'Data não definida';
    
    $meses = [
        1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
        5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
        9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro'
    ];
    
    $timestamp = strtotime($date_string);
    if ($timestamp === false) return 'Data inválida';
    
    $dia = (int)date('d', $timestamp);
    $mes = (int)date('m', $timestamp);
    $ano = date('Y', $timestamp);
    
    if ($format === 'completo') {
        return $dia . ' de ' . $meses[$mes] . ' de ' . $ano;
    } elseif ($format === 'curto') {
        return $dia . '/' . str_pad($mes, 2, '0', STR_PAD_LEFT) . '/' . $ano;
    }
    
    return $dia . ' de ' . $meses[$mes];
}

// Função: Redirecionar com mensagem
function redirect($url, $message = '', $status = 'info') {
    $query = [];
    if ($message !== '') {
        $query['message'] = urlencode($message);
        $query['status'] = urlencode($status);
    }
    $separator = (strpos($url, '?') === false) ? '?' : '&';
    $redirect_url = $url . ($query ? $separator . http_build_query($query) : '');
    header('Location: ' . $redirect_url);
    exit;
}

// Função: Autenticação de admin (tabela `admins` apenas)
function authenticateAdmin($user, $pass) {
    global $mysqli;
    if (!$user || !$pass) return false;

    $stmt = $mysqli->prepare("SELECT id, password_hash FROM admins WHERE username = ? LIMIT 1");
    if (!$stmt) return false;
    $stmt->bind_param('s', $user);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    
    if ($row && !empty($row['password_hash'])) {
        return password_verify($pass, $row['password_hash']);
    }
    return false;
}

// Função: Verificar autenticação de admin
function isAdminAuthenticated() {
    return isset($_SESSION[SESSION_ADMIN_AUTH]) && $_SESSION[SESSION_ADMIN_AUTH] === true && !empty($_SESSION['admin_username']);
}

// Retorna admin id pela username (ou null)
function getAdminIdByUsername($username) {
    global $mysqli;
    if (empty($username)) return null;
    $stmt = $mysqli->prepare("SELECT id FROM admins WHERE username = ? LIMIT 1");
    if (!$stmt) return null;
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return $row['id'] ?? null;
}

// Grava ação administrativa em admin_actions
function logAdminAction($admin_id, $action_type, $target_table = null, $target_id = null, $notes = null) {
    global $mysqli;
    $stmt = $mysqli->prepare("INSERT INTO admin_actions (admin_id, action_type, target_table, target_id, notes) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) return false;
    $stmt->bind_param('issis', $admin_id, $action_type, $target_table, $target_id, $notes);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

// Cria novo admin
function createAdmin($username, $password, $email = null, $role = 'super') {
    global $mysqli;
    if (empty($username) || empty($password)) return false;
    if (strlen($password) < 6) return false; // Senha mínima 6 caracteres
    
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("INSERT INTO admins (username, password_hash, email, role) VALUES (?, ?, ?, ?)");
    if (!$stmt) return false;
    $stmt->bind_param('ssss', $username, $hash, $email, $role);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

// Deleta um admin
function deleteAdmin($admin_id) {
    global $mysqli;
    if (!$admin_id) return false;
    // Evitar deixar sem nenhum admin: verifica se há outro super
    $check = $mysqli->prepare("SELECT COUNT(*) as cnt FROM admins WHERE role = 'super' AND id != ?");
    if ($check) {
        $check->bind_param('i', $admin_id);
        $check->execute();
        $checkRes = $check->get_result();
        $checkRow = $checkRes->fetch_assoc();
        $check->close();
        if (($checkRow['cnt'] ?? 0) < 1) {
            return false; // Não deletar único super admin
        }
    }
    $stmt = $mysqli->prepare("DELETE FROM admins WHERE id = ?");
    if (!$stmt) return false;
    $stmt->bind_param('i', $admin_id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

// Obtém lista de todos os admins
function getAllAdmins() {
    global $mysqli;
    $result = $mysqli->query("SELECT id, username, email, role, created_at FROM admins ORDER BY created_at DESC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}
?>
