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
$DB_HOST = 'alemdoespelho.mysql.dbaas.com.br';
$DB_USER = 'alemdoespelho';
$DB_PASS = 'IPM@1347New';
$DB_NAME = 'alemdoespelho';

// Configuração de pagamento
$PIX_PHONE = '11993813374';
$PAYMENT_BASE = 150.00;
$PAYMENT_AMOUNT = 150.00;

// Configuração PagBank Checkout
$PAGBANK_ENV = getenv('PAGBANK_ENV') ?: 'sandbox';
$PAGBANK_ACCESS_TOKEN = getenv('PAGBANK_ACCESS_TOKEN') ?: '';
$PAGBANK_REDIRECT_URL = getenv('PAGBANK_REDIRECT_URL') ?: 'https://alemdoespelhosp.com.br/payment_return.php';
$PAGBANK_WEBHOOK_URL = getenv('PAGBANK_WEBHOOK_URL') ?: 'https://alemdoespelhosp.com.br/pagbank_webhook.php';
$PAGBANK_DEFAULT_EMAIL = getenv('PAGBANK_DEFAULT_EMAIL') ?: 'dkaortiz@gmail.com';

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
    return $PAGBANK_ENV === 'production' ? 'https://api.pagseguro.com' : 'https://sandbox.api.pagseguro.com';
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
    $payload = [
        'reference_id' => $referenceId,
        'customer' => [
            'name' => $customerName,
            'email' => !empty($customerEmail) ? $customerEmail : $PAGBANK_DEFAULT_EMAIL,
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
        'redirect_url' => $redirectUrl ?: $PAGBANK_REDIRECT_URL,
        'return_url' => $redirectUrl ?: $PAGBANK_REDIRECT_URL,
        'notification_urls' => array_filter([$webhookUrl ?: $PAGBANK_WEBHOOK_URL]),
    ];

    $result = pagbankApiRequest('/checkouts', $payload, 'POST');
    if (!$result['ok']) {
        return ['success' => false, 'message' => $result['message'] ?? 'Erro ao criar checkout'];
    }

    $body = $result['body'] ?? [];
    $checkoutUrl = null;

    if (!empty($body['links'])) {
        foreach ($body['links'] as $link) {
            if (($link['rel'] ?? '') === 'PAY' || ($link['rel'] ?? '') === 'SELF') {
                $checkoutUrl = $link['href'];
                break;
            }
        }
    }

    if (empty($checkoutUrl) && !empty($body['checkout_url'])) {
        $checkoutUrl = $body['checkout_url'];
    }

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
    $stmt = $mysqli->prepare("UPDATE `$table` SET payment_status = ?, pagbank_status = ?, pagbank_last_event = ?, pagbank_payment_id = ?, pagbank_payload = ? WHERE id = ?");
    if (!$stmt) {
        return false;
    }

    $payloadJson = is_array($payload) ? json_encode($payload) : $payload;
    $stmt->bind_param('sssssi', $status, $status, $event, $paymentId, $payloadJson, $id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function normalizePhoneForLookup($value) {
    return preg_replace('/\D+/', '', (string) $value);
}

function extractPagbankCheckoutUrl($body) {
    if (!is_array($body)) {
        return null;
    }

    if (!empty($body['links']) && is_array($body['links'])) {
        foreach ($body['links'] as $link) {
            if (is_array($link) && in_array(($link['rel'] ?? ''), ['PAY', 'SELF', 'CHECKOUT'], true)) {
                return $link['href'] ?? null;
            }
        }
    }

    if (!empty($body['checkout_url'])) {
        return $body['checkout_url'];
    }

    if (!empty($body['payment_url'])) {
        return $body['payment_url'];
    }

    return null;
}

function mapPagbankStatusToRegistrationStatus($body) {
    $rawStatus = '';
    if (is_array($body)) {
        $rawStatus = (string) ($body['status'] ?? $body['payment_status'] ?? $body['payment_response']['status'] ?? '');
    }

    $status = strtolower($rawStatus);
    if (in_array($status, ['paid', 'approved', 'authorized', 'confirmed', 'settled', 'success', 'succeeded'], true)) {
        return 'confirmado';
    }

    if (in_array($status, ['canceled', 'cancelled', 'expired', 'failed', 'declined', 'rejected'], true)) {
        return 'cancelado';
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
