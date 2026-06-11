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

// Configuração do banco de dados
$DB_HOST = 'alemdoespelho.mysql.dbaas.com.br';
$DB_USER = 'alemdoespelho';
$DB_PASS = 'IPM@1347New';
$DB_NAME = 'alemdoespelho';

// Configuração de pagamento
$PIX_PHONE = '11993813374';
$PAYMENT_BASE = 150.00;
$PAYMENT_AMOUNT = 150.00;

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
