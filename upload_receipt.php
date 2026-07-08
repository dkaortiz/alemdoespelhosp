<?php
require_once 'config.php';
session_start();

header('Content-Type: application/json');

// Validar que é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Validar se tem arquivo
if (empty($_FILES['receipt_file'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nenhum arquivo foi selecionado']);
    exit;
}

$user_id = (int)($_POST['user_id'] ?? 0);
$user_type = trim($_POST['user_type'] ?? '');
$file = $_FILES['receipt_file'];

// Validações básicas
if ($user_id <= 0 || !in_array($user_type, ['peregrinos', 'anfitrioes'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados de usuário inválidos']);
    exit;
}

// Validar erro de upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'Arquivo excede o tamanho máximo do servidor',
        UPLOAD_ERR_FORM_SIZE => 'Arquivo excede 5MB',
        UPLOAD_ERR_PARTIAL => 'Upload foi interrompido',
        UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi selecionado',
        UPLOAD_ERR_NO_TMP_DIR => 'Diretório temporário ausente',
        UPLOAD_ERR_CANT_WRITE => 'Não foi possível salvar o arquivo',
        UPLOAD_ERR_EXTENSION => 'Extensão de arquivo não permitida'
    ];
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $errors[$file['error']] ?? 'Erro no upload']);
    exit;
}

// Validar tamanho (5MB)
$maxSize = 5 * 1024 * 1024; // 5MB
if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Arquivo excede o tamanho máximo de 5MB']);
    exit;
}

// Validar tipo de arquivo
$allowedMimes = ['image/jpeg', 'image/png', 'application/pdf'];
$mimeType = mime_content_type($file['tmp_name']);
if (!in_array($mimeType, $allowedMimes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido. Use JPG, PNG ou PDF']);
    exit;
}

// Validar extensão
$filename = basename($file['name']);
$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
$allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
if (!in_array($extension, $allowedExtensions)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Extensão de arquivo não permitida']);
    exit;
}

// Criar pasta de uploads se não existir
// Tentar usar CPF do usuário como pasta; se não houver, usar fallback por user_type_userid
$cpfFolder = '';
$check = $mysqli->query("SHOW COLUMNS FROM `" . $mysqli->real_escape_string($user_type) . "` LIKE 'cpf'");
if ($check && $check->num_rows > 0) {
    $stmtC = $mysqli->prepare("SELECT cpf FROM `" . $mysqli->real_escape_string($user_type) . "` WHERE id = ? LIMIT 1");
    if ($stmtC) {
        $stmtC->bind_param('i', $user_id);
        $stmtC->execute();
        $resC = $stmtC->get_result();
        $rowC = $resC->fetch_assoc();
        $stmtC->close();
        if (!empty($rowC['cpf'])) {
            $cpfDigits = preg_replace('/\D+/', '', $rowC['cpf']);
            if ($cpfDigits !== '') {
                $cpfFolder = $cpfDigits;
            }
        }
    }
}

if ($cpfFolder === '') {
    $cpfFolder = $user_type . '_' . $user_id;
}

$uploadDir = __DIR__ . '/uploads/receipts/' . $cpfFolder;
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao criar diretório de uploads']);
        exit;
    }
}

// Gerar nome único para o arquivo
$timestamp = date('Y-m-d-H-i-s');
$randomId = substr(md5(uniqid()), 0, 8);
$newFilename = "{$timestamp}_{$randomId}.{$extension}";
$uploadPath = $uploadDir . '/' . $newFilename;

// Mover arquivo
if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar o arquivo']);
    exit;
}

// Atualizar banco de dados
$relativePath = 'uploads/receipts/' . $cpfFolder . '/' . $newFilename;
$stmt = $mysqli->prepare("
    UPDATE `$user_type` 
    SET payment_receipt = ?, payment_status = 'comprovante_enviado' 
    WHERE id = ?
");

if (!$stmt) {
    // Deletar arquivo se falhar a query
    unlink($uploadPath);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar banco de dados']);
    exit;
}

$stmt->bind_param('si', $relativePath, $user_id);
if (!$stmt->execute()) {
    // Deletar arquivo se falhar a query
    unlink($uploadPath);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar banco de dados']);
    exit;
}
$stmt->close();

// Atualizar sessão
if (!empty($_SESSION['user_access'])) {
    $_SESSION['user_access']['payment_status'] = 'comprovante_enviado';
    $_SESSION['user_access']['payment_receipt'] = $relativePath;
}

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Comprovante enviado com sucesso! Aguardando validação do administrador.',
    'receipt_path' => $relativePath
]);
exit;
